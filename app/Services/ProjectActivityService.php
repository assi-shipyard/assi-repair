<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\PriceBook;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\Ship;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;

class ProjectActivityService
{
    private const FIELD_LABELS = [
        'project_code' => 'Kode proyek',
        'ship_id' => 'Kapal',
        'price_book_id' => 'Buku Kamus Harga',
        'project_leader_employee_id' => 'Pimpinan proyek',
        'project_ppc_employee_id' => 'PPC',
        'project_type' => 'Tipe proyek',
        'start_date_estimation' => 'Estimasi mulai',
        'end_date_estimation' => 'Estimasi selesai',
        'start_date_actual' => 'Aktual mulai',
        'end_date_actual' => 'Aktual selesai',
        'progress' => 'Progres',
        'status' => 'Status',
        'comment' => 'Catatan',
    ];

    private const STATUS_LABELS = [
        'Not Started' => 'Belum Mulai',
        'In Progress' => 'Berjalan',
        'Completed' => 'Selesai',
    ];

    /**
     * @param array<int, array{field: string, old: ?string, new: ?string}>|null $changes
     */
    public function record(Project $project, string $event, string $description, ?array $changes = null): void
    {
        ProjectActivity::query()->create([
            'project_id' => $project->id,
            'actor_id' => Auth::id(),
            'actor_name' => $this->actor_name(),
            'event' => $event,
            'description' => $description,
            'changes' => $changes,
        ]);
    }

    public function record_model_update(Project $project): void
    {
        $changes = [];

        foreach (array_keys(self::FIELD_LABELS) as $field) {
            if (! $project->wasChanged($field)) {
                continue;
            }

            $old = $this->format_value($field, $project->getOriginal($field));
            $new = $this->format_value($field, $project->getAttribute($field));

            if ($old === $new) {
                continue;
            }

            $changes[] = ['field' => self::FIELD_LABELS[$field], 'old' => $old, 'new' => $new];
        }

        if ($changes === []) {
            return;
        }

        $event = match (true) {
            $project->wasChanged('status') => 'status_changed',
            $project->wasChanged('progress') && count($changes) === 1 => 'progress_changed',
            default => 'updated',
        };

        $description = match ($event) {
            'status_changed' => 'Status proyek diubah menjadi '.$this->format_value('status', $project->status),
            'progress_changed' => 'Progres proyek diperbarui menjadi '.$this->format_value('progress', $project->progress),
            default => 'Data proyek diperbarui ('.implode(', ', array_column($changes, 'field')).')',
        };

        $this->record($project, $event, $description, $changes);
    }

    private function format_value(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field) {
            'ship_id' => Ship::query()->whereKey($value)->value('name') ?? '-',
            'price_book_id' => PriceBook::query()->whereKey($value)->value('name') ?? '-',
            'project_leader_employee_id', 'project_ppc_employee_id' => Employee::query()->whereKey($value)->value('name') ?? '-',
            'status' => self::STATUS_LABELS[$value] ?? (string) $value,
            'progress' => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.').'%',
            'start_date_estimation', 'end_date_estimation', 'start_date_actual', 'end_date_actual' => $value instanceof CarbonInterface
                ? $value->format('d/m/Y')
                : date('d/m/Y', strtotime((string) $value)),
            'comment' => mb_strimwidth((string) $value, 0, 120, '...'),
            default => (string) $value,
        };
    }

    private function actor_name(): ?string
    {
        $user_id = Auth::id();

        if ($user_id === null) {
            return null;
        }

        return Employee::query()->where('user_id', $user_id)->value('name')
            ?? Auth::user()?->employee_id;
    }
}
