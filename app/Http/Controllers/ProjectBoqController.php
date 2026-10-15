<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\SaveDocumentJobMaterialRequest;
use App\Http\Requests\Project\SaveDocumentJobRequest;
use App\Http\Requests\Project\SaveMinimumOverrideRequest;
use App\Http\Requests\Project\SearchPriceItemsRequest;
use App\Http\Requests\Project\DecideOwnerVendorJobRequest;
use App\Http\Resources\price_book_item_option_resource;
use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectJobDocument;
use App\Services\project_boq_service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class ProjectBoqController extends Controller
{
    public function __construct(private readonly project_boq_service $boq, private readonly Guard $auth) {}

    public function store_job(SaveDocumentJobRequest $request, string $project_id, string $document_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $job = $this->boq->save_job($document, null, $request->validated(), $this->auth->id());

        return $this->back_to($document, $job->parent_job_id ? 'Sub pekerjaan berhasil ditambahkan.' : 'Pekerjaan berhasil ditambahkan.');
    }

    public function update_job(SaveDocumentJobRequest $request, string $project_id, string $document_id, string $job_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $this->boq->save_job($document, $this->job($document, $job_id), $request->validated(), $this->auth->id());

        return $this->back_to($document, 'Pekerjaan berhasil diperbarui.');
    }

    public function decide_owner_vendor_job(DecideOwnerVendorJobRequest $request, string $project_id, string $document_id, string $job_id): RedirectResponse
    {
        $document = $this->document($this->project($project_id), $document_id);
        abort_unless($document->document_type === ProjectJobDocument::TYPE_FINAL_BOQ, 404);

        if (! $document->is_editable()) {
            abort(redirect()->route('project.job-document.workflow.show', [$project_id, $document->unique_id])
                ->with('error', 'Persetujuan vendor pemilik hanya dapat diubah selama RAB Akhir berstatus Draft.'));
        }

        $decision = $request->validated();
        $this->boq->decide_owner_vendor_job(
            $document,
            $this->job($document, $job_id),
            $decision['decision'],
            $decision['note'] ?? null,
            $this->auth->id(),
        );

        return $this->back_to($document, $decision['decision'] === 'approved'
            ? 'Penggunaan vendor pemilik disetujui.'
            : 'Penggunaan vendor pemilik ditolak.');
    }

    public function destroy_job(string $project_id, string $document_id, string $job_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $this->boq->delete_job($document, $this->job($document, $job_id), $this->auth->id());

        return $this->back_to($document, 'Pekerjaan beserta sub pekerjaannya berhasil dihapus.');
    }

    public function store_material(SaveDocumentJobMaterialRequest $request, string $project_id, string $document_id, string $job_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $this->boq->save_material($document, $this->job($document, $job_id), null, $request->validated(), $this->auth->id());

        return $this->back_to($document, 'Material berhasil ditambahkan.');
    }

    public function update_material(SaveDocumentJobMaterialRequest $request, string $project_id, string $document_id, string $job_id, string $material_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $job = $this->job($document, $job_id);
        $this->boq->save_material($document, $job, $this->material($job, $material_id), $request->validated(), $this->auth->id());

        return $this->back_to($document, 'Material berhasil diperbarui.');
    }

    public function destroy_material(string $project_id, string $document_id, string $job_id, string $material_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $this->boq->delete_material($document, $this->material($this->job($document, $job_id), $material_id), $this->auth->id());

        return $this->back_to($document, 'Material berhasil dihapus.');
    }

    public function save_minimum_override(SaveMinimumOverrideRequest $request, string $project_id, string $document_id): RedirectResponse
    {
        $document = $this->editable_document($project_id, $document_id);
        $is_set = $request->validated('action') === 'set';

        $this->boq->set_minimum_override(
            $document,
            $request->validated('price_book_item_id'),
            $is_set ? (string) $request->validated('minimum_quantity') : null,
            $is_set ? $request->validated('reason') : null,
            $this->auth->id(),
        );

        return $this->back_to($document, $is_set ? 'Minimum hasil negosiasi berhasil disimpan.' : 'Minimum dikembalikan sesuai Kamus Harga.');
    }

    public function price_items(SearchPriceItemsRequest $request, string $project_id): AnonymousResourceCollection
    {
        $items = $this->boq->search_price_items($this->project($project_id), $request->validated('q'), $request->validated('group'));

        return price_book_item_option_resource::collection($items);
    }

    public function export(string $project_id, string $document_id): Response
    {
        $project = $this->project($project_id)->load('ship');
        $document = $this->document($project, $document_id)->load(['prepared_by', 'approved_by']);

        // Dompdf caches embedded font metrics here and fails if the directory is missing.
        File::ensureDirectoryExists(config('dompdf.options.font_cache'));

        $pdf = Pdf::loadView('project.job-document.export', [
            'project' => $project,
            'document' => $document,
            'sheet' => $this->boq->build_sheet($document),
            'document_label' => ProjectJobDocument::TYPE_LABELS[$document->document_type] ?? $document->document_type,
            'printed_at' => now()->locale('id'),
        ])->setPaper('a4', 'landscape');

        $file_name = str($document->document_type.'-'.$project->project_code.'-rev'.$document->revision_no)->slug().'.pdf';

        return $pdf->download($file_name);
    }

    private function project(string $project_id): Project
    {
        return Project::query()->where('unique_id', $project_id)->firstOrFail();
    }

    private function document(Project $project, string $document_id): ProjectJobDocument
    {
        return ProjectJobDocument::query()->where('project_id', $project->id)->where('unique_id', $document_id)->firstOrFail();
    }

    private function editable_document(string $project_id, string $document_id): ProjectJobDocument
    {
        $document = $this->document($this->project($project_id), $document_id);
        abort_unless($document->can_be_edited_by($this->auth->user()), 403);

        if (! $document->is_editable()) {
            abort(redirect()->route('project.job-document.workflow.show', [$project_id, $document->unique_id])
                ->with('error', 'Hanya revisi berstatus Draft yang dapat diubah. Buat revisi baru untuk melakukan perubahan.'));
        }

        return $document;
    }

    private function job(ProjectJobDocument $document, string $job_id): ProjectDocumentJob
    {
        return ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->where('unique_id', $job_id)->firstOrFail();
    }

    private function material(ProjectDocumentJob $job, string $material_id): ProjectDocumentJobMaterial
    {
        return ProjectDocumentJobMaterial::query()->where('project_document_job_id', $job->id)->where('unique_id', $material_id)->firstOrFail();
    }

    private function back_to(ProjectJobDocument $document, string $message): RedirectResponse
    {
        $project_id = Project::query()->whereKey($document->project_id)->value('unique_id');

        return redirect()->route('project.job-document.workflow.show', [$project_id, $document->unique_id])->with('success', $message);
    }
}
