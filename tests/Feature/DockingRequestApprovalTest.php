<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\FloatingRepairHistory;
use App\Models\Project;
use App\Models\ProjectDockingRequest;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DockingRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function make_user(string $employee_id, ?string $permission = null): User
    {
        $user = User::create(['employee_id' => $employee_id, 'email' => $employee_id.'@example.test', 'password' => 'secret-pass']);

        if ($permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    private function make_ship(array $overrides = []): Ship
    {
        return Ship::create(array_merge([
            'name' => 'KM Test', 'company_id' => Company::firstOrCreate(['name' => 'PT Uji'], ['unique_id' => (string) \Illuminate\Support\Str::uuid(), 'address' => 'Jakarta'])->id, 'length_overall' => 80, 'breadth' => 14, 'height' => 10, 'empty_draft' => 3, 'loaded_draft' => 4, 'gross_tonnage' => 2000,
        ], $overrides));
    }

    private function make_space(array $overrides = []): DockingSpace
    {
        return DockingSpace::create(array_merge([
            'name' => 'Dock A', 'status' => 'active', 'max_length' => 100, 'max_breadth' => 20, 'max_width' => 20,
            'max_draft' => 6, 'max_tonnage' => 5000, 'max_weight' => 5000, 'max_capacity' => 1,
        ], $overrides));
    }

    private function payload(Ship $ship, array $overrides = []): array
    {
        return array_merge([
            'ship_id' => $ship->id,
            'requested_start_at' => '2027-01-10 08:00',
            'requested_end_at' => '2027-01-20 08:00',
            'documents' => [['type' => 'ship_particular', 'file' => UploadedFile::fake()->create('sp.pdf', 100, 'application/pdf')]],
        ], $overrides);
    }

    public function test_ship_only_request_passes_system_check_and_waits_for_engineering(): void
    {
        Storage::fake('ship');
        $space = $this->make_space();
        $ship = $this->make_ship();

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $this->payload($ship))
            ->assertRedirect(route('docking-space-request.index'))
            ->assertSessionHas('success');

        $request = ProjectDockingRequest::firstOrFail();
        $this->assertSame('submitted', $request->request_status);
        $this->assertSame($space->id, $request->requested_docking_space_id);
        $this->assertCount(1, $request->documents);
    }

    public function test_request_form_defaults_to_ship_and_provides_ship_specific_dock_options(): void
    {
        $space = $this->make_space();
        $small_ship = $this->make_ship();
        $large_ship = $this->make_ship(['name' => 'KM Besar', 'breadth' => 25]);
        DockingOccupancy::create([
            'ship_id' => $small_ship->id,
            'docking_space_id' => $space->id,
            'occupancy_status' => 'occupied',
            'docked_at' => '2026-10-01 08:00',
        ]);

        $this->actingAs($this->make_user('111111111'))
            ->get(route('docking-space-request.create'))
            ->assertOk()
            ->assertSeeInOrder(['Kapal (Vessel)', 'Proyek yang sudah ada'])
            ->assertSee('id="request_scope_ship" value="ship" checked', false)
            ->assertSee('accept: false', false)
            ->assertSee('maks. 50MB per file', false)
            ->assertSee('Panduan awal kesesuaian kapal', false)
            ->assertSee('>Lebar</th>', false)
            ->assertSee('>Draft</th>', false)
            ->assertSee('>GT</th>', false)
            ->assertSee('>Kapasitas</th>', false)
            ->assertSee('Pilih docking space (opsional)', false)
            ->assertSee('<option value="'.$space->id.'"', false)
            ->assertSee($space->name, false)
            ->assertDontSee('undefined')
            ->assertDontSee('Sistem akan mengevaluasi otomatis')
            ->assertSee('Repair List dari Owner', false)
            ->assertSee('data-date-display="long"', false)
            ->assertViewHas('dock_options_by_ship', function ($options) use ($small_ship, $large_ship, $space): bool {
                return $options[$small_ship->id][$space->id]['checks']['breadth']
                    && $options[$small_ship->id][$space->id]['checks']['draft']
                    && $options[$small_ship->id][$space->id]['checks']['tonnage']
                    && ! $options[$small_ship->id][$space->id]['checks']['capacity']
                    && ! $options[$large_ship->id][$space->id]['checks']['breadth']
                    && $options[$large_ship->id][$space->id]['checks']['draft']
                    && $options[$large_ship->id][$space->id]['checks']['tonnage'];
            });
    }

    public function test_owner_repair_list_is_stored_with_ship_particular(): void
    {
        Storage::fake('ship');
        $this->make_space();

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $this->payload($this->make_ship(), [
                'documents' => [
                    ['type' => 'ship_particular', 'file' => UploadedFile::fake()->create('particular.pdf', 10, 'application/pdf')],
                    ['type' => 'owner_repair_list', 'file' => UploadedFile::fake()->create('repair-list.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')],
                ],
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('docking_request_documents', [
            'document_type' => 'owner_repair_list',
            'document_name' => 'Repair List dari Owner',
        ]);
    }

    public function test_docking_request_document_limit_is_50_mb_per_file(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $user = $this->make_user('111111111');
        $ship = $this->make_ship();

        $this->actingAs($user)
            ->post(route('docking-space-request.store'), $this->payload($ship, [
                'documents' => [['type' => 'ship_particular', 'file' => UploadedFile::fake()->create('particular.pdf', 51200, 'application/pdf')]],
            ]))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('docking-space-request.store'), $this->payload($ship, [
                'documents' => [['type' => 'ship_particular', 'file' => UploadedFile::fake()->create('particular-large.pdf', 51201, 'application/pdf')]],
            ]))
            ->assertSessionHasErrors('documents.0.file');
    }

    public function test_schedule_conflict_is_rejected(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $user = $this->make_user('111111111');

        $this->actingAs($user)->post(route('docking-space-request.store'), $this->payload($this->make_ship()))->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('docking-space-request.store'), $this->payload($this->make_ship(['name' => 'KM Dua']), ['requested_start_at' => '2027-01-15 08:00', 'requested_end_at' => '2027-01-25 08:00']))
            ->assertSessionHas('error');

        $this->assertSame(1, ProjectDockingRequest::count());
    }

    public function test_request_can_be_submitted_when_ship_does_not_fit_selected_dock(): void
    {
        Storage::fake('ship');
        $space = $this->make_space();

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $this->payload(
                $this->make_ship(['length_overall' => 150]),
                ['requested_docking_space_id' => $space->id]
            ))
            ->assertRedirect(route('docking-space-request.index'))
            ->assertSessionHas('success');

        $request = ProjectDockingRequest::firstOrFail();
        $this->assertSame($space->id, $request->requested_docking_space_id);
    }

    public function test_request_can_be_submitted_without_documents(): void
    {
        Storage::fake('ship');
        $space = $this->make_space();
        $ship = $this->make_ship();
        $payload = $this->payload($ship);
        unset($payload['documents']);

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $payload)
            ->assertRedirect(route('docking-space-request.index'))
            ->assertSessionHas('success');

        $request = ProjectDockingRequest::firstOrFail();
        $this->assertSame($space->id, $request->requested_docking_space_id);
        $this->assertCount(0, $request->documents);
    }

    public function test_two_tier_approval_creates_scheduled_occupancy(): void
    {
        Storage::fake('ship');
        $space = $this->make_space();
        $marketing = $this->make_user('111111111');
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $producer = $this->make_user('333333333', 'approve-docking-production');

        $this->actingAs($marketing)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::firstOrFail();

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('error');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'recommended_docking_space' => $space->unique_id])
            ->assertSessionHas('error');

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHasErrors('recommended_docking_space');

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'recommended_docking_space' => $space->unique_id])
            ->assertSessionHas('success');
        $this->assertSame('engineering_approved', $request->fresh()->request_status);
        $this->assertSame($space->id, $request->fresh()->recommended_docking_space_id);

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('success');

        $this->assertSame('approved', $request->fresh()->request_status);
        $this->assertSame(1, DockingOccupancy::where('occupancy_status', 'scheduled')->count());
    }

    public function test_rejection_requires_reason_and_frees_slot(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');

        $this->actingAs($engineer)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::firstOrFail();

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'reject'])
            ->assertSessionHasErrors('notes');

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'reject', 'notes' => 'Stabilitas tidak memenuhi.']);

        $this->assertSame('rejected', $request->fresh()->request_status);

        $this->actingAs($engineer)
            ->post(route('docking-space-request.store'), $this->payload($this->make_ship(['name' => 'KM Dua'])))
            ->assertSessionHas('success');
    }

    public function test_index_page_renders(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $user = $this->make_user('222222222', 'approve-docking-engineering');
        $this->actingAs($user)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));

        $this->actingAs($user)->get(route('docking-space-request.index'))->assertOk()->assertSee('Tinjau Engineering')->assertDontSee('Setujui (Engineering)');
        $this->actingAs($user)->get(route('docking-space-request.create'))->assertOk();
    }

    public function test_approval_pages_are_permission_gated_and_decision_returns_to_queue(): void
    {
        Storage::fake('ship');
        $space = $this->make_space();
        $marketing = $this->make_user('111111111');
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $this->actingAs($marketing)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::firstOrFail();

        $this->actingAs($marketing)->get(route('docking-approval.index', 'engineering'))->assertForbidden();
        $this->actingAs($engineer)->get(route('docking-approval.index', 'production'))->assertForbidden();
        $this->actingAs($engineer)->get(route('docking-approval.index', 'engineering'))->assertOk()->assertSee('KM Test');
        $this->actingAs($engineer)->get(route('docking-approval.show', ['engineering', $request->unique_id]))
            ->assertOk()->assertSee('Lolos pemeriksaan sistem');

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'return_to_queue' => 1, 'recommended_docking_space' => $space->unique_id])
            ->assertRedirect(route('docking-approval.index', 'engineering'));
    }

    public function test_pending_files_are_stored_under_docking_request_then_moved_to_project_folder(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $this->actingAs($this->make_user('111111111'))->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::with('documents')->firstOrFail();
        $document = $request->documents->first();

        $this->assertStringStartsWith('docking-request/'.$request->unique_id.'/', $document->document_path);
        Storage::disk('ship')->assertExists($document->document_path);
        $pending_path = $document->document_path;

        app(\App\Services\DockingRequestDocumentStorage::class)
            ->move_to_project($request, new \App\Models\Project(['project_code' => 'DR.001.KM.10.26']));

        $document->refresh();
        $this->assertSame('projects/DR.001.KM.10.26/documents/'.basename($pending_path), $document->document_path);
        Storage::disk('ship')->assertExists($document->document_path);
        Storage::disk('ship')->assertMissing($pending_path);
        $this->assertEmpty(Storage::disk('ship')->directories('docking-request'));
    }

    public function test_rejected_request_keeps_documents_downloadable_for_history(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $marketing = $this->make_user('111111111');
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $outsider = $this->make_user('444444444');

        $this->actingAs($marketing)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::with('documents')->firstOrFail();
        $document = $request->documents->first();

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'reject', 'notes' => 'Tidak memenuhi.']);

        $this->assertSame('rejected', $request->fresh()->request_status);
        Storage::disk('ship')->assertExists($document->fresh()->document_path);

        $url = route('docking-space-request.documents.download', [$request->unique_id, $document->unique_id]);
        $this->actingAs($marketing)->get($url)->assertOk();
        $this->actingAs($engineer)->get($url)->assertOk();
        $this->actingAs($outsider)->get($url)->assertForbidden();

        $this->actingAs($engineer)->get(route('docking-approval.show', ['engineering', $request->unique_id]))
            ->assertOk()->assertSee('Ship Particular');
    }

    public function test_rejected_request_files_move_to_rejected_folder_and_stay_downloadable(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $this->actingAs($engineer)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::with('documents')->firstOrFail();
        $old_path = $request->documents->first()->document_path;

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'reject', 'notes' => 'Tidak sesuai.']);

        $document = $request->documents()->first();
        $this->assertSame('docking-request/rejected/'.$request->unique_id.'/'.basename($old_path), $document->document_path);
        Storage::disk('ship')->assertExists($document->document_path);
        Storage::disk('ship')->assertMissing($old_path);

        $this->actingAs($engineer)
            ->get(route('docking-space-request.documents.download', [$request->unique_id, $document->unique_id]))
            ->assertOk();
    }

    public function test_rejected_requests_page_lists_reason_and_filters_by_stage(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $this->actingAs($engineer)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::firstOrFail();
        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'reject', 'notes' => 'Gambar tidak lengkap.']);

        $this->actingAs($engineer)->get(route('docking-space-request.rejected'))
            ->assertOk()->assertSee('Gambar tidak lengkap.');
        $this->actingAs($engineer)->get(route('docking-space-request.rejected', ['stage' => 'production']))
            ->assertOk()->assertDontSee('Gambar tidak lengkap.');
    }

    public function test_engineering_recommendation_overrides_requested_dock_and_is_validated(): void
    {
        Storage::fake('ship');
        $requested = $this->make_space(['name' => 'Dock A']);
        $recommended = $this->make_space(['name' => 'Dock B', 'max_length' => 120]);
        $too_small = $this->make_space(['name' => 'Dock C', 'max_length' => 10]);
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $producer = $this->make_user('333333333', 'approve-docking-production');

        $this->actingAs($engineer)->post(route('docking-space-request.store'), $this->payload(
            $this->make_ship(),
            ['requested_docking_space_id' => $requested->id]
        ));
        $request = ProjectDockingRequest::firstOrFail();
        $this->assertSame($requested->id, $request->requested_docking_space_id);

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'recommended_docking_space' => $too_small->unique_id])
            ->assertSessionHasErrors('recommended_docking_space');
        $this->assertSame('submitted', $request->fresh()->request_status);

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'recommended_docking_space' => $recommended->unique_id]);
        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('success');

        $this->assertSame($recommended->id, DockingOccupancy::where('occupancy_status', 'scheduled')->firstOrFail()->docking_space_id);
    }

    private function request_awaiting_production(): array
    {
        Storage::fake('ship');
        $space = $this->make_space();
        $marketing = $this->make_user('111111111');
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $producer = $this->make_user('333333333', 'approve-docking-production');

        $this->actingAs($marketing)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::firstOrFail();
        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'recommended_docking_space' => $space->unique_id]);

        return [$request, $marketing, $producer];
    }

    public function test_production_postpone_recommendation_needs_marketing_confirmation(): void
    {
        [$request, $marketing, $producer] = $this->request_awaiting_production();

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'postpone', 'notes' => 'Dok penuh.'])
            ->assertSessionHasErrors(['recommended_start_at', 'recommended_end_at']);

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), [
                'decision' => 'postpone', 'notes' => 'Dok penuh.',
                'recommended_start_at' => '2027-02-01 08:00', 'recommended_end_at' => '2027-02-10 08:00',
            ])->assertSessionHas('success');
        $this->assertSame('awaiting_marketing', $request->fresh()->request_status);
        $this->assertSame(0, DockingOccupancy::count());

        $this->actingAs($producer)
            ->post(route('docking-space-request.marketing-response', $request->unique_id), ['response' => 'accept'])
            ->assertSessionHas('error');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.marketing-response', $request->unique_id), ['response' => 'decline'])
            ->assertSessionHasErrors('notes');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.marketing-response', $request->unique_id), ['response' => 'accept'])
            ->assertSessionHas('success');

        $fresh = $request->fresh();
        $this->assertSame('engineering_approved', $fresh->request_status);
        $this->assertSame('2027-02-01', $fresh->requested_start_at->format('Y-m-d'));
        $this->assertSame('accept', $fresh->marketing_response);
    }

    public function test_customer_objection_returns_request_to_production(): void
    {
        [$request, $marketing, $producer] = $this->request_awaiting_production();

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'cancel', 'notes' => 'Tidak ada slot.']);
        $this->actingAs($marketing)
            ->post(route('docking-space-request.marketing-response', $request->unique_id), ['response' => 'decline', 'notes' => 'Customer tidak bisa mundur.'])
            ->assertSessionHas('success');

        $this->assertSame('engineering_approved', $request->fresh()->request_status);
        $this->assertSame('Customer tidak bisa mundur.', $request->fresh()->marketing_notes);
    }

    public function test_confirmed_cancellation_moves_request_to_rejected_with_files(): void
    {
        [$request, $marketing, $producer] = $this->request_awaiting_production();

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'cancel', 'notes' => 'Tidak ada slot.']);
        $this->actingAs($marketing)
            ->post(route('docking-space-request.marketing-response', $request->unique_id), ['response' => 'accept'])
            ->assertSessionHas('success');

        $fresh = $request->fresh();
        $this->assertSame('rejected', $fresh->request_status);
        $this->assertSame('production', $fresh->rejection_stage);
        $this->assertStringStartsWith('docking-request/rejected/', $fresh->documents()->first()->document_path);
        $this->actingAs($marketing)->get(route('docking-space-request.index'))->assertOk();
        $this->actingAs($producer)->get(route('docking-approval.show', ['production', $request->unique_id]))->assertOk();
    }

    private function approved_request(): array
    {
        [$request, $marketing, $producer] = $this->request_awaiting_production();
        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'approve']);

        return [$request->fresh(), $marketing, $producer];
    }

    public function test_marketing_can_postpone_docking_to_free_slot_and_occupancy_follows(): void
    {
        [$request, $marketing] = $this->approved_request();

        $this->actingAs($marketing)->get(route('docking-space-request.postpone', $request->unique_id))
            ->assertOk()->assertSee('Saran jadwal kosong');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.postpone.store', $request->unique_id), ['new_start_at' => '2027-02-01 08:00', 'new_end_at' => '2027-02-11 08:00'])
            ->assertSessionHasErrors('reason');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.postpone.store', $request->unique_id), [
                'new_start_at' => '2027-02-01 08:00', 'new_end_at' => '2027-02-11 08:00', 'reason' => 'Kapal sebelumnya belum selesai.',
            ])->assertSessionHas('success');

        $this->assertSame('2027-02-01', $request->fresh()->requested_start_at->format('Y-m-d'));
        $this->assertSame('2027-02-01', DockingOccupancy::firstOrFail()->docked_at->format('Y-m-d'));
        $this->assertSame(1, $request->postponements()->count());
    }

    public function test_postponement_conflict_is_rejected_and_next_free_slot_is_suggested(): void
    {
        [$request, $marketing] = $this->approved_request();
        $space = DockingSpace::firstOrFail();

        DockingOccupancy::create([
            'ship_id' => $request->ship_id, 'docking_space_id' => $space->id, 'occupancy_status' => 'scheduled',
            'docked_at' => '2027-02-01 08:00', 'estimated_undock_at' => '2027-02-20 08:00',
        ]);

        $this->actingAs($marketing)
            ->post(route('docking-space-request.postpone.store', $request->unique_id), [
                'new_start_at' => '2027-02-05 08:00', 'new_end_at' => '2027-02-15 08:00', 'reason' => 'Uji.',
            ])->assertSessionHasErrors('new_start_at');
        $this->assertSame('2027-01-10', $request->fresh()->requested_start_at->format('Y-m-d'));

        $this->actingAs($marketing)
            ->get(route('docking-space-request.postpone', [$request->unique_id, 'from' => '2027-02-02 08:00']))
            ->assertOk()->assertSee('20 Februari 2027, 08:01');

        $outsider = $this->make_user('444444444');
        $this->actingAs($outsider)
            ->post(route('docking-space-request.postpone.store', $request->unique_id), [
                'new_start_at' => '2027-03-01 08:00', 'new_end_at' => '2027-03-11 08:00', 'reason' => 'Uji.',
            ])->assertSessionHas('error');
    }

    public function test_pending_request_cancel_requires_reason_and_archives_files(): void
    {
        [$request, $marketing, $producer] = $this->request_awaiting_production();
        $old_path = $request->documents()->first()->document_path;

        $this->actingAs($marketing)->get(route('docking-space-request.index'))
            ->assertOk()->assertSee('cancel-request-modal', false)->assertSee('Batalkan permohonan');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.cancel', $request->unique_id), ['cancellation_reason' => ''])
            ->assertSessionHasErrors('cancellation_reason');

        $this->actingAs($producer)
            ->post(route('docking-space-request.cancel', $request->unique_id), ['cancellation_reason' => 'Bukan pemohon.'])
            ->assertSessionHas('error');
        $this->assertSame('engineering_approved', $request->fresh()->request_status);

        $this->actingAs($marketing)
            ->post(route('docking-space-request.cancel', $request->unique_id), ['cancellation_reason' => 'Owner batal docking.'])
            ->assertSessionHas('success');

        $fresh = $request->fresh();
        $this->assertSame('cancelled', $fresh->request_status);
        $this->assertSame('Owner batal docking.', $fresh->cancellation_reason);
        $this->assertSame($marketing->id, $fresh->cancelled_by);
        $this->assertNotNull($fresh->cancelled_at);

        $new_path = $fresh->documents()->first()->document_path;
        $this->assertStringStartsWith('docking-request/cancelled/'.$request->unique_id.'/', $new_path);
        Storage::disk('ship')->assertExists($new_path);
        Storage::disk('ship')->assertMissing($old_path);

        $this->actingAs($marketing)->get(route('docking-space-request.index', ['tab' => 'selesai']))
            ->assertOk()->assertSee('Owner batal docking.');
    }

    public function test_approved_request_cancel_releases_scheduled_slot(): void
    {
        [$request, $marketing] = $this->approved_request();
        $this->assertSame(1, DockingOccupancy::where('occupancy_status', 'scheduled')->count());

        $this->actingAs($marketing)
            ->post(route('docking-space-request.cancel', $request->unique_id), ['cancellation_reason' => 'Owner pindah galangan.'])
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $request->fresh()->request_status);
        $this->assertSame(0, DockingOccupancy::where('occupancy_status', 'scheduled')->count());
        $this->assertSame(1, DockingOccupancy::where('occupancy_status', 'cancelled')->count());
    }

    public function test_docked_request_cannot_be_cancelled(): void
    {
        [$request, $marketing] = $this->approved_request();
        DockingOccupancy::query()->update(['occupancy_status' => 'occupied']);

        $this->actingAs($marketing)
            ->post(route('docking-space-request.cancel', $request->unique_id), ['cancellation_reason' => 'Coba batal.'])
            ->assertSessionHas('error');

        $this->assertSame('approved', $request->fresh()->request_status);
        $this->actingAs($marketing)->get(route('docking-space-request.index'))
            ->assertOk()->assertDontSee('data-bs-target="#cancel-request-modal"', false);
    }

    private function at(string $modifier): string
    {
        return now()->modify($modifier)->format('Y-m-d H:i');
    }

    public function test_full_visit_lifecycle_with_floating_repair_and_departure_rules(): void
    {
        [$request, $marketing] = $this->approved_request();
        $operator = $this->make_user('444444444', 'docking.manage');
        $this->assertSame('awaiting_arrival', $request->visit_stage);

        $this->actingAs($operator)->post(route('docking-space-request.start-docking', $request->unique_id), ['docked_at' => $this->at('-5 hours')])
            ->assertSessionHas('error');

        $this->actingAs($operator)->post(route('docking-space-request.arrival', $request->unique_id), ['arrived_at' => $this->at('-6 hours')])
            ->assertRedirect(route('docking-space-request.show', $request->unique_id));
        $this->assertSame('arrived', $request->fresh()->visit_stage);

        $this->actingAs($operator)->post(route('docking-space-request.start-docking', $request->unique_id), [
            'docking_space_id' => $request->fresh()->recommended_docking_space_id, 'docked_at' => $this->at('-5 hours'),
        ])->assertSessionHas('success');
        $this->assertSame('docked', $request->fresh()->visit_stage);
        $this->assertSame(1, DockingOccupancy::where('project_docking_request_id', $request->id)->where('occupancy_status', 'occupied')->count());

        $this->actingAs($marketing)->post(route('docking-space-request.cancel', $request->unique_id), ['cancellation_reason' => 'Tidak boleh lagi.'])
            ->assertSessionHas('error');

        $this->actingAs($operator)->post(route('docking-space-request.undock', $request->unique_id), [
            'undocked_at' => $this->at('-3 hours'), 'next_step' => 'floating',
        ])->assertSessionHasErrors('floating_started_at');

        $this->actingAs($operator)->post(route('docking-space-request.undock', $request->unique_id), [
            'undocked_at' => $this->at('-3 hours'), 'next_step' => 'floating', 'floating_started_at' => $this->at('-3 hours'),
        ])->assertSessionHas('success');
        $this->assertSame('floating', $request->fresh()->visit_stage);
        $this->assertSame(1, FloatingRepairHistory::where('floating_status', 'active')->count());

        $this->actingAs($operator)->get(route('docking-space-request.index', ['tab' => 'galangan']))
            ->assertOk()->assertSee('Selesaikan atas air');

        $this->actingAs($operator)->post(route('docking-space-request.complete-floating', $request->unique_id), ['floating_completed_at' => $this->at('-2 hours')])
            ->assertSessionHas('success');
        $this->assertSame('ready_to_depart', $request->fresh()->visit_stage);

        $this->actingAs($operator)->post(route('docking-space-request.depart', $request->unique_id), ['departed_at' => $this->at('-1 hour')])
            ->assertSessionHas('error');

        $project = Project::create(['project_code' => 'RP-UJI-01', 'ship_id' => $request->ship_id, 'project_type' => 'Docking Repair', 'status' => 'In Progress', 'progress' => 90]);
        $request->update(['project_id' => $project->id]);

        $this->actingAs($marketing)->post(route('docking-space-request.administration', $request->unique_id), ['administration_notes' => 'BAST dan invoice lunas.'])
            ->assertSessionHasErrors('administration_confirmed');
        $this->actingAs($marketing)->post(route('docking-space-request.administration', $request->unique_id), [
            'administration_notes' => 'BAST dan invoice lunas.', 'administration_confirmed' => '1',
        ])->assertSessionHas('success');

        $this->actingAs($operator)->get(route('docking-space-request.show', $request->unique_id))
            ->assertOk()->assertSee('belum berstatus selesai');
        $this->actingAs($operator)->post(route('docking-space-request.depart', $request->unique_id), ['departed_at' => $this->at('-1 hour')])
            ->assertSessionHas('error');

        $project->update(['status' => 'Completed']);
        $this->actingAs($operator)->post(route('docking-space-request.depart', $request->unique_id), ['departed_at' => $this->at('-1 hour')])
            ->assertSessionHas('success');

        $fresh = $request->fresh();
        $this->assertSame('completed', $fresh->request_status);
        $this->assertSame('departed', $fresh->visit_stage);
        $this->assertSame($operator->id, $fresh->departed_recorded_by);

        $this->actingAs($operator)->get(route('docking-space-request.index', ['tab' => 'selesai']))
            ->assertOk()->assertSee('KM Test');
    }

    public function test_undock_without_floating_goes_straight_to_ready_to_depart(): void
    {
        [$request] = $this->approved_request();
        $operator = $this->make_user('444444444', 'docking.manage');

        $this->actingAs($operator)->post(route('docking-space-request.arrival', $request->unique_id), ['arrived_at' => $this->at('-6 hours')]);
        $this->actingAs($operator)->post(route('docking-space-request.start-docking', $request->unique_id), ['docked_at' => $this->at('-5 hours')]);
        $this->actingAs($operator)->post(route('docking-space-request.undock', $request->unique_id), [
            'undocked_at' => $this->at('-1 hour'), 'next_step' => 'ready_to_depart',
        ])->assertSessionHas('success');

        $this->assertSame('ready_to_depart', $request->fresh()->visit_stage);
        $this->assertSame(0, FloatingRepairHistory::count());
    }

    public function test_visit_actions_require_operator_and_valid_dates(): void
    {
        [$request, $marketing] = $this->approved_request();
        $operator = $this->make_user('444444444', 'docking.manage');

        $this->actingAs($marketing)->post(route('docking-space-request.arrival', $request->unique_id), ['arrived_at' => $this->at('-1 hour')])
            ->assertSessionHas('error');
        $this->assertSame('awaiting_arrival', $request->fresh()->visit_stage);

        $this->actingAs($operator)->post(route('docking-space-request.arrival', $request->unique_id), ['arrived_at' => $this->at('+2 days')])
            ->assertSessionHasErrors('arrived_at');

        $this->actingAs($operator)->post(route('docking-space-request.undock', $request->unique_id), [
            'undocked_at' => $this->at('-1 hour'), 'next_step' => 'ready_to_depart',
        ])->assertSessionHas('error');
    }

    public function test_detail_page_shows_lifecycle_actions_and_auto_opens_requested_modal(): void
    {
        [$request, $marketing] = $this->approved_request();
        $operator = $this->make_user('444444444', 'docking.manage');

        $this->actingAs($operator)->get(route('docking-space-request.show', ['docking_request' => $request->unique_id, 'action' => 'arrival']))
            ->assertOk()->assertSee('visit-arrival-modal')->assertSee('Catat kedatangan kapal')->assertSee('"arrival"', false);

        $this->actingAs($marketing)->get(route('docking-space-request.show', $request->unique_id))
            ->assertOk()->assertDontSee('visit-arrival-modal')->assertSee('Batalkan permohonan')->assertSee('Tunda jadwal docking');

        $this->get(route('docking-space-request.show', '00000000-0000-0000-0000-000000000000'))->assertNotFound();
    }

    public function test_index_lanes_filter_and_search(): void
    {
        [$request, $marketing] = $this->approved_request();

        $this->actingAs($marketing)->get(route('docking-space-request.index'))
            ->assertOk()->assertDontSee(route('docking-space-request.show', $request->unique_id));
        $this->actingAs($marketing)->get(route('docking-space-request.index', ['tab' => 'kedatangan']))
            ->assertOk()->assertSee(route('docking-space-request.show', $request->unique_id));
        $this->actingAs($marketing)->get(route('docking-space-request.index', ['tab' => 'kedatangan', 'q' => 'Tidak Ada']))
            ->assertOk()->assertSee('Tidak ada data yang cocok');
        $this->actingAs($marketing)->get(route('docking-space-request.index', ['tab' => 'kedatangan', 'q' => 'KM Te']))
            ->assertOk()->assertSee(route('docking-space-request.show', $request->unique_id));
    }
}
