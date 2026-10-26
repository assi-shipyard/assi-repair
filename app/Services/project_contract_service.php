<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\project_contract;
use App\Models\project_contract_document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class project_contract_service
{
    public function __construct(private readonly ProjectActivityService $activity_service) {}

    public function save_contract(Project $project, array $data, ?UploadedFile $file, User $user): void
    {
        $document_path = null;
        $committed = false;

        try {
            if ($file !== null) {
                $document_path = $file->store('projects/'.$project->unique_id.'/contracts', project_contract_document::DISK);
                if ($document_path === false) {
                    throw new RuntimeException('Penyimpanan dokumen kontrak gagal.');
                }
            }

            DB::transaction(function () use ($project, $data, $file, $user, $document_path): void {
                // Lock the parent so concurrent first saves cannot create duplicate contracts.
                Project::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();
                $contract = $project->contract()->first() ?? new project_contract(['project_id' => $project->id]);
                $before = $contract->only(['contract_number', 'status', 'signing_due_date']);
                $contract->fill($data)->save();
                $after = $contract->only(['contract_number', 'status', 'signing_due_date']);
                $document = $document_path !== null ? $contract->documents()->create([
                    'document_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
                    'document_path' => $document_path,
                    'status' => $contract->status,
                    'uploaded_by' => $user->id,
                ]) : null;

                $changes = [];
                foreach (['contract_number' => 'Nomor kontrak', 'status' => 'Status kontrak', 'signing_due_date' => 'Target penandatanganan'] as $field => $label) {
                    $old = $this->format_value($field, $before[$field] ?? null);
                    $new = $this->format_value($field, $after[$field] ?? null);
                    if ($old !== $new) {
                        $changes[] = ['field' => $label, 'old' => $old, 'new' => $new];
                    }
                }
                if ($document !== null) {
                    $changes[] = ['field' => 'Dokumen kontrak', 'old' => null, 'new' => $document->document_name];
                }

                if ($changes === []) {
                    return;
                }

                $this->activity_service->record($project, 'contract_updated', 'Kontrak proyek diperbarui', $changes);
                AuditLog::query()->create([
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id,
                    'event_name' => 'project.contract_updated',
                    'event_payload' => [
                        'project_uuid' => $project->unique_id,
                        'before' => $before,
                        'after' => $after,
                        'document_uuid' => $document?->unique_id,
                    ],
                ]);
            });
            $committed = true;
        } finally {
            if (! $committed && is_string($document_path)) {
                if (! Storage::disk(project_contract_document::DISK)->delete($document_path)) {
                    throw new RuntimeException('Pembersihan unggahan kontrak yang gagal tidak berhasil.');
                }
            }
        }
    }

    private function format_value(string $field, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($field) {
            'status' => project_contract::STATUS_LABELS[$value],
            'signing_due_date' => $value->copy()->locale('id')->translatedFormat('j F Y'),
            default => (string) $value,
        };
    }
}
