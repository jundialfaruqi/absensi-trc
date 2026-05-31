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
        Schema::table('apk_releases', function (Blueprint $table) {
            $table->unsignedInteger('min_version_code')->default(1)->after('version')
                ->comment('Minimum build version code required to use the app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_releases', function (Blueprint $table) {
            $table->dropColumn('min_version_code');
        });
    }
};
