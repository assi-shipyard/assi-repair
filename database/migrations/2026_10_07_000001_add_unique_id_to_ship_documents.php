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
        if (! Schema::hasTable('ship_documents') || Schema::hasColumn('ship_documents', 'unique_id')) {
            return;
        }

        Schema::table('ship_documents', function (Blueprint $table): void {
            $table->string('unique_id', 36)->nullable()->after('id');
        });

        DB::table('ship_documents')
            ->whereNull('unique_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('ship_documents')
                        ->where('id', $row->id)
                        ->update(['unique_id' => (string) Str::uuid()]);
                }
            });

        Schema::table('ship_documents', function (Blueprint $table): void {
            $table->unique('unique_id', 'ship_documents_unique_id_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ship_documents') || ! Schema::hasColumn('ship_documents', 'unique_id')) {
            return;
        }

        Schema::table('ship_documents', function (Blueprint $table): void {
            $table->dropUnique('ship_documents_unique_id_unique');
            $table->dropColumn('unique_id');
        });
    }
};
