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
            ['name' => 'M', 'keterangan' => 'MALAM', 'start_time' => '20:00', 'end_time' => '08:00', 'color' => '#2563eb', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'P', 'keterangan' => 'PAGI', 'start_time' => '08:00', 'end_time' => '20:00', 'color' => '#22c55e', 'created_at' => now(), 'updated_at' => now()],
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
