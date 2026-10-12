<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontractors', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->string('code', 50)->unique();
            $table->string('name', 191);
            $table->string('contact_name', 191);
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email', 254)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractors');
    }
};
