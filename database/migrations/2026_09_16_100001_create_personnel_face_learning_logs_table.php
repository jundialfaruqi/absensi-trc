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
        Schema::create('personnel_face_learning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->string('pose_type', 32)->default('FRONT');
            $table->foreignId('absensi_id')->nullable()->constrained('absensis')->nullOnDelete();
            $table->float('confidence_score', 8, 4); // Misal 0.8920 (89.2%)
            $table->float('drift_to_master', 8, 4); // Cosine sim hasil blend vs master anchor
            $table->unsignedInteger('adaptation_index')->default(1);
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->index(['personnel_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_face_learning_logs');
    }
};
