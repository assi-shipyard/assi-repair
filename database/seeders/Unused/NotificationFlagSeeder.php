<?php

declare(strict_types=1);

namespace Database\Seeders\Unused;

use Illuminate\Database\Seeder;
use App\Models\NotificationFlag;

class NotificationFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NotificationFlag::firstOrCreate(
            ['code' => 'project.created'],
            ['name' => 'Proyek Baru Dibuat', 'description' => 'Menerima notifikasi ketika ada proyek baru yang dibuat di dalam sistem.']
        );

        NotificationFlag::firstOrCreate(
            ['code' => 'project.finished'],
            ['name' => 'Proyek Selesai', 'description' => 'Menerima notifikasi ketika proyek telah ditandai sebagai selesai.']
        );
    }
}
