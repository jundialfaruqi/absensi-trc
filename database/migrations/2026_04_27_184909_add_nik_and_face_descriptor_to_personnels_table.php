<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            if (!Schema::hasColumn('personnels', 'nik')) {
                $table->string('nik')->nullable()->unique()->after('name');
            } else {
                $table->string('nik')->nullable()->unique()->change();
            }
            
            if (!Schema::hasColumn('personnels', 'face_descriptor')) {
                $table->longText('face_descriptor')->nullable()->after('foto');
            }

            $table->string('pin', 100)->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropColumn(['nik', 'face_descriptor']);
            $table->string('pin')->nullable()->change();
            $table->dropUnique(['pin']);
        });
    }
};
