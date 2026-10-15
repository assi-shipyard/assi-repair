<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_document_job_materials', function (Blueprint $table): void {
            $table->boolean('owner_supplied')->default(false)->after('material_name');
        });
    }

    public function down(): void
    {
        Schema::table('project_document_job_materials', function (Blueprint $table): void {
            $table->dropColumn('owner_supplied');
        });
    }
};
