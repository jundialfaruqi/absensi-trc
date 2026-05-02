<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedBatch('DISKOMINFO', 'Call Taker', 'Call Taker', 'calltaker');
        $this->seedBatch('DINSOS', 'Responder', 'Responder', 'responder');
    }

    private function seedBatch(string $opdSingkatan, string $penugasanName, string $namePrefix, string $emailPrefix): void
    {
        $opd = DB::table('opds')->where('singkatan', $opdSingkatan)->first();
        $penugasan = DB::table('penugasans')->where('name', $penugasanName)->first();

        if (!$opd || !$penugasan) {
            return;
        }

        $personnels = [];
        for ($i = 1; $i <= 8; $i++) {
            // Generate unique values based on OPD to avoid collision if run multiple times in same session
            $uniqueSuffix = substr(md5($opdSingkatan . $i), 0, 4); 
            
            $personnels[] = [
                'name' => $namePrefix . ' ' . $i,
                'nik' => '12345' . str_pad($opd->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 3, '0', STR_PAD_LEFT) . $uniqueSuffix,
                'opd_id' => $opd->id,
                'penugasan_id' => $penugasan->id,
                'regu' => 'A',
                'nomor_hp' => '08' . str_pad($opd->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 8, '0', STR_PAD_LEFT),
                'foto' => '',
                'email' => $emailPrefix . $i . '@pekanbaru.go.id',
                'password' => Hash::make('password'),
                'pin' => str_pad($opd->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 4, '0', STR_PAD_LEFT),
                'kantor_id' => null,
                'wajib_absen_di_lokasi' => false,
                'face_recognition' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('personnels')->insert($personnels);
    }
}
