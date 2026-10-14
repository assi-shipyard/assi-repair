<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Subcontractor;
use App\Models\User;
use App\Services\subcontractor_service;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// PHPUnit requires the class name to match its *Test.php discovery filename.
class SubcontractorMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_open_listing_with_active_database_menu_link(): void
    {
        $this->actingAs($this->internal_user())->get(route('subcontractor.index'))
            ->assertOk()
            ->assertSeeInOrder(['Database', 'Entitas Utama', 'Data Perusahaan', 'Data Subkontraktor', 'Data Kapal'])
            ->assertSee('href="'.route('subcontractor.index').'" class="dropdown-item active"', false)
            ->assertSee('Belum ada data subkontraktor.');
    }

    public function test_listing_displays_active_and_inactive_vendors_and_escapes_names(): void
    {
        Subcontractor::create([
            'code' => 'SUB-001', 'name' => '<script>alert(1)</script>',
            'contact_name' => 'Kontak Uji', 'contact_phone' => '+62 812 0000 0000',
            'contact_email' => 'vendor@example.test', 'notes' => 'Catatan internal rahasia',
        ]);
        Subcontractor::create([
            'code' => 'SUB-002', 'name' => 'Vendor Nonaktif', 'contact_name' => 'Kontak Lain', 'is_active' => false,
        ]);

        $this->actingAs($this->internal_user())->get(route('subcontractor.index'))
            ->assertOk()->assertSee('SUB-001')->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Kontak Uji')->assertSee('+62 812 0000 0000')->assertSee('vendor@example.test')
            ->assertSee('Vendor Nonaktif')->assertSee('Nonaktif')->assertSee('Aktif')
            ->assertDontSee('Catatan internal rahasia');
    }

    public function test_listing_is_paginated(): void
    {
        for ($index = 1; $index <= 21; $index++) {
            Subcontractor::create([
                'code' => sprintf('SUB-%03d', $index),
                'name' => sprintf('Vendor %03d', $index),
                'contact_name' => 'Kontak Uji',
            ]);
        }

        $this->actingAs($this->internal_user())->get(route('subcontractor.index'))
            ->assertOk()->assertSee('Vendor 001')->assertDontSee('Vendor 021')
            ->assertViewHas('subcontractors', fn ($subcontractors): bool => $subcontractors->total() === 21 && $subcontractors->count() === 20);
        $this->get(route('subcontractor.index', ['page' => 2]))
            ->assertOk()->assertSee('Vendor 021')->assertDontSee('Vendor 001');
    }

    public function test_guest_cannot_access_listing(): void
    {
        $this->get(route('subcontractor.index'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_internal_listing(): void
    {
        $customer = User::create([
            'account_type' => 'customer', 'email' => 'customer@example.test', 'password' => 'secret-pass',
        ]);

        $this->actingAs($customer)->get(route('subcontractor.index'))->assertForbidden();
    }

    public function test_admin_can_create_edit_and_change_vendor_status_with_audit_records(): void
    {
        $admin = $this->internal_user();
        $admin->assignRole(Role::findOrCreate('admin'));
        $this->actingAs($admin)->get(route('subcontractor.index'))->assertOk()->assertSee('Tambah Subkontraktor');
        $this->get(route('subcontractor.create'))->assertOk()->assertSee('Nama Kontak')->assertSee('subcontractor_form');
        $payload = $this->vendor_payload();
        $this->post(route('subcontractor.store'), $payload)->assertSessionHasNoErrors()
            ->assertRedirect(route('subcontractor.index'))->assertSessionHas('success');
        $vendor = Subcontractor::query()->firstOrFail();
        $this->assertSame('CV. Berjaya Las', $vendor->name);
        $this->assertSame('Kontak Vendor', $vendor->contact_name);
        $this->assertTrue($vendor->is_active);
        $audit = AuditLog::query()->where('event_name', 'subcontractor.created')->firstOrFail();
        $this->assertSame($admin->id, $audit->user_id);
        $this->assertSame($vendor->unique_id, $audit->event_payload['resource_uuid']);
        $this->assertSame([], $audit->event_payload['before']);
        $this->assertSame('CV. Berjaya Las', $audit->event_payload['after']['name']);

        $this->get(route('subcontractor.edit', $vendor))->assertOk()->assertSee('CV. Berjaya Las');
        $this->put(route('subcontractor.update', $vendor), [
            ...$payload, 'name' => 'CV. Berjaya Las Baru', 'is_active' => '0',
        ])->assertSessionHasNoErrors()->assertRedirect(route('subcontractor.index'));
        $this->assertFalse($vendor->fresh()->is_active);
        $audit = AuditLog::query()->where('event_name', 'subcontractor.updated')->firstOrFail();
        $this->assertSame('CV. Berjaya Las', $audit->event_payload['before']['name']);
        $this->assertSame('CV. Berjaya Las Baru', $audit->event_payload['after']['name']);
        $this->assertFalse((bool) $audit->event_payload['after']['is_active']);

        $this->put(route('subcontractor.update', $vendor), $payload)->assertSessionHasNoErrors();
        $this->assertTrue($vendor->fresh()->is_active);
        $this->assertDatabaseCount('subcontractors', 1);
        $this->assertDatabaseCount('project_document_jobs', 0);
    }

    public function test_non_admin_cannot_manage_vendors_or_see_management_buttons(): void
    {
        $vendor = Subcontractor::create($this->vendor_payload());
        $this->actingAs($this->internal_user())->get(route('subcontractor.index'))
            ->assertOk()->assertDontSee('Tambah Subkontraktor')
            ->assertDontSee(route('subcontractor.edit', $vendor));
        $this->get(route('subcontractor.create'))->assertForbidden();
        $this->get(route('subcontractor.edit', $vendor))->assertForbidden();
        $this->post(route('subcontractor.store'), $this->vendor_payload())->assertForbidden();
        $this->put(route('subcontractor.update', $vendor), [...$this->vendor_payload(), 'is_active' => '0'])->assertForbidden();
        $this->assertTrue($vendor->fresh()->is_active);
        $this->assertDatabaseCount('subcontractors', 1);
    }

    public function test_customer_and_guest_cannot_manage_vendors(): void
    {
        $vendor = Subcontractor::create($this->vendor_payload());
        $this->get(route('subcontractor.create'))->assertRedirect(route('login'));
        $this->post(route('subcontractor.store'), $this->vendor_payload())->assertRedirect(route('login'));
        $this->put(route('subcontractor.update', $vendor), $this->vendor_payload())->assertRedirect(route('login'));
        $customer = User::create([
            'account_type' => 'customer', 'email' => 'customer@example.test', 'password' => 'secret-pass',
        ]);
        $this->actingAs($customer)->get(route('subcontractor.create'))->assertForbidden();
        $this->get(route('subcontractor.edit', $vendor))->assertForbidden();
        $this->post(route('subcontractor.store'), $this->vendor_payload())->assertForbidden();
        $this->put(route('subcontractor.update', $vendor), $this->vendor_payload())->assertForbidden();
    }

    public function test_invalid_vendor_input_is_rejected(): void
    {
        $admin = $this->internal_user();
        $admin->assignRole(Role::findOrCreate('admin'));
        $this->actingAs($admin)->post(route('subcontractor.store'), [])
            ->assertSessionHasErrors(['code', 'name', 'contact_name', 'is_active']);

        $invalid_fields = [
            'code' => ['invalid code', str_repeat('A', 51), ['array']],
            'name' => [str_repeat('A', 192), ['array']],
            'contact_name' => [str_repeat('A', 192), ['array']],
            'contact_email' => ['invalid-email', str_repeat('a', 245).'@example.test'],
            'contact_phone' => ['not-a-phone', '---', str_repeat('1', 31)],
            'address' => [str_repeat('A', 2001)],
            'notes' => [str_repeat('A', 2001)],
            'is_active' => ['invalid'],
        ];
        foreach ($invalid_fields as $field => $invalid_values) {
            foreach ($invalid_values as $value) {
                $this->post(route('subcontractor.store'), [...$this->vendor_payload(), $field => $value])
                    ->assertSessionHasErrors($field);
            }
        }
        $this->assertDatabaseCount('subcontractors', 0);
        $this->assertDatabaseMissing('audit_logs', ['event_name' => 'subcontractor.created']);
    }

    public function test_duplicate_codes_are_rejected_and_update_cannot_override_uuid(): void
    {
        $admin = $this->internal_user();
        $admin->assignRole(Role::findOrCreate('admin'));
        $vendor = Subcontractor::create($this->vendor_payload());
        $other_vendor = Subcontractor::create([...$this->vendor_payload(), 'code' => 'SUB-002']);
        $this->actingAs($admin)->post(route('subcontractor.store'), $this->vendor_payload())
            ->assertSessionHasErrors('code');
        $this->put(route('subcontractor.update', $other_vendor), $this->vendor_payload())
            ->assertSessionHasErrors('code');
        $this->put(route('subcontractor.update', $vendor), [
            ...$this->vendor_payload(), 'unique_id' => (string) Str::uuid(), 'id' => $other_vendor->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($vendor->unique_id, $vendor->fresh()->unique_id);
        $this->assertSame('SUB-002', $other_vendor->fresh()->code);
        $this->get('/subcontractor/'.$vendor->id.'/edit')->assertNotFound();
        $this->put('/subcontractor/'.$vendor->id, $this->vendor_payload())->assertNotFound();
        $this->get(route('subcontractor.edit', (string) Str::uuid()))->assertNotFound();
    }

    public function test_failed_audit_rolls_back_vendor_creation_and_update(): void
    {
        $user = $this->internal_user();
        $vendor = Subcontractor::create($this->vendor_payload());
        $service = app(subcontractor_service::class);
        DB::statement("CREATE TRIGGER reject_subcontractor_audit BEFORE INSERT ON audit_logs BEGIN SELECT RAISE(ABORT, 'Audit write failed'); END");

        try {
            foreach ([null, $vendor] as $target) {
                try {
                    $service->save_vendor([...$this->vendor_payload(), 'code' => 'SUB-NEW', 'is_active' => false], $user, $target);
                    $this->fail('An audit failure must abort the mutation.');
                } catch (QueryException $exception) {
                    $this->assertStringContainsString('Audit write failed', $exception->getMessage());
                }
            }
            $this->assertDatabaseCount('subcontractors', 1);
            $this->assertSame('SUB-001', $vendor->fresh()->code);
            $this->assertTrue($vendor->fresh()->is_active);
        } finally {
            DB::statement('DROP TRIGGER reject_subcontractor_audit');
        }
    }

    private function vendor_payload(): array
    {
        return [
            'code' => 'SUB-001', 'name' => 'CV. Berjaya Las', 'contact_name' => 'Kontak Vendor',
            'contact_phone' => '+62 812 0000 0000', 'contact_email' => 'vendor@example.test',
            'address' => 'Alamat Uji', 'notes' => 'Catatan internal', 'is_active' => '1',
        ];
    }

    private function internal_user(): User
    {
        return User::create([
            'employee_id' => '900000001', 'email' => 'internal@example.test', 'password' => 'secret-pass',
        ]);
    }
}
