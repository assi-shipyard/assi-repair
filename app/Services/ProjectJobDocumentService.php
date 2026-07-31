<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectDocumentJobMaterial;
use App\Models\ProjectDocumentJobPhoto;
use App\Models\ProjectJobDocument;

class ProjectJobDocumentService
{
    public function nextRevision(int $projectId, string $documentType): int
    {
        $currentMax = ProjectJobDocument::query()
            ->where('project_id', $projectId)
            ->where('document_type', $documentType)
            ->max('revision_no');

        return ((int) $currentMax) + 1;
    }

    public function createDocument(Project $project, array $data, ?int $actorId = null): ProjectJobDocument
    {
        if (!isset($data['revision_no'])) {
            $data['revision_no'] = $this->nextRevision($project->id, (string) $data['document_type']);
        }

        $data['project_id'] = $project->id;

        $document = ProjectJobDocument::create($data);

        $this->recordHistory($document, 'created', [
            'document_type' => $document->document_type,
            'revision_no' => $document->revision_no,
        ], $actorId);

        return $document;
    }

    public function updateDocument(ProjectJobDocument $document, array $data, ?int $actorId = null): ProjectJobDocument
    {
        $document->update($data);

        $this->recordHistory($document, 'updated', $data, $actorId);

        return $document;
    }

    public function copyDocument(ProjectJobDocument $source, array $attributes, ?int $actorId = null): ProjectJobDocument
    {
        if (!isset($attributes['revision_no'])) {
            $targetType = $attributes['document_type'] ?? $source->document_type;
            $attributes['revision_no'] = $this->nextRevision($source->project_id, (string) $targetType);
        }

        $attributes['actor_id'] = $actorId;

        return ProjectJobDocument::cloneWithChildren($source, $attributes);
    }

    public function createJob(ProjectJobDocument $document, array $data, ?int $actorId = null): ProjectDocumentJob
    {
        $data['project_job_document_id'] = $document->id;

        $job = ProjectDocumentJob::create($data);

        $this->recordHistory($document, 'job_created', [
            'job_id' => $job->id,
            'job_name' => $job->job_name,
        ], $actorId);

        return $job;
    }

    public function updateJob(ProjectDocumentJob $job, array $data, ?int $actorId = null): ProjectDocumentJob
    {
        $job->update($data);

        $this->recordHistory($job->document, 'job_updated', [
            'job_id' => $job->id,
            'changes' => $data,
        ], $actorId);

        return $job;
    }

    public function deleteJob(ProjectDocumentJob $job, ?int $actorId = null): void
    {
        $jobName = $job->job_name;
        $jobId = $job->id;
        $document = $job->document;

        $job->delete();

        $this->recordHistory($document, 'job_deleted', [
            'job_id' => $jobId,
            'job_name' => $jobName,
        ], $actorId);
    }

    public function createMaterial(ProjectDocumentJob $job, array $data, ?int $actorId = null): ProjectDocumentJobMaterial
    {
        $data['project_document_job_id'] = $job->id;

        $material = ProjectDocumentJobMaterial::create($data);

        $this->recordHistory($job->document, 'material_created', [
            'job_id' => $job->id,
            'material_id' => $material->id,
            'material_name' => $material->material_name,
        ], $actorId);

        return $material;
    }

    public function updateMaterial(ProjectDocumentJobMaterial $material, array $data, ?int $actorId = null): ProjectDocumentJobMaterial
    {
        $material->update($data);

        $this->recordHistory($material->job->document, 'material_updated', [
            'job_id' => $material->job->id,
            'material_id' => $material->id,
            'changes' => $data,
        ], $actorId);

        return $material;
    }

    public function deleteMaterial(ProjectDocumentJobMaterial $material, ?int $actorId = null): void
    {
        $job = $material->job;
        $materialId = $material->id;

        $material->delete();

        $this->recordHistory($job->document, 'material_deleted', [
            'job_id' => $job->id,
            'material_id' => $materialId,
        ], $actorId);
    }

    public function createPhoto(ProjectDocumentJob $job, array $data, ?int $actorId = null): ProjectDocumentJobPhoto
    {
        $data['project_document_job_id'] = $job->id;

        $photo = ProjectDocumentJobPhoto::create($data);

        $this->recordHistory($job->document, 'photo_created', [
            'job_id' => $job->id,
            'photo_id' => $photo->id,
        ], $actorId);

        return $photo;
    }

    public function updatePhoto(ProjectDocumentJobPhoto $photo, array $data, ?int $actorId = null): ProjectDocumentJobPhoto
    {
        $photo->update($data);

        $this->recordHistory($photo->job->document, 'photo_updated', [
            'job_id' => $photo->job->id,
            'photo_id' => $photo->id,
            'changes' => $data,
        ], $actorId);

        return $photo;
    }

    public function deletePhoto(ProjectDocumentJobPhoto $photo, ?int $actorId = null): void
    {
        $job = $photo->job;
        $photoId = $photo->id;

        $photo->delete();

        $this->recordHistory($job->document, 'photo_deleted', [
            'job_id' => $job->id,
            'photo_id' => $photoId,
        ], $actorId);
    }

    private function recordHistory(ProjectJobDocument $document, string $action, array $payload = [], ?int $actorId = null): void
    {
        $document->histories()->create([
            'action' => $action,
            'actor_id' => $actorId,
            'payload' => $payload,
        ]);
    }
}
