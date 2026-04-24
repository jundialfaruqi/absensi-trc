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
        Schema::create('shift_cycle_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('opd_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('sequence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_cycle_templates');
    }
};
