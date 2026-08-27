<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Kredensial Administrator
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh Database\Seeders\AdminSeeder. Kata sandi tidak pernah
    | disimpan di dalam kode; isi ADMIN_PASSWORD pada file .env sebelum
    | menjalankan seeder di environment production.
    |
    */

    'admin' => [
        'employee_id' => env('ADMIN_EMPLOYEE_ID', 'assiadmin'),
        'email' => env('ADMIN_EMAIL'),
        'name' => env('ADMIN_NAME', 'Administrator Sistem'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
