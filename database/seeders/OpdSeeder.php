<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('opds')->insert([
            [
                'name' => 'Dinas Komunikasi Informatika Statistik dan Persandian',
                'singkatan' => 'DISKOMINFO',
                'alamat' => 'Komp. Perkantoran Walikota Pekanbaru Lt. III Jalan Abdul Rahman Hamid Kel. Tuah Negeri Kec. Tenayan Raya.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Badan Penanggulangan Bencana Daerah',
                'singkatan' => 'BPBD',
                'alamat' => 'Jl. Mustafa Sari No.1, Tengkerang Sel., Kec. Bukit Raya, Kota Pekanbaru, Riau 28125.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dinas Kesehatan',
                'singkatan' => 'DINKES',
                'alamat' => 'Jl. Abdul Rahman Hamid - Komplek Perkantoran Walikota Pekanbaru Gedung Belah Bubung Lt. 1 & 2 Kel. Tuah Negeri Kec. Tenayan Raya.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dinas Perhubungan',
                'singkatan' => 'DISHUB',
                'alamat' => 'Kompleks Perkantoran Pemerintah Kota Pekanbaru, Lt. IV, Jalan Abdul Rahman Hamid, Kelurahan Tuah Negeri, Kecamatan Tenayan Raya.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dinas Lingkungan Hidup dan Kebersihan',
                'singkatan' => 'DLHK',
                'alamat' => 'Jl. Datuk Setia Maharaja No. 04, Kelurahan Simpang Tiga, Kecamatan Bukit Raya, Kota Pekanbaru, Riau 28125.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dinas Pemadam Kebakaran',
                'singkatan' => 'DAMKAR',
                'alamat' => 'Jl. Cempaka No.31A, Kelurahan Pulau Karomah, Kecamatan Sukajadi, Kota Pekanbaru, Riau.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dinas Sosial',
                'singkatan' => 'DINSOS',
                'alamat' => 'Jl. Parit Indah Jl. Datuk Setia Maharaja No.6, Simpang Tiga, Kec. Bukit Raya, Kota Pekanbaru, Riau 28289.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Satuan Polisi Pamong Praja',
                'singkatan' => 'SATPOLPP',
                'alamat' => 'Komp. Perkantoran Walikota Pekanbaru, Gedung Limas Kajang, Jalan Abdul Rahman Hamid Kel. Tuah Negeri Kec. Tenayan Raya.',
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
