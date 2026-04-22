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
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('color')->nullable()->default('#64748b')->after('end_time');
        });

         // Seed default data
        DB::table('shifts')->insert([
            ['name' => 'NS-SR', 'keterangan' => 'Non-Shift Senin - Rabu', 'start_time' => '07:30', 'end_time' => '16:00', 'color' => '#0ea5e9', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NS-KJ', 'keterangan' => 'Non-Shift Kamis - Jumat', 'start_time' => '08:00', 'end_time' => '16:30', 'color' => '#06b6d4', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
