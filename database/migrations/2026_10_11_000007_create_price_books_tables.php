<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_books', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->string('code', 30)->unique();
            $table->string('name');
            // A nullable unique slot allows many customer books but only one main book.
            $table->unsignedTinyInteger('reference_slot')->nullable()->unique();
            $table->foreignId('company_id')->nullable()->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('price_book_items', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('price_book_id')->constrained()->restrictOnDelete();
            $table->string('group', 20);
            $table->string('code', 30);
            $table->string('name');
            $table->string('unit', 30);
            $table->decimal('unit_price', 14, 2);
            $table->boolean('has_minimum_charge')->default(false);
            $table->decimal('minimum_quantity', 12, 3)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['price_book_id', 'group', 'code', 'unit'], 'price_book_item_code_unit_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_book_items');
        Schema::dropIfExists('price_books');
    }
};
