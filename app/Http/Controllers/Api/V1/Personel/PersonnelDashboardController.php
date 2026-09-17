<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelDashboardController extends Controller
{
    /**
     * Dapatkan ringkasan statistik kehadiran bulan berjalan & log aktifitas terbaru milik personel yang sedang login.
     */
    public function summary(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');
        $personnel->load(['opd', 'kantor', 'penugasan']);

        $now = Carbon::now();
        $year = (int) $request->query('year', $now->year);
        $month = (int) $request->query('month', $now->month);

        $targetMonth = Carbon::create($year, $month, 1);
        $startOfMonth = $targetMonth->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $targetMonth->copy()->endOfMonth()->format('Y-m-d');
        $todayStr = $now->format('Y-m-d');

        // 1. Ambil seluruh data absensi personel di bulan tersebut
        $absensis = Absensi::where('personnel_id', $personnel->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $hadirCount = 0;
        $alpaCount = 0;
        $izinCount = 0;
        $liburCount = 0;
        $today = Carbon::today();

        foreach ($absensis as $a) {
            $tgl = $a->tanggal instanceof Carbon ? $a->tanggal : Carbon::parse($a->tanggal);
            $isFuture = $tgl->startOfDay()->greaterThan($today);
            $statusUpper = strtoupper((string) $a->status);

            if (in_array($statusUpper, ['HADIR', 'TELAT']) || $a->jam_masuk || $a->jam_pulang) {
                $hadirCount++;
            } elseif ($statusUpper === 'ALPA') {
                if (!$isFuture) {
                    $alpaCount++;
                }
            } elseif (in_array($statusUpper, ['IZIN', 'SAKIT', 'CUTI'])) {
                $izinCount++;
            } elseif (in_array($statusUpper, ['LIBUR', 'DINAS'])) {
                $liburCount++;
            }
        }

        // 2. Hitung total hari dinas/kerja bulan ini
        if ($personnel->attendance_type === 'FLEXIBLE') {
            $totalHari = max($hadirCount + $alpaCount + $izinCount, $now->isSameMonth($targetMonth) ? $now->day : $targetMonth->daysInMonth);
        } else {
            $totalJadwal = Jadwal::where('personnel_id', $personnel->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereHas('shift', fn ($q) => $q->where('type', 'shift'))
                ->count();

            $totalHari = $totalJadwal > 0 ? $totalJadwal : ($now->isSameMonth($targetMonth) ? $now->day : $targetMonth->daysInMonth);
        }

        $hadirPercentage = $totalHari > 0 ? (int) round(($hadirCount / $totalHari) * 100) : 0;
        if ($hadirPercentage > 100) {
            $hadirPercentage = 100;
        }

        // 3. Tentukan status presensi hari ini / shift aktif
        $yesterdayStr = $now->copy()->subDay()->format('Y-m-d');
        $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

        $activeDate = $todayStr;
        $activeJadwal = null;
        $activeAbsensi = null;

        // Cek apakah ada jadwal shift malam kemarin yang masih aktif berjalan (belum pulang & dalam batas window pulang)
        $yesterdayJadwal = Jadwal::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $yesterdayStr)
            ->with('shift')
            ->first();

        if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->type !== 'off' && $yesterdayJadwal->shift->start_time && $yesterdayJadwal->shift->end_time) {
            $sTime = Carbon::parse($yesterdayJadwal->shift->start_time);
            $eTime = Carbon::parse($yesterdayJadwal->shift->end_time);

            if ($sTime->format('H:i:s') >= $eTime->format('H:i:s')) {
                $endDatetime = Carbon::parse($todayStr)->setTimeFrom($eTime);
                $windowOutEnd = $endDatetime->copy()->addMinutes($selesaiOut);

                if ($now->lessThanOrEqualTo($windowOutEnd)) {
                    $existingYest = Absensi::where('personnel_id', $personnel->id)
                        ->whereDate('tanggal', $yesterdayStr)
                        ->with(['kantor', 'kantorPulang', 'jadwal.shift'])
                        ->first();
                    if ($existingYest && $existingYest->jam_masuk && !$existingYest->jam_pulang) {
                        $activeJadwal = $yesterdayJadwal;
                        $activeAbsensi = $existingYest;
                        $activeDate = $yesterdayStr;
                    }
                }
            }
        }

        if (!$activeJadwal) {
            $activeJadwal = Jadwal::where('personnel_id', $personnel->id)
                ->whereDate('tanggal', $todayStr)
                ->with('shift')
                ->first();
            $activeAbsensi = Absensi::where('personnel_id', $personnel->id)
                ->whereDate('tanggal', $todayStr)
                ->with(['kantor', 'kantorPulang', 'jadwal.shift'])
                ->first();
        }

        $statusHariIni = 'Belum Melakukan Presensi';
        $todayAbsensi = $activeAbsensi;
        $todayJadwal = $activeJadwal;

        if ($todayAbsensi) {
            if ($todayAbsensi->jam_masuk && $todayAbsensi->jam_pulang) {
                $statusHariIni = 'Sudah Selesai (Pulang: ' . Carbon::parse($todayAbsensi->jam_pulang)->format('H:i') . ' WIB)';
            } elseif ($todayAbsensi->jam_masuk) {
                $statusHariIni = 'Sudah Absen Masuk (' . Carbon::parse($todayAbsensi->jam_masuk)->format('H:i') . ' WIB)';
            } elseif ($todayAbsensi->jam_pulang) {
                $statusHariIni = 'Sudah Absen Pulang (' . Carbon::parse($todayAbsensi->jam_pulang)->format('H:i') . ' WIB)';
            } elseif (in_array(strtoupper((string) $todayAbsensi->status), ['IZIN', 'SAKIT', 'CUTI', 'DINAS'])) {
                $statusHariIni = ucfirst(strtolower($todayAbsensi->status));
            } elseif (strtoupper((string) $todayAbsensi->status) === 'LIBUR') {
                $statusHariIni = 'Hari Ini Libur';
            }
        } else {
            if ($personnel->attendance_type !== 'FLEXIBLE') {
                if ($todayJadwal && $todayJadwal->shift && $todayJadwal->shift->type === 'off') {
                    $statusHariIni = 'Hari Ini Libur (OFF)';
                } elseif ($todayJadwal && $todayJadwal->shift) {
                    $statusHariIni = 'Belum Absen (Shift ' . $todayJadwal->shift->name . ')';
                }
            }
        }

        // 4. Bangun Aktifitas Hari Ini (Section Aktifitas Hari Ini)
        $tglCarbon = Carbon::parse($activeDate);
        $tglStr = $tglCarbon->translatedFormat('d M Y');
        $tglFullStr = $tglCarbon->translatedFormat('l, d M Y');

        $shift = $todayAbsensi?->jadwal?->shift ?? $todayJadwal?->shift;
        $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : null);
        $shiftHours = null;
        if ($shift && $shift->start_time && $shift->end_time) {
            $sStart = Carbon::parse($shift->start_time)->format('H:i');
            $sEnd = Carbon::parse($shift->end_time)->format('H:i');
            $shiftHours = "{$sStart} - {$sEnd}";
        }

        $kantorName = $todayAbsensi?->kantor?->name 
            ?? $todayAbsensi?->kantor?->nama_kantor 
            ?? $personnel->kantor?->name 
            ?? '-';

        $kantorPulangName = $todayAbsensi?->kantorPulang?->name 
            ?? $todayAbsensi?->kantorPulang?->nama_kantor 
            ?? $todayAbsensi?->kantor?->name 
            ?? $personnel->kantor?->name 
            ?? '-';

        // Tentukan status utama secara dinamis dari tabel absensi
        $statusUtama = $todayAbsensi?->status;
        if (empty($statusUtama)) {
            $shiftObj = $todayJadwal?->shift;
            $isShiftOff = $shiftObj && ($shiftObj->type === 'off' || stripos($shiftObj->name ?? '', 'libur') !== false);

            if ($isShiftOff) {
                $statusUtama = $shiftObj->keterangan 
                    ?: (stripos($shiftObj->name ?? '', 'dinas') !== false ? 'DINAS' : 'LIBUR');
            } elseif ($todayJadwal?->status && !in_array(strtoupper($todayJadwal->status), ['SHIFT', 'KERJA'])) {
                $statusUtama = $todayJadwal->status;
            } else {
                $statusUtama = 'ALPA';
            }
        }
        $statusUpper = strtoupper(trim((string) $statusUtama));

        // Jika status nya ALPA atau jam kerja normal (HADIR/sudah absen), tampilkan 2 Card (Presensi Masuk dan Pulang).
        // Selain itu (LIBUR, DINAS, IZIN, SAKIT, CUTI, dll), cukup tampilkan 1 Card dengan judul sesuai status utama di tabel absensi.
        $isWorkingShift = ($statusUpper === 'ALPA' || $statusUpper === 'HADIR' || ($todayAbsensi && ($todayAbsensi->jam_masuk || $todayAbsensi->jam_pulang)));

        $recentActivities = [];

        if (!$isWorkingShift) {
            // Status utama non-kerja (LIBUR, DINAS, IZIN, SAKIT, CUTI, dll): Cukup 1 Card
            $statusName = $statusUpper;
            $statusType = strtolower($statusUpper);
            $subtitle = $todayAbsensi?->keterangan 
                ?: ($shift?->keterangan 
                    ?: ($todayAbsensi?->nomor_surat ? "No: {$todayAbsensi->nomor_surat}" : $statusName));

            $recentActivities[] = [
                'id' => ($todayAbsensi ? (string) $todayAbsensi->id : 'today') . '_' . $statusType,
                'type' => $statusType,
                'title' => $statusName,
                'subtitle' => $subtitle,
                'time' => '-',
                'date' => $tglStr,
                'full_date' => $tglFullStr,
                'status' => $statusName,
                'status_type' => $statusType,
                'foto_url' => null,
                'shift_name' => $shiftName,
                'shift_hours' => null,
                'jarak_meter' => null,
                'created_at' => $now->startOfDay()->toISOString(),
            ];
        } else {
            // Jadwal Kerja Normal / Status ALPA: TAMPILKAN 2 CARD (Presensi Masuk dan Presensi Pulang)
            $mulaiIn = (int) Setting::get('absensi_masuk_mulai', 30);
            $selesaiIn = (int) Setting::get('absensi_masuk_selesai', 120);
            $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 30);
            $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

            $windowInEnd = null;
            $windowOutEnd = null;

            if ($shift && $shift->start_time && $shift->end_time) {
                $startTime = Carbon::parse($activeDate)->setTimeFrom($shift->start_time);
                $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

                $isNightShift = Carbon::parse($shift->start_time)->format('H:i:s') >= Carbon::parse($shift->end_time)->format('H:i:s');
                $endDate = $isNightShift ? Carbon::parse($activeDate)->addDay()->format('Y-m-d') : $activeDate;
                $endTime = Carbon::parse($endDate)->setTimeFrom($shift->end_time);
                $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);
            }

            // --- 1. Card Presensi Masuk ---
            if ($todayAbsensi && $todayAbsensi->jam_masuk) {
                $jamMasukCarbon = $todayAbsensi->jam_masuk instanceof Carbon
                    ? $todayAbsensi->jam_masuk
                    : Carbon::parse($todayStr . ' ' . $todayAbsensi->jam_masuk);
                $isTelat = strtoupper((string) $todayAbsensi->status_masuk) === 'TELAT';
                $cardMasuk = [
                    'id' => (string) $todayAbsensi->id . '_masuk',
                    'type' => 'masuk',
                    'title' => 'Presensi Masuk',
                    'subtitle' => $kantorName,
                    'time' => $jamMasukCarbon->format('H:i') . ' WIB',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isTelat ? 'Terlambat' : 'Hadir',
                    'status_type' => $isTelat ? 'telat' : 'masuk',
                    'foto_url' => $todayAbsensi->foto_masuk ? asset('storage/' . $todayAbsensi->foto_masuk) : null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => $todayAbsensi->jarak_meter,
                    'created_at' => $jamMasukCarbon->toISOString(),
                ];
            } else {
                $isMasukAlpa = false;
                if ($windowInEnd && $now->greaterThan($windowInEnd)) {
                    $isMasukAlpa = true;
                } elseif ($todayAbsensi && (in_array(strtoupper((string) $todayAbsensi->status_masuk), ['ALPA', 'TIDAK MASUK']) || strtoupper((string) $todayAbsensi->status) === 'ALPA')) {
                    $isMasukAlpa = true;
                }

                $cardMasuk = [
                    'id' => ($todayAbsensi ? (string) $todayAbsensi->id : 'today') . '_masuk',
                    'type' => 'masuk',
                    'title' => 'Presensi Masuk',
                    'subtitle' => $kantorName,
                    'time' => '-',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isMasukAlpa ? 'Alpa' : '-',
                    'status_type' => $isMasukAlpa ? 'alpa' : 'pending',
                    'foto_url' => null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => null,
                    'created_at' => $now->startOfDay()->toISOString(),
                ];
            }

            // --- 2. Card Presensi Pulang ---
            if ($todayAbsensi && $todayAbsensi->jam_pulang) {
                $jamPulangCarbon = $todayAbsensi->jam_pulang instanceof Carbon
                    ? $todayAbsensi->jam_pulang
                    : Carbon::parse($todayStr . ' ' . $todayAbsensi->jam_pulang);
                $isPulangCepat = strtoupper((string) $todayAbsensi->status_pulang) === 'PULANG CEPAT';
                $cardPulang = [
                    'id' => (string) $todayAbsensi->id . '_pulang',
                    'type' => 'pulang',
                    'title' => 'Presensi Pulang',
                    'subtitle' => $kantorPulangName,
                    'time' => $jamPulangCarbon->format('H:i') . ' WIB',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isPulangCepat ? 'Pulang Cepat' : 'Hadir',
                    'status_type' => $isPulangCepat ? 'pulang_cepat' : 'pulang',
                    'foto_url' => $todayAbsensi->foto_pulang ? asset('storage/' . $todayAbsensi->foto_pulang) : null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => $todayAbsensi->jarak_meter_pulang,
                    'created_at' => $jamPulangCarbon->toISOString(),
                ];
            } else {
                $isPulangAlpa = false;
                if ($windowOutEnd && $now->greaterThan($windowOutEnd)) {
                    $isPulangAlpa = true;
                }

                $cardPulang = [
                    'id' => ($todayAbsensi ? (string) $todayAbsensi->id : 'today') . '_pulang',
                    'type' => 'pulang',
                    'title' => 'Presensi Pulang',
                    'subtitle' => $kantorPulangName,
                    'time' => '-',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isPulangAlpa ? 'Alpa' : '-',
                    'status_type' => $isPulangAlpa ? 'alpa' : 'pending',
                    'foto_url' => null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => null,
                    'created_at' => $now->startOfDay()->addMinute()->toISOString(),
                ];
            }

            $recentActivities = [$cardMasuk, $cardPulang];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'month_name' => $targetMonth->translatedFormat('F Y'),
                'month' => $month,
                'year' => $year,
                'hadir_count' => $hadirCount,
                'alpa_count' => $alpaCount,
                'izin_count' => $izinCount,
                'libur_count' => $liburCount,
                'total_hari' => $totalHari,
                'hadir_percentage' => $hadirPercentage,
                'status_hari_ini' => $statusHariIni,
                'recent_activities' => $recentActivities,
            ],
        ]);
    }
}
