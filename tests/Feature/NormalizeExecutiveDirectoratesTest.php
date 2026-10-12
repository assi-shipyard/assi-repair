<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class NormalizeExecutiveDirectoratesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_cpo_and_nested_directorate_are_merged_without_losing_positions_or_divisions(): void
    {
        Schema::table('organizational_units', function (Blueprint $table): void {
            $table->enum('type', ['ceo', 'chrgao', 'cfo', 'cpo', 'directorate', 'division', 'bureau', 'subdivision', 'workshop'])->change();
        });
        Schema::table('positions', function (Blueprint $table): void {
            $table->enum('category', ['director', 'manager', 'assistant_manager', 'supervisor_staff', 'pelaksana'])->default('supervisor_staff')->change();
        });

        $timestamp = now();
        $executive_directorate_id = DB::table('organizational_units')->insertGetId([
            'name' => 'CEO',
            'type' => 'ceo',
            'parent_id' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $production_unit_id = DB::table('organizational_units')->insertGetId([
            'name' => 'CPO',
            'type' => 'cpo',
            'parent_id' => $executive_directorate_id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $nested_directorate_id = DB::table('organizational_units')->insertGetId([
            'name' => 'Direktorat Produksi',
            'type' => 'directorate',
            'parent_id' => $production_unit_id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $division_id = DB::table('organizational_units')->insertGetId([
            'name' => 'Divisi RRU',
            'type' => 'division',
            'parent_id' => $nested_directorate_id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $orphaned_directorate_id = DB::table('organizational_units')->insertGetId([
            'name' => 'Direktorat Administrasi',
            'type' => 'directorate',
            'parent_id' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        DB::table('positions')->insert([
            [
                'name' => 'Direktur Produksi',
                'level' => 1,
                'category' => 'director',
                'is_head_position' => true,
                'organizational_unit_id' => $nested_directorate_id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'Manajer RRU',
                'level' => 2,
                'category' => 'manager',
                'is_head_position' => true,
                'organizational_unit_id' => $division_id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        $migration = require database_path('migrations/2026_10_12_090000_normalize_executive_directorates.php');
        $migration->up();

        $executive_directorate = DB::table('organizational_units')->find($executive_directorate_id);
        $production_directorate = DB::table('organizational_units')->find($production_unit_id);
        $division = DB::table('organizational_units')->find($division_id);

        self::assertSame('executive_directorate', $executive_directorate->type);
        self::assertSame('Direktorat Direksi', $executive_directorate->name);
        self::assertSame('production_directorate', $production_directorate->type);
        self::assertSame('Direktorat Produksi', $production_directorate->name);
        self::assertSame($executive_directorate_id, $production_directorate->parent_id);
        self::assertSame($production_unit_id, $division->parent_id);
        self::assertSame($executive_directorate_id, DB::table('organizational_units')->find($orphaned_directorate_id)->parent_id);
        self::assertDatabaseMissing('organizational_units', ['id' => $nested_directorate_id]);
        self::assertDatabaseHas('positions', [
            'name' => 'Direktur Produksi',
            'category' => 'c_suite',
            'organizational_unit_id' => $production_unit_id,
        ]);
        self::assertDatabaseHas('positions', [
            'name' => 'Manajer RRU',
            'organizational_unit_id' => $division_id,
        ]);
    }
}
