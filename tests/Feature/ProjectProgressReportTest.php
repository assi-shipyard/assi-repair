<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\PriceBook;
use App\Models\Project;
use App\Models\ProjectJobGroup;
use App\Models\ProjectProgressReport;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectJobDocument;
use App\Models\Ship;
use App\Models\User;
use App\Services\project_boq_service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectProgressReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $leader_user;

    private User $ppc_user;

    private Project $project;

    private ProjectJobDocument $notes;

    /** @var array<string, ProjectDocumentJob> */
    private array $jobs = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create(['employee_id' => '800000021', 'email' => 'progress-admin@example.test', 'password' => 'secret-pass']);
        $this->admin->assignRole(Role::findOrCreate('admin'));
        $this->actingAs($this->admin);

        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Pemilik Kapal', 'address' => 'Alamat Uji']);
        $ship = Ship::create([
            'name' => 'Kapal Progres', 'company_id' => $company->id, 'length_overall' => 80,
            'breadth' => 14, 'height' => 10, 'empty_draft' => 3, 'gross_tonnage' => 2000,
        ]);
        $division = OrganizationalUnit::create(['name' => 'Divisi Reparasi dan Rekayasa Umum', 'type' => 'division']);
        $position = Position::create(['name' => 'Pimpinan Uji', 'organizational_unit_id' => $division->id]);

        $this->leader_user = User::create(['employee_id' => '800000022', 'email' => 'pimpro@example.test', 'password' => 'secret-pass']);
        $this->ppc_user = User::create(['employee_id' => '800000023', 'email' => 'ppc@example.test', 'password' => 'secret-pass']);
        $leader = Employee::create(['employee_id' => '800000022', 'name' => 'PIMPRO Uji', 'position_id' => $position->id, 'user_id' => $this->leader_user->id]);
        $ppc = Employee::create(['employee_id' => '800000023', 'name' => 'PPC Uji', 'position_id' => $position->id, 'user_id' => $this->ppc_user->id]);
        $book = PriceBook::create(['code' => 'MAIN', 'name' => 'Buku Utama']);

        $this->post(route('project.store'), [
            'ship_id' => $ship->id, 'project_type' => 'Floating Repair',
            'project_leader_employee_id' => $leader->id, 'division_ids' => [$division->id],
            'status' => 'Not Started', 'progress' => 0, 'price_book_uuid' => $book->unique_id,
        ])->assertSessionHasNoErrors();
        $this->project = Project::firstOrFail();
        $this->project->forceFill(['project_ppc_employee_id' => $ppc->id])->save();

        // Root "Pelat" (no own price) has two priced children; "Cat" is a priced leaf with a material that must not affect weights.
        $this->notes = ProjectJobDocument::create(['project_id' => $this->project->id, 'document_type' => 'satisfaction_notes', 'status' => 'draft', 'revision_no' => 1]);
        $hull = $this->group();
        $this->jobs['pelat'] = $this->store_job(['job_name' => 'Pelat', 'project_job_group_id' => $hull->unique_id]);
        $this->jobs['potong'] = $this->store_job(['job_name' => 'Potong', 'parent_job_id' => $this->jobs['pelat']->unique_id, 'job_volume_estimated' => '1', 'unit' => 'ls', 'unit_price' => '200000']);
        $this->jobs['las'] = $this->store_job(['job_name' => 'Las', 'parent_job_id' => $this->jobs['pelat']->unique_id, 'job_volume_estimated' => '1', 'unit' => 'ls', 'unit_price' => '600000']);
        $this->jobs['cat'] = $this->store_job(['job_name' => 'Cat', 'project_job_group_id' => $this->group('Pengecatan')->unique_id, 'job_volume_estimated' => '1', 'unit' => 'ls', 'unit_price' => '200000']);
        $this->post(route('project.job-document.workflow.material.store', [$this->project->unique_id, $this->notes->unique_id, $this->jobs['cat']->unique_id]), [
            'material_name' => 'Cat epoxy', 'volume' => '10', 'unit' => 'ltr', 'unit_price' => '1000000',
        ])->assertSessionHasNoErrors();
    }

    private function group(string $name = 'Lambung & Konstruksi'): ProjectJobGroup
    {
        return ProjectJobGroup::query()->whereIn('name', [$name, 'Pekerjaan '.$name])->firstOrFail();
    }

    private function store_job(array $data): ProjectDocumentJob
    {
        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $this->notes->unique_id]), $data)
            ->assertSessionHasNoErrors()->assertRedirect();

        return ProjectDocumentJob::query()->latest('id')->firstOrFail();
    }

    private function create_report(string $date): ProjectProgressReport
    {
        $this->post(route('project.progress-report.store', $this->project->unique_id), ['report_date' => $date])->assertSessionHasNoErrors();

        return ProjectProgressReport::query()->latest('id')->firstOrFail();
    }

    private function save(ProjectProgressReport $report, array $values, string $action = 'save'): TestResponse
    {
        $entries = [];
        foreach ($values as $key => $value) {
            $entries[$this->jobs[$key]->fresh()->lineage_uuid] = is_array($value) ? $value : ['progress_percent' => $value];
        }

        return $this->put(route('project.progress-report.update', [$this->project->unique_id, $report->unique_id]), ['entries' => $entries, 'action' => $action]);
    }

    private function approve_as(User $user, ProjectProgressReport $report): TestResponse
    {
        return $this->actingAs($user)->post(route('project.progress-report.approve', [$this->project->unique_id, $report->unique_id]));
    }

    public function test_weights_use_job_lines_only_and_parents_roll_up(): void
    {
        $this->assertSame('20.00', $this->jobs['potong']->fresh()->job_weight_percent);
        $this->assertSame('60.00', $this->jobs['las']->fresh()->job_weight_percent);
        $this->assertSame('20.00', $this->jobs['cat']->fresh()->job_weight_percent);

        ProjectDocumentJob::whereKey($this->jobs['potong']->id)->update(['progress_percent' => 100, 'status' => 'completed']);
        ProjectDocumentJob::whereKey($this->jobs['las']->id)->update(['progress_percent' => 50, 'status' => 'ongoing']);

        $sheet = app(project_boq_service::class)->build_sheet($this->notes->fresh());
        $parent = $sheet['groups'][0]['rows'][0]['progress'];

        $this->assertSame('80.0000', $parent['weight']);
        $this->assertSame('62.50', $parent['actual']);
        $this->assertSame('50.0000', $parent['weighted']);
        $this->assertSame('ongoing', $parent['status']);
        $this->assertSame('50.00', $sheet['progress']);
        $this->assertSame('80.00', $sheet['groups'][0]['weight']);
    }

    public function test_daily_report_requires_both_approvals_before_progress_applies(): void
    {
        $report = $this->create_report('2026-01-05');
        $this->save($report, ['potong' => '100', 'las' => '50'], 'submit')->assertSessionHasNoErrors();
        $this->assertSame('submitted', $report->fresh()->status);
        $this->assertSame(2, $report->entries()->count());

        $this->approve_as($this->leader_user, $report)->assertSessionHasNoErrors();
        $this->assertNotNull($report->fresh()->leader_approved_at);
        $this->assertSame('submitted', $report->fresh()->status);
        $this->assertNull($this->jobs['las']->fresh()->progress_percent);

        $this->approve_as($this->ppc_user, $report)->assertSessionHasNoErrors();
        $report->refresh();

        $this->assertSame('approved', $report->status);
        $this->assertSame('50.00', $report->reported_progress);
        $this->assertSame('50.00', $this->project->fresh()->progress);

        $potong = $this->jobs['potong']->fresh();
        $this->assertSame(['100.00', 'completed', '2026-01-05', '2026-01-05'], [$potong->progress_percent, $potong->status, $potong->actual_start_date->toDateString(), $potong->actual_finish_date->toDateString()]);
        $this->assertSame(['62.50', 'ongoing'], [$this->jobs['pelat']->fresh()->progress_percent, $this->jobs['pelat']->fresh()->status]);
        $this->assertTrue($this->notes->histories()->where('action', 'progress_report_approved')->exists());

        $this->actingAs($this->admin)->get(route('project.job-document.workflow.show', [$this->project->unique_id, $this->notes->unique_id]))
            ->assertOk()->assertSee('Progres Harian')->assertSee('5 Januari 2026')->assertSee('62,50%');
        $this->get(route('project.progress-report.show', [$this->project->unique_id, $report->unique_id]))->assertOk()->assertSee('Disetujui');
    }

    public function test_report_rules_for_dates_open_reports_and_decreases(): void
    {
        $this->post(route('project.progress-report.store', $this->project->unique_id), ['report_date' => now('Asia/Jakarta')->addDays(2)->toDateString()])
            ->assertSessionHasErrors('report_date');

        $report = $this->create_report('2026-01-05');
        $this->post(route('project.progress-report.store', $this->project->unique_id), ['report_date' => '2026-01-06'])->assertSessionHasErrors('report_date');

        $this->put(route('project.progress-report.update', [$this->project->unique_id, $report->unique_id]), ['action' => 'submit'])->assertSessionHasErrors('entries');
        $this->save($report, ['potong' => '40'], 'submit')->assertSessionHasNoErrors();
        $this->approve_as($this->leader_user, $report);
        $this->approve_as($this->ppc_user, $report);

        $this->actingAs($this->admin);
        $this->post(route('project.progress-report.store', $this->project->unique_id), ['report_date' => '2026-01-05'])->assertSessionHasErrors('report_date');

        $next = $this->create_report('2026-01-06');
        $lineage = $this->jobs['potong']->fresh()->lineage_uuid;
        $this->save($next, ['potong' => '30'])->assertSessionHasErrors('entries.'.$lineage.'.notes');
        $this->save($next, ['potong' => ['progress_percent' => '30', 'notes' => 'Koreksi pengukuran']])->assertSessionHasNoErrors();
        $this->save($next, ['potong' => '40', 'las' => '0'])->assertSessionHasNoErrors();
        $this->assertSame(0, $next->entries()->count());

        // Parent rows are rolled up and can never be reported directly.
        $this->put(route('project.progress-report.update', [$this->project->unique_id, $next->unique_id]), [
            'entries' => [$this->jobs['pelat']->fresh()->lineage_uuid => ['progress_percent' => '10']],
        ])->assertSessionHasErrors('entries');
    }

    public function test_only_assigned_approvers_can_approve_or_return_and_returned_reports_are_editable(): void
    {
        $report = $this->create_report('2026-01-05');
        $this->save($report, ['cat' => '100'], 'submit');

        $this->post(route('project.progress-report.approve', [$this->project->unique_id, $report->unique_id]))->assertForbidden();
        $this->save($report, ['cat' => '90'])->assertSessionHasErrors('report');

        $this->approve_as($this->leader_user, $report);
        $this->actingAs($this->ppc_user)->post(route('project.progress-report.return', [$this->project->unique_id, $report->unique_id]), ['reason' => 'Foto belum ada'])
            ->assertSessionHasNoErrors();

        $report->refresh();
        $this->assertSame('returned', $report->status);
        $this->assertNull($report->leader_approved_at);
        $this->assertNull($this->jobs['cat']->fresh()->progress_percent);

        $this->actingAs($this->leader_user);
        $this->save($report, ['cat' => '90'])->assertSessionHasNoErrors();
        $this->delete(route('project.progress-report.destroy', [$this->project->unique_id, $report->unique_id]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('project_progress_reports', 0);

        $outsider = User::create(['employee_id' => '800000029', 'email' => 'outsider@example.test', 'password' => 'secret-pass']);
        $this->actingAs($outsider)->post(route('project.progress-report.store', $this->project->unique_id), ['report_date' => '2026-01-05'])->assertForbidden();
    }

    public function test_manual_progress_edits_are_ignored_and_finalize_is_blocked_by_open_report(): void
    {
        $this->put(route('project.job-document.workflow.job.update', [$this->project->unique_id, $this->notes->unique_id, $this->jobs['cat']->unique_id]), [
            'job_name' => 'Cat', 'project_job_group_id' => $this->group('Pengecatan')->unique_id, 'job_volume_estimated' => '1', 'unit' => 'ls',
            'unit_price' => '200000', 'progress_percent' => '100', 'status' => 'completed',
        ])->assertSessionHasNoErrors();
        $this->assertNull($this->jobs['cat']->fresh()->progress_percent);

        $this->create_report('2026-01-05');
        ProjectDocumentJob::where('project_job_document_id', $this->notes->id)->update(['progress_percent' => 100, 'status' => 'completed']);

        $this->post(route('project.job-document.workflow.finalize-satisfaction-notes', [$this->project->unique_id, $this->notes->unique_id]))
            ->assertSessionHas('error');
        $this->assertSame('draft', $this->notes->fresh()->status);
    }

    public function test_approved_progress_follows_lineage_into_new_revision(): void
    {
        $report = $this->create_report('2026-01-05');
        $this->save($report, ['las' => '50'], 'submit');

        $revision = ProjectJobDocument::cloneWithChildren($this->notes, ['revision_no' => 2, 'status' => 'draft']);
        $this->approve_as($this->leader_user, $report);
        $this->approve_as($this->ppc_user, $report);

        $copied = ProjectDocumentJob::where('project_job_document_id', $revision->id)->where('lineage_uuid', $this->jobs['las']->lineage_uuid)->firstOrFail();
        $this->assertSame('50.00', $copied->progress_percent);
        $this->assertNull($this->jobs['las']->fresh()->progress_percent);
        $this->assertSame($revision->id, $report->fresh()->project_job_document_id);
    }
}
