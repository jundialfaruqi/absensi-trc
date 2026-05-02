<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shifts')->insert([
            [
                'name' => 'M',
                'type' => 'shift',
                'keterangan' => 'MALAM',
                'start_time' => '20:00',
                'end_time' => '08:00',
                'color' => '#2563eb',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'P',
                'type' => 'shift',
                'keterangan' => 'PAGI',
                'start_time' => '08:00',
                'end_time' => '20:00',
                'color' => '#22c55e',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'L',
                'type' => 'off',
                'keterangan' => 'LIBUR',
                'start_time' => null,
                'end_time' => null,
                'color' => '#eab308',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'D',
                'type' => 'off',
                'keterangan' => 'DINAS',
                'start_time' => null,
                'end_time' => null,
                'color' => '#64748b',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
