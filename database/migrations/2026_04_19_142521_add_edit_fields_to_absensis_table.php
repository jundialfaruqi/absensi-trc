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
            $table->foreignId('edited_by_user_id')->nullable()->after('lng_pulang')->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable()->after('edited_by_user_id');
            $table->text('alasan_edit')->nullable()->after('edited_at');
            $table->string('original_status_masuk')->nullable()->after('alasan_edit');
            $table->string('original_status_pulang')->nullable()->after('original_status_masuk');
            $table->string('nomor_surat')->nullable()->after('original_status_pulang');
            $table->foreignId('cuti_id')->nullable()->after('nomor_surat')->constrained('cutis')->nullOnDelete();
            $table->text('keterangan')->nullable()->after('cuti_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('edited_by_user_id');
            $table->dropConstrainedForeignId('cuti_id');
            $table->dropColumn([
                'edited_at',
                'alasan_edit',
                'original_status_masuk',
                'original_status_pulang',
                'nomor_surat',
                'keterangan'
            ]);
        });
    }
};
