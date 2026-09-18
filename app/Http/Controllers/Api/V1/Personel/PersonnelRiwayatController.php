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

class PersonnelRiwayatController extends Controller
{
    /**
     * Dapatkan daftar riwayat presensi bulanan lengkap untuk personel login.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');

        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('m');
        }
        if ($year < 2020 || $year > 2050) {
            $year = (int) date('Y');
        }

        $targetDate = Carbon::createFromDate($year, $month, 1);
        $monthName = $targetDate->translatedFormat('F Y');
        $daysInMonth = $targetDate->daysInMonth;

        // Ambil seluruh data absensi pada bulan & tahun yang diminta
        $records = Absensi::where('personnel_id', $personnel->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with(['jadwal.shift', 'kantor', 'kantorPulang'])
            ->get()
            ->keyBy(function ($item) {
                $t = $item->tanggal instanceof Carbon ? $item->tanggal : Carbon::parse($item->tanggal);
                return $t->format('Y-m-d');
            });

        // Ambil jadwal jika ada
        $jadwals = Jadwal::where('personnel_id', $personnel->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with('shift')
            ->get()
            ->keyBy(function ($item) {
                $t = $item->tanggal instanceof Carbon ? $item->tanggal : Carbon::parse($item->tanggal);
                return $t->format('Y-m-d');
            });

        $items = [];
        $hadirCount = 0;
        $telatCount = 0;
        $alpaCount = 0;
        $izinCount = 0;
        $liburCount = 0;
        $today = Carbon::today();
        $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

        // Generate seluruh tanggal dari 1 sampai akhir bulan (1 kalender penuh)
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = Carbon::createFromDate($year, $month, $d)->startOfDay();
            $tglStr = $currentDate->format('Y-m-d');
            $isFuture = $currentDate->greaterThan($today);
            $isToday = $currentDate->equalTo($today);

            $rec = $records->get($tglStr);
            $jadwal = $jadwals->get($tglStr);

            if ($rec) {
                $shift = $rec->jadwal?->shift ?? $jadwal?->shift;
                $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : null);
                
                $shiftHours = null;
                if ($shift && $shift->start_time && $shift->end_time) {
                    $sStart = Carbon::parse($shift->start_time)->format('H:i');
                    $sEnd = Carbon::parse($shift->end_time)->format('H:i');
                    $shiftHours = "{$sStart} - {$sEnd}";
                }

                // Stat counters
                $statusUpper = strtoupper((string) $rec->status);
                $statusMasukUpper = strtoupper((string) $rec->status_masuk);
                $itemStatus = $rec->status;

                if ($statusUpper === 'HADIR') {
                    $hadirCount++;
                } elseif ($statusUpper === 'ALPA') {
                    if (!$isFuture) {
                        if ($isToday) {
                            $isTodayExpired = false;
                            if ($shift && $shift->end_time) {
                                $isNightShiftToday = Carbon::parse($shift->start_time)->format('H:i:s') >= Carbon::parse($shift->end_time)->format('H:i:s');
                                $endDateToday = $isNightShiftToday ? Carbon::today()->addDay()->format('Y-m-d') : $tglStr;
                                $endLimitToday = Carbon::parse($endDateToday)->setTimeFrom($shift->end_time)->addMinutes($selesaiOut);
                                if (Carbon::now()->greaterThan($endLimitToday)) {
                                    $isTodayExpired = true;
                                }
                            }
                            if ($isTodayExpired) {
                                $alpaCount++;
                            } else {
                                $itemStatus = '-';
                            }
                        } else {
                            $alpaCount++;
                        }
                    } else {
                        // Tanggal di masa depan (> hari ini) tidak dihitung Alpa, tampilkan '-'
                        $itemStatus = '-';
                    }
                } elseif (in_array($statusUpper, ['IZIN', 'SAKIT', 'CUTI'])) {
                    $izinCount++;
                } elseif (in_array($statusUpper, ['LIBUR', 'DINAS'])) {
                    $liburCount++;
                }

                if ($statusMasukUpper === 'TELAT') {
                    $telatCount++;
                }

                // Format Jam Masuk
                $jamMasukStr = null;
                if ($rec->jam_masuk && (!$isFuture || $statusUpper !== 'ALPA')) {
                    $jm = $rec->jam_masuk instanceof Carbon ? $rec->jam_masuk : Carbon::parse($tglStr . ' ' . $rec->jam_masuk);
                    $jamMasukStr = $jm->format('H:i') . ' WIB';
                }

                // Format Jam Pulang
                $jamPulangStr = null;
                if ($rec->jam_pulang && (!$isFuture || $statusUpper !== 'ALPA')) {
                    $jp = $rec->jam_pulang instanceof Carbon ? $rec->jam_pulang : Carbon::parse($tglStr . ' ' . $rec->jam_pulang);
                    $jamPulangStr = $jp->format('H:i') . ' WIB';
                }

                $kantorName = $rec->kantor?->name ?? $rec->kantor?->nama_kantor ?? $personnel->kantor?->name ?? '-';

                $items[] = [
                    'id' => (string) $rec->id,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $itemStatus,
                    'keterangan' => $rec->keterangan,
                    'is_edited' => !empty($rec->edited_at),
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jam_masuk' => $jamMasukStr,
                    'status_masuk' => ($isFuture || ($isToday && $itemStatus === '-')) && $statusUpper === 'ALPA' ? null : $rec->status_masuk,
                    'foto_masuk' => $rec->foto_masuk ? asset('storage/' . $rec->foto_masuk) : null,
                    'jarak_masuk' => $rec->jarak_meter,
                    'jam_pulang' => $jamPulangStr,
                    'status_pulang' => ($isFuture || ($isToday && $itemStatus === '-')) && $statusUpper === 'ALPA' ? null : $rec->status_pulang,
                    'foto_pulang' => $rec->foto_pulang ? asset('storage/' . $rec->foto_pulang) : null,
                    'jarak_pulang' => $rec->jarak_meter_pulang,
                    'kantor_name' => $kantorName,
                ];
            } else {
                // Tidak ada data absensi di tanggal ini
                // Dapatkan status dari jadwal / shift off secara dinamis
                $shift = $jadwal?->shift;
                $isOff = false;
                if ($shift) {
                    $isOff = ($shift->type === 'off') || (stripos($shift->name ?? '', 'libur') !== false);
                }

                $status = '-';
                $keterangan = null;
                if ($isOff) {
                    $status = strtoupper(trim((string) ($shift->keterangan ?: ($jadwal?->status && $jadwal->status !== 'SHIFT' ? $jadwal->status : ($shift->name === 'L' ? 'LIBUR' : 'DINAS')))));
                    if (empty($status) || $status === 'OFF') {
                        $status = 'LIBUR';
                    }
                    $keterangan = $shift->keterangan ?: 'Hari Libur';
                    if (in_array($status, ['LIBUR', 'DINAS'])) {
                        $liburCount++;
                    } elseif (in_array($status, ['IZIN', 'SAKIT', 'CUTI'])) {
                        $izinCount++;
                    } else {
                        $liburCount++;
                    }
                }

                $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : null);

                $items[] = [
                    'id' => 'empty_' . $tglStr,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'is_edited' => false,
                    'shift_name' => $shiftName,
                    'shift_hours' => null,
                    'jam_masuk' => null,
                    'status_masuk' => null,
                    'foto_masuk' => null,
                    'jarak_masuk' => null,
                    'jam_pulang' => null,
                    'status_pulang' => null,
                    'foto_pulang' => null,
                    'jarak_pulang' => null,
                    'kantor_name' => $personnel->kantor?->name ?? '-',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data riwayat presensi berhasil dimuat.',
            'data' => [
                'month' => $month,
                'year' => $year,
                'month_name' => $monthName,
                'summary' => [
                    'total_hari' => $daysInMonth,
                    'total_hadir' => $hadirCount,
                    'total_terlambat' => $telatCount,
                    'total_alpa' => $alpaCount,
                    'total_izin' => $izinCount,
                    'total_libur' => $liburCount,
                ],
                'riwayat' => $items,
            ],
        ]);
    }
}
