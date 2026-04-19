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
        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('kantor_id_pulang')->nullable()->constrained('kantors')->nullOnDelete();
            $table->boolean('is_within_radius_pulang')->nullable();
            $table->integer('jarak_meter_pulang')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['kantor_id_pulang']);
            $table->dropColumn(['kantor_id_pulang', 'is_within_radius_pulang', 'jarak_meter_pulang']);
        });
    }
};
