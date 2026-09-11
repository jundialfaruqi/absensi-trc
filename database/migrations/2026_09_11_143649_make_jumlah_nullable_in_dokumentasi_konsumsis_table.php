<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make jumlah_siang and jumlah_malam nullable so that NULL means
     * "no dokumentasi for this sesi yet" (distinct from 0 = "documented, 0 portions").
     */
    public function up(): void
    {
        Schema::table('dokumentasi_konsumsis', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_siang')->nullable()->default(null)->change();
            $table->unsignedInteger('jumlah_malam')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('dokumentasi_konsumsis', function (Blueprint $table) {
            // Revert: set existing NULLs to 0 first, then add NOT NULL + default(0)
            \DB::statement('UPDATE dokumentasi_konsumsis SET jumlah_siang = 0 WHERE jumlah_siang IS NULL');
            \DB::statement('UPDATE dokumentasi_konsumsis SET jumlah_malam = 0 WHERE jumlah_malam IS NULL');
            $table->unsignedInteger('jumlah_siang')->nullable(false)->default(0)->change();
            $table->unsignedInteger('jumlah_malam')->nullable(false)->default(0)->change();
        });
    }
};
