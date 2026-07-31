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
        Schema::create('projects', function (Blueprint $table) {
			$table->id();
			$table->string('unique_id')->unique();
			$table->string('project_code')->unique();
			$table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
			$table->foreignId('project_leader_employee_id')->nullable()->constrained('employees')->nullOnDelete();
			$table->foreignId('project_ppc_employee_id')->nullable()->constrained('employees')->nullOnDelete();
			$table->string('project_type');

			$table->date('start_date_estimation')->nullable();
			$table->date('end_date_estimation')->nullable();
			$table->date('start_date_actual')->nullable();
			$table->date('end_date_actual')->nullable();

			$table->decimal('progress', 5, 2)->default(0);
			$table->enum('status', ['Not Started', 'In Progress', 'Completed'])->default('Not Started');

			$table->text('comment')->nullable();
			$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

			$table->timestamps();
		});

		Schema::create('project_divisions', function (Blueprint $table) {
			$table->id();
			$table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
			$table->foreignId('organizational_unit_id')->constrained('organizational_units')->cascadeOnDelete();
			$table->timestamps();

			$table->unique(['project_id', 'organizational_unit_id']);
		});

		Schema::create('project_owner_surveyors', function (Blueprint $table) {
			$table->id();
			$table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
			$table->string('name');
			$table->string('company')->nullable();
			$table->string('position')->nullable();
			$table->string('email')->nullable();
			$table->string('phone')->nullable();

			$table->timestamps();
		});

		Schema::create('project_documents', function (Blueprint $table) {
			$table->id();
			$table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
			$table->string('document_name');
			$table->string('document_type');
			$table->string('document_path');
			$table->timestamps();
		});

		Schema::create('project_access', function (Blueprint $table) {
			$table->id();
			$table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
			$table->string('password');
			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::dropIfExists('project_access');
		Schema::dropIfExists('project_documents');
		Schema::dropIfExists('project_owner_surveyors');
		Schema::dropIfExists('project_divisions');
		Schema::dropIfExists('projects');
    }
};
