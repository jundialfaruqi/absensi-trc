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
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->foreignId('penugasan_id')->constrained('penugasans')->cascadeOnDelete();
            $table->string('nomor_hp')->nullable();
            $table->string('foto');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('pin'); 
            // changed to simple string to avoid 1406 error.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
