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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->onDelete('cascade');
            $table->foreignId('personnel_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('name')->comment('Nama Perangkat dari Admin');
            $table->string('license_key')->unique();
            $table->string('unique_device_id')->nullable()->unique()->comment('Android ID / Hardware ID');
            
            // Data tambahan dari perangkat
            $table->string('brand')->nullable()->comment('Contoh: Samsung, Oppo');
            $table->string('model')->nullable()->comment('Contoh: SM-A546E');
            $table->string('android_version')->nullable();
            
            $table->enum('status', ['inactive', 'active', 'suspended'])->default('inactive');
            $table->text('notes')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
