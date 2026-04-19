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
        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('kantor_id')->nullable()->constrained('kantors')->onDelete('set null');
            $table->boolean('is_within_radius')->nullable();
            $table->integer('jarak_meter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['kantor_id']);
            $table->dropColumn(['kantor_id', 'is_within_radius', 'jarak_meter']);
        });
    }
};
