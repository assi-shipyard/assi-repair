<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_job_documents', function (Blueprint $table): void {
            $table->text('revision_reason')->nullable()->after('notes');
            $table->timestamp('submitted_at')->nullable()->after('revision_reason');
            $table->unsignedBigInteger('submitted_by_user_id')->nullable()->after('submitted_at');
            $table->timestamp('superseded_at')->nullable()->after('submitted_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_job_documents', function (Blueprint $table): void {
            $table->dropColumn(['revision_reason', 'submitted_at', 'submitted_by_user_id', 'superseded_at']);
        });
    }
};
