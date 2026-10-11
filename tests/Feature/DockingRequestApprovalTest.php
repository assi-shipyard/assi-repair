<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
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

    public function test_ship_too_large_for_every_space_is_rejected(): void
    {
        Storage::fake('ship');
        $this->make_space();

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $this->payload($this->make_ship(['length_overall' => 150])))
            ->assertSessionHas('error');

        $this->assertSame(0, ProjectDockingRequest::count());
    }

    public function test_ship_particular_document_is_required(): void
    {
        Storage::fake('ship');
        $this->make_space();
        $ship = $this->make_ship();

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $this->payload($ship, [
                'documents' => [['type' => 'other', 'file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]],
            ]))
            ->assertSessionHasErrors('documents');
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

        $this->actingAs($user)->get(route('docking-space-request.index'))->assertOk()->assertSee('Tinjau detail')->assertDontSee('Setujui (Engineering)');
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

        $this->actingAs($engineer)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
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
}
