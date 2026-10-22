<?php

use App\Imports\JadwalImport;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new #[Title('Import Jadwal')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public $file;

    public $month;

    public $year;

    public $detectedPeriod = null;

    public $detectedMonth = null;

    public $detectedYear = null;

    public $detectedDays = null;

    public $detectedSheets = null;

    public $showConfirmModal = false;

    public $importId;

    public $progress = null;

    public function mount()
    {
        $this->month = request('month', date('m'));
        $this->year = request('year', date('Y'));
    }

    public function rules()
    {
        return [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'month' => 'required',
            'year' => 'required',
        ];
    }

    public function updatedFile()
    {
        $this->detectedPeriod = null;
        $this->detectedMonth = null;
        $this->detectedYear = null;
        $this->detectedDays = null;
        $this->detectedSheets = null;

        if ($this->file) {
            try {
                $mimeType = $this->file->getMimeType();
                $allowedMimes = [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv',
                    'text/plain',
                    'application/csv',
                    'application/excel',
                    'application/vnd.ms-excel',
                    'application/vnd.msexcel',
                ];

                if (! in_array($mimeType, $allowedMimes)) {
                    $this->reset('file');
                    $this->addError('file', 'File yang diunggah bukan merupakan dokumen Excel atau CSV yang valid.');

                    return;
                }

                // Auto-detect periode dari isi file Excel atau nama file
                $detected = $this->detectPeriodFromFile(
                    $this->file->getRealPath(),
                    $this->file->getClientOriginalName()
                );

                if ($detected) {
                    $this->month = $detected['month'];
                    $this->year = $detected['year'];
                    $this->detectedMonth = $detected['month'];
                    $this->detectedYear = $detected['year'];
                    $this->detectedPeriod = Carbon::create((int) $this->year, (int) $this->month, 1)->translatedFormat('F Y');
                    $this->detectedDays = Carbon::create((int) $this->year, (int) $this->month, 1)->daysInMonth;
                    $this->detectedSheets = $detected['sheets'] ?? 1;
                }
            } catch (Exception $e) {
                $this->reset('file');
                $this->addError('file', 'File tidak dapat dibaca atau rusak: '.$e->getMessage());

                return;
            }
        }

        $this->validateOnly('file');
    }

    protected function detectPeriodFromFile(string $filePath, ?string $originalFilename = null): ?array
    {
        $monthMap = [
            'januari' => '01', 'january' => '01', 'jan' => '01',
            'februari' => '02', 'february' => '02', 'feb' => '02',
            'maret' => '03', 'march' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05', 'may' => '05',
            'juni' => '06', 'june' => '06', 'jun' => '06',
            'juli' => '07', 'july' => '07', 'jul' => '07',
            'agustus' => '08', 'august' => '08', 'agu' => '08', 'aug' => '08',
            'september' => '09', 'sep' => '09',
            'oktober' => '10', 'october' => '10', 'okt' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'december' => '12', 'des' => '12', 'dec' => '12',
        ];

        $cellsToCheck = [];
        $sheetCount = 1;

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);

            $filter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                public function readCell($columnAddress, $row, $worksheetName = '') {
                    return $row <= 3;
                }
            };
            $reader->setReadFilter($filter);
            $spreadsheet = $reader->load($filePath);
            $sheetCount = $spreadsheet->getSheetCount();
            $sheet = $spreadsheet->getSheet(0);

            $cellsToCheck[] = (string) $sheet->getCell('B2')->getValue();
            $cellsToCheck[] = (string) $sheet->getCell('A2')->getValue();
            $cellsToCheck[] = (string) $sheet->getCell('B1')->getValue();
            $cellsToCheck[] = (string) $sheet->getCell('A1')->getValue();
        } catch (\Throwable $e) {
            // Jika bukan format excel standar, lewati pembacaan sel
        }

        if ($originalFilename) {
            $cellsToCheck[] = $originalFilename;
        }

        foreach ($cellsToCheck as $text) {
            $cleanText = strtolower(trim((string) $text));
            if (! $cleanText) {
                continue;
            }

            $foundMonth = null;
            foreach ($monthMap as $name => $num) {
                if (preg_match('/\b'.$name.'\b/i', $cleanText)) {
                    $foundMonth = $num;
                    break;
                }
            }

            $foundYear = null;
            if (preg_match('/\b(20\d{2})\b/', $cleanText, $matches)) {
                $foundYear = $matches[1];
            }

            if ($foundMonth && $foundYear) {
                return [
                    'month' => $foundMonth,
                    'year' => $foundYear,
                    'sheets' => $sheetCount,
                ];
            }

            if (preg_match('/(20\d{2})[_-](\d{1,2})/', $cleanText, $m)) {
                $mMonth = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                if ((int) $mMonth >= 1 && (int) $mMonth <= 12) {
                    return [
                        'month' => $mMonth,
                        'year' => $m[1],
                        'sheets' => $sheetCount,
                    ];
                }
            }
        }

        return null;
    }

    public function import()
    {
        $this->validate();

        // Safety Guard: Validasi keselarasan jika file memiliki periode yang terdeteksi
        if ($this->detectedMonth && $this->detectedYear) {
            if ($this->month !== $this->detectedMonth || $this->year !== $this->detectedYear) {
                $chosen = Carbon::create((int) $this->year, (int) $this->month, 1)->translatedFormat('F Y');
                $this->addError('file', "Peringatan: File Excel terdeteksi untuk periode {$this->detectedPeriod}, namun formulir memilih {$chosen}. Harap sesuaikan periode atau unggah file yang sesuai.");

                return;
            }
        }

        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        // Check if data already exists
        $exists = Jadwal::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function ($q) use ($opdId) {
                if ($opdId) {
                    $q->where('opd_id', $opdId);
                }
            })
            ->exists();

        if ($exists) {
            $this->showConfirmModal = true;

            return;
        }

        $this->executeImport(false);
    }

    public function confirmImport()
    {
        $this->executeImport(true);
    }

    protected function executeImport($shouldReset)
    {
        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        try {
            $path = $this->file->store('imports');
            $importId = (string) Str::uuid();

            // Set progress awal di Cache
            Cache::put("import_progress_{$importId}", [
                'total' => 0,
                'processed' => 0,
                'status' => 'processing',
                'percentage' => 0,
            ], 3600);

            Excel::queueImport(new JadwalImport($this->month, $this->year, $opdId, $shouldReset, $importId), $path);

            $this->importId = $importId;
            $this->progress = [
                'total' => 0,
                'processed' => 0,
                'status' => 'processing',
                'percentage' => 0,
            ];

            $this->showConfirmModal = false;
            $this->dispatch('toast', type: 'info', title: 'Impor Dimulai', message: 'Proses impor sedang berjalan di latar belakang.');
        } catch (Exception $e) {
            $this->showConfirmModal = false;
            $this->addError('file', 'Terjadi kesalahan saat mengimpor file: '.$e->getMessage());
        }
    }

    public function finishImport()
    {
        $this->dispatch('set-pending-toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Jadwal berhasil diimpor.',
        ]);
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Jadwal berhasil diimpor.');
        $this->reset(['file', 'importId', 'progress', 'detectedPeriod', 'detectedMonth', 'detectedYear', 'detectedDays', 'detectedSheets']);
        $this->showConfirmModal = false;

        return $this->redirectRoute('jadwal', ['month' => $this->month, 'year' => $this->year], navigate: true);
    }

    #[Renderless]
    public function getImportProgress()
    {
        if (! $this->importId) {
            return null;
        }

        $progress = Cache::get("import_progress_{$this->importId}");
        if ($progress) {
            $this->progress = $progress;

            return $progress;
        }

        return null;
    }

    public function resetImport()
    {
        $this->reset(['file', 'importId', 'progress', 'detectedPeriod', 'detectedMonth', 'detectedYear', 'detectedDays', 'detectedSheets']);
        $this->showConfirmModal = false;
    }
};
