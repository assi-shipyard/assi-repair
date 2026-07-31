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
        // Organizational units: directorate, division, subdivision, workshop.
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->enum('type', ['directorate', 'division', 'subdivision', 'workshop']);
            $table->foreignId('parent_id')->nullable()->constrained('organizational_units')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'type', 'parent_id']);
        });

        // Master positions table: each position belongs to one organizational unit.
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('level')->default(1);
            $table->enum('category', ['director', 'manager', 'assistant_manager', 'supervisor_staff', 'pelaksana'])->default('supervisor_staff');
            $table->boolean('is_head_position')->default(false);
            $table->string('code')->nullable()->unique();
            $table->foreignId('organizational_unit_id')->constrained('organizational_units')->cascadeOnDelete();
            $table->timestamps();
        });

        // Employees table: one employee holds exactly one position.
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_id', 64)->unique();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('direct_manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
        });

        // Employee profile table
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('organizational_units');
    }
};

