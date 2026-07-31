<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_job_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('document_number')->nullable();
            $table->unsignedInteger('revision_no')->default(1);
            $table->string('status', 30)->default('draft');

            $table->foreignId('source_document_id')->nullable()->constrained('project_job_documents')->nullOnDelete();
            $table->unsignedInteger('source_revision_no')->nullable();

            $table->foreignId('prepared_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('approved_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'document_type']);
            $table->unique(['project_id', 'document_type', 'revision_no'], 'project_doc_type_revision_unique');
        });

        Schema::create('project_document_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_job_document_id')->constrained('project_job_documents')->cascadeOnDelete();
            $table->foreignId('source_job_id')->nullable()->constrained('project_document_jobs')->nullOnDelete();

            $table->string('job_name');
            $table->decimal('job_volume_estimated', 18, 4)->nullable();
            $table->string('responsible_kind', 30)->nullable();
            $table->string('responsible_name')->nullable();

            $table->date('est_start_date')->nullable();
            $table->date('est_finish_date')->nullable();
            $table->unsignedInteger('est_duration_days')->nullable();
            $table->decimal('progress_percent', 5, 2)->nullable();

            $table->decimal('est_price', 18, 2)->nullable();
            $table->char('est_currency', 3)->nullable();
            $table->decimal('job_weight_percent', 5, 2)->nullable();

            $table->date('actual_start_date')->nullable();
            $table->date('actual_finish_date')->nullable();
            $table->unsignedInteger('actual_duration_days')->nullable();
            $table->decimal('actual_cost', 18, 2)->nullable();
            $table->char('actual_currency', 3)->nullable();
            $table->decimal('actual_volume', 18, 4)->nullable();

            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['project_job_document_id', 'sort_order']);
        });

        Schema::create('project_document_job_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_document_job_id')->constrained('project_document_jobs')->cascadeOnDelete();

            $table->string('material_name');
            $table->decimal('volume', 18, 4)->nullable();
            $table->decimal('density', 18, 6)->nullable();
            $table->string('dimension')->nullable();
            $table->decimal('price', 18, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('diameter', 18, 4)->nullable();
            $table->decimal('length', 18, 4)->nullable();
            $table->decimal('thickness', 18, 4)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('project_document_job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_document_job_id')->constrained('project_document_jobs')->cascadeOnDelete();

            $table->string('photo_category', 30)->nullable();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('photo_category');
        });

        Schema::create('project_job_document_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_job_document_id')->constrained('project_job_documents')->cascadeOnDelete();
            $table->string('action', 50);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_job_document_histories');
        Schema::dropIfExists('project_document_job_photos');
        Schema::dropIfExists('project_document_job_materials');
        Schema::dropIfExists('project_document_jobs');
        Schema::dropIfExists('project_job_documents');
    }
};
