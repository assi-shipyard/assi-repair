<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->foreignId('recommended_docking_space_id')->nullable()->constrained('docking_spaces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('recommended_docking_space_id');
        });
    }
};
