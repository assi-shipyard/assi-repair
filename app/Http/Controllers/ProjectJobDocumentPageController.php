<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectDocumentJobPhoto;
use App\Models\ProjectJobDocument;
use App\Services\ProjectDocumentComparisonService;
use App\Services\ProjectJobDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProjectJobDocumentPageController extends Controller
{
    // Forward declare the service in the constructor
    public function __construct(private ProjectJobDocumentService $service, private ProjectDocumentComparisonService $comparison) {}

    public function index(string $projectId): View|RedirectResponse
    {
        // Find the project by its unique ID
        $project = $this->findProject($projectId);

        // If the project is not found, redirect to the project index with an error message
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        // Retrieve all job documents for the project, including a count of jobs for each document
        $documents = ProjectJobDocument::query()
            ->withCount('jobs')
            ->where('project_id', $project->id)
            ->orderBy('document_type')
            ->orderByDesc('revision_no')
            ->get();

        // Group the documents by their type and build the SOP state
        $documentsByType = $documents->groupBy('document_type');
        $sop = $this->buildSopState($documentsByType);

        return view('project.job-document.workflow', [
            'project' => $project,
            'documents' => $documents,
            'documentsByType' => $documentsByType,
            'documentTypeLabels' => $this->documentTypeLabels(),
            'sop' => $sop,
        ]);
    }

    // Show the details of a specific job document
    public function show(string $projectId, string $documentId): View|RedirectResponse
    {
        // Find the project by its unique ID
        $project = $this->findProject($projectId);

        // If the project is not found, redirect to the project index with an error message
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        // Find the specific job document by its ID and project ID
        $document = $this->find_document_by_public_id($project->id, $documentId);

        // If the document is not found, redirect to the workflow page with an error message
        if (! $document) {
            return redirect()->route('project.job-document.workflow.index', $project->unique_id)
                ->with('error', 'Dokumen pekerjaan tidak ditemukan.');
        }

        // Load the related jobs, materials, photos, and histories for the document
        $document->load(['jobs.materials', 'jobs.photos', 'prepared_by', 'approved_by']);

        $revisions = ProjectJobDocument::query()
            ->withCount('jobs')
            ->where('project_id', $project->id)
            ->where('document_type', $document->document_type)
            ->orderByDesc('revision_no')
            ->get();

        $previous = $revisions->where('revision_no', '<', $document->revision_no)->first();
        $comparison = $previous ? $this->comparison->compare($previous->load('jobs.materials'), $document) : null;

        $histories = $document->histories()->latest('id')->limit(100)->get();
        $actor_names = $this->actor_names($histories->pluck('actor_id'));

        return view($this->documentView($document), [
            'project' => $project,
            'document' => $document,
            'documentTypeLabels' => $this->documentTypeLabels(),
            'revisions' => $revisions,
            'previous' => $previous,
            'comparison' => $comparison,
            'totals' => $this->comparison->document_totals($document),
            'histories' => $histories,
            'actor_names' => $actor_names,
            'can_approve' => $this->can_approve_document($document),
            'is_latest' => $revisions->first()?->id === $document->id,
        ]);
    }

    // The following methods handle the creation, updating, and deletion of jobs, materials, and photos associated with a job document. They also include validation and error handling to ensure that the operations are performed correctly and that the user is informed of any issues.
    public function storeJob(Request $request, string $projectId, string $documentId): RedirectResponse
    {
        // Find the document for mutation (creation of a new job)
        $document = $this->findDocumentForMutation($projectId, $documentId);
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        // Validate the incoming request data for creating a new job
        $validator = Validator::make($request->all(), $this->jobRules(), $this->jobMessages());

        // If validation fails, redirect back with errors and input data
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get the validated data and determine the next sort order for the new job
        $data = $validator->validated();
        $data['sort_order'] = ((int) ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->max('sort_order')) + 1;

        // Create the new job using the service and associate it with the document
        $this->service->createJob($document, $data, auth()->id());

        return redirect()->route('project.job-document.workflow.show', [$projectId, $document->unique_id ?? $document->id])
            ->with('success', 'Pekerjaan berhasil ditambahkan.');
    }

    // The following methods handle updating and deleting jobs, as well as managing materials and photos associated with a job. They include validation, error handling, and appropriate redirects to ensure a smooth user experience.
    public function updateJob(Request $request, string $projectId, string $documentId, string $jobId): RedirectResponse
    {
        // Find the document for mutation (updating an existing job)
        $document = $this->findDocumentForMutation($projectId, $documentId);

        // If the document is not found or cannot be mutated, redirect back with an error message
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        // Find the specific job to be updated within the document
        $job = $this->findJob($document->id, $jobId);

        // If the job is not found, redirect back with an error message
        if (! $job) {
            return back()->with('error', 'Pekerjaan tidak ditemukan.');
        }

        // Validate the incoming request data for updating the job
        $validator = Validator::make($request->all(), $this->jobRules(), $this->jobMessages());

        // If validation fails, redirect back with errors and input data
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update the job using the service with the validated data and the authenticated user's ID
        $this->service->updateJob($job, $validator->validated(), auth()->id());

        return redirect()->route('project.job-document.workflow.show', [$projectId, $document->unique_id ?? $document->id])
            ->with('success', 'Pekerjaan berhasil diperbarui.');
    }

    // The following methods handle the deletion of jobs, as well as the management of materials and photos associated with a job. They include validation, error handling, and appropriate redirects to ensure a smooth user experience.
    public function destroyJob(string $projectId, string $documentId, string $jobId): RedirectResponse
    {
        // Find the document for mutation (deleting an existing job)
        $document = $this->findDocumentForMutation($projectId, $documentId);

        // If the document is not found or cannot be mutated, redirect back with an error message
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        // Find the specific job to be deleted within the document
        $job = $this->findJob($document->id, $jobId);

        // If the job is not found, redirect back with an error message
        if (! $job) {
            return back()->with('error', 'Pekerjaan tidak ditemukan.');
        }

        // Delete the job using the service with the authenticated user's ID
        $this->service->deleteJob($job, auth()->id());

        return redirect()->route('project.job-document.workflow.show', [$projectId, $document->unique_id ?? $document->id])
            ->with('success', 'Pekerjaan berhasil dihapus.');
    }

    // The following methods handle the creation, updating, and deletion of materials and photos associated with a job document. They include validation, error handling, and appropriate redirects to ensure a smooth user experience.
    public function storeMaterial(Request $request, string $projectId, string $documentId, string $jobId): RedirectResponse
    {
        // Find the document for mutation (adding a new material to a job)
        $document = $this->findDocumentForMutation($projectId, $documentId);

        // If the document is not found or cannot be mutated, redirect back with an error message
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        // Find the specific job to which the material will be added within the document
        $job = $this->findJob($document->id, $jobId);

        // If the job is not found, redirect back with an error message
        if (! $job) {
            return back()->with('error', 'Pekerjaan tidak ditemukan.');
        }

        // Validate the incoming request data for creating a new material
        $validator = Validator::make($request->all(), [
            'material_name' => 'required|string|max:191',
            'volume' => 'nullable|numeric|min:0',
            'density' => 'nullable|numeric|min:0',
            'dimension' => 'nullable|string|max:191',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'diameter' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'thickness' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'material_name.required' => 'Nama material wajib diisi.',
            'volume.numeric' => 'Volume harus berupa angka.',
            'density.numeric' => 'Kepadatan material harus berupa angka.',
            'price.numeric' => 'Harga harus berupa angka.',
            'currency.size' => 'Mata uang harus terdiri dari 3 karakter.',
            'diameter.numeric' => 'Diameter harus berupa angka.',
            'length.numeric' => 'Panjang harus berupa angka.',
            'thickness.numeric' => 'Ketebalan harus berupa angka.',
        ]);

        // If validation fails, redirect back with errors and input data
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create the new material using the service and associate it with the job
        $this->service->createMaterial($job, $validator->validated(), auth()->id());

        return redirect()->route('project.job-document.workflow.show', [$projectId, $document->unique_id ?? $document->id])
            ->with('success', 'Material berhasil ditambahkan.');
    }

    // The following methods handle the deletion of materials and photos associated with a job document. They include validation, error handling, and appropriate redirects to ensure a smooth user experience.
    public function destroyMaterial(string $projectId, string $documentId, string $jobId, string $materialId): RedirectResponse
    {
        // Find the document for mutation (deleting an existing material from a job)
        $document = $this->findDocumentForMutation($projectId, $documentId);

        // If the document is not found or cannot be mutated, redirect back with an error message
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        // Find the specific job from which the material will be deleted within the document
        $job = $this->findJob($document->id, $jobId);

        // If the job is not found, redirect back with an error message
        if (! $job) {
            return back()->with('error', 'Pekerjaan tidak ditemukan.');
        }

        // Find the specific material to be deleted within the job
        $material = $this->find_material($job->id, $materialId);

        // If the material is not found, redirect back with an error message
        if (! $material) {
            return back()->with('error', 'Material tidak ditemukan.');
        }

        // Delete the material using the service with the authenticated user's ID
        $this->service->deleteMaterial($material, auth()->id());

        return redirect()->route('project.job-document.workflow.show', [$projectId, $document->unique_id ?? $document->id])
            ->with('success', 'Material berhasil dihapus.');
    }

    // The following methods handle the management of photos associated with a job document. They include validation, error handling, and appropriate redirects to ensure a smooth user experience.
    public function photos(string $projectId, string $documentId, string $jobId): View|RedirectResponse
    {
        // Find the project by its unique ID
        $project = $this->findProject($projectId);
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        $document = $this->find_document_by_public_id($project->id, $documentId);
        if (! $document) {
            return redirect()->route('project.job-document.workflow.index', $project->unique_id)->with('error', 'Dokumen pekerjaan tidak ditemukan.');
        }

        $job = $this->findJob($document->id, $jobId, ['photos']);

        if (! $job) {
            return redirect()->route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id ?? $document->id])->with('error', 'Pekerjaan tidak ditemukan.');
        }

        return view('project.job-document.photos', [
            'project' => $project,
            'document' => $document,
            'job' => $job,
            'photos' => $job->photos,
            'documentTypeLabels' => $this->documentTypeLabels(),
        ]);
    }

    public function storePhoto(Request $request, string $projectId, string $documentId, string $jobId): RedirectResponse
    {
        $document = $this->findDocumentForMutation($projectId, $documentId);
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        $job = $this->findJob($document->id, $jobId);
        if (! $job) {
            return back()->with('error', 'Pekerjaan tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'job_photo' => 'required|image|max:4096',
            'photo_category' => 'nullable|in:quotation,progress,result_before,result_after',
            'caption' => 'nullable|string|max:191',
            'taken_at' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('job_photo');
        $filename = now()->format('YmdHis').'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('project_job_photos', $filename, 'public');

        $data = $validator->validated();
        $this->service->createPhoto($job, [
            'photo_category' => $data['photo_category'] ?? null,
            'photo_path' => $path,
            'caption' => $data['caption'] ?? null,
            'taken_at' => $data['taken_at'] ?? null,
            'uploaded_by' => auth()->id(),
        ], auth()->id());

        return redirect()->route('project.job-document.workflow.job.photos', [$projectId, $document->unique_id ?? $document->id, $job->unique_id ?? $job->id])
            ->with('success', 'Foto berhasil diunggah.');
    }

    public function destroyPhoto(string $projectId, string $documentId, string $jobId, string $photoId): RedirectResponse
    {
        $document = $this->findDocumentForMutation($projectId, $documentId);
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        $job = $this->findJob($document->id, $jobId);
        if (! $job) {
            return back()->with('error', 'Pekerjaan tidak ditemukan.');
        }

        $photo = $this->find_photo($job->id, $photoId);
        if (! $photo) {
            return back()->with('error', 'Foto tidak ditemukan.');
        }

        if ($photo->photo_path) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $this->service->deletePhoto($photo, auth()->id());

        return redirect()->route('project.job-document.workflow.job.photos', [$projectId, $document->unique_id ?? $document->id, $job->unique_id ?? $job->id])
            ->with('success', 'Foto berhasil dihapus.');
    }

    public function store(Request $request, string $projectId): RedirectResponse
    {
        $project = $this->findProject($projectId);

        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:'.implode(',', array_keys($this->documentTypeLabels())),
            'document_number' => 'nullable|string|max:100',
            'source_document_id' => 'nullable|uuid',
            'revision_reason' => 'required_with:source_document_id|nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ], [
            'document_type.required' => 'Tipe dokumen wajib dipilih.',
            'document_type.in' => 'Tipe dokumen tidak valid.',
            'revision_reason.required_with' => 'Alasan revisi wajib diisi agar perubahan dapat ditelusuri.',
            'revision_reason.max' => 'Alasan revisi maksimal 1000 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $documentsByType = ProjectJobDocument::query()
            ->where('project_id', $project->id)
            ->get()
            ->groupBy('document_type');

        if (! $this->canCreateDocumentType($data['document_type'], $this->buildSopState($documentsByType))) {
            return back()->with('error', 'Tahap SOP belum terpenuhi untuk membuat dokumen ini.')->withInput();
        }

        $existing = $documentsByType->get($data['document_type'], collect())->sortByDesc('revision_no');

        if (empty($data['source_document_id'])) {
            if ($existing->isNotEmpty()) {
                return back()->with('error', 'Dokumen untuk tahap ini sudah ada. Buat revisi dari versi terakhir.')->withInput();
            }

            $this->service->createDocument($project, [
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'] ?? null,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'prepared_by_employee_id' => $this->current_employee_id(),
            ], auth()->id());

            return redirect()->route('project.job-document.workflow.index', $project->unique_id)
                ->with('success', 'Dokumen pekerjaan berhasil dibuat.');
        }

        $source = $existing->firstWhere('unique_id', $data['source_document_id']);
        $latest = $existing->first();

        if (! $source || $source->document_type !== $data['document_type']) {
            return back()->with('error', 'Dokumen sumber tidak ditemukan.')->withInput();
        }

        if ($latest->id !== $source->id) {
            return back()->with('error', 'Revisi hanya dapat dibuat dari versi terakhir.')->withInput();
        }

        if ($source->is_editable()) {
            return back()->with('error', 'Revisi terakhir masih berstatus Draft. Selesaikan atau ajukan terlebih dahulu.')->withInput();
        }

        $revision = $this->service->createRevision($source, $data['revision_reason'], $data['notes'] ?? null, auth()->id());
        $revision->update([
            'document_number' => $data['document_number'] ?? $source->document_number,
            'prepared_by_employee_id' => $this->current_employee_id(),
        ]);

        return redirect()->route('project.job-document.workflow.show', [$project->unique_id, $revision->unique_id])
            ->with('success', 'Revisi '.$revision->revision_no.' berhasil dibuat dari revisi '.$source->revision_no.'.');
    }

    public function submit(string $projectId, string $documentId): RedirectResponse
    {
        $document = $this->findDocumentForMutation($projectId, $documentId);
        if ($document instanceof RedirectResponse) {
            return $document;
        }

        $document->loadCount('jobs');
        $route = ['project.job-document.workflow.show', [$projectId, $document->unique_id]];

        if ($document->jobs_count === 0) {
            return redirect()->route(...$route)->with('error', 'Dokumen belum memiliki pekerjaan.');
        }

        $this->service->submit($document, auth()->id());

        return redirect()->route(...$route)->with('success', 'Revisi diajukan untuk negosiasi dan dikunci dari perubahan.');
    }

    public function return_to_draft(string $projectId, string $documentId): RedirectResponse
    {
        $project = $this->findProject($projectId);
        $document = $project ? $this->find_document_by_public_id($project->id, $documentId) : null;

        if (! $document) {
            return redirect()->route('project.index')->with('error', 'Dokumen pekerjaan tidak ditemukan.');
        }

        $latest_no = ProjectJobDocument::query()->where('project_id', $project->id)->where('document_type', $document->document_type)->max('revision_no');

        if ($document->status !== 'submitted' || (int) $latest_no !== $document->revision_no) {
            return back()->with('error', 'Hanya revisi terakhir yang berstatus Diajukan yang dapat dikembalikan ke Draft.');
        }

        $this->service->return_to_draft($document, auth()->id());

        return back()->with('success', 'Revisi dikembalikan ke Draft.');
    }

    public function approve(string $projectId, string $documentId): RedirectResponse
    {
        $project = $this->findProject($projectId);
        $document = $project ? $this->find_document_by_public_id($project->id, $documentId) : null;

        if (! $document) {
            return redirect()->route('project.index')->with('error', 'Dokumen pekerjaan tidak ditemukan.');
        }

        if (! $this->can_approve_document($document)) {
            abort(403);
        }

        $latest_no = ProjectJobDocument::query()->where('project_id', $project->id)->where('document_type', $document->document_type)->max('revision_no');

        if ($document->status !== 'submitted' || (int) $latest_no !== $document->revision_no) {
            return back()->with('error', 'Hanya revisi terakhir berstatus Diajukan yang dapat disetujui.');
        }

        $this->service->approve($document, $this->current_employee_id(), auth()->id());

        return back()->with('success', 'Revisi disetujui. Revisi sebelumnya ditandai sebagai Digantikan.');
    }

    public function compare(Request $request, string $projectId, string $documentId): View|RedirectResponse
    {
        $project = $this->findProject($projectId);
        $document = $project ? $this->find_document_by_public_id($project->id, $documentId) : null;

        if (! $document) {
            return redirect()->route('project.index')->with('error', 'Dokumen pekerjaan tidak ditemukan.');
        }

        $with = $request->query('with');
        $base = null;

        if (is_string($with) && preg_match('/^[0-9a-fA-F-]{36}$/', $with) === 1) {
            $base = ProjectJobDocument::query()
                ->where('project_id', $project->id)
                ->where('document_type', $document->document_type)
                ->where('unique_id', $with)
                ->first();
        }

        $base ??= ProjectJobDocument::query()
            ->where('project_id', $project->id)
            ->where('document_type', $document->document_type)
            ->where('revision_no', '<', $document->revision_no)
            ->orderByDesc('revision_no')
            ->first();

        if (! $base || $base->id === $document->id) {
            return redirect()->route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id])
                ->with('error', 'Tidak ada revisi pembanding untuk dokumen ini.');
        }

        // Always compare older (base) against newer (target) so the delta reads chronologically.
        [$base, $target] = $base->revision_no < $document->revision_no ? [$base, $document] : [$document, $base];

        $revisions = ProjectJobDocument::query()
            ->where('project_id', $project->id)
            ->where('document_type', $document->document_type)
            ->orderByDesc('revision_no')
            ->get();

        return view('project.job-document.compare', [
            'project' => $project,
            'document' => $document,
            'base' => $base,
            'target' => $target,
            'revisions' => $revisions,
            'comparison' => $this->comparison->compare($base, $target),
            'documentTypeLabels' => $this->documentTypeLabels(),
        ]);
    }

    public function finalizeSatisfactionNotes(string $projectId, string $documentId): RedirectResponse
    {
        $project = $this->findProject($projectId);

        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        $document = $this->find_document_by_public_id($project->id, $documentId);

        if (! $document || $document->document_type !== ProjectJobDocument::TYPE_SATISFACTION_NOTES) {
            return redirect()->route('project.job-document.workflow.index', $project->unique_id)
                ->with('error', 'Dokumen Satisfaction Notes tidak ditemukan.');
        }

        $document->load('jobs');

        if ($document->jobs->isEmpty()) {
            return redirect()->route('project.job-document.workflow.index', $project->unique_id)
                ->with('error', 'Satisfaction Notes belum memiliki pekerjaan.');
        }

        $allCompleted = $document->jobs->every(function ($job): bool {
            $progress = (float) ($job->progress_percent ?? 0);
            $status = strtolower((string) ($job->status ?? ''));

            return $progress >= 100 || $status === 'completed';
        });

        if (! $allCompleted) {
            return redirect()->route('project.job-document.workflow.index', $project->unique_id)
                ->with('error', 'Satisfaction Notes hanya bisa difinalisasi ketika semua pekerjaan sudah 100% atau Completed.');
        }

        $this->service->updateDocument($document, [
            'status' => 'approved',
            'approved_at' => now(),
            'locked_at' => now(),
        ], auth()->id());

        return redirect()->route('project.job-document.workflow.index', $project->unique_id)
            ->with('success', 'Satisfaction Notes berhasil difinalisasi.');
    }

    private function current_employee_id(): ?int
    {
        return Employee::query()->where('user_id', auth()->id())->value('id');
    }

    private function actor_names(Collection $user_ids): Collection
    {
        return Employee::query()->whereIn('user_id', $user_ids->filter()->unique())->pluck('name', 'user_id');
    }

    private function can_approve_document(ProjectJobDocument $document): bool
    {
        $user = auth()->user();
        $permission = match ($document->document_type) {
            ProjectJobDocument::TYPE_INITIAL_BOQ => 'edit-project-quotation',
            ProjectJobDocument::TYPE_FINAL_BOQ => 'edit-project-realization',
            ProjectJobDocument::TYPE_SATISFACTION_NOTES => 'edit-project-satisfaction-note',
            default => 'edit-project',
        };

        return $user !== null && ($user->hasRole('admin') || $user->can($permission));
    }

    private function findProject(string $projectId): ?Project
    {
        return Project::query()
            ->with(['ship', 'job_documents'])
            ->firstWhere('unique_id', $projectId);
    }

    private function findDocumentForMutation(string $projectId, string $documentId): ProjectJobDocument|RedirectResponse
    {
        $project = $this->findProject($projectId);
        if (! $project) {
            return redirect()->route('project.index')->with('error', 'Proyek tidak ditemukan.');
        }

        $document = $this->find_document_by_public_id($project->id, $documentId);
        if (! $document) {
            return redirect()->route('project.job-document.workflow.index', $project->unique_id)->with('error', 'Dokumen pekerjaan tidak ditemukan.');
        }

        if (! $document->is_editable()) {
            return redirect()->route('project.job-document.workflow.show', [$project->unique_id, $document->unique_id])->with('error', 'Hanya revisi berstatus Draft yang dapat diubah. Buat revisi baru untuk melakukan perubahan.');
        }

        return $document;
    }

    private function findJob(int $documentId, string $jobId, array $relations = []): ?ProjectDocumentJob
    {
        $query = ProjectDocumentJob::query()
            ->with($relations)
            ->where('project_job_document_id', $documentId);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $jobId) === 1) {
            return $query->where('unique_id', $jobId)->first();
        }

        return $query->find((int) $jobId);
    }

    private function find_document_by_public_id(int $project_id, string $document_id): ?ProjectJobDocument
    {
        $query = ProjectJobDocument::query()->where('project_id', $project_id);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $document_id) === 1) {
            return $query->where('unique_id', $document_id)->first();
        }

        return $query->find((int) $document_id);
    }

    private function find_material(int $job_id, string $material_id): ?ProjectDocumentJobMaterial
    {
        $query = ProjectDocumentJobMaterial::query()->where('project_document_job_id', $job_id);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $material_id) === 1) {
            return $query->where('unique_id', $material_id)->first();
        }

        return $query->find((int) $material_id);
    }

    private function find_photo(int $job_id, string $photo_id): ?ProjectDocumentJobPhoto
    {
        $query = ProjectDocumentJobPhoto::query()->where('project_document_job_id', $job_id);

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $photo_id) === 1) {
            return $query->where('unique_id', $photo_id)->first();
        }

        return $query->find((int) $photo_id);
    }

    private function documentView(ProjectJobDocument $document): string
    {
        $map = [
            ProjectJobDocument::TYPE_REPAIR_LIST => 'repair-list.index',
            ProjectJobDocument::TYPE_INITIAL_BOQ => 'initial-boq.index',
            ProjectJobDocument::TYPE_SATISFACTION_NOTES => 'satisfaction-notes.index',
            ProjectJobDocument::TYPE_FINAL_BOQ => 'final-boq.index',
            ProjectJobDocument::TYPE_DOCKING_REPORT => 'docking.index',
        ];

        return $map[$document->document_type] ?? 'repair-list.index';
    }

    private function jobRules(): array
    {
        return [
            'job_name' => 'required|string|max:191',
            'job_volume_estimated' => 'nullable|numeric|min:0',
            'responsible_kind' => 'nullable|in:internal_unit,subcontractor',
            'responsible_name' => 'nullable|string|max:191',
            'est_start_date' => 'nullable|date',
            'est_finish_date' => 'nullable|date|after_or_equal:est_start_date',
            'est_duration_days' => 'nullable|integer|min:0',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'est_price' => 'nullable|numeric|min:0',
            'est_currency' => 'nullable|string|size:3',
            'job_weight_percent' => 'nullable|numeric|min:0|max:100',
            'actual_start_date' => 'nullable|date',
            'actual_finish_date' => 'nullable|date|after_or_equal:actual_start_date',
            'actual_duration_days' => 'nullable|integer|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'actual_currency' => 'nullable|string|size:3',
            'actual_volume' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,ongoing,completed,cancelled',
        ];
    }

    private function jobMessages(): array
    {
        return [
            'job_name.required' => 'Nama pekerjaan wajib diisi.',
            'status.in' => 'Status pekerjaan tidak valid.',
            'est_finish_date.after_or_equal' => 'Tanggal estimasi selesai harus sama atau setelah tanggal estimasi mulai.',
            'actual_finish_date.after_or_equal' => 'Tanggal aktual selesai harus sama atau setelah tanggal aktual mulai.',
        ];
    }

    private function buildSopState(Collection $documentsByType): array
    {
        $repairListReady = $documentsByType->has(ProjectJobDocument::TYPE_REPAIR_LIST)
            && $documentsByType[ProjectJobDocument::TYPE_REPAIR_LIST]->isNotEmpty();

        $initialBoqReady = $documentsByType->has(ProjectJobDocument::TYPE_INITIAL_BOQ)
            && $documentsByType[ProjectJobDocument::TYPE_INITIAL_BOQ]->isNotEmpty();

        $satisfactionNotes = $documentsByType->get(ProjectJobDocument::TYPE_SATISFACTION_NOTES, collect());

        $finalizedSatisfactionReady = $satisfactionNotes->contains(function (ProjectJobDocument $doc): bool {
            return in_array($doc->status, ['approved', 'locked'], true);
        });

        return [
            'repair_list' => [
                'ready' => true,
                'label' => 'Repair List',
                'description' => 'Tahap awal data pekerjaan dan kebutuhan material.',
            ],
            'initial_boq' => [
                'ready' => $repairListReady,
                'label' => 'RAB Awal',
                'description' => 'RAB awal setelah Repair List tersedia.',
            ],
            'satisfaction_notes' => [
                'ready' => $initialBoqReady,
                'label' => 'Satisfaction Notes',
                'description' => 'Dokumen progres selama proyek berlangsung.',
            ],
            'docking_report' => [
                'ready' => $initialBoqReady,
                'label' => 'Docking Report',
                'description' => 'Laporan harian docking selama proyek.',
            ],
            'final_boq' => [
                'ready' => $finalizedSatisfactionReady,
                'label' => 'RAB Akhir',
                'description' => 'RAB akhir setelah Satisfaction Notes difinalisasi.',
            ],
        ];
    }

    private function canCreateDocumentType(string $documentType, array $sop): bool
    {
        return (bool) ($sop[$documentType]['ready'] ?? false);
    }

    private function documentTypeLabels(): array
    {
        return [
            ProjectJobDocument::TYPE_REPAIR_LIST => 'Repair List',
            ProjectJobDocument::TYPE_INITIAL_BOQ => 'RAB Awal',
            ProjectJobDocument::TYPE_SATISFACTION_NOTES => 'Satisfaction Notes',
            ProjectJobDocument::TYPE_DOCKING_REPORT => 'Docking Report',
            ProjectJobDocument::TYPE_FINAL_BOQ => 'RAB Akhir',
        ];
    }
}
