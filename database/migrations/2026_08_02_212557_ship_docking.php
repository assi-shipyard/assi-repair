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
        if (!Schema::hasTable('docking_spaces')) {
            Schema::create('docking_spaces', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('max_draft');
                $table->string('max_tonnage');
                $table->string('max_breadth');
                $table->string('max_capacity');
                $table->string('location')->nullable();
                $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
                $table->decimal('max_length', 10, 2)->nullable();
                $table->decimal('max_width', 10, 2)->nullable();
                $table->decimal('max_weight', 12, 2)->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('docking_spaces', function (Blueprint $table) {
                if (!Schema::hasColumn('docking_spaces', 'location')) {
                    $table->string('location')->nullable()->after('max_capacity');
                }

                if (!Schema::hasColumn('docking_spaces', 'status')) {
                    $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active')->after('location');
                }

                if (!Schema::hasColumn('docking_spaces', 'max_length')) {
                    $table->decimal('max_length', 10, 2)->nullable()->after('status');
                }

                if (!Schema::hasColumn('docking_spaces', 'max_width')) {
                    $table->decimal('max_width', 10, 2)->nullable()->after('max_length');
                }

                if (!Schema::hasColumn('docking_spaces', 'max_weight')) {
                    $table->decimal('max_weight', 12, 2)->nullable()->after('max_width');
                }
            });
        }

        Schema::create('project_docking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('requested_docking_space_id')->nullable()->constrained('docking_spaces')->nullOnDelete();
            $table->dateTime('requested_start_at');
            $table->dateTime('requested_end_at')->nullable();
            $table->text('request_notes')->nullable();
            $table->enum('request_status', ['draft', 'submitted', 'reviewed', 'approved', 'rejected', 'cancelled'])->default('submitted');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['request_status', 'requested_start_at']);
            $table->index(['ship_id', 'requested_start_at']);
        });

        Schema::create('docking_capacity_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_docking_request_id')->constrained('project_docking_requests')->cascadeOnDelete();
            $table->foreignId('docking_space_id')->constrained('docking_spaces')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
            $table->boolean('is_compatible')->default(false);
            $table->unsignedTinyInteger('compatibility_score')->nullable();
            $table->json('compatibility_detail')->nullable();
            $table->json('ship_snapshot')->nullable();
            $table->json('docking_space_snapshot')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('evaluated_at')->nullable();
            $table->timestamps();

            $table->unique(['project_docking_request_id', 'docking_space_id'], 'dock_eval_request_space_unique');
        });

        Schema::create('docking_occupancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
            $table->foreignId('docking_space_id')->constrained('docking_spaces')->cascadeOnDelete();
            $table->foreignId('project_docking_request_id')->nullable()->constrained('project_docking_requests')->nullOnDelete();
            $table->dateTime('docked_at');
            $table->dateTime('estimated_undock_at')->nullable();
            $table->dateTime('undocked_at')->nullable();
            $table->enum('occupancy_status', ['scheduled', 'occupied', 'undocked', 'cancelled'])->default('scheduled');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['docking_space_id', 'occupancy_status', 'undocked_at'], 'dock_occupancy_status_idx');
            $table->index(['project_id', 'ship_id']);
        });

        Schema::create('floating_repair_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
            $table->foreignId('docking_occupancy_id')->nullable()->constrained('docking_occupancies')->nullOnDelete();
            $table->dateTime('floating_started_at');
            $table->dateTime('floating_completed_at')->nullable();
            $table->enum('floating_status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'ship_id', 'floating_status'], 'floating_history_project_ship_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floating_repair_histories');
        Schema::dropIfExists('docking_occupancies');
        Schema::dropIfExists('docking_capacity_evaluations');
        Schema::dropIfExists('project_docking_requests');

        if (Schema::hasTable('docking_spaces')) {
            Schema::table('docking_spaces', function (Blueprint $table) {
                if (Schema::hasColumn('docking_spaces', 'max_weight')) {
                    $table->dropColumn('max_weight');
                }

                if (Schema::hasColumn('docking_spaces', 'max_width')) {
                    $table->dropColumn('max_width');
                }

                if (Schema::hasColumn('docking_spaces', 'max_length')) {
                    $table->dropColumn('max_length');
                }

                if (Schema::hasColumn('docking_spaces', 'status')) {
                    $table->dropColumn('status');
                }

                if (Schema::hasColumn('docking_spaces', 'location')) {
                    $table->dropColumn('location');
                }
            });
        }
    }
};
