<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->update_enum_columns(
            ['ceo', 'chrgao', 'cfo', 'cpo', 'executive_directorate', 'directorate', 'production_directorate', 'division', 'bureau', 'subdivision', 'workshop'],
            ['director', 'c_suite', 'manager', 'head_of_bureau', 'assistant_manager', 'supervisor_staff', 'pelaksana']
        );

        DB::transaction(function (): void {
            DB::table('organizational_units')
                ->where('type', 'ceo')
                ->update([
                    'type' => 'executive_directorate',
                    'name' => DB::raw("CASE WHEN name = 'CEO' THEN 'Direktorat Direksi' ELSE name END"),
                ]);

            DB::table('positions')
                ->where('category', 'director')
                ->update(['category' => 'c_suite']);

            $executive_units = DB::table('organizational_units')
                ->whereIn('type', ['chrgao', 'cpo', 'cfo'])
                ->get();

            foreach ($executive_units as $executive_unit) {
                $nested_directorates = DB::table('organizational_units')
                    ->where('parent_id', $executive_unit->id)
                    ->where('type', 'directorate')
                    ->get();

                if ($nested_directorates->count() > 1) {
                    throw new \RuntimeException('An executive unit has multiple nested directorates and requires manual consolidation.');
                }

                $nested_directorate = $nested_directorates->first();

                if ($nested_directorate !== null) {
                    $position_name_conflict = DB::table('positions as parent_positions')
                        ->join('positions as nested_positions', 'parent_positions.name', '=', 'nested_positions.name')
                        ->where('parent_positions.organizational_unit_id', $executive_unit->id)
                        ->where('nested_positions.organizational_unit_id', $nested_directorate->id)
                        ->exists();

                    if ($position_name_conflict) {
                        throw new \RuntimeException('A nested directorate contains a position name that already exists in its executive unit.');
                    }

                    $unit_name_conflict = DB::table('organizational_units as parent_units')
                        ->join('organizational_units as nested_units', function ($join): void {
                            $join->on('parent_units.name', '=', 'nested_units.name')
                                ->on('parent_units.type', '=', 'nested_units.type');
                        })
                        ->where('parent_units.parent_id', $executive_unit->id)
                        ->where('parent_units.id', '!=', $nested_directorate->id)
                        ->where('nested_units.parent_id', $nested_directorate->id)
                        ->exists();

                    if ($unit_name_conflict) {
                        throw new \RuntimeException('Merging a nested directorate would create duplicate sibling units.');
                    }

                    DB::table('positions')
                        ->where('organizational_unit_id', $nested_directorate->id)
                        ->update(['organizational_unit_id' => $executive_unit->id]);

                    DB::table('organizational_units')
                        ->where('parent_id', $nested_directorate->id)
                        ->update(['parent_id' => $executive_unit->id]);

                    DB::table('organizational_units')
                        ->where('id', $executive_unit->id)
                        ->update([
                            'name' => $nested_directorate->name,
                            'type' => $executive_unit->type === 'cpo' ? 'production_directorate' : 'directorate',
                        ]);

                    DB::table('organizational_units')
                        ->where('id', $nested_directorate->id)
                        ->delete();

                    continue;
                }

                $default_names = [
                    'chrgao' => 'Direktorat SDM dan Umum',
                    'cpo' => 'Direktorat Produksi',
                    'cfo' => 'Direktorat Keuangan',
                ];
                $name = $default_names[$executive_unit->type];

                DB::table('organizational_units')
                    ->where('id', $executive_unit->id)
                    ->update([
                        'name' => in_array($executive_unit->name, ['CHRGAO', 'CPO', 'CFO'], true)
                            ? $name
                            : $executive_unit->name,
                        'type' => $executive_unit->type === 'cpo' ? 'production_directorate' : 'directorate',
                    ]);
            }

            $executive_roots = DB::table('organizational_units')
                ->where('type', 'executive_directorate')
                ->get();

            if ($executive_roots->count() > 1) {
                throw new \RuntimeException('Multiple Direktorat Direksi units exist and require manual consolidation.');
            }

            $executive_root = $executive_roots->first();
            $orphaned_directorates = DB::table('organizational_units')
                ->whereIn('type', ['directorate', 'production_directorate'])
                ->whereNull('parent_id')
                ->get();

            if ($executive_root === null && $orphaned_directorates->isNotEmpty()) {
                $executive_root_id = DB::table('organizational_units')->insertGetId([
                    'name' => 'Direktorat Direksi',
                    'type' => 'executive_directorate',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $executive_root_id = $executive_root?->id;
            }

            if ($executive_root_id !== null) {
                DB::table('organizational_units')
                    ->where('type', 'directorate')
                    ->whereNull('parent_id')
                    ->update(['parent_id' => $executive_root_id]);
            }
        });

        $this->update_enum_columns(
            ['executive_directorate', 'directorate', 'production_directorate', 'division', 'bureau', 'subdivision', 'workshop'],
            ['c_suite', 'manager', 'head_of_bureau', 'assistant_manager', 'supervisor_staff', 'pelaksana']
        );
    }

    public function down(): void
    {
        if (DB::table('organizational_units')
            ->whereIn('type', ['executive_directorate', 'production_directorate'])
            ->exists()) {
            throw new \RuntimeException('Cannot roll back while executive directorate units exist.');
        }

        DB::table('positions')->where('category', 'c_suite')->update(['category' => 'director']);
        DB::table('positions')->where('category', 'head_of_bureau')->update(['category' => 'manager']);

        $this->update_enum_columns(
            ['ceo', 'chrgao', 'cfo', 'cpo', 'directorate', 'division', 'bureau', 'subdivision', 'workshop'],
            ['director', 'manager', 'assistant_manager', 'supervisor_staff', 'pelaksana']
        );
    }

    /**
     * @param list<string> $unit_types
     * @param list<string> $position_categories
     */
    private function update_enum_columns(array $unit_types, array $position_categories): void
    {
        if (DB::getDriverName() === 'mysql') {
            $unit_values = implode("','", $unit_types);
            $category_values = implode("','", $position_categories);
            DB::statement("ALTER TABLE organizational_units MODIFY type ENUM('$unit_values') NOT NULL");
            DB::statement("ALTER TABLE positions MODIFY category ENUM('$category_values') NOT NULL DEFAULT 'supervisor_staff'");

            return;
        }

    }
};
