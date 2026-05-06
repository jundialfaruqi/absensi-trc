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
        Schema::create('apk_releases', function (Blueprint $row) {
            $row->id();
            $row->string('version');
            $row->date('release_date');
            $row->text('description')->nullable();
            $row->json('whats_new')->nullable();
            $row->text('optional_message')->nullable();
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apk_releases');
    }
};
