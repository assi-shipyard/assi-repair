<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('contract_number', 100);
            $table->string('status', 40);
            $table->date('signing_due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('project_contract_documents', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('project_contract_id')->constrained()->cascadeOnDelete();
            $table->string('document_name');
            $table->string('document_path');
            $table->string('status', 40);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_contract_documents');
        Schema::dropIfExists('project_contracts');
    }
};
