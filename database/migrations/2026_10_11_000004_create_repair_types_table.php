<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_types', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category', 50);
            $table->string('unit', 30);
            $table->decimal('standard_price', 18, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_types');
    }
};
