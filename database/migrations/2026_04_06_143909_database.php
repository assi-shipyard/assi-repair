<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
            Schema::create('ship_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });

            Schema::create('ship_classes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
				$table->string('abbreviation')->nullable();
                $table->timestamps();
            });

			Schema::create('companies', function (Blueprint $table) {
				$table->id();
				$table->string('unique_id')->unique();
				$table->string('name');
				$table->string('address');
				$table->string('phone_1')->nullable();
				$table->string('phone_2')->nullable();
				$table->string('email')->nullable();
				$table->string('ceo_name')->nullable();
				$table->string('ceo_phone')->nullable();
				$table->string('ceo_email')->nullable();
				$table->string('pic_name')->nullable();
				$table->string('pic_phone')->nullable();
				$table->string('pic_email')->nullable();
				$table->string('registration_number')->nullable();
				$table->string('tax_id')->nullable();
				$table->string('logo_path')->nullable();
				$table->string('comment')->nullable();
				$table->timestamps();
			});

			Schema::create('company_documents', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('company_id');
				$table->string('document_name');
				$table->string('document_type');
				$table->string('document_path');
				$table->timestamps();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			});

			Schema::create('ships', function (Blueprint $table) {
				$table->id();
				$table->string('name');
				$table->unsignedBigInteger('company_id');
				$table->unsignedBigInteger('ship_type_id')->nullable();
				$table->unsignedBigInteger('ship_class_id')->nullable();

				$table->string('length_overall');
				$table->string('breadth');
				$table->string('height');
				$table->string('empty_draft');
				$table->string('loaded_draft')->nullable();
				$table->string('gross_tonnage')->nullable();
				$table->string('net_tonnage')->nullable();

				$table->string('engine_brand')->nullable();
				$table->string('engine_model')->nullable();
				$table->string('engine_power')->nullable();
				$table->string('engine_type')->nullable();
				$table->string('engine_rpm')->nullable();
				$table->string('engine_fuel_type')->nullable();
				$table->string('engine_fuel_capacity')->nullable();
				$table->string('engine_fuel_consumption')->nullable();

				$table->string('imo_number')->unique()->nullable();
				$table->string('mmsi_number')->unique()->nullable();
				$table->string('call_sign')->unique()->nullable();
				$table->string('flag')->nullable();
				$table->date('build_year')->nullable();
				$table->timestamps();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
				$table->foreign('ship_type_id')->references('id')->on('ship_types')->onDelete('set null');
				$table->foreign('ship_class_id')->references('id')->on('ship_classes')->onDelete('set null');
			});

			Schema::create('ship_images', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('ship_id');
				$table->string('image_path');
				$table->timestamps();

				$table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
			});

			Schema::create('ship_documents', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('ship_id');
				$table->string('document_name');
				$table->string('document_type');
				$table->string('document_path');
				$table->timestamps();

				$table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
			});

		}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::dropIfExists('ship_documents');
		Schema::dropIfExists('ship_images');
        Schema::dropIfExists('ships');
        Schema::dropIfExists('company_documents');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('ship_classes');
        Schema::dropIfExists('ship_types');
    }
};
