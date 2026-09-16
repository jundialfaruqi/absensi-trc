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
        Schema::table('personnel_face_embeddings', function (Blueprint $table) {
            $table->longText('adaptive_descriptor_mobile')->nullable()->after('face_descriptor_mobile');
            $table->unsignedInteger('adaptation_count')->default(0)->after('adaptive_descriptor_mobile');
            $table->timestamp('last_adapted_at')->nullable()->after('adaptation_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel_face_embeddings', function (Blueprint $table) {
            $table->dropColumn(['adaptive_descriptor_mobile', 'adaptation_count', 'last_adapted_at']);
        });
    }
};
