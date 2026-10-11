<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RepairType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuPagesTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $employee_id): User
    {
        return User::create(['employee_id' => $employee_id, 'email' => $employee_id.'@example.test', 'password' => 'secret-pass']);
    }

    private function admin(): User
    {
        $user = $this->user('100000001');
        $user->assignRole(Role::findOrCreate('admin'));

        return $user;
    }

    public function test_project_history_page_is_reachable(): void
    {
        $this->actingAs($this->admin())->get(route('project.history'))
            ->assertOk()->assertSee('Riwayat Proyek')->assertDontSee('belum diaktifkan');
    }

    public function test_repair_type_crud_for_admin(): void
    {
        $admin = $this->admin();
        $payload = ['code' => 'rp-01', 'name' => 'Sandblasting', 'category' => 'painting', 'unit' => 'm2', 'standard_price' => '1500.50', 'is_active' => '1'];

        $this->actingAs($admin)->post(route('price-dictionary.store'), $payload)->assertRedirect(route('price-dictionary.index'));
        $repair_type = RepairType::firstOrFail();
        $this->assertSame('RP-01', $repair_type->code);

        $this->actingAs($admin)->put(route('price-dictionary.update', $repair_type), [...$payload, 'name' => 'Blasting'])->assertSessionHasNoErrors();
        $this->assertSame('Blasting', $repair_type->fresh()->name);

        $this->actingAs($admin)->get(route('price-dictionary.index'))->assertOk()->assertSee('Blasting');
        $this->actingAs($admin)->delete(route('price-dictionary.destroy', $repair_type))->assertRedirect();
        $this->assertDatabaseCount('repair_types', 0);
    }

    public function test_repair_type_write_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->user('100000002'))
            ->post(route('price-dictionary.store'), ['code' => 'X', 'name' => 'X', 'category' => 'hull', 'unit' => 'x'])
            ->assertForbidden();
    }
}
