<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\save_project_contract_request;
use App\Models\Project;
use App\Models\project_contract_document;
use App\Services\project_contract_service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectContractController extends Controller
{
    public function update(save_project_contract_request $request, string $project_id, project_contract_service $service): RedirectResponse
    {
        $project = Project::query()->where('unique_id', $project_id)->firstOrFail();
        $service->save_contract(
            $project,
            $request->safe()->except('document'),
            $request->file('document'),
            $request->user(),
        );

        return redirect()->route('project.show', $project->unique_id)
            ->with('success', 'Kontrak proyek berhasil diperbarui.');
    }

    public function download(string $project_id, string $document_id): StreamedResponse
    {
        $project = Project::query()->where('unique_id', $project_id)->firstOrFail();
        $document = project_contract_document::query()
            ->whereHas('contract', fn ($query) => $query->where('project_id', $project->id))
            ->where('unique_id', $document_id)->firstOrFail();
        $disk = Storage::disk(project_contract_document::DISK);
        abort_unless($disk->exists($document->document_path), 404, 'Dokumen kontrak tidak ditemukan.');

        return $disk->download($document->document_path, $document->document_name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
