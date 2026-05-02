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
        Schema::create('penugasans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Seed default data
            DB::table('penugasans')->insert([
                ['name' => 'Test', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Call Taker', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Dokter', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Perawat', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Responder', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Security', 'created_at' => now(), 'updated_at' => now()],
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penugasans');
    }
};
