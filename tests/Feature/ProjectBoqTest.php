<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use App\Models\PriceBook;
use App\Models\PriceBookItem;
use App\Models\Project;
use App\Models\project_job_group;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectJobDocument;
use App\Models\Ship;
use App\Models\User;
use App\Services\project_boq_service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectBoqTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    private PriceBook $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create(['employee_id' => '800000011', 'email' => 'boq-admin@example.test', 'password' => 'secret-pass']);
        $this->admin->assignRole(Role::findOrCreate('admin'));
        $this->actingAs($this->admin);

        $company = Company::create(['unique_id' => (string) Str::uuid(), 'name' => 'Pemilik Kapal', 'address' => 'Alamat Uji']);
        $ship = Ship::create([
            'name' => 'Kapal Uji', 'company_id' => $company->id, 'length_overall' => 80,
            'breadth' => 14, 'height' => 10, 'empty_draft' => 3, 'gross_tonnage' => 2000,
        ]);
        $division = OrganizationalUnit::create(['name' => 'Divisi Reparasi dan Rekayasa Umum', 'type' => 'division']);
        $position = Position::create(['name' => 'Pimpinan Uji', 'organizational_unit_id' => $division->id]);
        $employee = Employee::create(['employee_id' => '800000012', 'name' => 'Pegawai Uji', 'position_id' => $position->id]);
        $this->book = PriceBook::create(['code' => 'MAIN', 'name' => 'Buku Utama']);

        $this->post(route('project.store'), [
            'ship_id' => $ship->id, 'project_type' => 'Floating Repair',
            'project_leader_employee_id' => $employee->id, 'division_ids' => [$division->id],
            'status' => 'Not Started', 'progress' => 0, 'price_book_uuid' => $this->book->unique_id,
        ])->assertSessionHasNoErrors();
        $this->project = Project::firstOrFail();
    }

    private function document(string $type = 'initial_boq', string $status = 'draft'): ProjectJobDocument
    {
        return ProjectJobDocument::create(['project_id' => $this->project->id, 'document_type' => $type, 'status' => $status, 'revision_no' => 1]);
    }

    private function group(string $name = 'Lambung & Konstruksi'): project_job_group
    {
        return project_job_group::query()->whereIn('name', [$name, 'Pekerjaan '.$name])->firstOrFail();
    }

    private function item(array $attributes = []): PriceBookItem
    {
        return $this->book->items()->create([
            'group' => 'service', 'code' => 'SVC-01', 'name' => 'Crane', 'unit' => 'jam',
            'unit_price' => '500000', 'has_minimum_charge' => false, 'minimum_quantity' => null, ...$attributes,
        ]);
    }

    private function store_job(ProjectJobDocument $document, array $data): ProjectDocumentJob
    {
        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), $data)
            ->assertSessionHasNoErrors()->assertRedirect();

        return ProjectDocumentJob::query()->latest('id')->firstOrFail();
    }

    public function test_jobs_are_grouped_into_a_numbered_tree_with_totals_and_weights(): void
    {
        $document = $this->document();
        $hull = $this->group();
        $paint = $this->group('Pengecatan');

        $root = $this->store_job($document, ['job_name' => 'Ganti pelat', 'project_job_group_id' => $hull->unique_id, 'job_volume_estimated' => '10', 'unit' => 'kg', 'unit_price' => '30000']);
        $child = $this->store_job($document, ['job_name' => 'Fit up', 'parent_job_id' => $root->unique_id, 'job_volume_estimated' => '2', 'unit' => 'ls', 'unit_price' => '50000']);
        $this->store_job($document, ['job_name' => 'Cat lambung', 'project_job_group_id' => $paint->unique_id, 'job_volume_estimated' => '100', 'unit' => 'm2', 'unit_price' => '6000']);

        $this->assertSame($hull->id, $child->project_job_group_id);
        $this->assertSame('300000.00', $root->fresh()->est_price);

        $sheet = app(project_boq_service::class)->build_sheet($document->fresh());
        $this->assertSame('1000000.00', $sheet['grand_total']);
        $this->assertSame(['1', '1.1'], array_column($sheet['groups'][0]['rows'], 'number'));
        $this->assertSame('I', $sheet['groups'][0]['roman']);
        $this->assertSame('40.00', $sheet['groups'][0]['weight']);
        $this->assertSame('30.00', $root->fresh()->job_weight_percent);
        $this->assertSame('60.00', ProjectDocumentJob::where('job_name', 'Cat lambung')->value('job_weight_percent'));

        $this->get(route('project.job-document.workflow.show', [$this->project->unique_id, $document->unique_id]))
            ->assertOk()->assertSee('1.1')->assertSee('Fit up')->assertSee('1.000.000,00');
    }

    public function test_root_job_requires_group_and_depth_is_limited(): void
    {
        $document = $this->document();
        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), ['job_name' => 'Tanpa kelompok'])
            ->assertSessionHasErrors('project_job_group_id');

        $parent = $this->store_job($document, ['job_name' => 'Level 1', 'project_job_group_id' => $this->group()->unique_id]);
        foreach (range(2, project_boq_service::MAX_DEPTH) as $level) {
            $parent = $this->store_job($document, ['job_name' => 'Level '.$level, 'parent_job_id' => $parent->unique_id]);
        }

        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), ['job_name' => 'Terlalu dalam', 'parent_job_id' => $parent->unique_id])
            ->assertSessionHasErrors('parent_job_id');
    }

    public function test_price_item_snapshot_applies_minimum_charge(): void
    {
        $document = $this->document();
        $item = $this->item(['has_minimum_charge' => true, 'minimum_quantity' => '4']);

        $job = $this->store_job($document, ['job_name' => 'Sewa crane', 'project_job_group_id' => $this->group('Docking')->unique_id, 'price_book_item_id' => $item->unique_id, 'job_volume_estimated' => '1', 'unit_price' => '500000']);

        $this->assertSame($item->id, $job->price_book_item_id);
        $this->assertSame('jam', $job->unit);
        $this->assertSame('4.0000', $job->minimum_quantity);
        $this->assertSame('2000000.00', $job->est_price);

        $item->update(['minimum_quantity' => '8']);
        $this->put(route('project.job-document.workflow.job.update', [$this->project->unique_id, $document->unique_id, $job->unique_id]), [
            'job_name' => 'Sewa crane', 'project_job_group_id' => $this->group('Docking')->unique_id,
            'price_book_item_id' => $item->unique_id, 'job_volume_estimated' => '1', 'unit_price' => '500000',
        ])->assertSessionHasNoErrors();
        $this->assertSame('4.0000', $job->fresh()->minimum_quantity, 'Minimum stays snapshotted while the item is unchanged.');
    }

    public function test_price_items_are_scoped_to_the_project_book(): void
    {
        $this->item(['code' => 'OWN-1', 'name' => 'Item Proyek']);
        PriceBook::create(['code' => 'OTHER', 'name' => 'Buku Lain'])->items()->create(['group' => 'service', 'code' => 'OTH-1', 'name' => 'Item Lain', 'unit' => 'ls', 'unit_price' => '1']);

        $this->getJson(route('project.job-document.workflow.price-items', [$this->project->unique_id, 'q' => 'Item']))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.text', 'OWN-1 — Item Proyek');

        $foreign = PriceBookItem::where('code', 'OTH-1')->firstOrFail();
        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $this->document()->unique_id]), [
            'job_name' => 'Uji', 'project_job_group_id' => $this->group()->unique_id, 'price_book_item_id' => $foreign->unique_id,
        ])->assertSessionHasErrors('price_book_item_id');
    }

    public function test_plate_and_pipe_calculator_sets_volume(): void
    {
        $document = $this->document();
        $plate = $this->store_job($document, [
            'job_name' => 'Pelat', 'project_job_group_id' => $this->group()->unique_id,
            'calculation' => ['type' => 'plate', 'basis' => 'kg', 'length' => 1000, 'width' => 1000, 'thickness' => 10, 'quantity' => 2, 'density' => 7850, 'safety_factor' => 1.1],
        ]);
        $this->assertSame('172.7000', $plate->job_volume_estimated);
        $this->assertSame('kg', $plate->unit);

        $pipe = $this->store_job($document, [
            'job_name' => 'Pipa', 'project_job_group_id' => $this->group('Perpipaan')->unique_id,
            'calculation' => ['type' => 'pipe', 'basis' => 'm', 'diameter' => 60.3, 'wall_thickness' => 3.9, 'pipe_length' => 300, 'quantity' => 3],
        ]);
        $this->assertSame('1.5000', $pipe->job_volume_estimated);
        $this->assertSame(500, (int) $pipe->calculation['billed_length_mm']);

        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), [
            'job_name' => 'Pipa salah', 'project_job_group_id' => $this->group('Perpipaan')->unique_id,
            'calculation' => ['type' => 'pipe', 'basis' => 'kg', 'diameter' => 50, 'wall_thickness' => 30, 'pipe_length' => 1000, 'quantity' => 1],
        ])->assertSessionHasErrors('calculation.wall_thickness');
    }

    public function test_materials_and_cascading_delete(): void
    {
        $document = $this->document();
        $root = $this->store_job($document, ['job_name' => 'Induk', 'project_job_group_id' => $this->group()->unique_id, 'job_volume_estimated' => '1', 'unit_price' => '100']);
        $child = $this->store_job($document, ['job_name' => 'Anak', 'parent_job_id' => $root->unique_id]);
        $base = [$this->project->unique_id, $document->unique_id, $child->unique_id];

        $this->post(route('project.job-document.workflow.material.store', $base), ['material_name' => 'Elektroda', 'volume' => '3', 'unit' => 'kg', 'unit_price' => '100'])
            ->assertSessionHasNoErrors();
        $material = $child->materials()->firstOrFail();
        $this->assertSame('300.00', $material->price);
        $this->assertSame('25.00', $root->fresh()->job_weight_percent);

        $this->put(route('project.job-document.workflow.material.update', [...$base, $material->unique_id]), ['material_name' => 'Elektroda', 'volume' => '1', 'unit_price' => '100'])
            ->assertSessionHasNoErrors();
        $this->assertSame('100.00', $material->fresh()->price);

        $this->delete(route('project.job-document.workflow.job.destroy', [$this->project->unique_id, $document->unique_id, $root->unique_id]))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('project_document_jobs', 0);
        $this->assertDatabaseCount('project_document_job_materials', 0);
    }

    public function test_next_phase_copies_tree_and_revision_remaps_parents(): void
    {
        $repair_list = $this->document('repair_list');
        $root = $this->store_job($repair_list, ['job_name' => 'Induk', 'project_job_group_id' => $this->group()->unique_id]);
        $this->store_job($repair_list, ['job_name' => 'Anak', 'parent_job_id' => $root->unique_id]);

        $this->post(route('project.job-document.workflow.store', $this->project->unique_id), ['document_type' => 'initial_boq', 'copy_previous' => '1'])
            ->assertSessionHasNoErrors();
        $boq = ProjectJobDocument::where('document_type', 'initial_boq')->firstOrFail();
        $copied_root = $boq->jobs()->where('job_name', 'Induk')->firstOrFail();
        $copied_child = $boq->jobs()->where('job_name', 'Anak')->firstOrFail();
        $this->assertSame($copied_root->id, $copied_child->parent_job_id);
        $this->assertSame($root->project_job_group_id, $copied_root->project_job_group_id);

        $revision = ProjectJobDocument::cloneWithChildren($boq, ['revision_no' => 2, 'status' => 'draft']);
        $revision_root = $revision->jobs()->where('job_name', 'Induk')->firstOrFail();
        $this->assertSame($revision_root->id, $revision->jobs()->where('job_name', 'Anak')->value('parent_job_id'));
    }

    public function test_export_downloads_pdf(): void
    {
        $document = $this->document();
        $this->store_job($document, ['job_name' => 'Pekerjaan', 'project_job_group_id' => $this->group()->unique_id, 'job_volume_estimated' => '1', 'unit_price' => '1000']);

        $response = $this->get(route('project.job-document.workflow.export', [$this->project->unique_id, $document->unique_id]));
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_user_without_permission_and_locked_document_cannot_edit(): void
    {
        $document = $this->document();
        $payload = ['job_name' => 'Uji', 'project_job_group_id' => $this->group()->unique_id];

        $this->actingAs(User::create(['employee_id' => '800000013', 'email' => 'boq-guest@example.test', 'password' => 'secret-pass']));
        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), $payload)->assertForbidden();

        $this->actingAs($this->admin);
        $locked = $this->document('final_boq', 'approved');
        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $locked->unique_id]), $payload)
            ->assertSessionHas('error');
        $this->assertDatabaseCount('project_document_jobs', 0);
    }

    public function test_admin_manages_job_groups_and_used_group_cannot_be_deleted(): void
    {
        $this->get(route('job-group.index'))->assertOk()->assertSee('Pengecatan');
        $this->post(route('job-group.store'), ['name' => 'Akomodasi', 'sort_order' => 90, 'is_active' => 1])->assertSessionHasNoErrors();
        $this->post(route('job-group.store'), ['name' => 'Akomodasi', 'sort_order' => 91])->assertSessionHasErrors('name');

        $group = $this->group('Akomodasi');
        $this->store_job($this->document(), ['job_name' => 'Kamar', 'project_job_group_id' => $group->unique_id]);
        $this->delete(route('job-group.destroy', $group))->assertSessionHas('error');
        $this->assertDatabaseHas('project_job_groups', ['id' => $group->id]);
    }
}
