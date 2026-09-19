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
        Schema::table('personnels', function (Blueprint $table) {
            $table->string('face_verification_status', 30)
                ->default('UNREGISTERED')
                ->after('face_recognition');
            $table->text('face_verification_notes')->nullable()->after('face_verification_status');
            $table->timestamp('face_verified_at')->nullable()->after('face_verification_notes');
            $table->foreignId('face_verified_by')->nullable()->after('face_verified_at')->constrained('users')->nullOnDelete();
        });

        // Set existing verified personnel to APPROVED
        \Illuminate\Support\Facades\DB::table('personnels')
            ->where('face_recognition', true)
            ->whereNotNull('face_descriptor_mobile')
            ->update([
                'face_verification_status' => 'APPROVED',
                'face_verified_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropForeign(['face_verified_by']);
            $table->dropColumn([
                'face_verification_status',
                'face_verification_notes',
                'face_verified_at',
                'face_verified_by',
            ]);
        });
    }
};
