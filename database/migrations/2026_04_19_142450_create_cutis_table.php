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
        if (!Schema::hasTable('cutis')) {
            Schema::create('cutis', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });

            // Seed default data
            DB::table('cutis')->insert([
                ['name' => 'Cuti Tahunan', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cuti Sakit', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cuti Alasan Penting', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cuti Melahirkan', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cuti Besar', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cuti Di Luar Tanggungan Negara', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutis');
    }
};
