<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create konsumsis table
        Schema::create('konsumsis', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        // 2. Create shift_konsumsi table
        Schema::create('shift_konsumsi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('konsumsi_id')->constrained('konsumsis')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Insert default data
        DB::table('konsumsis')->insert([
            ['nama' => 'siang', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'malam', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Migrate data
        $shifts = DB::table('shifts')->get();
        $siangId = DB::table('konsumsis')->where('nama', 'siang')->value('id');
        $malamId = DB::table('konsumsis')->where('nama', 'malam')->value('id');

        foreach ($shifts as $shift) {
            if ($shift->konsumsi === 'siang') {
                DB::table('shift_konsumsi')->insert([
                    'shift_id' => $shift->id,
                    'konsumsi_id' => $siangId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($shift->konsumsi === 'malam') {
                DB::table('shift_konsumsi')->insert([
                    'shift_id' => $shift->id,
                    'konsumsi_id' => $malamId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Drop column from shifts
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('konsumsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add column back to shifts
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('konsumsi')->nullable()->after('color');
        });

        // 2. Restore data
        $pivotData = DB::table('shift_konsumsi')->get();
        foreach ($pivotData as $data) {
            $konsumsi = DB::table('konsumsis')->where('id', $data->konsumsi_id)->value('nama');
            DB::table('shifts')->where('id', $data->shift_id)->update(['konsumsi' => $konsumsi]);
        }

        // 3. Drop tables
        Schema::dropIfExists('shift_konsumsi');
        Schema::dropIfExists('konsumsis');
    }
};
