<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Opd;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsensiExport implements WithMultipleSheets
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $search;
    protected ?string $opdId;
    protected array $dates = [];

    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $search = null, ?string $opdId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
        $this->opdId = $opdId;

        $this->calculateDates();
    }

    protected function calculateDates(): void
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);

            if ($start > $end) {
                $temp = $start;
                $start = $end;
                $end = $temp;
            }

            // Cap at maximum 31 days to prevent memory issues
            if ($start->diffInDays($end) > 31) {
                $end = $start->copy()->addDays(31);
            }

            while ($start <= $end) {
                $this->dates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        } elseif ($this->startDate) {
            $this->dates = [$this->startDate];
        } else {
            $start = Carbon::yesterday();
            $end = Carbon::yesterday()->addDays(10);
            while ($start <= $end) {
                $this->dates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }
    }

    public function sheets(): array
    {
        $sheets = [];

        $user = Auth::user();
        $targetOpdId = ($user && $user->hasRole('super-admin'))
            ? ($this->opdId ?: null)
            : ($user ? $user->opd()?->id : null);

        // Fetch personnel along with attendance & schedule within date range
        $personnels = Personnel::with([
            'absensis' => function ($query) {
                $query->whereIn('tanggal', $this->dates);
            },
            'jadwals' => function ($query) {
                $query->whereIn('tanggal', $this->dates)->with('shift');
            },
            'penugasan',
            'opd'
        ])
        ->when($targetOpdId, function ($q) use ($targetOpdId) {
            $q->where('opd_id', $targetOpdId);
        })
        ->when($this->search, function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%');
        })
        ->orderBy('name')
        ->get();

        foreach ($personnels as $p) {
            $p->absensi_map = $p->absensis->keyBy(fn ($a) => $a->tanggal->format('Y-m-d'));
            $p->jadwal_map = $p->jadwals->keyBy(fn ($j) => $j->tanggal->format('Y-m-d'));
        }

        $grouped = $personnels->groupBy('opd_id');

        if ($grouped->isEmpty()) {
            $opdName = 'Data Kosong';
            if ($targetOpdId) {
                $opd = Opd::find($targetOpdId);
                if ($opd) {
                    $opdName = $opd->singkatan ?: $opd->name;
                }
            }
            $sheets[] = new AbsensiOpdSheet(
                opdName: $opdName,
                opdSingkatan: $opdName,
                personnels: collect(),
                dates: $this->dates
            );
            return $sheets;
        }

        $usedSheetTitles = [];

        foreach ($grouped as $opdId => $group) {
            $opd = $group->first()->opd;
            $opdName = $opd?->name ?? 'TANPA OPD';
            $opdSingkatan = $opd?->singkatan ?: $opdName;

            // Generate clean sheet title (max 31 characters, strip illegal characters)
            $cleanTitle = preg_replace('/[\\\\\/\?\*\[\]\:]/', '', $opdSingkatan);
            $cleanTitle = trim(substr($cleanTitle ?: 'OPD', 0, 28));

            $sheetTitle = $cleanTitle;
            $counter = 1;
            while (in_array(strtolower($sheetTitle), $usedSheetTitles)) {
                $sheetTitle = substr($cleanTitle, 0, 25) . " ({$counter})";
                $counter++;
            }
            $usedSheetTitles[] = strtolower($sheetTitle);

            $sheets[] = new AbsensiOpdSheet(
                opdName: $opdName,
                opdSingkatan: $sheetTitle,
                personnels: $group,
                dates: $this->dates
            );
        }

        return $sheets;
    }
}
