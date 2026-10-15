<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DockingSpace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DockingSpaceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'name' => 'Dock Uji',
            'location' => 'Area Timur',
            'status' => 'active',
            'max_draft' => '6.50',
            'max_tonnage' => '5000.25',
            'max_breadth' => '20',
            'max_capacity' => '2',
        ];
    }

    private function sign_in(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_creating_a_space_persists_required_capacity_fields_and_audit_log(): void
    {
        $user = $this->sign_in();

        $this->post(route('docking-space.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('docking-space.index'));

        $space = DockingSpace::sole();
        $this->assertSame('5000.25', $space->max_tonnage);
        $this->assertNull($space->max_weight);
        $this->assertTrue(Str::isUuid($space->unique_id));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'route_name' => 'docking-space.store',
            'response_status' => 302,
        ]);
    }

    public function test_updating_a_space_persists_tonnage_without_overwriting_weight(): void
    {
        $user = $this->sign_in();
        $space = DockingSpace::create([...$this->payload(), 'max_weight' => '4000']);

        $this->put(route('docking-space.update', $space->unique_id), [
            ...$this->payload(),
            'max_tonnage' => '6000.75',
        ])->assertSessionHasNoErrors()->assertRedirect(route('docking-space.index'));

        $space->refresh();
        $this->assertSame('6000.75', $space->max_tonnage);
        $this->assertSame('4000.00', $space->max_weight);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'route_name' => 'docking-space.update',
            'response_status' => 302,
        ]);
    }

    public function test_omitted_tonnage_returns_a_validation_error_instead_of_a_database_error(): void
    {
        $this->sign_in();
        $payload = $this->payload();
        unset($payload['max_tonnage']);

        $this->post(route('docking-space.store'), $payload)
            ->assertSessionHasErrors([
                'max_tonnage' => 'tonase maksimum wajib diisi.',
            ]);

        $this->assertDatabaseCount('docking_spaces', 0);
    }

    #[DataProvider('invalid_capacity_values')]
    public function test_invalid_capacity_is_rejected_on_create_and_update(string $field, mixed $value): void
    {
        $this->sign_in();
        $space = DockingSpace::create($this->payload());
        $payload = [...$this->payload(), $field => $value];

        $this->post(route('docking-space.store'), $payload)
            ->assertSessionHasErrors($field);
        $this->put(route('docking-space.update', $space->unique_id), $payload)
            ->assertSessionHasErrors($field);

        $this->assertSame(1, DockingSpace::count());
        $this->assertDatabaseHas('docking_spaces', ['id' => $space->id, ...$this->payload()]);
    }

    public static function invalid_capacity_values(): array
    {
        return [
            'missing draft' => ['max_draft', null],
            'missing tonnage' => ['max_tonnage', null],
            'missing breadth' => ['max_breadth', null],
            'missing capacity' => ['max_capacity', null],
            'invalid tonnage' => ['max_tonnage', 'invalid'],
            'negative tonnage' => ['max_tonnage', -1],
            'zero tonnage' => ['max_tonnage', 0],
            'negative draft' => ['max_draft', -1],
            'negative breadth' => ['max_breadth', -1],
            'zero capacity' => ['max_capacity', 0],
            'fractional capacity' => ['max_capacity', 1.5],
            'oversized length' => ['max_length', '100000000'],
            'oversized width' => ['max_width', '100000000'],
            'oversized weight' => ['max_weight', '10000000000'],
        ];
    }

    public function test_form_shows_tonnage_for_creation_and_editing(): void
    {
        $this->sign_in();
        $space = DockingSpace::create($this->payload());

        $this->get(route('docking-space.index'))
            ->assertOk()
            ->assertSee('name="max_tonnage"', false)
            ->assertSee('Tonase Maksimum (GT)');
        $this->get(route('docking-space.edit', $space->unique_id))
            ->assertOk()
            ->assertSee('value="5000.25"', false);
    }

    public function test_directory_shows_statuses_specs_and_uuid_actions(): void
    {
        $this->sign_in();
        $space = DockingSpace::create([
            ...$this->payload(),
            'name' => 'Dock <Uji>',
            'max_width' => '21',
        ]);
        DockingSpace::create([...$this->payload(), 'name' => 'Dock Perawatan', 'status' => 'maintenance']);
        DockingSpace::create([...$this->payload(), 'name' => 'Dock Lama', 'status' => 'inactive']);

        $this->get(route('docking-space.index'))
            ->assertOk()
            ->assertSee('Data Dock')
            ->assertSee('Total Dock')
            ->assertSee('3 dock ditampilkan')
            ->assertSee('Pemeliharaan')
            ->assertSee('Nonaktif')
            ->assertSee('Dock &lt;Uji&gt;', false)
            ->assertDontSee('Dock <Uji>', false)
            ->assertSee('21.00 m')
            ->assertSee('id="dock-search"', false)
            ->assertSee('id="dock-status"', false)
            ->assertSee(route('docking-space.edit', $space->unique_id), false)
            ->assertSee(route('docking-space.destroy', $space->unique_id), false);

        $this->get(route('docking-space.edit', $space->unique_id))
            ->assertOk()
            ->assertSee('Sedang diubah')
            ->assertSee('Simpan Perubahan')
            ->assertSee('Batal');
    }

    public function test_empty_directory_guides_users_to_the_form(): void
    {
        $this->sign_in();

        $this->get(route('docking-space.index'))
            ->assertOk()
            ->assertSee('Belum ada data dock')
            ->assertSee('Tambah Dock Pertama')
            ->assertSee('id="docking-space-form"', false)
            ->assertDontSee('data-dock-card data-name=', false);
    }

    public function test_validation_errors_keep_input_and_appear_beside_capacity_fields(): void
    {
        $this->sign_in();
        $this->from(route('docking-space.index'))
            ->post(route('docking-space.store'), [...$this->payload(), 'max_tonnage' => null])
            ->assertRedirect(route('docking-space.index'));

        $this->get(route('docking-space.index'))
            ->assertOk()
            ->assertSee('value="Dock Uji"', false)
            ->assertSee('tonase maksimum wajib diisi.')
            ->assertSee('<div class="invalid-feedback">tonase maksimum wajib diisi.</div>', false);
    }
}
