<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminJadwalController extends Controller
{
    /**
     * Endpoint Data Jadwal & Monitoring Kehadiran (Stats Grid & Log Aktifitas).
     * Khusus aplikasi Absensi TRC Admin (role: super-admin & admin-opd).
     * 
     * Aturan Scoping:
     * - super-admin: Dapat melihat seluruh data personil dari semua OPD.
     * - admin-opd: Hanya dapat melihat data personil pada OPD yang diaturnya.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Validasi role admin
        if (!$user->hasAnyRole(['admin-opd', 'super-admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus untuk Admin OPD dan Super Admin.',
            ], 403);
        }

        $isSuperAdmin = $user->hasRole('super-admin');
        $opd = $user->opds()->first();
        $opdId = $opd?->id;

        // Tanggal filter (default hari ini)
        $tanggal = $request->query('tanggal', Carbon::today()->format('Y-m-d'));
        // Filter shift ('siang', 'malam', 'flexible', atau ID shift)
        $filterShift = $request->query('shift', '');

        // Base query absensi pada tanggal tersebut
        $absensiBase = Absensi::whereDate('tanggal', $tanggal)
            ->where(function ($q) {
                // Tidak termasuk LIBUR
                $q->whereHas('jadwal.shift', fn ($sq) => $sq->where('type', 'shift'))
                    ->orWhereHas('personnel', fn ($pq) => $pq->where('attendance_type', 'FLEXIBLE'));
            })
            ->when(!$isSuperAdmin, function ($q) use ($opdId) {
                $q->whereHas('personnel', fn ($pq) => $pq->where('opd_id', $opdId));
            })
            ->when(!empty($filterShift), function ($q) use ($filterShift) {
                if (is_numeric($filterShift)) {
                    $q->whereHas('jadwal.shift', fn ($sq) => $sq->where('id', $filterShift));
                } elseif ($filterShift === 'flexible') {
                    $q->whereHas('personnel', fn ($pq) => $pq->where('attendance_type', 'FLEXIBLE'));
                } else {
                    $q->whereHas('jadwal.shift.konsumsis', fn ($sq) => $sq->where('nama', $filterShift));
                }
            });

        // 1. Stats Grid 4 Kolom
        $totalRequired = (clone $absensiBase)->count();
        $totalHadir = (clone $absensiBase)->where('status', 'HADIR')->count();
        $totalAlpa = (clone $absensiBase)->where('status', 'ALPA')->count();
        $totalIzin = (clone $absensiBase)->whereIn('status', ['CUTI', 'IZIN', 'SAKIT', 'DINAS'])->count();

        $totalMasuk = (clone $absensiBase)->whereNotNull('jam_masuk')->count();
        $totalPulang = (clone $absensiBase)->whereNotNull('jam_pulang')->count();
        $totalTelat = (clone $absensiBase)->where('status_masuk', 'TELAT')->count();

        $hadirPercentage = $totalRequired > 0
            ? (int) round((($totalHadir + $totalIzin) / $totalRequired) * 100)
            : 0;

        // 2. Log Aktifitas Hari Ini
        $activitiesQuery = (clone $absensiBase)
            ->join('personnels', 'absensis.personnel_id', '=', 'personnels.id')
            ->leftJoin('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select('absensis.*')
            ->with(['personnel.opd', 'personnel.penugasan', 'jadwal.shift']);

        if ($isSuperAdmin) {
            $activitiesQuery->orderBy('opds.singkatan')->orderBy('personnels.name');
        } else {
            $activitiesQuery->orderBy('personnels.name');
        }

        $activities = $activitiesQuery
            ->latest('absensis.updated_at')
            ->get()
            ->map(function ($log) {
                $personnel = $log->personnel;
                $jadwal = $log->jadwal;
                $shift = $jadwal?->shift;

                // Format shift time
                $startTime = $shift?->start_time;
                if ($startTime instanceof Carbon || $startTime instanceof \DateTime) {
                    $startTime = $startTime->format('H:i');
                } elseif (is_string($startTime) && strlen($startTime) >= 5) {
                    $startTime = substr($startTime, str_contains($startTime, 'T') ? strpos($startTime, 'T') + 1 : 0, 5);
                }

                $endTime = $shift?->end_time;
                if ($endTime instanceof Carbon || $endTime instanceof \DateTime) {
                    $endTime = $endTime->format('H:i');
                } elseif (is_string($endTime) && strlen($endTime) >= 5) {
                    $endTime = substr($endTime, str_contains($endTime, 'T') ? strpos($endTime, 'T') + 1 : 0, 5);
                }

                // Format jam masuk
                $jamMasuk = $log->jam_masuk;
                if ($jamMasuk instanceof Carbon || $jamMasuk instanceof \DateTime) {
                    $jamMasuk = $jamMasuk->format('H:i');
                } elseif (is_string($jamMasuk) && strlen($jamMasuk) >= 5) {
                    $jamMasuk = substr($jamMasuk, 0, 5);
                }

                // Format jam pulang
                $jamPulang = $log->jam_pulang;
                if ($jamPulang instanceof Carbon || $jamPulang instanceof \DateTime) {
                    $jamPulang = $jamPulang->format('H:i');
                } elseif (is_string($jamPulang) && strlen($jamPulang) >= 5) {
                    $jamPulang = substr($jamPulang, 0, 5);
                }

                return [
                    'id' => $log->id,
                    'tanggal' => $log->tanggal instanceof Carbon ? $log->tanggal->format('Y-m-d') : substr((string) $log->tanggal, 0, 10),
                    'status' => $log->status,
                    'personnel' => [
                        'id' => $personnel?->id,
                        'name' => $personnel?->name ?? 'Tidak Diketahui',
                        'foto' => $personnel?->foto ? url('storage/' . $personnel->foto) : null,
                        'penugasan' => $personnel?->penugasan?->name ?? '-',
                        'opd' => $personnel?->opd ? [
                            'id' => $personnel->opd->id,
                            'name' => $personnel->opd->name,
                            'singkatan' => $personnel->opd->singkatan ?? $personnel->opd->name,
                        ] : null,
                    ],
                    'shift' => [
                        'id' => $shift?->id,
                        'name' => $shift?->name ?? 'FLEX / BEBAS',
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ],
                    'masuk' => [
                        'status_masuk' => $log->status_masuk,
                        'jam_masuk' => $jamMasuk,
                        'is_within_radius' => $log->is_within_radius !== null ? (bool) $log->is_within_radius : null,
                        'jarak_meter' => $log->jarak_meter !== null ? (int) $log->jarak_meter : null,
                    ],
                    'pulang' => [
                        'status_pulang' => $log->status_pulang,
                        'jam_pulang' => $jamPulang,
                        'is_within_radius_pulang' => $log->is_within_radius_pulang !== null ? (bool) $log->is_within_radius_pulang : null,
                        'jarak_meter_pulang' => $log->jarak_meter_pulang !== null ? (int) $log->jarak_meter_pulang : null,
                    ],
                ];
            });

        // 3. Opsi Filter Shift
        $availableShifts = Shift::where('type', 'shift')
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                $start = $s->start_time ? Carbon::parse($s->start_time)->format('H:i') : '';
                $end = $s->end_time ? Carbon::parse($s->end_time)->format('H:i') : '';
                return [
                    'id' => (string) $s->id,
                    'name' => $s->name,
                    'keterangan' => ($start && $end) ? "$start - $end" : ($s->keterangan ?? ''),
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Data jadwal dan log aktifitas berhasil diambil.',
            'data' => [
                'tanggal' => $tanggal,
                'filter_shift' => $filterShift,
                'is_super_admin' => $isSuperAdmin,
                'opd_name' => $isSuperAdmin ? 'Semua OPD' : ($opd?->name ?? 'OPD'),
                'stats' => [
                    'total_required' => $totalRequired,
                    'total_hadir' => $totalHadir,
                    'total_alpa' => $totalAlpa,
                    'total_izin' => $totalIzin,
                    'total_masuk' => $totalMasuk,
                    'total_pulang' => $totalPulang,
                    'total_telat' => $totalTelat,
                    'hadir_percentage' => $hadirPercentage,
                ],
                'shifts' => $availableShifts,
                'activities' => $activities,
            ],
        ]);
    }
}
