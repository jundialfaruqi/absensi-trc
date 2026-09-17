<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelJadwalController extends Controller
{
    /**
     * Dapatkan daftar jadwal kerja milik personel dalam satu bulan kalender penuh.
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

        // Ambil data jadwal personel pada bulan & tahun yang diminta
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

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = Carbon::createFromDate($year, $month, $d)->startOfDay();
            $tglStr = $currentDate->format('Y-m-d');

            $jadwal = $jadwals->get($tglStr);

            if ($jadwal) {
                $shift = $jadwal->shift;
                $shiftCode = $shift?->name;
                $shiftKeterangan = $shift?->keterangan;
                $statusUpper = strtoupper((string) ($jadwal->status ?: 'SHIFT'));
                $shiftCodeUpper = strtoupper((string) $shiftCode);
                $shiftKetUpper = strtoupper((string) $shiftKeterangan);

                // Cek apakah DINAS
                $isDinas = ($statusUpper === 'DINAS') ||
                    ($shiftCodeUpper === 'D') ||
                    (str_contains($shiftKetUpper, 'DINAS'));

                // Cek apakah LIBUR / OFF (hanya jika bukan DINAS)
                $isOff = !$isDinas && (
                    ($statusUpper === 'LIBUR') ||
                    ($statusUpper === 'OFF') ||
                    ($shiftCodeUpper === 'L') ||
                    (str_contains($shiftKetUpper, 'LIBUR')) ||
                    ($shift?->type === 'off')
                );

                $shiftType = $shift?->type ?? ($isOff ? 'off' : ($isDinas ? 'dinas' : 'shift'));
                $status = $isDinas ? 'DINAS' : ($isOff ? 'LIBUR' : ($jadwal->status ?: 'SHIFT'));
                $shiftName = $shiftCode ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : ($status ?: '-'));

                $shiftHours = null;
                $startTime = null;
                $endTime = null;

                if (!$isOff && !$isDinas && $shift && $shift->start_time && $shift->end_time) {
                    $startTime = Carbon::parse($shift->start_time)->format('H:i');
                    $endTime = Carbon::parse($shift->end_time)->format('H:i');
                    $shiftHours = "{$startTime} - {$endTime}";
                }

                $items[] = [
                    'id' => (string) $jadwal->id,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $status,
                    'shift_name' => $shiftName,
                    'shift_type' => $shiftType,
                    'shift_hours' => $shiftHours,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'color' => $shift?->color,
                    'keterangan' => $jadwal->keterangan ?: $shiftKeterangan,
                    'is_off' => $isOff,
                    'is_dinas' => $isDinas,
                ];
            } else {
                // Tidak ada entri jadwal di tanggal ini
                $isFlexible = $personnel->attendance_type === 'FLEXIBLE';
                $shiftName = $isFlexible ? 'Fleksibel' : '-';
                $shiftHours = null;

                $items[] = [
                    'id' => 'empty_' . $tglStr,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $isFlexible ? 'FLEXIBLE' : '-',
                    'shift_name' => $shiftName,
                    'shift_type' => $isFlexible ? 'flexible' : 'none',
                    'shift_hours' => $shiftHours,
                    'start_time' => null,
                    'end_time' => null,
                    'color' => null,
                    'keterangan' => null,
                    'is_off' => false,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data jadwal kerja berhasil dimuat.',
            'data' => [
                'month' => $month,
                'year' => $year,
                'month_name' => $monthName,
                'jadwal' => $items,
            ],
        ]);
    }
}
