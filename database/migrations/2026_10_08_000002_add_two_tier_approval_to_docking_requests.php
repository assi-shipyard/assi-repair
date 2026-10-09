<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->string('request_status', 30)->default('submitted')->change();
        });

        DB::table('project_docking_requests')->where('request_status', 'reviewed')->update(['request_status' => 'submitted']);

        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->foreignId('engineering_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('engineering_approved_at')->nullable();
            $table->text('engineering_notes')->nullable();
            $table->foreignId('production_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('production_approved_at')->nullable();
            $table->text('production_notes')->nullable();
            $table->string('rejection_stage', 20)->nullable();
        });

        Schema::create('docking_request_documents', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('project_docking_request_id')->constrained('project_docking_requests')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('document_name');
            $table->string('document_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docking_request_documents');

        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('engineering_approved_by');
            $table->dropConstrainedForeignId('production_approved_by');
            $table->dropColumn([
                'engineering_approved_at',
                'engineering_notes',
                'production_approved_at',
                'production_notes',
                'rejection_stage',
            ]);
        });
    }
};
