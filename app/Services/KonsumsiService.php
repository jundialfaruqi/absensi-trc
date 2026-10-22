<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class KonsumsiService
{
    /**
     * Menentukan jatah konsumsi ('siang', 'malam', atau keduanya) untuk absensi personel bertipe FLEXIBLE.
     *
     * @param mixed $abs
     * @return array
     */
    public static function resolveFlexibleKonsumsis($abs): array
    {
        if (!$abs) {
            return ['siang'];
        }

        $siangMulai = Setting::get('konsumsi_siang_mulai', '06:00');
        $siangSelesai = Setting::get('konsumsi_siang_selesai', '15:59');
        $malamMulai = Setting::get('konsumsi_malam_mulai', '16:00');
        $malamSelesai = Setting::get('konsumsi_malam_selesai', '05:59');

        $jamMasuk = $abs->jam_masuk ? Carbon::parse($abs->jam_masuk) : null;
        $jamPulang = $abs->jam_pulang ? Carbon::parse($abs->jam_pulang) : null;

        if (!$jamMasuk && !$jamPulang) {
            return ['siang'];
        }

        $masukTime = $jamMasuk ? $jamMasuk->format('H:i') : null;
        $pulangTime = $jamPulang ? $jamPulang->format('H:i') : null;

        $hasSiang = false;
        $hasMalam = false;

        // 1. Deteksi sesi masuk
        if ($masukTime) {
            if ($masukTime >= $siangMulai && $masukTime <= $siangSelesai) {
                $hasSiang = true;
            } elseif ($masukTime >= $malamMulai || $masukTime <= $malamSelesai) {
                $hasMalam = true;
            }
        }

        // 2. Deteksi sesi pulang (bekerja melewati jam makan)
        if ($pulangTime) {
            if ($pulangTime >= '11:30' && $pulangTime <= $siangSelesai) {
                $hasSiang = true;
            }
            if ($pulangTime >= '19:00' || $pulangTime <= $malamSelesai) {
                $hasMalam = true;
            }
        }

        // 3. Deteksi durasi dinas/lembur panjang (>= 9 jam melintasi siang & malam)
        if ($jamMasuk && $jamPulang) {
            $diffHours = $jamMasuk->diffInHours($jamPulang);
            if ($diffHours >= 9) {
                $hasSiang = true;
                $hasMalam = true;
            }
        }

        // Fallback jika hadir tapi belum match
        if (!$hasSiang && !$hasMalam) {
            $hasSiang = true;
        }

        $result = [];
        if ($hasSiang) {
            $result[] = 'siang';
        }
        if ($hasMalam) {
            $result[] = 'malam';
        }

        return $result;
    }

    /**
     * Menyelesaikan data tampilan sel konsumsi dan jatah siang/malam untuk suatu tanggal.
     *
     * @param mixed $abs
     * @param mixed $jadwal
     * @param string|null $attendanceType
     * @return array
     */
    public static function resolveCellKonsumsi($abs, $jadwal, ?string $attendanceType = null): array
    {
        $isHadir = $abs &&
            !in_array($abs->status, ['IZIN', 'SAKIT', 'CUTI', 'ALPA', 'LIBUR']) &&
            (
                $abs->status === 'HADIR' ||
                $abs->status === 'TELAT' ||
                $abs->status_masuk === 'HADIR' ||
                $abs->status_masuk === 'TELAT' ||
                !empty($abs->jam_masuk) ||
                !empty($abs->jam_pulang)
            );

        $hasSiang = false;
        $hasMalam = false;
        $cellText = '-';
        $cellClass = '';

        if ($isHadir) {
            if ($attendanceType === 'FLEXIBLE') {
                $flexibleKons = self::resolveFlexibleKonsumsis($abs);
                $hasSiang = in_array('siang', $flexibleKons);
                $hasMalam = in_array('malam', $flexibleKons);
            } elseif ($jadwal && $jadwal->shift) {
                $konsumsis = $jadwal->shift->konsumsis
                    ->pluck('nama')
                    ->map(fn($k) => strtolower(trim($k)))
                    ->toArray();
                $hasSiang = in_array('siang', $konsumsis);
                $hasMalam = in_array('malam', $konsumsis);
            }

            if ($hasSiang && $hasMalam) {
                $cellText = 'S+M';
                $cellClass = 'badge-both';
            } elseif ($hasSiang) {
                $cellText = 'S';
                $cellClass = 'badge-siang';
            } elseif ($hasMalam) {
                $cellText = 'M';
                $cellClass = 'badge-malam';
            } else {
                $cellText = 'H';
                $cellClass = '';
            }
        } elseif ($abs && in_array($abs->status, ['ALPA', 'IZIN', 'SAKIT', 'CUTI'])) {
            $cellText = substr($abs->status, 0, 1);
        }

        return [
            'is_hadir' => $isHadir,
            'has_siang' => $hasSiang,
            'has_malam' => $hasMalam,
            'text' => $cellText,
            'class' => $cellClass,
        ];
    }
}
