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
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->change();
            $table->string('status')->default('SHIFT')->after('shift_id'); // SHIFT, LIBUR
            $table->text('keterangan')->nullable()->after('status');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable(false)->change();
            $table->dropColumn(['status', 'keterangan']);
        });
    }
};
