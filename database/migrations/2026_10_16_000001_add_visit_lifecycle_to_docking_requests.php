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
            $table->string('visit_stage', 30)->nullable()->index();
            $table->dateTime('arrived_at')->nullable();
            $table->foreignId('arrived_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('arrival_notes')->nullable();
            $table->dateTime('administration_completed_at')->nullable();
            $table->foreignId('administration_completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('administration_notes')->nullable();
            $table->dateTime('departed_at')->nullable();
            $table->foreignId('departed_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('departure_notes')->nullable();
        });

        DB::table('project_docking_requests')
            ->where('request_status', 'approved')
            ->update(['visit_stage' => 'awaiting_arrival']);

        // Approved requests that already reached the dock before this lifecycle existed.
        $docked_ids = DB::table('docking_occupancies')
            ->where('occupancy_status', 'occupied')
            ->whereNotNull('project_docking_request_id')
            ->pluck('project_docking_request_id');

        DB::table('project_docking_requests')->whereIn('id', $docked_ids)
            ->update(['visit_stage' => 'docked', 'arrived_at' => DB::raw('requested_start_at')]);

        $floating_ids = DB::table('floating_repair_histories')
            ->join('docking_occupancies', 'docking_occupancies.id', '=', 'floating_repair_histories.docking_occupancy_id')
            ->where('floating_repair_histories.floating_status', 'active')
            ->whereNotNull('docking_occupancies.project_docking_request_id')
            ->pluck('docking_occupancies.project_docking_request_id');

        DB::table('project_docking_requests')->whereIn('id', $floating_ids)
            ->update(['visit_stage' => 'floating', 'arrived_at' => DB::raw('requested_start_at')]);
    }

    public function down(): void
    {
        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('arrived_recorded_by');
            $table->dropConstrainedForeignId('administration_completed_by');
            $table->dropConstrainedForeignId('departed_recorded_by');
            $table->dropIndex(['visit_stage']);
            $table->dropColumn([
                'visit_stage', 'arrived_at', 'arrival_notes',
                'administration_completed_at', 'administration_notes',
                'departed_at', 'departure_notes',
            ]);
        });
    }
};
