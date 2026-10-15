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
use App\Models\ProjectJobGroup;
use App\Models\ProjectDocumentJob;
use App\Models\ProjectJobDocument;
use App\Models\Ship;
use App\Models\User;
use App\Services\project_boq_service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Database\Seeders\RolePermissionSeeder;
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

    private function group(string $name = 'Lambung & Konstruksi'): ProjectJobGroup
    {
        return ProjectJobGroup::query()->whereIn('name', [$name, 'Pekerjaan '.$name])->firstOrFail();
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
        $this->assertSame('1.0000', $job->fresh()->job_volume_estimated);
        $this->assertSame('4.0000', $job->fresh()->billed_quantity);
        $this->assertSame('2000000.00', $job->fresh()->est_price);

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

    public function test_plate_rule_converts_dimensions_to_kg_and_applies_minimum_per_piece(): void
    {
        $document = $this->document();
        $item = $this->item(['code' => 'PLT', 'name' => 'Pelat', 'unit' => 'kg', 'unit_price' => '1000', 'calculation_rule' => 'plate', 'has_minimum_charge' => true, 'minimum_quantity' => '15']);
        $plate = fn (array $calculation): array => [
            'job_name' => 'Pelat', 'project_job_group_id' => $this->group()->unique_id, 'price_book_item_id' => $item->unique_id,
            'calculation' => ['type' => 'plate', 'material' => 'steel', 'quantity' => 1, 'safety_factor' => 1, ...$calculation],
        ];

        $small = $this->store_job($document, $plate(['length' => 300, 'width' => 200, 'thickness' => 8, 'quantity' => 3]));
        $large = $this->store_job($document, $plate(['length' => 1000, 'width' => 1000, 'thickness' => 10, 'quantity' => 2, 'safety_factor' => 1.1]));
        $aluminium = $this->store_job($document, $plate(['material' => 'aluminium', 'length' => 1000, 'width' => 1000, 'thickness' => 10]));

        $this->assertSame('plate', $small->fresh()->calculation_rule);
        $this->assertSame('11.3040', $small->fresh()->job_volume_estimated);
        $this->assertSame('45.0000', $small->fresh()->billed_quantity, '3 pieces of 3,768 kg are each billed at the 15 kg minimum.');
        $this->assertSame('45000.00', $small->fresh()->est_price);
        $this->assertSame('172.7000', $large->fresh()->billed_quantity);
        $this->assertSame('27.0000', $aluminium->fresh()->billed_quantity);
        $this->assertSame('kg', $aluminium->fresh()->unit);

        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), [
            'job_name' => 'Tanpa kalkulator', 'project_job_group_id' => $this->group()->unique_id, 'price_book_item_id' => $item->unique_id, 'job_volume_estimated' => '10',
        ])->assertSessionHasErrors('calculation');

        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), $plate(['material' => 'gold', 'length' => 1, 'width' => 1, 'thickness' => 1, 'safety_factor' => 5]))
            ->assertSessionHasErrors(['calculation.material', 'calculation.safety_factor']);
    }

    public function test_pipe_rule_bills_short_pieces_in_steps(): void
    {
        $document = $this->document();
        $item = $this->item(['code' => 'PIP', 'name' => 'Pipa', 'unit' => 'm', 'unit_price' => '100', 'calculation_rule' => 'pipe']);
        $billed = [];

        foreach (['0.3' => '1.0000', '0.5' => '1.0000', '0.75' => '2.0000', '1.2' => '2.4000'] as $length => $expected) {
            $job = $this->store_job($document, [
                'job_name' => 'Pipa '.$length, 'project_job_group_id' => $this->group('Perpipaan')->unique_id, 'price_book_item_id' => $item->unique_id,
                'calculation' => ['type' => 'pipe', 'size' => '2 inci', 'length_m' => $length, 'quantity' => 2],
            ]);
            $billed[$length] = [$expected, $job];
        }

        foreach ($billed as $length => [$expected, $job]) {
            $this->assertSame($expected, $job->fresh()->billed_quantity, 'Pipa '.$length.' m');
            $this->assertSame(number_format((float) $length * 2, 4, '.', ''), $job->fresh()->job_volume_estimated);
        }
    }

    public function test_project_minimum_aggregates_rounded_usage_and_can_be_negotiated(): void
    {
        $document = $this->document();
        $crane = $this->item(['code' => 'CRN', 'calculation_rule' => 'crane', 'unit_price' => '100', 'has_minimum_charge' => true, 'minimum_quantity' => '8']);
        $job_data = fn (string $name, string $hours): array => ['job_name' => $name, 'project_job_group_id' => $this->group('Docking')->unique_id, 'price_book_item_id' => $crane->unique_id, 'job_volume_estimated' => $hours];

        $first = $this->store_job($document, $job_data('Crane propeller', '2.5'));
        $second = $this->store_job($document, $job_data('Crane katup', '1.2'));

        $this->assertSame('6.0000', $first->fresh()->billed_quantity, '3 h rounded up plus the 3 h shortfall to reach 8 h.');
        $this->assertSame('2.0000', $second->fresh()->billed_quantity);
        $this->assertSame('800.00', app(project_boq_service::class)->build_sheet($document->fresh())['grand_total']);

        $route = route('project.job-document.workflow.minimum-override.save', [$this->project->unique_id, $document->unique_id]);
        $this->put($route, ['price_book_item_id' => $crane->unique_id, 'action' => 'set', 'minimum_quantity' => '4'])->assertSessionHasErrors('reason');
        $this->put($route, ['price_book_item_id' => $crane->unique_id, 'action' => 'set', 'minimum_quantity' => '4', 'reason' => 'Disepakati saat negosiasi'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('3.0000', $first->fresh()->billed_quantity);
        $this->assertSame('4.0000', $document->fresh()->minimum_overrides[$crane->unique_id]['minimum']);
        $this->assertDatabaseHas('project_job_document_histories', ['project_job_document_id' => $document->id, 'action' => 'minimum_override_set']);

        $minimums = app(project_boq_service::class)->build_sheet($document->fresh())['minimums'];
        $this->assertSame('Disepakati saat negosiasi', $minimums[0]['override']['reason']);

        $this->put($route, ['price_book_item_id' => $crane->unique_id, 'action' => 'clear'])->assertSessionHasNoErrors();
        $this->assertSame('6.0000', $first->fresh()->billed_quantity);
        $this->assertDatabaseHas('project_job_document_histories', ['project_job_document_id' => $document->id, 'action' => 'minimum_override_cleared']);

        $unused = $this->item(['code' => 'UNUSED']);
        $this->put($route, ['price_book_item_id' => $unused->unique_id, 'action' => 'set', 'minimum_quantity' => '1', 'reason' => 'Tidak dipakai'])
            ->assertSessionHasErrors();
    }

    public function test_tugboat_rule_applies_day_and_time_rates(): void
    {
        $document = $this->document();
        $tug = $this->item(['code' => 'TUG', 'calculation_rule' => 'tugboat', 'unit_price' => '1000']);

        $job = $this->store_job($document, [
            'job_name' => 'Kapal tunda', 'project_job_group_id' => $this->group('Docking')->unique_id, 'price_book_item_id' => $tug->unique_id,
            'calculation' => ['type' => 'tugboat', 'entries' => [
                ['start_at' => '2026-10-19T15:30', 'end_at' => '2026-10-19T17:10', 'is_holiday' => '0'],
                ['start_at' => '2026-10-24T06:00', 'end_at' => '2026-10-24T08:00', 'is_holiday' => '0'],
                ['start_at' => '2026-10-20T10:00', 'end_at' => '2026-10-20T11:00', 'is_holiday' => '1'],
            ]],
        ])->fresh();

        // Mon 2 h (1 h × 100% + 1 h × 150%) + Sat 2 h (1,5 h × 300% + 0,5 h × 200%) + holiday 1 h × 200%.
        $this->assertSame('5.0000', $job->job_volume_estimated);
        $this->assertSame('10.0000', $job->billed_quantity);
        $this->assertSame('10000.00', $job->est_price);
        $this->assertSame('jam', $job->unit);

        $this->post(route('project.job-document.workflow.job.store', [$this->project->unique_id, $document->unique_id]), [
            'job_name' => 'Kapal tunda salah', 'project_job_group_id' => $this->group('Docking')->unique_id, 'price_book_item_id' => $tug->unique_id,
            'calculation' => ['type' => 'tugboat', 'entries' => [['start_at' => '2026-10-19T10:00', 'end_at' => '2026-10-19T09:00']]],
        ])->assertSessionHasErrors('calculation.entries.0.end_at');
    }

    public function test_customer_text_hides_minimums_and_price_item_rule_forces_unit(): void
    {
        $document = $this->document();
        $item = $this->item(['code' => 'PLT', 'unit' => 'kg', 'calculation_rule' => 'plate', 'has_minimum_charge' => true, 'minimum_quantity' => '15']);
        $this->store_job($document, [
            'job_name' => 'Pelat doubler', 'project_job_group_id' => $this->group()->unique_id, 'price_book_item_id' => $item->unique_id,
            'calculation' => ['type' => 'plate', 'material' => 'steel', 'length' => 300, 'width' => 200, 'thickness' => 8, 'quantity' => 3, 'safety_factor' => 1],
        ]);

        $row = app(project_boq_service::class)->build_sheet($document->fresh())['groups'][0]['rows'][0];
        $this->assertStringContainsString('kg/keping', $row['calculation_text']);
        $this->assertStringNotContainsString('kg/keping', $row['calculation_text_customer']);
        $this->assertStringContainsString('300 × 200 × 8 mm × 3 keping', $row['calculation_text_customer']);

        $this->get(route('project.job-document.workflow.show', [$this->project->unique_id, $document->unique_id]))
            ->assertOk()->assertSee('Batas Minimum')->assertSee('Tagih 45');

        $this->post(route('price-book.items.store', $this->book), [
            'group' => 'service', 'code' => 'PIPE-2', 'name' => 'Pipa baru', 'unit' => 'batang', 'calculation_rule' => 'pipe',
            'unit_price' => '1000', 'has_minimum_charge' => '1', 'minimum_quantity' => '5',
        ])->assertSessionHasNoErrors();
        $pipe = PriceBookItem::where('code', 'PIPE-2')->firstOrFail();
        $this->assertSame(['m', 'pipe', false], [$pipe->unit, $pipe->calculation_rule, (bool) $pipe->has_minimum_charge]);
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
        // Materials never carry weight; the only priced job line gets the full 100%.
        $this->assertSame('100.00', $root->fresh()->job_weight_percent);
        $this->assertSame('0.00', $child->fresh()->job_weight_percent);

        $this->put(route('project.job-document.workflow.material.update', [...$base, $material->unique_id]), ['material_name' => 'Elektroda', 'volume' => '1', 'unit_price' => '100'])
            ->assertSessionHasNoErrors();
        $this->assertSame('100.00', $material->fresh()->price);

        $this->delete(route('project.job-document.workflow.job.destroy', [$this->project->unique_id, $document->unique_id, $root->unique_id]))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('project_document_jobs', 0);
        $this->assertDatabaseCount('project_document_job_materials', 0);
    }

    public function test_owner_vendor_job_requires_director_approval_and_adds_surcharge_only_to_job_price(): void
    {
        $document = $this->document('final_boq');
        $job = $this->store_job($document, [
            'job_name' => 'Perbaikan oleh vendor pemilik',
            'project_job_group_id' => $this->group()->unique_id,
            'job_volume_estimated' => '2',
            'unit' => 'ls',
            'unit_price' => '100',
            'responsible_kind' => 'owner',
        ]);
        $job_route = [$this->project->unique_id, $document->unique_id, $job->unique_id];
        $this->assertSame('pending', $job->owner_vendor_approval_status);
        $this->assertSame('0.00', $job->owner_vendor_surcharge);
        $this->assertSame('200.00', $job->est_price);

        $this->post(route('project.job-document.workflow.submit', [$this->project->unique_id, $document->unique_id]))
            ->assertSessionHas('error', 'Setiap pekerjaan vendor pemilik harus disetujui Direksi sebelum RAB Akhir diajukan.');

        $non_approver = User::create(['employee_id' => '800000013', 'email' => 'boq-observer@example.test', 'password' => 'secret-pass']);
        $this->actingAs($non_approver)
            ->post(route('project.job-document.workflow.job.owner-vendor-decision', $job_route), ['decision' => 'approved'])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post(route('project.job-document.workflow.job.owner-vendor-decision', $job_route), ['decision' => 'rejected'])
            ->assertRedirect();
        $this->assertSame('rejected', $job->fresh()->owner_vendor_approval_status);
        $this->post(route('project.job-document.workflow.submit', [$this->project->unique_id, $document->unique_id]))
            ->assertSessionHas('error', 'Setiap pekerjaan vendor pemilik harus disetujui Direksi sebelum RAB Akhir diajukan.');

        $this->actingAs($this->admin)
            ->post(route('project.job-document.workflow.job.owner-vendor-decision', $job_route), ['decision' => 'approved'])
            ->assertRedirect();

        $job->refresh();
        $this->assertSame('approved', $job->owner_vendor_approval_status);
        $this->assertSame($this->admin->id, $job->owner_vendor_decided_by_user_id);
        $this->assertSame('40.00', $job->owner_vendor_surcharge);
        $this->assertSame('240.00', $job->est_price);
        $this->get(route('project.job-document.workflow.show', [$this->project->unique_id, $document->unique_id]))
            ->assertOk()
            ->assertSee(route('project.job-document.workflow.job.owner-vendor-decision', $job_route), false)
            ->assertSee('Surcharge 20%: 40,00');

        $this->put(route('project.job-document.workflow.job.update', $job_route), [
            'job_name' => 'Perbaikan oleh vendor pemilik',
            'project_job_group_id' => $this->group()->unique_id,
            'job_volume_estimated' => '2',
            'unit' => 'ls',
            'unit_price' => '110',
            'responsible_kind' => 'owner',
        ])->assertSessionHasNoErrors();
        $job->refresh();
        $this->assertSame('pending', $job->owner_vendor_approval_status);
        $this->assertSame('0.00', $job->owner_vendor_surcharge);

        $this->post(route('project.job-document.workflow.job.owner-vendor-decision', $job_route), ['decision' => 'approved'])
            ->assertRedirect();
        $job->refresh();
        $this->assertSame('44.00', $job->owner_vendor_surcharge);
        $this->assertSame('264.00', $job->est_price);

        $this->post(route('project.job-document.workflow.material.store', $job_route), [
            'material_name' => 'Material dari galangan',
            'volume' => '3',
            'unit' => 'kg',
            'unit_price' => '50',
        ])->assertSessionHasNoErrors();
        $this->post(route('project.job-document.workflow.material.store', $job_route), [
            'material_name' => 'Material disediakan pemilik',
            'volume' => '1',
            'unit' => 'kg',
            'unit_price' => '500',
            'owner_supplied' => '1',
        ])->assertSessionHasNoErrors();

        $materials = $job->materials()->orderBy('id')->get();
        $this->assertSame(['150.00', '0.00'], $materials->pluck('price')->all());
        $this->assertSame('414.00', app(project_boq_service::class)->build_sheet($document->fresh())['grand_total']);
        $this->assertDatabaseHas('project_job_document_histories', [
            'project_job_document_id' => $document->id,
            'action' => 'owner_vendor_job_approved',
            'actor_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('project_job_document_histories', [
            'project_job_document_id' => $document->id,
            'action' => 'owner_vendor_job_rejected',
            'actor_id' => $this->admin->id,
        ]);
    }

    public function test_c_suite_roles_receive_owner_vendor_approval_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        foreach (['ceo', 'chrga', 'cpo', 'cfo'] as $role_name) {
            $role = Role::findByName($role_name);
            $this->assertTrue($role->hasPermissionTo('approve-owner-vendor-job'));
            $this->assertTrue($role->hasPermissionTo('view-project-realization'));
            $this->assertFalse($role->hasPermissionTo('edit-project-realization'));
        }
    }

    public function test_owner_supplied_materials_are_not_charged_on_initial_or_final_boq(): void
    {
        foreach (['initial_boq', 'final_boq'] as $type) {
            $document = $this->document($type);
            $job = $this->store_job($document, [
                'job_name' => 'Pekerjaan '.$type,
                'project_job_group_id' => $this->group()->unique_id,
                'job_volume_estimated' => '1',
                'unit_price' => '100',
            ]);
            $item = $this->item([
                'group' => 'material',
                'code' => 'MAT-'.strtoupper($type),
                'name' => 'Pelat dari pemilik',
                'unit' => 'kg',
                'unit_price' => '200',
                'has_minimum_charge' => true,
                'minimum_quantity' => '10',
            ]);
            $job_route = [$this->project->unique_id, $document->unique_id, $job->unique_id];

            $this->post(route('project.job-document.workflow.material.store', $job_route), [
                'material_name' => 'Pelat dari pemilik',
                'price_book_item_id' => $item->unique_id,
                'volume' => '3',
                'unit' => 'kg',
                'unit_price' => '200',
                'owner_supplied' => '1',
            ])->assertSessionHasNoErrors();

            $material = $job->materials()->firstOrFail();
            $sheet = app(project_boq_service::class)->build_sheet($document->fresh());
            $this->assertTrue($material->owner_supplied);
            $this->assertSame('0.0000', $material->billed_quantity);
            $this->assertSame('0.00', $material->price);
            $this->assertSame('100.00', $sheet['grand_total']);
            $this->assertSame([], $sheet['minimums']);

            $this->get(route('project.job-document.workflow.show', [$this->project->unique_id, $document->unique_id]))
                ->assertOk()
                ->assertSee('Disediakan Pemilik')
                ->assertSee('tidak ditagihkan');

            if ($type === 'initial_boq') {
                $satisfaction_notes = ProjectJobDocument::cloneWithChildren($document->fresh(), [
                    'document_type' => 'satisfaction_notes',
                    'revision_no' => 1,
                ]);
                $final_boq = ProjectJobDocument::cloneWithChildren($satisfaction_notes, [
                    'document_type' => 'final_boq',
                    'revision_no' => 2,
                ]);
                app(project_boq_service::class)->recalculate_document($final_boq);

                $copied_material = $final_boq->jobs()->firstOrFail()->materials()->firstOrFail();
                $this->assertTrue($copied_material->owner_supplied);
                $this->assertSame('0.00', $copied_material->price);
            }
        }
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

    public function test_final_boq_requires_at_least_one_completed_satisfaction_note_job(): void
    {
        $satisfaction_notes = $this->document('satisfaction_notes');
        $incomplete_job = $this->store_job($satisfaction_notes, [
            'job_name' => 'Pekerjaan belum selesai',
            'project_job_group_id' => $this->group()->unique_id,
        ]);
        $incomplete_job->update(['progress_percent' => '99']);

        $payload = ['document_type' => 'final_boq', 'copy_previous' => '1'];
        $this->post(route('project.job-document.workflow.store', $this->project->unique_id), $payload)
            ->assertSessionHas('error', 'Tahap SOP belum terpenuhi untuk membuat dokumen ini.');
        $this->assertDatabaseMissing('project_job_documents', ['document_type' => 'final_boq']);

        $completed_job = $this->store_job($satisfaction_notes, [
            'job_name' => 'Pekerjaan selesai',
            'project_job_group_id' => $this->group()->unique_id,
        ]);
        $completed_job->update(['progress_percent' => '100']);
        $this->assertSame('100.00', $completed_job->fresh()->progress_percent);

        $this->post(route('project.job-document.workflow.store', $this->project->unique_id), $payload)
            ->assertSessionHas('success', 'Dokumen dibuat dan daftar pekerjaan disalin dari tahap sebelumnya.');
        $this->assertDatabaseHas('project_job_documents', ['document_type' => 'final_boq', 'status' => 'draft']);
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
