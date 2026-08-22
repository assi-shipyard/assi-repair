<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'ships',
            'employees',
            'organizational_units',
            'positions',
            'docking_spaces',
            'company_documents',
            'project_docking_requests',
            'docking_occupancies',
            'project_job_documents',
            'project_document_jobs',
            'project_document_job_materials',
            'project_document_job_photos',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'unique_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('unique_id', 36)->nullable()->after('id');
            });

            DB::table($table)
                ->whereNull('unique_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['unique_id' => (string) Str::uuid()]);
                    }
                });

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->unique('unique_id', $table.'_unique_id_unique');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'ships',
            'employees',
            'organizational_units',
            'positions',
            'docking_spaces',
            'company_documents',
            'project_docking_requests',
            'docking_occupancies',
            'project_job_documents',
            'project_document_jobs',
            'project_document_job_materials',
            'project_document_job_photos',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'unique_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique($table.'_unique_id_unique');
                $blueprint->dropColumn('unique_id');
            });
        }
    }
};
