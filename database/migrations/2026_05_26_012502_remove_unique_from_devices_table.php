<?php
     use Illuminate\Database\Migrations\Migration;
     use Illuminate\Database\Schema\Blueprint;
     use Illuminate\Support\Facades\Schema;

     return new class extends Migration
     {
         public function up(): void
        {
            Schema::table('devices', function (Blueprint $table) {
                // Hapus index unique yang lama
                $table->dropUnique('devices_unique_device_id_unique');
            });
        }

        public function down(): void
        {
            Schema::table('devices', function (Blueprint $table) {
                // Tambahkan kembali jika ingin dibatalkan (opsional)
                // $table->unique('unique_device_id');
            });
        }
};
