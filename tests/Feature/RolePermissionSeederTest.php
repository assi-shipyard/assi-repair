<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_roles_are_seeded_with_only_staf_it_getting_admin_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $position_role_labels = [
            'ceo' => 'CEO (Direktur Utama)',
            'chrga' => 'CHRGA (Direktur SDM dan Umum)',
            'cpo' => 'CPO (Direktur Produksi)',
            'cfo' => 'CFO (Direktur Keuangan)',
            'engineering' => 'Engineering (Biro Litbang)',
            'legal' => 'Legal',
            'marketing' => 'Marketing',
            'marketing-manager' => 'Marketing Manager (Manajer Marketing)',
            'repair-manager' => 'Repair Manager (Manajer Reparasi dan Rekayasa Umum)',
            'repair-ppc' => 'Repair PPC (Staf PPC RRU)',
            'repair-project-leader' => 'Repair Project Leader (PIMPRO Reparasi/Pimpinan Proyek)',
            'newbuilding-manager' => 'Newbuilding Manager (Manajer Bangunan Baru)',
            'newbuilding-ppc' => 'Newbuilding PPC (Staf PPC BB)',
            'newbuilding-project-leader' => 'Newbuilding Project Leader (PIMPRO Bangunan Baru)',
            'staf-it' => 'Staf IT',
        ];

        $admin_permissions = Role::findByName('admin', 'web')
            ->getPermissionNames()
            ->sort()
            ->values()
            ->all();

        foreach ($position_role_labels as $name => $label) {
            $role = Role::findByName($name, 'web');

            $this->assertSame($label, $role->role_name);
            $this->assertSame(1, Role::query()->where('name', $name)->count());

            if ($name === 'staf-it') {
                $it_permissions = $role->getPermissionNames()->sort()->values()->all();
                $this->assertSame($admin_permissions, $it_permissions);

                continue;
            }

            $this->assertCount(0, $role->permissions);
        }
    }
}
