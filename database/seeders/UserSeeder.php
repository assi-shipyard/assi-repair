<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Kredensial admin kini dikelola oleh AdminSeeder melalui environment.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminSeeder::class);
    }
}
