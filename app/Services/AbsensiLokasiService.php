<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\Kantor;

class AbsensiLokasiService
{
    /**
     * Memvalidasi apakah personel diperbolehkan absen berdasarkan lokasinya.
     * 
     * @param Personnel $personnel
     * @param float $lat
     * @param float $lng
     * @return array
     */
    public function validasiLokasi(Personnel $personnel, float $lat, float $lng): array
    {
        // 1. Jika personel tidak terhubung ke kantor manapun
        if (!$personnel->kantor_id) {
            return [
                'boleh' => true,
                'is_within_radius' => null,
                'jarak_meter' => null,
                'kantor_id' => null,
                'kantor_name' => null,
                'pesan' => '',
            ];
        }

        $kantor = $personnel->kantor;
        
        // Jika kantor tidak ditemukan (meskipun ada ID-nya)
        if (!$kantor) {
            return [
                'boleh' => true,
                'is_within_radius' => null,
                'jarak_meter' => null,
                'kantor_id' => null,
                'kantor_name' => null,
                'pesan' => '',
            ];
        }

        $jarak = $kantor->hitungJarak($lat, $lng);
        $dalamRadius = $jarak <= $kantor->radius_meter;
        $jarakBulat = (int) round($jarak);

        // 2. Punya kantor, tapi wajib_absen_di_lokasi = false
        if (!$personnel->wajib_absen_di_lokasi) {
            return [
                'boleh' => true,
                'is_within_radius' => $dalamRadius,
                'jarak_meter' => $jarakBulat,
                'kantor_id' => $kantor->id,
                'kantor_name' => $kantor->name,
                'pesan' => '',
            ];
        }

        // 3. Punya kantor, wajib absen di lokasi, dan sedang dalam radius
        if ($dalamRadius) {
            return [
                'boleh' => true,
                'is_within_radius' => true,
                'jarak_meter' => $jarakBulat,
                'kantor_id' => $kantor->id,
                'kantor_name' => $kantor->name,
                'pesan' => '',
            ];
        }

        // 4. Punya kantor, wajib absen di lokasi, tapi sedang di luar radius
        return [
            'boleh' => false,
            'is_within_radius' => false,
            'jarak_meter' => $jarakBulat,
            'kantor_id' => $kantor->id,
            'kantor_name' => $kantor->name,
            'pesan' => "Anda berada {$jarakBulat}m dari kantor {$kantor->name}. Maksimal radius adalah {$kantor->radius_meter}m.",
        ];
    }
}
