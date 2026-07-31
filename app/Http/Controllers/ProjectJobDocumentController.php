<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectDocumentJobPhoto;
use App\Models\ProjectJobDocument;
use App\Services\ProjectJobDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectJobDocumentController extends Controller
{
    public function __construct(private ProjectJobDocumentService $service) {}

    public function index(string $projectId): JsonResponse
    {
        $authorization = $this->authorizeAction('view');

        if ($authorization) {
            return $authorization;
        }

        $project = $this->findProject($projectId);

        if (! $project) {
            return response()->json(['message' => 'Proyek tidak ditemukan.'], 404);
        }

        $documents = ProjectJobDocument::query()
            ->withCount('jobs')
            ->where('project_id', $project->id)
            ->orderBy('document_type')
            ->orderByDesc('revision_no')
            ->get();

        return response()->json(['data' => $documents]);
    }

    public function store(Request $request, string $projectId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $project = $this->findProject($projectId);

        if (! $project) {
            return response()->json(['message' => 'Proyek tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:'.implode(',', $this->documentTypes()),
            'document_number' => 'nullable|string|max:100',
            'revision_no' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,approved,archived,locked',
            'prepared_by_employee_id' => 'nullable|exists:employees,id',
            'approved_by_employee_id' => 'nullable|exists:employees,id',
            'approved_at' => 'nullable|date',
            'locked_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'document_type.required' => 'Tipe dokumen wajib dipilih.',
            'document_type.in' => 'Tipe dokumen tidak valid.',
            'status.in' => 'Status dokumen tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $document = $this->service->createDocument($project, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Dokumen pekerjaan berhasil dibuat.',
            'data' => $document,
        ], 201);
    }

    public function show(string $projectId, int $documentId): JsonResponse
    {
        $authorization = $this->authorizeAction('view');

        if ($authorization) {
            return $authorization;
        }

        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        $document->load([
            'jobs.materials',
            'jobs.photos',
            'histories',
        ]);

        return response()->json(['data' => $document]);
    }

    public function update(Request $request, string $projectId, int $documentId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'document_number' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,approved,archived,locked',
            'prepared_by_employee_id' => 'nullable|exists:employees,id',
            'approved_by_employee_id' => 'nullable|exists:employees,id',
            'approved_at' => 'nullable|date',
            'locked_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'status.in' => 'Status dokumen tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $document = $this->service->updateDocument($document, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Dokumen pekerjaan berhasil diperbarui.',
            'data' => $document,
        ]);
    }

    public function destroy(string $projectId, int $documentId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        if ($document->status === 'locked') {
            return response()->json(['message' => 'Dokumen terkunci dan tidak dapat dihapus.'], 422);
        }

        $document->delete();

        return response()->json(['message' => 'Dokumen pekerjaan berhasil dihapus.']);
    }

    public function copy(Request $request, string $projectId, int $documentId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $source = $this->findDocument($projectId, $documentId);

        if (! $source) {
            return response()->json(['message' => 'Dokumen sumber tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'nullable|in:'.implode(',', $this->documentTypes()),
            'document_number' => 'nullable|string|max:100',
            'revision_no' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,approved,archived,locked',
            'prepared_by_employee_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $attributes = $validator->validated();
        $copied = $this->service->copyDocument($source, $attributes, $this->currentActorId());

        return response()->json([
            'message' => 'Dokumen berhasil disalin.',
            'data' => $copied->load('jobs.materials', 'jobs.photos'),
        ], 201);
    }

    public function storeJob(Request $request, string $projectId, int $documentId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), $this->jobRules(), $this->jobMessages());

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $job = $this->service->createJob($document, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Pekerjaan berhasil ditambahkan.',
            'data' => $job,
        ], 201);
    }

    public function updateJob(Request $request, string $projectId, int $documentId, int $jobId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        $job = ProjectDocumentJob::query()
            ->where('id', $jobId)
            ->where('project_job_document_id', $document->id)
            ->first();

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), $this->jobRules(false), $this->jobMessages());

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $job = $this->service->updateJob($job, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Pekerjaan berhasil diperbarui.',
            'data' => $job,
        ]);
    }

    public function destroyJob(string $projectId, int $documentId, int $jobId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        $job = ProjectDocumentJob::query()
            ->where('id', $jobId)
            ->where('project_job_document_id', $document->id)
            ->first();

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $this->service->deleteJob($job, $this->currentActorId());

        return response()->json(['message' => 'Pekerjaan berhasil dihapus.']);
    }

    public function storeMaterial(Request $request, string $projectId, int $documentId, int $jobId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $job = $this->findJob($projectId, $documentId, $jobId);

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $material = $this->service->createMaterial($job, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Material berhasil ditambahkan.',
            'data' => $material,
        ], 201);
    }

    public function updateMaterial(Request $request, string $projectId, int $documentId, int $jobId, int $materialId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $job = $this->findJob($projectId, $documentId, $jobId);

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $material = ProjectDocumentJobMaterial::query()
            ->where('id', $materialId)
            ->where('project_document_job_id', $job->id)
            ->first();

        if (! $material) {
            return response()->json(['message' => 'Material tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'material_name' => 'sometimes|required|string|max:191',
            'volume' => 'nullable|numeric|min:0',
            'density' => 'nullable|numeric|min:0',
            'dimension' => 'nullable|string|max:191',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'diameter' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'thickness' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $material = $this->service->updateMaterial($material, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Material berhasil diperbarui.',
            'data' => $material,
        ]);
    }

    public function destroyMaterial(string $projectId, int $documentId, int $jobId, int $materialId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $job = $this->findJob($projectId, $documentId, $jobId);

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $material = ProjectDocumentJobMaterial::query()
            ->where('id', $materialId)
            ->where('project_document_job_id', $job->id)
            ->first();

        if (! $material) {
            return response()->json(['message' => 'Material tidak ditemukan.'], 404);
        }

        $this->service->deleteMaterial($material, $this->currentActorId());

        return response()->json(['message' => 'Material berhasil dihapus.']);
    }

    public function storePhoto(Request $request, string $projectId, int $documentId, int $jobId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $job = $this->findJob($projectId, $documentId, $jobId);

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'photo_category' => 'nullable|in:quotation,progress,result_before,result_after',
            'photo_path' => 'required|string|max:255',
            'caption' => 'nullable|string|max:191',
            'taken_at' => 'nullable|date',
            'uploaded_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['project_document_job_id'] = $job->id;

        if (! isset($data['uploaded_by']) && auth()->check()) {
            $data['uploaded_by'] = auth()->id();
        }

        $photo = $this->service->createPhoto($job, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Foto berhasil ditambahkan.',
            'data' => $photo,
        ], 201);
    }

    public function updatePhoto(Request $request, string $projectId, int $documentId, int $jobId, int $photoId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $job = $this->findJob($projectId, $documentId, $jobId);

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $photo = ProjectDocumentJobPhoto::query()
            ->where('id', $photoId)
            ->where('project_document_job_id', $job->id)
            ->first();

        if (! $photo) {
            return response()->json(['message' => 'Foto tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'photo_category' => 'nullable|in:quotation,progress,result_before,result_after',
            'photo_path' => 'sometimes|required|string|max:255',
            'caption' => 'nullable|string|max:191',
            'taken_at' => 'nullable|date',
            'uploaded_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $photo = $this->service->updatePhoto($photo, $data, $this->currentActorId());

        return response()->json([
            'message' => 'Foto berhasil diperbarui.',
            'data' => $photo,
        ]);
    }

    public function destroyPhoto(string $projectId, int $documentId, int $jobId, int $photoId): JsonResponse
    {
        $authorization = $this->authorizeAction('manage');

        if ($authorization) {
            return $authorization;
        }

        $job = $this->findJob($projectId, $documentId, $jobId);

        if (! $job) {
            return response()->json(['message' => 'Pekerjaan tidak ditemukan.'], 404);
        }

        $photo = ProjectDocumentJobPhoto::query()
            ->where('id', $photoId)
            ->where('project_document_job_id', $job->id)
            ->first();

        if (! $photo) {
            return response()->json(['message' => 'Foto tidak ditemukan.'], 404);
        }

        $this->service->deletePhoto($photo, $this->currentActorId());

        return response()->json(['message' => 'Foto berhasil dihapus.']);
    }

    private function findProject(string $projectId): ?Project
    {
        return Project::query()->firstWhere('unique_id', $projectId);
    }

    private function findDocument(string $projectId, int $documentId): ?ProjectJobDocument
    {
        $project = $this->findProject($projectId);

        if (! $project) {
            return null;
        }

        return ProjectJobDocument::query()
            ->where('id', $documentId)
            ->where('project_id', $project->id)
            ->first();
    }

    private function findJob(string $projectId, int $documentId, int $jobId): ?ProjectDocumentJob
    {
        $document = $this->findDocument($projectId, $documentId);

        if (! $document) {
            return null;
        }

        return ProjectDocumentJob::query()
            ->where('id', $jobId)
            ->where('project_job_document_id', $document->id)
            ->first();
    }

    private function documentTypes(): array
    {
        return [
            ProjectJobDocument::TYPE_REPAIR_LIST,
            ProjectJobDocument::TYPE_INITIAL_BOQ,
            ProjectJobDocument::TYPE_SATISFACTION_NOTES,
            ProjectJobDocument::TYPE_FINAL_BOQ,
            ProjectJobDocument::TYPE_DOCKING_REPORT,
        ];
    }

    private function jobRules(bool $requiredName = true): array
    {
        $jobNameRule = $requiredName ? 'required|string|max:191' : 'sometimes|required|string|max:191';

        return [
            'job_name' => $jobNameRule,
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
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    private function jobMessages(): array
    {
        return [
            'job_name.required' => 'Nama pekerjaan wajib diisi.',
            'progress_percent.max' => 'Progress pekerjaan maksimal 100%.',
            'progress_percent.min' => 'Progress pekerjaan minimal 0%.',
            'job_weight_percent.max' => 'Bobot pekerjaan maksimal 100%.',
            'job_weight_percent.min' => 'Bobot pekerjaan minimal 0%.',
            'status.in' => 'Status pekerjaan tidak valid.',
            'est_finish_date.after_or_equal' => 'Estimasi selesai harus sama atau setelah estimasi mulai.',
            'actual_finish_date.after_or_equal' => 'Aktual selesai harus sama atau setelah aktual mulai.',
        ];
    }

    private function authorizeAction(string $action): ?JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return null;
        }

        $permissionMap = [
            'view' => ['project.view', 'project-job-document.view', 'project-job-document.manage'],
            'manage' => ['project.manage', 'project-job-document.manage'],
        ];

        $permissions = $permissionMap[$action] ?? [];

        if (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($permissions)) {
            return null;
        }

        foreach ($permissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                return null;
            }
        }

        return response()->json(['message' => 'Anda tidak memiliki izin untuk aksi ini.'], 403);
    }

    private function currentActorId(): ?int
    {
        return auth()->id();
    }
}
