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
    // Table for docking spaces
    Schema::create('docking_spaces', function (Blueprint $table) {
		$table->id();
		$table->string('name');
		$table->string('max_draft');
		$table->string('max_tonnage');
		$table->string('max_breadth');
		$table->string('max_capacity');
		$table->timestamps();
    });

    // Table for ship docking requests (with approval)
    Schema::create('ship_docking_requests', function (Blueprint $table) {
		$table->id();
		// Marketing scheduling fields
		$table->dateTime('requested_docking_date'); // date/time marketing wants to dock
		$table->unsignedBigInteger('requested_docking_space_id')->nullable(); // preferred docking space by marketing
		$table->unsignedBigInteger('ship_id');
		$table->unsignedBigInteger('requested_by')->nullable();
		$table->dateTime('requested_at');

		// Engineering approval and recommendation
		$table->enum('engineering_status', ['pending', 'recommended', 'not_recommended', 'overridden', 'approved', 'rejected'])->default('pending');
		$table->json('engineering_recommendation')->nullable(); // recommended docking_space_id(s) or "Not to Dock"
		$table->unsignedBigInteger('engineering_approved_by')->nullable();
		$table->dateTime('engineering_approved_at')->nullable();
		$table->unsignedBigInteger('final_docking_space_id')->nullable(); // chosen by engineering, can override recommendation
		$table->text('engineering_override_reason')->nullable();

		// Production department approval (handles ship docking and repair)
		$table->enum('second_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
		$table->unsignedBigInteger('second_approved_by')->nullable();
		$table->dateTime('second_approved_at')->nullable();

		$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // overall status
		$table->timestamps();

		$table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
		$table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
		$table->foreign('requested_docking_space_id')->references('id')->on('docking_spaces')->onDelete('set null');
		$table->foreign('engineering_approved_by')->references('id')->on('users')->onDelete('set null');
		$table->foreign('final_docking_space_id')->references('id')->on('docking_spaces')->onDelete('set null');
		$table->foreign('second_approved_by')->references('id')->on('users')->onDelete('set null');
    });

    // Table for tracking current ships on dock
    Schema::create('current_ship_dockings', function (Blueprint $table) {
		$table->id();
		$table->unsignedBigInteger('ship_id');
		$table->unsignedBigInteger('docking_space_id');
		$table->dateTime('docked_at');
		$table->dateTime('expected_departure')->nullable();
		$table->dateTime('actual_departure')->nullable();
		$table->timestamps();

		$table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
		$table->foreign('docking_space_id')->references('id')->on('docking_spaces')->onDelete('cascade');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_ship_dockings');
        Schema::dropIfExists('ship_docking_requests');
        Schema::dropIfExists('docking_spaces');
    }
};
