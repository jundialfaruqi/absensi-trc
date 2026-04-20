<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $col) {
            $col->id();
            $col->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $col->foreignId('cuti_id')->constrained('cutis')->onDelete('cascade');
            $col->date('tanggal_mulai');
            $col->date('tanggal_selesai');
            $col->text('alasan');
            $col->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $col->text('admin_note')->nullable();
            $col->foreignId('processed_by_user_id')->nullable()->constrained('users');
            $col->timestamp('processed_at')->nullable();
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
