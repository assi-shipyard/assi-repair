<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Master reference data used by ship/company seeding.
        // 2) Authorization data and admin account.
        // 3) Notification flags and docking spaces.
        // 4) Operational dummy datasets (depends on all previous seeders).
        $this->call([
            InitialSeeder::class,
            RolePermissionSeeder::class,
            AdminSeeder::class,
            NotificationFlagSeeder::class,
            DockingSpaceSeeder::class,
            DummyCompanyShipDockingSeeder::class,
        ]);
    }
}
