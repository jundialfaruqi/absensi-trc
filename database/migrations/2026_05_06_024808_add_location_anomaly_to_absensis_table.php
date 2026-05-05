<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->boolean('is_location_anomaly')->default(false)->after('jarak_meter_pulang');
            $table->string('anomaly_reason')->nullable()->after('is_location_anomaly');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn(['is_location_anomaly', 'anomaly_reason']);
        });
    }
};
