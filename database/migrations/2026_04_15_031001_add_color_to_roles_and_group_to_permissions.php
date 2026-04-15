<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambahkan kolom `color` pada tabel roles dan kolom `group` pada tabel permissions.
     */
    public function up(): void
    {
        // Tambah kolom color ke tabel roles
        Schema::table(config('permission.table_names.roles', 'roles'), function (Blueprint $table) {
            $table->string('color', 20)->nullable()->default('#64748b')->after('guard_name');
        });

        // Tambah kolom group ke tabel permissions
        Schema::table(config('permission.table_names.permissions', 'permissions'), function (Blueprint $table) {
            $table->string('group', 100)->nullable()->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('permission.table_names.roles', 'roles'), function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table(config('permission.table_names.permissions', 'permissions'), function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
