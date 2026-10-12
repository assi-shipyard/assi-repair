<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const DEFAULT_GROUPS = [
        'Pekerjaan Umum',
        'Pekerjaan Docking',
        'Pekerjaan Lambung & Konstruksi',
        'Pekerjaan Pengecatan',
        'Pekerjaan Permesinan',
        'Pekerjaan Perpipaan',
        'Pekerjaan Kelistrikan',
        'Pekerjaan Lain-lain',
    ];

    public function up(): void
    {
        Schema::create('project_job_groups', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Phase-independent parameters (plate/pipe geometry) live in one JSON column instead of
        // a dozen nullable columns duplicated across every phase table.
        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->foreignId('project_job_group_id')->nullable()->after('source_job_id')->constrained('project_job_groups')->restrictOnDelete();
            $table->foreignId('parent_job_id')->nullable()->after('project_job_group_id')->constrained('project_document_jobs')->cascadeOnDelete();
            $table->foreignId('price_book_item_id')->nullable()->after('parent_job_id')->constrained('price_book_items')->nullOnDelete();
            $table->string('unit', 30)->nullable()->after('job_volume_estimated');
            $table->decimal('unit_price', 18, 2)->nullable()->after('unit');
            $table->decimal('minimum_quantity', 18, 4)->nullable()->after('unit_price');
            $table->json('calculation')->nullable()->after('minimum_quantity');
            $table->text('notes')->nullable()->after('calculation');
            $table->index(['project_job_document_id', 'project_job_group_id', 'parent_job_id', 'sort_order'], 'document_job_tree_index');
        });

        Schema::table('project_document_job_materials', function (Blueprint $table): void {
            $table->foreignId('price_book_item_id')->nullable()->after('project_document_job_id')->constrained('price_book_items')->nullOnDelete();
            $table->string('unit', 30)->nullable()->after('volume');
            $table->decimal('unit_price', 18, 2)->nullable()->after('unit');
            $table->decimal('minimum_quantity', 18, 4)->nullable()->after('unit_price');
            $table->unsignedInteger('sort_order')->default(0)->after('notes');
        });

        $now = now();
        DB::table('project_job_groups')->insert(array_map(fn (string $name, int $index): array => [
            'unique_id' => (string) Str::uuid(),
            'name' => $name,
            'sort_order' => $index + 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], self::DEFAULT_GROUPS, array_keys(self::DEFAULT_GROUPS)));
    }

    public function down(): void
    {
        Schema::table('project_document_job_materials', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('price_book_item_id');
            $table->dropColumn(['unit', 'unit_price', 'minimum_quantity', 'sort_order']);
        });

        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->dropIndex('document_job_tree_index');
            $table->dropConstrainedForeignId('project_job_group_id');
            $table->dropConstrainedForeignId('parent_job_id');
            $table->dropConstrainedForeignId('price_book_item_id');
            $table->dropColumn(['unit', 'unit_price', 'minimum_quantity', 'calculation', 'notes']);
        });

        Schema::dropIfExists('project_job_groups');
    }
};
