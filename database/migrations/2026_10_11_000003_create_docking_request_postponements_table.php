<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docking_request_postponements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_docking_request_id')->constrained('project_docking_requests')->cascadeOnDelete();
            $table->foreignId('docking_space_id')->nullable()->constrained('docking_spaces')->nullOnDelete();
            $table->dateTime('previous_start_at');
            $table->dateTime('previous_end_at')->nullable();
            $table->dateTime('new_start_at');
            $table->dateTime('new_end_at');
            $table->text('reason');
            $table->foreignId('postponed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docking_request_postponements');
    }
};
