<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\ReturnProgressReportRequest;
use App\Http\Requests\Project\SaveProgressEntriesRequest;
use App\Http\Requests\Project\StoreProgressReportRequest;
use App\Models\Project;
use App\Models\ProjectProgressReport;
use App\Services\project_progress_service;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgressReportController extends Controller
{
    public function __construct(private readonly project_progress_service $progress, private readonly Guard $auth) {}

    public function store(StoreProgressReportRequest $request, string $project_id): RedirectResponse
    {
        $project = $this->reportable_project($project_id);
        $report = $this->progress->create_report($project, $request->validated('report_date'), $request->validated('notes'), $this->auth->id());

        return $this->to_report($project, $report, 'Laporan progres harian berhasil dibuat. Isi progres pekerjaan lalu ajukan persetujuan.');
    }

    public function show(string $project_id, string $report_id): View
    {
        $project = $this->project($project_id)->load(['ship', 'leader:id,name', 'ppc:id,name']);
        $report = $this->report($project, $report_id)->load(['entries', 'leader_approved_by:id,name', 'ppc_approved_by:id,name', 'created_by:id,name']);
        $document = $this->progress->current_document($project);
        abort_if($document === null, 404);

        return view('project.progress-report.show', [
            'project' => $project,
            'report' => $report,
            'document' => $document,
            'rows' => $this->progress->leaf_rows($document),
            'entries' => $report->entries->keyBy('job_lineage_uuid'),
            'can_edit' => $report->is_editable() && $this->progress->can_report($this->auth->user(), $project),
            'approver_slots' => $this->progress->approver_slots($this->auth->user(), $project),
        ]);
    }

    public function update(SaveProgressEntriesRequest $request, string $project_id, string $report_id): RedirectResponse
    {
        $project = $this->reportable_project($project_id);
        $report = $this->report($project, $report_id);
        $this->progress->save_entries($report, $request->validated('entries') ?? [], $request->validated('notes'), $this->auth->id());

        if ($request->validated('action') === 'submit') {
            $this->progress->submit($report, $this->auth->id());

            return $this->to_report($project, $report, 'Laporan progres diajukan ke PIMPRO Reparasi dan PPC.');
        }

        return $this->to_report($project, $report, 'Progres pekerjaan berhasil disimpan.');
    }

    public function approve(string $project_id, string $report_id): RedirectResponse
    {
        $project = $this->project($project_id);
        $report = $this->report($project, $report_id);
        $completed = $this->progress->approve($report, $this->auth->user());

        return $this->to_report($project, $report, $completed
            ? 'Laporan progres disetujui PIMPRO Reparasi dan PPC. Progres proyek telah diperbarui.'
            : 'Persetujuan Anda tersimpan. Menunggu persetujuan pihak lainnya.');
    }

    public function return(ReturnProgressReportRequest $request, string $project_id, string $report_id): RedirectResponse
    {
        $project = $this->project($project_id);
        $report = $this->report($project, $report_id);
        $this->progress->return_report($report, $this->auth->user(), $request->validated('reason'));

        return $this->to_report($project, $report, 'Laporan progres dikembalikan untuk diperbaiki.');
    }

    public function destroy(string $project_id, string $report_id): RedirectResponse
    {
        $project = $this->reportable_project($project_id);
        $report = $this->report($project, $report_id);
        $this->progress->delete_report($report, $this->auth->id());
        $document = $this->progress->current_document($project);

        return redirect()->route('project.job-document.workflow.show', [$project->unique_id, $document?->unique_id, 'tab' => 'progress'])
            ->with('success', 'Laporan progres berhasil dihapus.');
    }

    private function project(string $project_id): Project
    {
        return Project::query()->where('unique_id', $project_id)->firstOrFail();
    }

    private function reportable_project(string $project_id): Project
    {
        $project = $this->project($project_id);
        abort_unless($this->progress->can_report($this->auth->user(), $project), 403);

        return $project;
    }

    private function report(Project $project, string $report_id): ProjectProgressReport
    {
        return ProjectProgressReport::query()->where('project_id', $project->id)->where('unique_id', $report_id)->firstOrFail();
    }

    private function to_report(Project $project, ProjectProgressReport $report, string $message): RedirectResponse
    {
        return redirect()->route('project.progress-report.show', [$project->unique_id, $report->unique_id])->with('success', $message);
    }
}
