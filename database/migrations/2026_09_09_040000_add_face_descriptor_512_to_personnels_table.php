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
            if (!Schema::hasColumn('personnels', 'face_descriptor_512')) {
                $table->longText('face_descriptor_512')->nullable()->after('face_descriptor_mobile');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            if (Schema::hasColumn('personnels', 'face_descriptor_512')) {
                $table->dropColumn('face_descriptor_512');
            }
        });
    }
};
