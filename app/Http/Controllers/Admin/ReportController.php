<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Models\Personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            return $pdf->download("rekap_absensi_{$month}_{$year}.pdf");
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

            $filename = 'rekap_absensi';
            if ($startDate && $endDate) {
                $filename .= "_{$startDate}_{$endDate}";
            } elseif ($startDate) {
                $filename .= "_{$startDate}";
            } else {
                $filename .= '_' . date('Y_m_d');
            }
            $filename .= '.xlsx';

            return Excel::download(
                new AbsensiExport($startDate, $endDate, $search, $opdId, $excludedShiftIds),
                $filename
            );
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
}
