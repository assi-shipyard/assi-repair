<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE positions MODIFY category ENUM('c_suite', 'manager', 'head', 'staff', 'director', 'assistant_manager', 'supervisor_staff', 'pelaksana') NOT NULL DEFAULT 'staff'");
        }

        DB::table('positions')->where('category', 'c_suite')->update(['category' => 'director']);
        DB::table('positions')->where('category', 'head')->update(['category' => 'assistant_manager']);
        DB::table('positions')->where('category', 'staff')->update(['category' => 'supervisor_staff']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE positions MODIFY category ENUM('director', 'manager', 'assistant_manager', 'supervisor_staff', 'pelaksana') NOT NULL DEFAULT 'supervisor_staff'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE positions MODIFY category ENUM('c_suite', 'manager', 'head', 'staff', 'director', 'assistant_manager', 'supervisor_staff', 'pelaksana') NOT NULL DEFAULT 'supervisor_staff'");
        }

        DB::table('positions')->where('category', 'director')->update(['category' => 'c_suite']);
        DB::table('positions')->where('category', 'assistant_manager')->update(['category' => 'head']);
        DB::table('positions')->where('category', 'supervisor_staff')->update(['category' => 'staff']);
        DB::table('positions')->where('category', 'pelaksana')->update(['category' => 'staff']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE positions MODIFY category ENUM('c_suite', 'manager', 'head', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
