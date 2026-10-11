<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DockingOccupancy;
use App\Models\DockingSpace;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DockingDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_login(): void
    {
        $this->get(route('docking-dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_summarises_dock_usage_and_overdue_ships(): void
    {
        $user = User::create(['employee_id' => '111111111', 'email' => 'a@example.test', 'password' => 'secret-pass']);
        $busy = DockingSpace::create(['name' => 'Dock Sibuk', 'status' => 'active', 'max_length' => 100, 'max_breadth' => 20, 'max_width' => 20, 'max_draft' => 6, 'max_tonnage' => 5000, 'max_weight' => 5000, 'max_capacity' => 1]);
        DockingSpace::create(['name' => 'Dock Kosong', 'status' => 'active', 'max_length' => 100, 'max_breadth' => 20, 'max_width' => 20, 'max_draft' => 6, 'max_tonnage' => 5000, 'max_weight' => 5000, 'max_capacity' => 1]);

        $company = Company::create(['name' => 'PT Uji', 'unique_id' => (string) Str::uuid(), 'address' => 'Jakarta']);
        $ship_id = Ship::create(['name' => 'KM Lambat', 'company_id' => $company->id, 'length_overall' => 80, 'breadth' => 14, 'height' => 10, 'empty_draft' => 3, 'gross_tonnage' => 2000])->id;

        DockingOccupancy::create([
            'ship_id' => $ship_id, 'docking_space_id' => $busy->id, 'occupancy_status' => 'occupied',
            'docked_at' => now()->subDays(20), 'estimated_undock_at' => now()->subDays(2),
        ]);

        $this->actingAs($user)->get(route('docking-dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Docking')
            ->assertSee('Terlambat keluar')
            ->assertSee('Dock Sibuk')
            ->assertSee('Dock Kosong');
    }
}
