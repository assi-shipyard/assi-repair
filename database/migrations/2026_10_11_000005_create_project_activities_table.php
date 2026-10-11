<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_activities', function (Blueprint $table): void {
            $table->id();
            $table->string('unique_id', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('event', 40);
            $table->string('description');
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
