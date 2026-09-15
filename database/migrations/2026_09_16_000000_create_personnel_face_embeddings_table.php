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
        Schema::create('personnel_face_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->string('pose_type', 32); // 'FRONT', 'RIGHT', 'LEFT', 'UP'
            $table->longText('face_descriptor')->nullable(); // 128-D JSON (face-api.js)
            $table->longText('face_descriptor_mobile')->nullable(); // 192-D JSON (MobileFaceNet)
            $table->string('foto')->nullable(); // Storage path
            $table->timestamps();

            $table->unique(['personnel_id', 'pose_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_face_embeddings');
    }
};
