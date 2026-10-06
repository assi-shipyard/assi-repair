<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE organizational_units MODIFY type ENUM('ceo', 'chrgao', 'cfo', 'cpo', 'directorate', 'division', 'bureau', 'subdivision', 'workshop') NOT NULL");
            DB::statement("ALTER TABLE positions MODIFY category ENUM('director', 'c_suite', 'manager', 'head_of_bureau', 'assistant_manager', 'supervisor_staff', 'pelaksana') NOT NULL DEFAULT 'supervisor_staff'");
        }

        DB::table('positions')->where('category', 'director')->update(['category' => 'c_suite']);

        Schema::table('positions', function (Blueprint $table): void {
            $table->dropUnique('positions_name_unique');
            $table->unique(['name', 'organizational_unit_id'], 'positions_name_unit_unique');
        });
    }

    public function down(): void
    {
        $has_new_unit_types = DB::table('organizational_units')
            ->whereIn('type', ['ceo', 'chrgao', 'cfo', 'cpo', 'bureau'])
            ->exists();

        if ($has_new_unit_types) {
            throw new \RuntimeException('Cannot roll back organizational structure while executive or bureau units exist.');
        }

        $has_duplicate_position_names = DB::table('positions')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($has_duplicate_position_names) {
            throw new \RuntimeException('Cannot roll back while position names are duplicated across organizational units.');
        }

        DB::table('positions')->where('category', 'c_suite')->update(['category' => 'director']);
        DB::table('positions')->where('category', 'head_of_bureau')->update(['category' => 'manager']);

        Schema::table('positions', function (Blueprint $table): void {
            $table->dropUnique('positions_name_unit_unique');
            $table->unique('name', 'positions_name_unique');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE positions MODIFY category ENUM('director', 'manager', 'assistant_manager', 'supervisor_staff', 'pelaksana') NOT NULL DEFAULT 'supervisor_staff'");
            DB::statement("ALTER TABLE organizational_units MODIFY type ENUM('directorate', 'division', 'subdivision', 'workshop') NOT NULL");
        }
    }
};
