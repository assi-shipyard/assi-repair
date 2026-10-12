<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_book_items', function (Blueprint $table): void {
            $table->string('calculation_rule', 20)->nullable()->after('unit');
        });

        // The rule is snapshotted per line so later price-book edits never reprice approved documents.
        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->string('calculation_rule', 20)->nullable()->after('price_book_item_id');
            $table->decimal('billed_quantity', 18, 4)->nullable()->after('minimum_quantity');
        });

        Schema::table('project_document_job_materials', function (Blueprint $table): void {
            $table->string('calculation_rule', 20)->nullable()->after('price_book_item_id');
            $table->decimal('billed_quantity', 18, 4)->nullable()->after('minimum_quantity');
        });

        Schema::table('project_job_documents', function (Blueprint $table): void {
            $table->json('minimum_overrides')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_job_documents', function (Blueprint $table): void {
            $table->dropColumn('minimum_overrides');
        });

        Schema::table('project_document_job_materials', function (Blueprint $table): void {
            $table->dropColumn(['calculation_rule', 'billed_quantity']);
        });

        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->dropColumn(['calculation_rule', 'billed_quantity']);
        });

        Schema::table('price_book_items', function (Blueprint $table): void {
            $table->dropColumn('calculation_rule');
        });
    }
};
