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
        Schema::table('shifts', function (Blueprint $col) {
            $col->enum('type', ['shift', 'off'])->default('shift')->after('name');
            $col->time('start_time')->nullable()->change();
            $col->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $col) {
            $col->dropColumn('type');
            $col->time('start_time')->nullable(false)->change();
            $col->time('end_time')->nullable(false)->change();
        });
    }
};
