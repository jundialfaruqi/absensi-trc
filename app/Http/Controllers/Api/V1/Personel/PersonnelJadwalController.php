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
                $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : ($jadwal->status ?: '-'));
                $shiftType = $shift?->type ?? ($jadwal->status === 'LIBUR' ? 'off' : 'shift');
                
                $shiftHours = null;
                $startTime = null;
                $endTime = null;

                if ($shift && $shift->start_time && $shift->end_time) {
                    $startTime = Carbon::parse($shift->start_time)->format('H:i');
                    $endTime = Carbon::parse($shift->end_time)->format('H:i');
                    $shiftHours = "{$startTime} - {$endTime}";
                }

                $statusUpper = strtoupper((string) ($jadwal->status ?: 'SHIFT'));
                $isOff = ($shiftType === 'off') || (stripos($shiftName, 'libur') !== false) || in_array($statusUpper, ['LIBUR', 'OFF']);

                $items[] = [
                    'id' => (string) $jadwal->id,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $jadwal->status ?: 'SHIFT',
                    'shift_name' => $shiftName,
                    'shift_type' => $shiftType,
                    'shift_hours' => $shiftHours,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'color' => $shift?->color,
                    'keterangan' => $jadwal->keterangan ?: $shift?->keterangan,
                    'is_off' => $isOff,
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
