<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\subcontractor;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

// PHPUnit requires the class name to match its *Test.php discovery filename.
class SubcontractorTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_details_are_persisted_without_employee_or_customer_records(): void
    {
        $vendor = subcontractor::create([
            'code' => 'SUB-001',
            'name' => 'Vendor Reparasi Uji',
            'contact_name' => 'Kontak Uji',
            'contact_phone' => '+62 812 0000 0000',
            'contact_email' => 'vendor@example.test',
            'address' => 'Alamat Vendor Uji',
            'notes' => 'Spesialis pekerjaan perpipaan.',
        ])->fresh();

        $this->assertNotNull($vendor);
        $this->assertTrue(Str::isUuid($vendor->unique_id));
        $this->assertTrue($vendor->is_active);
        $this->assertSame('Kontak Uji', $vendor->contact_name);
        $this->assertSame('+62 812 0000 0000', $vendor->contact_phone);
        $this->assertSame('vendor@example.test', $vendor->contact_email);
        $this->assertSame('Alamat Vendor Uji', $vendor->address);
        $this->assertSame('Spesialis pekerjaan perpipaan.', $vendor->notes);
        $this->assertDatabaseCount('employees', 0);
        $this->assertDatabaseCount('companies', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('project_document_jobs', 0);
    }

    public function test_optional_details_can_be_omitted(): void
    {
        $vendor = $this->create_vendor('SUB-001')->fresh();

        $this->assertNotNull($vendor);
        $this->assertNull($vendor->contact_phone);
        $this->assertNull($vendor->contact_email);
        $this->assertNull($vendor->address);
        $this->assertNull($vendor->notes);
    }

    public function test_public_binding_uses_uuid_instead_of_database_id(): void
    {
        $vendor = $this->create_vendor('SUB-001');
        $other_vendor = $this->create_vendor('SUB-002');

        $this->assertNotSame($vendor->unique_id, $other_vendor->unique_id);
        $this->assertSame('unique_id', $vendor->getRouteKeyName());
        $this->assertSame($vendor->unique_id, $vendor->getRouteKey());
        $this->assertTrue($vendor->is($vendor->resolveRouteBinding($vendor->unique_id)));
        $this->assertNull($vendor->resolveRouteBinding((string) $vendor->id));
    }

    public function test_inactive_vendors_are_retained_but_excluded_from_active_scope(): void
    {
        $active_vendor = $this->create_vendor('SUB-001');
        $inactive_vendor = $this->create_vendor('SUB-002');
        $inactive_vendor->update(['is_active' => false]);

        $this->assertFalse($inactive_vendor->fresh()->is_active);
        $this->assertSame([$active_vendor->id], subcontractor::query()->active()->pluck('id')->all());
        $this->assertDatabaseCount('subcontractors', 2);
    }

    public function test_vendor_codes_are_unique(): void
    {
        $this->create_vendor('SUB-001');

        $this->expectException(QueryException::class);

        $this->create_vendor('SUB-001');
    }

    public function test_vendor_uuids_are_unique(): void
    {
        $vendor = $this->create_vendor('SUB-001');
        $other_vendor = $this->create_vendor('SUB-002');
        $other_vendor->unique_id = $vendor->unique_id;

        $this->expectException(QueryException::class);

        $other_vendor->save();
    }

    public function test_migration_can_be_reversed_and_reapplied(): void
    {
        $migration = require database_path('migrations/2026_10_12_000001_create_subcontractors_table.php');

        $migration->down();
        $this->assertFalse(Schema::hasTable('subcontractors'));
        $this->assertTrue(Schema::hasTable('project_document_jobs'));
        $this->assertTrue(Schema::hasTable('employees'));

        $migration->up();
        $this->create_vendor('SUB-001');
        $this->assertDatabaseCount('subcontractors', 1);
    }

    private function create_vendor(string $code): subcontractor
    {
        return subcontractor::create([
            'code' => $code,
            'name' => 'Vendor Reparasi Uji',
            'contact_name' => 'Kontak Uji',
        ]);
    }
}
