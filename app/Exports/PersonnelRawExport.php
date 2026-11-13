<?php

namespace App\Exports;

use App\Models\Personnel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PersonnelRawExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles
{
    protected ?int $opdId;

    protected int $rowNumber = 0;

    public function __construct(?int $opdId = null)
    {
        $this->opdId = $opdId;
    }

    public function collection(): Collection
    {
        return Personnel::query()
            ->with(['opd', 'penugasan', 'kantor'])
            ->leftJoin('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select(['personnels.*'])
            ->when($this->opdId, fn ($q) => $q->where('personnels.opd_id', $this->opdId))
            ->orderBy('opds.name', 'asc')
            ->orderBy('personnels.name', 'asc')
            ->orderBy('personnels.id', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'Mode Absen',
            'Kontak',
            'Penugasan',
            'Kantor Lokasi',
            'OPD Induk',
        ];
    }

    /**
     * @param  Personnel  $personnel
     */
    public function map($personnel): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $personnel->name,
            $personnel->email ?: '-',
            $personnel->attendance_type ?: '-',
            $personnel->nomor_hp ?: '-',
            $personnel->penugasan?->name ?: '-',
            $personnel->kantor?->name ?: '-',
            $personnel->opd?->name ?: ($personnel->opd?->singkatan ?: 'Tanpa OPD'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'E' && is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getStyle("A1:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
