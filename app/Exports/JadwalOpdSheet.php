<?php
 
namespace App\Exports;
 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\Personnel;
use App\Models\Shift;
use Carbon\Carbon;
 
class JadwalOpdSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $month;
    protected $year;
    protected $opdId;
    protected $opdName;
    protected $personnelCount = 0;
 
    public function __construct($month, $year, $opdId, $opdName)
    {
        $this->month = $month;
        $this->year = $year;
        $this->opdId = $opdId;
        $this->opdName = $opdName;
    }
 
    public function collection()
    {
        $personnels = Personnel::where('opd_id', $this->opdId)
            ->where('attendance_type', '!=', 'FLEXIBLE')
            ->with(['jadwals' => function($q) {
                $q->whereYear('tanggal', $this->year)
                  ->whereMonth('tanggal', $this->month)
                  ->with('shift');
            }])
            ->orderBy('name')
            ->get();
            
        $this->personnelCount = $personnels->count();
        $data = collect();
 
        // 1. Data Personnel
        foreach ($personnels as $p) {
            $row = [
                'id' => $p->id,
                'nama' => $p->name,
            ];

            $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
            
            // Map jadwals by date for quick lookup
            $mappedJadwal = $p->jadwals->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->format('j');
            });

            for ($i = 1; $i <= $daysInMonth; $i++) { 
                $jadwal = $mappedJadwal->get($i);
                if ($jadwal) {
                    // Gunakan nama asli shift dari database (misal: PAGI, SIANG, OFF-A, dll)
                    // Jika jadwal tidak terikat ke shift (manual), baru gunakan fallback 'LIBUR'
                    $row[] = $jadwal->shift ? $jadwal->shift->name : 'LIBUR';
                } else {
                    $row[] = ''; 
                }
            }
            $data->push($row);
        }
 
        // 2. Separator Empty Rows
        $data->push(['', '']);
        $data->push(['', '']);
 
        // 3. Shift Reference Section
        $data->push(['DAFTAR REFERENSI NAMA SHIFT']);
        $data->push(['Gunakan nama di bawah ini untuk mengisi kolom tanggal di atas (Copy-Paste)']);
        $data->push(['Nama Shift', 'Jam Kerja', 'Keterangan']);
 
        $shifts = Shift::orderBy('name')->get();
        foreach ($shifts as $s) {
            $data->push([
                $s->name,
                $s->type === 'shift' 
                    ? Carbon::parse($s->start_time)->format('H:i') . ' - ' . Carbon::parse($s->end_time)->format('H:i')
                    : '---',
                $s->keterangan
            ]);
        }
 
        return $data;
    }
 
    public function headings(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        
        $dayNames = ['ID Personnel', 'Nama Personnel']; 
        $dateNumbers = ['', '']; 
        
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($this->year, $this->month, $i);
            $dayNames[] = $date->translatedFormat('D');
            $dateNumbers[] = (string)$i;
        }
 
        return [
            ['TEMPLATE IMPORT JADWAL SHIFT - ' . strtoupper($this->opdName)],
            ['Periode:', Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y')],
            ['Instruksi:', 'JANGAN MENGUBAH FORMAT. Isi tanggal dengan NAMA SHIFT. Scroll ke bawah untuk daftar shift.'],
            [''], 
            $dayNames,     // Row 5
            $dateNumbers,  // Row 6
        ];
    }
 
    public function columnWidths(): array
    {
        // Meningkatkan lebar kolom untuk menampung font yang lebih besar
        return [
            'A' => 20, 
            'B' => 50, // Nama Personnel lebih lebar
        ];
    }
 
    public function styles(Worksheet $sheet)
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        $lastColumn = Coordinate::stringFromColumnIndex($daysInMonth + 2);
        $highestPersonnelRow = 6 + $this->personnelCount;
        
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        $sheet->getStyle('B3')->getFont()->setBold(true)->getColor()->setARGB('FFFF0000');
 
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->getStyle('A5:B6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
 
        $headerRange = 'A5:' . $lastColumn . '6';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Style untuk Nama Personnel (B7 sampai akhir)
        $sheet->getStyle('B7:B' . $highestPersonnelRow)->getFont()->setBold(true)->setSize(16);

        // Style untuk Matrix Jadwal (C7 sampai akhir)
        $sheet->getStyle('C7:' . $lastColumn . $highestPersonnelRow)->getFont()->setSize(16);

        // Mengatur tinggi baris agar terlihat seperti ada padding
        for ($i = 7; $i <= $highestPersonnelRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(35);
        }

        // Mengatur lebar kolom tanggal agar proporsional dengan font besar
        for ($i = 3; $i <= $daysInMonth + 2; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(12);
        }
 
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($this->year, $this->month, $i);
            if ($date->isWeekend()) {
                $col = Coordinate::stringFromColumnIndex($i + 2);
                $sheet->getStyle($col . '5')->getFont()->getColor()->setARGB('FFFCA5A5');
            }
        }
 
        $refStartRow = $highestPersonnelRow + 5;
        $sheet->mergeCells('A' . $refStartRow . ':C' . $refStartRow);
        $sheet->getStyle('A' . $refStartRow)->getFont()->setBold(true)->setSize(14);
        
        $refHeaderRow = $refStartRow + 2;
        $sheet->getStyle('A' . $refHeaderRow . ':C' . $refHeaderRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']],
        ]);
 
        return [];
    }
 
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
                $lastColumn = Coordinate::stringFromColumnIndex($daysInMonth + 2);
                $highestPersonnelRow = 6 + $this->personnelCount;
                $matrixRange = 'C7:' . $lastColumn . $highestPersonnelRow;
                
                // 1. Basic Borders & Alignment
                $event->sheet->getStyle('A5:' . $lastColumn . $highestPersonnelRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                ]);

                // 2. Conditional Formatting for Shifts
                $shifts = Shift::all();
                $conditionalStyles = [];

                foreach ($shifts as $s) {
                    if ($s->color) {
                        $color = str_replace('#', '', $s->color);
                        // Ensure it's a valid hex
                        if (strlen($color) === 6) {
                            $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                            $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
                            $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL);
                            $conditional->addCondition('"' . $s->name . '"');
                            $conditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . strtoupper($color));
                            $conditional->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF'); // White text for colored bg
                            $conditionalStyles[] = $conditional;
                        }
                    }
                }

                // Fallback for 'LIBUR' if not in shifts
                $liburConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $liburConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
                $liburConditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL);
                $liburConditional->addCondition('"LIBUR"');
                $liburConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCA5A5'); // Light Red
                $liburConditional->getStyle()->getFont()->getColor()->setARGB('FF991B1B'); // Dark Red Text
                $conditionalStyles[] = $liburConditional;

                $event->sheet->getStyle($matrixRange)->setConditionalStyles($conditionalStyles);

                // 3. Data Validation (Dropdown)
                $options = $shifts->pluck('name')->push('LIBUR')->unique()->implode(',');
                
                if (!empty($options)) {
                    $validation = $event->sheet->getDelegate()->getDataValidation($matrixRange);
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input Tidak Valid');
                    $validation->setError('Harap pilih shift yang tersedia dari daftar atau ketik dengan benar.');
                    $validation->setPromptTitle('Pilih Jadwal');
                    $validation->setPrompt('Pilih shift dari daftar dropdown yang muncul.');
                    $validation->setFormula1('"' . $options . '"');
                }

                $refStartRow = $highestPersonnelRow + 5;
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A' . $refStartRow . ':C' . $highestRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                ]);

                $event->sheet->freezePane('C7');
                $event->sheet->getStyle('A7:A' . $highestPersonnelRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getStyle($matrixRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
            },
        ];
    }
 
    public function title(): string
    {
        // Limit title to 31 chars (Excel limit)
        return substr($this->opdName, 0, 31);
    }
}
