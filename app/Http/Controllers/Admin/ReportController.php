<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Models\Personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DokumentasiKonsumsi;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

class ReportController extends Controller
{
    public function exportAbsensiPdf(Request $request)
    {
        try {
            // Increase memory limit and execution time to prevent crashes on large PDF reports
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $month = (int) $request->get('month', date('m'));
            $year = (int) $request->get('year', date('Y'));
            $search = $request->get('search');
            $paperSize = $request->get('paperSize', 'a4');

            $opdId = Auth::user()->hasRole('super-admin') ? ($request->get('opd_id') ?: null) : Auth::user()->opd()?->id;

            $dates = [];
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);

                while ($start <= $end) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
            } else {
                // Fallback to month/year logic
                $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $dates[] = Carbon::create($year, $month, $i)->format('Y-m-d');
                }
            }

            // Get Personnel Data with their Attendance and Schedule
            $personnels = Personnel::with(['absensis' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates);
            }, 'jadwals' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates)
                    ->with('shift');
            }, 'penugasan', 'opd'])
                ->when($opdId, function ($q) use ($opdId) {
                    $q->where('opd_id', $opdId);
                })
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orderBy('name')
                ->get();

            // Transform for easier view lookup
            foreach ($personnels as $p) {
                $p->absensi_map = $p->absensis->keyBy(fn ($a) => $a->tanggal->format('Y-m-d'));
                $p->jadwal_map = $p->jadwals->keyBy(fn ($j) => $j->tanggal->format('Y-m-d'));
            }

            $opdName = $opdId ? Opd::find($opdId)->name : 'Semua OPD';
            $monthName = Carbon::create()->month($month)->translatedFormat('F');

            $excludedShifts = $request->get('excluded_shifts', $request->get('shift_ids', []));
            $excludedShiftIds = is_array($excludedShifts) ? $excludedShifts : explode(',', (string) $excludedShifts);
            $excludedShiftIds = array_map('intval', array_filter($excludedShiftIds));

            $data = [
                'personnels' => $personnels,
                'dates' => $dates,
                'month' => $month,
                'year' => $year,
                'monthName' => $monthName,
                'opdName' => $opdName,
                'excludedShiftIds' => $excludedShiftIds,
            ];

            // Define paper dimensions (landscape)
            $paperFormat = $paperSize;
            if ($paperSize === 'f4') {
                // F4 size in points (72 points per inch)
                // 215mm x 330mm -> ~609pt x 935pt
                $paperFormat = [0, 0, 609, 935];
            }

            // Load PDF view
            $pdf = Pdf::loadView('reports.absensi-pdf', $data)
                ->setPaper($paperFormat, 'landscape');

            $filename = $this->generateExportFilename($request, 'pdf');

            return $pdf->download($filename)->withHeaders([
                'Access-Control-Expose-Headers' => 'X-Filename, Content-Disposition',
                'X-Filename' => $filename,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }
    }

    public function exportAbsensiExcel(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $search = $request->get('search');
            $opdId = $request->get('opd_id');

            $excludedShifts = $request->get('excluded_shifts', $request->get('shift_ids', []));
            $excludedShiftIds = is_array($excludedShifts) ? $excludedShifts : explode(',', (string) $excludedShifts);
            $excludedShiftIds = array_map('intval', array_filter($excludedShiftIds));

            $filename = $this->generateExportFilename($request, 'xlsx');

            $response = Excel::download(
                new AbsensiExport($startDate, $endDate, $search, $opdId, $excludedShiftIds),
                $filename
            );

            $response->headers->set('Access-Control-Expose-Headers', 'X-Filename, Content-Disposition');
            $response->headers->set('X-Filename', $filename);

            return $response;
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }
    }

    public function exportKonsumsiPdf(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $month = (int) $request->get('month', date('m'));
            $year = (int) $request->get('year', date('Y'));
            $search = $request->get('search');
            $paperSize = $request->get('paperSize', 'a4');
            $includeRekap = $request->has('include_rekap') ? $request->boolean('include_rekap') : true;
            $includeRincian = $request->has('include_rincian') ? $request->boolean('include_rincian') : false;

            // Pastikan minimal salah satu aktif
            if (!$includeRekap && !$includeRincian) {
                $includeRekap = true;
            }

            $opdId = Auth::user()->hasRole('super-admin') ? ($request->get('opd_id') ?: null) : Auth::user()->opd()?->id;

            $dates = [];
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);

                if ($start->diffInDays($end) > 31) {
                    $end = $start->copy()->addDays(31);
                }

                while ($start <= $end) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
            } else {
                $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $dates[] = Carbon::create($year, $month, $i)->format('Y-m-d');
                }
            }

            // Get Personnel Data with Attendance and Schedule
            $personnels = Personnel::with(['absensis' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates);
            }, 'jadwals' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates)
                    ->with('shift.konsumsis');
            }, 'penugasan', 'opd'])
                ->when($opdId, function ($q) use ($opdId) {
                    $q->where('opd_id', $opdId);
                })
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orderBy('name')
                ->get();

            // Transform maps for rapid lookup
            $dailySummary = [];
            foreach ($dates as $d) {
                $dailySummary[$d] = [
                    'siang' => 0,
                    'malam' => 0,
                    'total' => 0,
                ];
            }

            $totalSiangAll = 0;
            $totalMalamAll = 0;

            foreach ($personnels as $p) {
                $p->absensi_map = $p->absensis->keyBy(fn ($a) => $a->tanggal->format('Y-m-d'));
                $p->jadwal_map = $p->jadwals->keyBy(fn ($j) => $j->tanggal->format('Y-m-d'));

                $p->total_siang = 0;
                $p->total_malam = 0;

                foreach ($dates as $d) {
                    $abs = $p->absensi_map->get($d);
                    $jadwal = $p->jadwal_map->get($d);

                    $isHadir = $abs && (
                        $abs->status === 'HADIR' ||
                        $abs->status === 'TELAT' ||
                        !empty($abs->jam_masuk)
                    );

                    if ($isHadir && $jadwal && $jadwal->shift) {
                        $konsumsis = $jadwal->shift->konsumsis->pluck('nama')->map(fn ($k) => strtolower(trim($k)))->toArray();

                        if (in_array('siang', $konsumsis)) {
                            $p->total_siang++;
                            $dailySummary[$d]['siang']++;
                            $totalSiangAll++;
                        }
                        if (in_array('malam', $konsumsis)) {
                            $p->total_malam++;
                            $dailySummary[$d]['malam']++;
                            $totalMalamAll++;
                        }
                    }
                    $dailySummary[$d]['total'] = $dailySummary[$d]['siang'] + $dailySummary[$d]['malam'];
                }
            }

            // Override nilai SIANG/MALAM dari tabel dokumentasi_konsumsis jika tersedia
            $dokRecords = DokumentasiKonsumsi::whereIn('tanggal', $dates)
                ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
                ->get()
                ->keyBy(fn ($item) => $item->tanggal->format('Y-m-d'));

            foreach ($dates as $d) {
                $dok = $dokRecords->get($d);
                if ($dok !== null) {
                    // Pakai nilai aktual dari dokumentasi (override absensi)
                    $dailySummary[$d]['siang'] = $dok->jumlah_siang ?? 0;
                    $dailySummary[$d]['malam'] = $dok->jumlah_malam ?? 0;
                    $dailySummary[$d]['total'] = $dailySummary[$d]['siang'] + $dailySummary[$d]['malam'];
                } else {
                    // Tidak ada record dokumentasi → tampilkan null (akan ditampilkan sebagai "-" di PDF)
                    $dailySummary[$d]['siang'] = null;
                    $dailySummary[$d]['malam'] = null;
                    $dailySummary[$d]['total'] = null;
                }
            }

            // Hitung ulang grand total dari dailySummary — null diabaikan
            $totalSiangAll = array_sum(array_filter(array_column($dailySummary, 'siang'), fn ($v) => $v !== null));
            $totalMalamAll = array_sum(array_filter(array_column($dailySummary, 'malam'), fn ($v) => $v !== null));

            $opdName = $opdId ? Opd::find($opdId)->name : 'Semua OPD';
            $monthName = Carbon::create()->month($month)->translatedFormat('F');

            $data = [
                'personnels' => $personnels,
                'dates' => $dates,
                'dailySummary' => $dailySummary,
                'totalSiangAll' => $totalSiangAll,
                'totalMalamAll' => $totalMalamAll,
                'grandTotalAll' => $totalSiangAll + $totalMalamAll,
                'month' => $month,
                'year' => $year,
                'monthName' => $monthName,
                'opdName' => $opdName,
                'startDate' => $startDate ?? $dates[0] ?? null,
                'endDate' => $endDate ?? end($dates) ?? null,
                'includeRekap' => $includeRekap,
                'includeRincian' => $includeRincian,
            ];

            $paperFormat = $paperSize;
            if ($paperSize === 'f4') {
                $paperFormat = [0, 0, 609, 935];
            }

            $pdf = Pdf::loadView('reports.konsumsi-pdf', $data)
                ->setPaper($paperFormat, 'landscape');

            $filename = $this->generateKonsumsiExportFilename($request, 'pdf');

            return $pdf->download($filename)->withHeaders([
                'Access-Control-Expose-Headers' => 'X-Filename, Content-Disposition',
                'X-Filename' => $filename,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }
    }

    private function generateKonsumsiExportFilename(Request $request, string $extension): string
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $includeRekap = $request->has('include_rekap') ? $request->boolean('include_rekap') : true;
        $includeRincian = $request->has('include_rincian') ? $request->boolean('include_rincian') : false;

        $prefix = 'rekap_konsumsi';
        if (!$includeRekap && $includeRincian) {
            $prefix = 'rincian_konsumsi_personel';
        } elseif ($includeRekap && $includeRincian) {
            $prefix = 'rekap_dan_rincian_konsumsi';
        }

        if ($startDate && $endDate) {
            $startFormatted = Carbon::parse($startDate)->format('d-m-Y');
            $endFormatted = Carbon::parse($endDate)->format('d-m-Y');

            return "{$prefix}_{$startFormatted}_{$endFormatted}.{$extension}";
        }

        if ($startDate) {
            $startFormatted = Carbon::parse($startDate)->format('d-m-Y');

            return "{$prefix}_{$startFormatted}.{$extension}";
        }

        $month = (int) $request->get('month', date('m'));
        $year = (int) $request->get('year', date('Y'));
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $startFormatted = Carbon::create($year, $month, 1)->format('d-m-Y');
        $endFormatted = Carbon::create($year, $month, $daysInMonth)->format('d-m-Y');

        return "{$prefix}_{$startFormatted}_{$endFormatted}.{$extension}";
    }

    private function generateExportFilename(Request $request, string $extension): string
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        if ($startDate && $endDate) {
            $startFormatted = Carbon::parse($startDate)->format('d-m-Y');
            $endFormatted = Carbon::parse($endDate)->format('d-m-Y');

            return "rekap_absensi_{$startFormatted}_{$endFormatted}.{$extension}";
        }

        if ($startDate) {
            $startFormatted = Carbon::parse($startDate)->format('d-m-Y');

            return "rekap_absensi_{$startFormatted}.{$extension}";
        }

        $month = (int) $request->get('month', date('m'));
        $year = (int) $request->get('year', date('Y'));
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $startFormatted = Carbon::create($year, $month, 1)->format('d-m-Y');
        $endFormatted = Carbon::create($year, $month, $daysInMonth)->format('d-m-Y');

        return "rekap_absensi_{$startFormatted}_{$endFormatted}.{$extension}";
    }
}
