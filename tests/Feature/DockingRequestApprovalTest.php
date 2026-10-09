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
        Storage::fake('local');
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
        Storage::fake('local');
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
        Storage::fake('local');
        $this->make_space();

        $this->actingAs($this->make_user('111111111'))
            ->post(route('docking-space-request.store'), $this->payload($this->make_ship(['length_overall' => 150])))
            ->assertSessionHas('error');

        $this->assertSame(0, ProjectDockingRequest::count());
    }

    public function test_ship_particular_document_is_required(): void
    {
        Storage::fake('local');
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
        Storage::fake('local');
        $this->make_space();
        $marketing = $this->make_user('111111111');
        $engineer = $this->make_user('222222222', 'approve-docking-engineering');
        $producer = $this->make_user('333333333', 'approve-docking-production');

        $this->actingAs($marketing)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));
        $request = ProjectDockingRequest::firstOrFail();

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('error');

        $this->actingAs($marketing)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('error');

        $this->actingAs($engineer)
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('success');
        $this->assertSame('engineering_approved', $request->fresh()->request_status);

        $this->actingAs($producer)
            ->post(route('docking-space-request.production-review', $request->unique_id), ['decision' => 'approve'])
            ->assertSessionHas('success');

        $this->assertSame('approved', $request->fresh()->request_status);
        $this->assertSame(1, DockingOccupancy::where('occupancy_status', 'scheduled')->count());
    }

    public function test_rejection_requires_reason_and_frees_slot(): void
    {
        Storage::fake('local');
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
        Storage::fake('local');
        $this->make_space();
        $user = $this->make_user('222222222', 'approve-docking-engineering');
        $this->actingAs($user)->post(route('docking-space-request.store'), $this->payload($this->make_ship()));

        $this->actingAs($user)->get(route('docking-space-request.index'))->assertOk()->assertSee('Setujui (Engineering)');
        $this->actingAs($user)->get(route('docking-space-request.create'))->assertOk();
    }

    public function test_approval_pages_are_permission_gated_and_decision_returns_to_queue(): void
    {
        Storage::fake('local');
        $this->make_space();
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
            ->post(route('docking-space-request.engineering-review', $request->unique_id), ['decision' => 'approve', 'return_to_queue' => 1])
            ->assertRedirect(route('docking-approval.index', 'engineering'));
    }
}
