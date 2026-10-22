<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiOpdSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    public string $opdName;
    public string $opdSingkatan;
    public Collection $personnels;
    public array $dates;
    public array $excludedShiftIds;
    public string $timeDisplay;

    public function __construct(
        string $opdName,
        string $opdSingkatan,
        Collection $personnels,
        array $dates,
        array $excludedShiftIds = [],
        string $timeDisplay = 'none'
    ) {
        $this->opdName = $opdName;
        $this->opdSingkatan = $opdSingkatan;
        $this->personnels = $personnels;
        $this->dates = $dates;
        $this->excludedShiftIds = $excludedShiftIds;
        $this->timeDisplay = $timeDisplay;
    }

    public function view(): View
    {
        return view('exports.absensi-excel', [
            'opdName' => $this->opdName,
            'personnels' => $this->personnels,
            'dates' => $this->dates,
            'excludedShiftIds' => $this->excludedShiftIds,
            'timeDisplay' => $this->timeDisplay,
        ]);
    }

    public function title(): string
    {
        return $this->opdSingkatan;
    }

    public function styles(Worksheet $sheet)
    {
        if ($this->timeDisplay === 'masuk_pulang') {
            $sheet->getStyle($sheet->calculateWorksheetDimension())
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        return [];
    }
}
