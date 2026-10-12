<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Revisions and later phases clone jobs with new ids; the lineage keeps progress history attached to the same job.
        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->uuid('lineage_uuid')->nullable()->after('source_job_id')->index();
        });

        $lineages = [];
        DB::table('project_document_jobs')->select(['id', 'source_job_id'])->lazyById(500)->each(function (object $job) use (&$lineages): void {
            $lineages[$job->id] = $lineages[$job->source_job_id] ?? (string) Str::uuid();
            DB::table('project_document_jobs')->where('id', $job->id)->update(['lineage_uuid' => $lineages[$job->id]]);
        });

        Schema::create('project_progress_reports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_job_document_id')->nullable()->constrained('project_job_documents')->nullOnDelete();
            $table->date('report_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('reported_progress', 5, 2)->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('leader_approved_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('leader_approved_at')->nullable();
            $table->foreignId('ppc_approved_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('ppc_approved_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('returned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->string('return_reason', 1000)->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'report_date']);
            $table->index(['project_id', 'status']);
        });

        Schema::create('project_progress_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('project_progress_report_id')->constrained('project_progress_reports')->cascadeOnDelete();
            $table->foreignId('project_document_job_id')->nullable()->constrained('project_document_jobs')->nullOnDelete();
            $table->uuid('job_lineage_uuid')->index();
            $table->string('job_name');
            $table->decimal('previous_percent', 5, 2)->default(0);
            $table->decimal('progress_percent', 5, 2);
            $table->decimal('weight_percent', 7, 4)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['project_progress_report_id', 'job_lineage_uuid'], 'progress_entries_report_lineage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_progress_entries');
        Schema::dropIfExists('project_progress_reports');

        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->dropIndex(['lineage_uuid']);
            $table->dropColumn('lineage_uuid');
        });
    }
};
