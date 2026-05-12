<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->renameColumn('kategori_makan', 'konsumsi');
        });

        // Update data
        DB::table('shifts')->where('konsumsi', 'makan_siang')->update(['konsumsi' => 'siang']);
        DB::table('shifts')->where('konsumsi', 'makan_malam')->update(['konsumsi' => 'malam']);
        DB::table('shifts')->where('konsumsi', 'tanpa_makan')->update(['konsumsi' => 'none']);
        
        // Handle null values if any
        DB::table('shifts')->whereNull('konsumsi')->update(['konsumsi' => 'none']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback data first
        DB::table('shifts')->where('konsumsi', 'siang')->update(['konsumsi' => 'makan_siang']);
        DB::table('shifts')->where('konsumsi', 'malam')->update(['konsumsi' => 'makan_malam']);
        DB::table('shifts')->where('konsumsi', 'none')->update(['konsumsi' => 'tanpa_makan']);

        Schema::table('shifts', function (Blueprint $table) {
            $table->renameColumn('konsumsi', 'kategori_makan');
        });
    }
};
