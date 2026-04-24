<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function exportAbsensiPdf(Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $month = (int) $request->get('month', date('m'));
        $year = (int) $request->get('year', date('Y'));
        $search = $request->get('search');
        $paperSize = $request->get('paperSize', 'a4');

        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

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
                $q->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->get();

        // Transform for easier view lookup
        foreach ($personnels as $p) {
            $p->absensi_map = $p->absensis->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
            $p->jadwal_map = $p->jadwals->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));
        }

        $opdName = $opdId ? \App\Models\Opd::find($opdId)->name : 'Semua OPD';
        $monthName = Carbon::create()->month($month)->translatedFormat('F');

        $data = [
            'personnels' => $personnels,
            'dates' => $dates,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
            'opdName' => $opdName,
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
    }
}
