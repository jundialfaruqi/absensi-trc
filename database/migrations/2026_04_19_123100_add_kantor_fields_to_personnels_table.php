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
            $table->foreignId('kantor_id')->nullable()->constrained('kantors')->onDelete('set null');
            $table->boolean('wajib_absen_di_lokasi')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropForeign(['kantor_id']);
            $table->dropColumn(['kantor_id', 'wajib_absen_di_lokasi']);
        });
    }
};
