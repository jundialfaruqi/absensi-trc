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
            $table->string('attendance_type')->default('SCHEDULED')->after('face_recognition'); // SCHEDULED, FLEXIBLE
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->unsignedBigInteger('jadwal_id')->nullable()->change();
            
            $table->string('platform_masuk')->nullable()->after('foto_pulang');
            $table->string('platform_pulang')->nullable()->after('platform_masuk');
            $table->string('device_name_masuk')->nullable()->after('platform_pulang');
            $table->string('device_name_pulang')->nullable()->after('device_name_masuk');
            $table->string('unique_device_id_masuk')->nullable()->after('device_name_pulang');
            $table->string('unique_device_id_pulang')->nullable()->after('unique_device_id_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $table->dropColumn('attendance_type');
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->unsignedBigInteger('jadwal_id')->nullable(false)->change();
            $table->dropColumn([
                'platform_masuk',
                'platform_pulang',
                'device_name_masuk',
                'device_name_pulang',
                'unique_device_id_masuk',
                'unique_device_id_pulang'
            ]);
        });
    }
};
