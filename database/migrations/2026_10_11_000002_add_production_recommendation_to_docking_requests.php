<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->string('production_recommendation', 20)->nullable();
            $table->dateTime('recommended_start_at')->nullable();
            $table->dateTime('recommended_end_at')->nullable();
            $table->foreignId('marketing_responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('marketing_responded_at')->nullable();
            $table->string('marketing_response', 20)->nullable();
            $table->text('marketing_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_docking_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('marketing_responded_by');
            $table->dropColumn([
                'production_recommendation', 'recommended_start_at', 'recommended_end_at',
                'marketing_responded_at', 'marketing_response', 'marketing_notes',
            ]);
        });
    }
};
