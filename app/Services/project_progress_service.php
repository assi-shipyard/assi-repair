<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectProgressEntry;
use App\Models\ProjectProgressReport;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectJobDocument;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Daily progress reports are append-only: each approved report stores the cumulative % per job lineage,
// so the S-curve can replay history with both the weights reported at the time and the current weights.
final class project_progress_service
{
    public const TIMEZONE = 'Asia/Jakarta';

    public function __construct(private readonly project_boq_service $boq, private readonly progress_rollup_service $rollup) {}

    public function current_document(Project $project): ?ProjectJobDocument
    {
        return ProjectJobDocument::query()
            ->where('project_id', $project->id)
            ->where('document_type', ProjectJobDocument::TYPE_SATISFACTION_NOTES)
            ->orderByDesc('revision_no')
            ->first();
    }

    public function reports(Project $project): EloquentCollection
    {
        return ProjectProgressReport::query()
            ->where('project_id', $project->id)
            ->with(['leader_approved_by:id,name', 'ppc_approved_by:id,name'])
            ->withCount('entries')
            ->orderByDesc('report_date')
            ->get();
    }

    public function open_report(Project $project): ?ProjectProgressReport
    {
        return ProjectProgressReport::query()->where('project_id', $project->id)->where('status', '!=', 'approved')->first();
    }

    public function can_report(?User $user, Project $project): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->hasRole('admin') || $user->can('edit-project-satisfaction-note') || in_array(true, $this->approver_slots($user, $project), true);
    }

    /** @return array{leader: bool, ppc: bool} */
    public function approver_slots(?User $user, Project $project): array
    {
        $employee_id = $user === null ? null : Employee::query()->where('user_id', $user->id)->value('id');

        return [
            'leader' => $employee_id !== null && $employee_id === $project->project_leader_employee_id,
            'ppc' => $employee_id !== null && $employee_id === $project->project_ppc_employee_id,
        ];
    }

    public function create_report(Project $project, string $report_date, ?string $notes, ?int $actor_id): ProjectProgressReport
    {
        return DB::transaction(function () use ($project, $report_date, $notes, $actor_id): ProjectProgressReport {
            Project::query()->whereKey($project->id)->lockForUpdate()->first();
            $document = $this->current_document($project)
                ?? throw ValidationException::withMessages(['report_date' => 'Satisfaction Notes proyek ini belum dibuat.']);

            if ($this->open_report($project) !== null) {
                throw ValidationException::withMessages(['report_date' => 'Masih ada laporan progres yang belum disetujui. Selesaikan laporan tersebut terlebih dahulu.']);
            }

            $last_date = ProjectProgressReport::query()->where('project_id', $project->id)->max('report_date');
            if ($last_date !== null && $report_date <= substr((string) $last_date, 0, 10)) {
                throw ValidationException::withMessages([
                    'report_date' => 'Tanggal laporan harus setelah laporan terakhir ('.CarbonImmutable::parse($last_date)->tanggal_indonesia().').',
                ]);
            }

            $report = ProjectProgressReport::query()->create([
                'project_id' => $project->id,
                'project_job_document_id' => $document->id,
                'report_date' => $report_date,
                'status' => 'draft',
                'notes' => $notes,
                'created_by_user_id' => $actor_id,
            ]);

            $this->record($document, 'progress_report_created', $report, [], $actor_id);

            return $report;
        });
    }

    /**
     * Leaf jobs of the current Satisfaction Notes revision, in sheet order, with their last approved progress.
     *
     * @return list<array{job: ProjectDocumentJob, number: string, group: string, depth: int, parents: string, weight: string, previous: string}>
     */
    public function leaf_rows(ProjectJobDocument $document): array
    {
        $sheet = $this->boq->build_sheet($document);
        $rows = [];

        foreach ($sheet['groups'] as $group) {
            $trail = [];
            foreach ($group['rows'] as $row) {
                $trail[$row['depth']] = $row['job']->job_name;
                if ($row['has_children']) {
                    continue;
                }

                $rows[] = [
                    'job' => $row['job'],
                    'number' => $group['roman'].'.'.$row['number'],
                    'group' => $group['label'],
                    'depth' => $row['depth'],
                    'parents' => implode(' › ', array_slice($trail, 0, $row['depth'])),
                    'weight' => $row['progress']['weight'] ?? '0',
                    'previous' => bcadd((string) ($row['job']->progress_percent ?? '0'), '0', 2),
                ];
            }
        }

        return $rows;
    }

    /** @param array<string, array{progress_percent?: mixed, notes?: ?string}> $entries keyed by job lineage */
    public function save_entries(ProjectProgressReport $report, array $entries, ?string $notes, ?int $actor_id): void
    {
        DB::transaction(function () use ($report, $entries, $notes, $actor_id): void {
            $report = ProjectProgressReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->ensure_editable($report);
            $document = $this->current_document(Project::query()->findOrFail($report->project_id));
            $leaves = collect($this->leaf_rows($document))->keyBy(fn (array $row): string => $row['job']->lineage_uuid);
            $records = [];
            $errors = [];

            foreach ($entries as $lineage => $entry) {
                $percent = $entry['progress_percent'] ?? null;
                if ($percent === null || $percent === '') {
                    continue;
                }

                $row = $leaves->get($lineage);
                if ($row === null) {
                    $errors['entries'] = 'Sebagian pekerjaan sudah tidak ada atau memiliki sub pekerjaan. Muat ulang halaman.';

                    continue;
                }

                $percent = bcadd((string) $percent, '0', 2);
                if (bccomp($percent, $row['previous'], 2) === 0) {
                    continue;
                }

                $entry_notes = trim((string) ($entry['notes'] ?? '')) ?: null;
                if (bccomp($percent, $row['previous'], 2) < 0 && $entry_notes === null) {
                    $errors['entries.'.$lineage.'.notes'] = 'Catatan wajib diisi jika progres '.$row['job']->job_name.' turun.';

                    continue;
                }

                $records[] = [
                    'project_document_job_id' => $row['job']->id,
                    'job_lineage_uuid' => $lineage,
                    'job_name' => $row['job']->job_name,
                    'previous_percent' => $row['previous'],
                    'progress_percent' => $percent,
                    'weight_percent' => $row['weight'],
                    'notes' => $entry_notes,
                ];
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            $report->entries()->delete();
            foreach ($records as $record) {
                $report->entries()->create($record);
            }

            $report->update(['notes' => $notes, 'project_job_document_id' => $document->id]);
            $this->record($document, 'progress_report_saved', $report, ['entry_count' => count($records)], $actor_id);
        });
    }

    public function submit(ProjectProgressReport $report, ?int $actor_id): void
    {
        DB::transaction(function () use ($report, $actor_id): void {
            $report = ProjectProgressReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->ensure_editable($report);

            if (! $report->entries()->exists()) {
                throw ValidationException::withMessages(['entries' => 'Isi minimal satu progres pekerjaan sebelum mengajukan laporan.']);
            }

            $report->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by_user_id' => $actor_id,
                'leader_approved_by_employee_id' => null,
                'leader_approved_at' => null,
                'ppc_approved_by_employee_id' => null,
                'ppc_approved_at' => null,
            ]);

            $this->record($report->document, 'progress_report_submitted', $report, [], $actor_id);
        });
    }

    // PIMPRO and PPC sign separately; the report only counts once both slots are filled.
    public function approve(ProjectProgressReport $report, User $user): bool
    {
        return DB::transaction(function () use ($report, $user): bool {
            $report = ProjectProgressReport::query()->lockForUpdate()->findOrFail($report->id);
            $project = Project::query()->lockForUpdate()->findOrFail($report->project_id);
            $slots = $this->approver_slots($user, $project);

            if (! in_array(true, $slots, true)) {
                throw new AuthorizationException('Hanya PIMPRO atau PPC proyek yang dapat menyetujui laporan progres.');
            }

            if ($report->status !== 'submitted') {
                throw ValidationException::withMessages(['report' => 'Hanya laporan berstatus Menunggu Persetujuan yang dapat disetujui.']);
            }

            $employee_id = Employee::query()->where('user_id', $user->id)->value('id');
            $signed = [];
            foreach (['leader', 'ppc'] as $slot) {
                if ($slots[$slot] && $report->{$slot.'_approved_by_employee_id'} === null) {
                    $report->{$slot.'_approved_by_employee_id'} = $employee_id;
                    $report->{$slot.'_approved_at'} = now();
                    $signed[] = $slot;
                }
            }

            if ($signed === []) {
                throw ValidationException::withMessages(['report' => 'Anda sudah menyetujui laporan ini.']);
            }

            $report->save();
            $this->record($report->document, 'progress_report_signed', $report, ['slots' => $signed], $user->id);

            if ($report->leader_approved_by_employee_id === null || $report->ppc_approved_by_employee_id === null) {
                return false;
            }

            $this->apply($report, $project, $user->id);

            return true;
        });
    }

    public function return_report(ProjectProgressReport $report, User $user, string $reason): void
    {
        DB::transaction(function () use ($report, $user, $reason): void {
            $report = ProjectProgressReport::query()->lockForUpdate()->findOrFail($report->id);
            $project = Project::query()->findOrFail($report->project_id);

            if (! in_array(true, $this->approver_slots($user, $project), true)) {
                throw new AuthorizationException('Hanya PIMPRO atau PPC proyek yang dapat mengembalikan laporan progres.');
            }

            if ($report->status !== 'submitted') {
                throw ValidationException::withMessages(['report' => 'Hanya laporan berstatus Menunggu Persetujuan yang dapat dikembalikan.']);
            }

            $report->update([
                'status' => 'returned',
                'returned_by_user_id' => $user->id,
                'returned_at' => now(),
                'return_reason' => $reason,
                'leader_approved_by_employee_id' => null,
                'leader_approved_at' => null,
                'ppc_approved_by_employee_id' => null,
                'ppc_approved_at' => null,
            ]);

            $this->record($report->document, 'progress_report_returned', $report, ['reason' => $reason], $user->id);
        });
    }

    public function delete_report(ProjectProgressReport $report, ?int $actor_id): void
    {
        DB::transaction(function () use ($report, $actor_id): void {
            $report = ProjectProgressReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->ensure_editable($report);

            $this->record($report->document, 'progress_report_deleted', $report, [], $actor_id);
            $report->delete();
        });
    }

    // Approved values land on the latest Satisfaction Notes revision, matched by lineage so a revision
    // created while the report was pending still receives the progress.
    private function apply(ProjectProgressReport $report, Project $project, int $actor_id): void
    {
        $document = $this->current_document($project);
        $report_date = $report->report_date->toDateString();
        $jobs = ProjectDocumentJob::query()->where('project_job_document_id', $document->id)->get();
        $parent_ids = $jobs->pluck('parent_job_id')->filter()->flip();
        $leaves = $jobs->reject(fn (ProjectDocumentJob $job): bool => $parent_ids->has($job->id))->keyBy('lineage_uuid');

        foreach ($report->entries()->get() as $entry) {
            /** @var project_progress_entry $entry */
            $job = $leaves->get($entry->job_lineage_uuid);
            if ($job === null) {
                continue;
            }

            $percent = bcadd((string) $entry->progress_percent, '0', 2);
            $job->progress_percent = $percent;
            $job->status = $this->rollup->status_for($percent);
            $job->actual_start_date = bccomp($percent, '0', 2) > 0 ? ($job->actual_start_date ?? $report_date) : null;
            $job->actual_finish_date = $job->status === 'completed' ? ($job->actual_finish_date ?? $report_date) : null;
            $job->save();
        }

        $this->boq->sync_rollup($document);
        $progress = $this->boq->document_progress($document);

        $report->update([
            'status' => 'approved',
            'approved_at' => now(),
            'reported_progress' => $progress,
            'project_job_document_id' => $document->id,
        ]);

        $project->progress = $progress ?? '0';
        $project->save();

        $this->record($document, 'progress_report_approved', $report, ['reported_progress' => $progress], $actor_id);
    }

    private function ensure_editable(ProjectProgressReport $report): void
    {
        if (! $report->is_editable()) {
            throw ValidationException::withMessages(['report' => 'Laporan yang sudah diajukan atau disetujui tidak dapat diubah.']);
        }
    }

    private function record(?ProjectJobDocument $document, string $action, ProjectProgressReport $report, array $payload, ?int $actor_id): void
    {
        $document?->histories()->create([
            'action' => $action,
            'actor_id' => $actor_id,
            'payload' => ['report_id' => $report->unique_id, 'report_date' => $report->report_date?->toDateString(), ...$payload],
        ]);
    }
}
