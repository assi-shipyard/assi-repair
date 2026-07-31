<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions by group
        $permissions = [
            // General
            'view-dashboard', 'view-settings', 'view-profile', 'edit-profile',

            // Company
            'add-company', 'edit-company', 'delete-company', 'view-company', 'view-company-data', 'export-company-data',

            // Ship
            'add-ship', 'edit-ship', 'delete-ship', 'view-ship', 'view-ship-data', 'export-ship-data',

            // Docking Space
            'add-docking-space', 'edit-docking-space', 'delete-docking-space', 'view-docking-space', 'view-docking-space-data', 'export-docking-space-data',

            // Ship Docking
            'add-ship-docking', 'edit-ship-docking', 'delete-ship-docking', 'view-ship-docking', 'view-ship-docking-data', 'export-ship-docking-data',

            // Project
            'create-new-project', 'edit-project', 'delete-project', 'view-project', 'view-project-data', 'export-project-data',

            // RAB Awal (Initial Budget)
            'edit-project-quotation', 'view-project-quotation', 'export-project-quotation',

            // Satisfaction Notes
            'edit-project-satisfaction-note', 'view-project-satisfaction-note', 'export-project-satisfaction-note',

            // RAB Akhir (Realization)
            'edit-project-realization', 'view-project-realization', 'export-project-realization',

            // User & Role Management
            'add-user', 'edit-user', 'delete-user', 'view-user',
            'add-role', 'edit-role', 'delete-role', 'view-role',
            'add-permission', 'edit-permission', 'delete-permission', 'view-permission',
        ];

        // Create all permissions if not exist
        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
                'permission_name' => $perm, // optional, for display
            ]);
        }

        // Role: admin (all permissions)
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
            'role_name' => 'Admin', // optional, for display
        ]);
        $admin->syncPermissions($permissions);

        // Role: director (limited permissions, only view and export)
        $directorPermissions = [
            'view-dashboard', 'view-settings', 'view-profile', 'edit-profile',
            'view-company', 'view-company-data', 'export-company-data',
            'view-ship', 'view-ship-data', 'export-ship-data',
            'view-docking-space', 'view-docking-space-data', 'export-docking-space-data',
            'view-ship-docking', 'view-ship-docking-data', 'export-ship-docking-data',
            'view-project', 'view-project-data', 'export-project-data',
            'view-project-quotation',
            'view-project-satisfaction-note',
            'view-project-realization',
            'view-user',
            'view-role',
            'view-permission'
        ];
        $director = Role::firstOrCreate(['name' => 'director', 'guard_name' => 'web']);
        $director->syncPermissions($directorPermissions);

        // Role: marketing-manager (limited permissions, only view and export)
        $marketingManagerPermissions = [
            'view-dashboard', 'view-settings', 'view-profile', 'edit-profile',
            'view-company', 'view-company-data', 'export-company-data',
            'view-ship', 'view-ship-data', 'export-ship-data',
            'view-docking-space', 'view-docking-space-data', 'export-docking-space-data',
            'view-ship-docking', 'view-ship-docking-data', 'export-ship-docking-data',
            'view-project', 'view-project-data', 'export-project-data',
            'view-project-quotation',
            'view-project-satisfaction-note',
            'view-project-realization',
        ];
        $marketingManager = Role::firstOrCreate(['name' => 'marketing-manager', 'guard_name' => 'web']);
        $marketingManager->syncPermissions($marketingManagerPermissions);

        // Role: marketing-staff (limited permission, but can edit company data, ship data, ship docking, and project)
        $marketingStaffPermissions = [
            'view-dashboard', 'view-settings', 'view-profile', 'edit-profile',
            'edit-company', 'edit-ship', 'edit-ship-docking', 'edit-project',
        ];
        $marketingStaff = Role::firstOrCreate(['name' => 'marketing-staff', 'guard_name' => 'web']);
        $marketingStaff->syncPermissions($marketingStaffPermissions);
    }
}
