<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DockingRequestDocument;
use App\Models\Project;
use App\Models\ProjectDockingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DockingRequestDocumentStorage
{
    private const PENDING_ROOT = 'docking-request';

    private const REJECTED_ROOT = 'docking-request/rejected';

    private const CANCELLED_ROOT = 'docking-request/cancelled';

    public function store_pending(ProjectDockingRequest $docking_request, UploadedFile $file): string
    {
        // Random stored name; the client file name is never used on disk.
        return $file->store(self::PENDING_ROOT.'/'.$docking_request->unique_id, DockingRequestDocument::DISK);
    }

    public function move_to_project(ProjectDockingRequest $docking_request, Project $project): void
    {
        $this->move_documents($docking_request, 'projects/'.$this->safe_segment($project->project_code).'/documents');
    }

    public function move_to_rejected(ProjectDockingRequest $docking_request): void
    {
        $this->move_documents($docking_request, self::REJECTED_ROOT.'/'.$docking_request->unique_id);
    }

    /**
     * Only files still in the pending folder are archived; files already moved to a project folder stay with the project.
     */
    public function move_to_cancelled(ProjectDockingRequest $docking_request): void
    {
        $pending_prefix = self::PENDING_ROOT.'/'.$docking_request->unique_id.'/';
        $pending_documents = $docking_request->documents
            ->filter(fn (DockingRequestDocument $document): bool => str_starts_with($document->document_path, $pending_prefix));

        $this->move_documents($docking_request->setRelation('documents', $pending_documents), self::CANCELLED_ROOT.'/'.$docking_request->unique_id);
    }

    /**
     * Moves the request's files and updates their stored paths.
     * Files already moved are restored if any step fails, so disk and database stay consistent.
     */
    private function move_documents(ProjectDockingRequest $docking_request, string $target_dir): void
    {
        $disk = Storage::disk(DockingRequestDocument::DISK);
        $moved = [];

        try {
            foreach ($docking_request->documents as $document) {
                if (! $disk->exists($document->document_path)) {
                    continue;
                }

                $target = $target_dir.'/'.basename($document->document_path);
                $disk->move($document->document_path, $target);
                $moved[] = [$document->document_path, $target];
                $document->update(['document_path' => $target]);
            }
        } catch (Throwable $exception) {
            foreach (array_reverse($moved) as [$original, $target]) {
                $disk->move($target, $original);
            }

            throw $exception;
        }

        $disk->deleteDirectory(self::PENDING_ROOT.'/'.$docking_request->unique_id);
    }

    private function safe_segment(string $value): string
    {
        return trim(preg_replace('/[^A-Za-z0-9._-]/', '_', $value), '.');
    }
}
