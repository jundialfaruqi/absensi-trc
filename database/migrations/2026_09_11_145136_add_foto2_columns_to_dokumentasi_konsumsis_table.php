<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumentasi_konsumsis', function (Blueprint $table) {
            $table->string('foto_siang_2')->nullable()->after('foto_siang');
            $table->string('foto_malam_2')->nullable()->after('foto_malam');
        });
    }

    public function down(): void
    {
        Schema::table('dokumentasi_konsumsis', function (Blueprint $table) {
            $table->dropColumn(['foto_siang_2', 'foto_malam_2']);
        });
    }
};
