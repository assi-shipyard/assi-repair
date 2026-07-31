<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionIdAndDescriptionToRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        // Add custom columns to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->string('role_name')->nullable()->after('name');
            $table->text('role_description')->nullable()->after('role_name');
        });

        // Add custom columns to permissions table
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('permission_name')->nullable()->after('name');
            $table->text('permission_description')->nullable()->after('permission_name');
        });
    }

    public function down() {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['role_name', 'role_description']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['permission_name', 'permission_description']);
        });
    }
}
