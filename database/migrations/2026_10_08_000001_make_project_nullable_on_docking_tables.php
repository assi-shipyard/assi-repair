<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['project_docking_requests', 'docking_occupancies', 'floating_repair_histories'];

    public function up(): void
    {
        foreach ($this->tables as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->unsignedBigInteger('project_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->unsignedBigInteger('project_id')->nullable(false)->change();
            });
        }
    }
};
