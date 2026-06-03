<?php

namespace App\Imports;

use App\Events\ImportProgressUpdated;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use PhpOffice\PhpSpreadsheet\IOFactory;

class JadwalImport implements ShouldQueue, ToCollection, WithChunkReading, WithEvents, WithStartRow
{
    use Queueable;

    protected $month;

    protected $year;

    protected $opdId;

    protected $shifts;

    protected $shouldReset;

    protected $importId;

    public function __construct($month = null, $year = null, $opdId = null, $shouldReset = false, $importId = null)
    {
        $this->month = $month ?: date('m');
        $this->year = $year ?: date('Y');
        $this->opdId = $opdId;
        $this->shouldReset = $shouldReset;
        $this->importId = $importId;

        if ($this->shouldReset) {
            $this->resetExistingData();
        }

        // Load all shifts into a memory map
        $this->shifts = Shift::all()->mapWithKeys(function ($shift) {
            return [$this->slugify($shift->name) => $shift->id];
        });
    }

    protected function resetExistingData()
    {
        // Hapus permanen absensi default (belum terisi) agar tidak memenuhi kotak sampah
        $defaultAbsensis = Absensi::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function ($q) {
                if ($this->opdId) {
                    $q->where('opd_id', $this->opdId);
                }
            })
            ->whereNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->whereNull('foto_masuk')
            ->whereNull('foto_pulang')
            ->get();

        foreach ($defaultAbsensis as $absensi) {
            $absensi->forceDelete();
        }

        // Delete existing schedules (hanya jadwal yang absensinya default/sudah di-soft-delete)
        Jadwal::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function ($q) {
                if ($this->opdId) {
                    $q->where('opd_id', $this->opdId);
                }
            })
            ->whereDoesntHave('absensis', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('jam_masuk')
                        ->orWhereNotNull('jam_pulang')
                        ->orWhereNotNull('foto_masuk')
                        ->orWhereNotNull('foto_pulang');
                });
            })
            ->delete();
    }

    public function collection(Collection $rows)
    {
        $validRowsInChunk = 0;
        foreach ($rows as $row) {
            $rowData = $row instanceof Collection ? $row->toArray() : $row;
            $personnelId = $rowData[0] ?? null;
            $trimmedId = trim((string) $personnelId);

            if ($trimmedId === '') {
                continue;
            }

            if (stripos($trimmedId, 'DAFTAR REFERENSI') !== false) {
                break;
            }

            $validRowsInChunk++;
        }

        if ($this->importId && $validRowsInChunk > 0) {
            $processed = Cache::increment("import_processed_{$this->importId}", $validRowsInChunk);
            $total = Cache::get("import_total_{$this->importId}", 0);
            $percentage = $total > 0 ? min(99, round(($processed / $total) * 100)) : 0;

            $progress = [
                'total' => $total,
                'processed' => $processed,
                'status' => 'processing',
                'percentage' => $percentage,
            ];

            Cache::put("import_progress_{$this->importId}", $progress, 3600);
            ImportProgressUpdated::dispatch($this->importId, $progress);
        }

        foreach ($rows as $row) {
            // Convert row to array to ensure we can use numeric indices reliably
            $rowData = $row instanceof Collection ? $row->toArray() : $row;

            $personnelId = $rowData[0] ?? null; // ID Personnel is in first column
            $trimmedId = trim((string) $personnelId);

            if (is_null($personnelId) || $trimmedId === '') {
                continue;
            }

            if (stripos($trimmedId, 'DAFTAR REFERENSI') !== false) {
                break;
            }

            $personnel = Personnel::when($this->opdId, function ($q) {
                $q->where('opd_id', $this->opdId);
            })
                ->where('attendance_type', '!=', 'FLEXIBLE')
                ->find($personnelId);
            if (! $personnel) {
                Log::warning("JadwalImport: Personnel with ID {$personnelId} not found or unauthorized for current OPD context.");

                continue;
            }

            // Iterate through date columns starting from index 2 (Day 1)
            $dayCount = Carbon::create($this->year, $this->month, 1)->daysInMonth;

            for ($day = 1; $day <= $dayCount; $day++) {
                $colIndex = $day + 1; // Col 0=ID, 1=Name, 2=Day 1...
                $shiftValue = $rowData[$colIndex] ?? null;

                // Trimming and checking if it's actually filled
                $shiftValue = trim((string) $shiftValue);

                if ($shiftValue !== '') {
                    $tanggal = Carbon::create($this->year, $this->month, $day)->format('Y-m-d');

                    $shiftId = $this->lookupShiftId($shiftValue);
                    $sObj = $shiftId ? Shift::find($shiftId) : null;

                    if ($sObj) {
                        $status = $sObj->type === 'off' ? ($sObj->keterangan ?? 'OFF') : 'SHIFT';
                        $absensiStatus = $sObj->type === 'off' ? ($sObj->keterangan ?? 'OFF') : 'ALPA';

                        // Skip jika absensi sudah terisi (bukan default)
                        $existingAbsensi = Absensi::where('personnel_id', $personnel->id)->where('tanggal', $tanggal)->first();
                        if ($existingAbsensi && ($existingAbsensi->jam_masuk || $existingAbsensi->jam_pulang || $existingAbsensi->foto_masuk || $existingAbsensi->foto_pulang)) {
                            continue;
                        }

                        $jadwal = Jadwal::updateOrCreate(
                            [
                                'personnel_id' => $personnel->id,
                                'tanggal' => $tanggal,
                            ],
                            [
                                'shift_id' => $shiftId,
                                'status' => $status,
                                'is_manual' => false,
                            ]
                        );

                        // CREATE/UPDATE ABSENSI
                        Absensi::updateOrCreate(
                            [
                                'personnel_id' => $personnel->id,
                                'tanggal' => $tanggal,
                            ],
                            [
                                'jadwal_id' => $jadwal->id,
                                'status' => $absensiStatus,
                                'status_masuk' => $absensiStatus,
                                'status_pulang' => $absensiStatus,
                            ]
                        );
                    } else {
                        // Fallback logic for literal "LIBUR" if no shift matches
                        if (strtoupper($shiftValue) === 'LIBUR') {
                            // Skip jika absensi sudah terisi (bukan default)
                            $existingAbsensi = Absensi::where('personnel_id', $personnel->id)->where('tanggal', $tanggal)->first();
                            if ($existingAbsensi && ($existingAbsensi->jam_masuk || $existingAbsensi->jam_pulang || $existingAbsensi->foto_masuk || $existingAbsensi->foto_pulang)) {
                                continue;
                            }

                            $jadwal = Jadwal::updateOrCreate(
                                [
                                    'personnel_id' => $personnel->id,
                                    'tanggal' => $tanggal,
                                ],
                                [
                                    'shift_id' => null,
                                    'status' => 'LIBUR',
                                    'is_manual' => false,
                                ]
                            );

                            Absensi::updateOrCreate(
                                [
                                    'personnel_id' => $personnel->id,
                                    'tanggal' => $tanggal,
                                ],
                                [
                                    'jadwal_id' => $jadwal->id,
                                    'status' => 'LIBUR',
                                    'status_masuk' => 'LIBUR',
                                    'status_pulang' => 'LIBUR',
                                ]
                            );
                        } else {
                            Log::warning("JadwalImport: Data '{$shiftValue}' tidak dikenali sebagai Shift atau Status (Personnel {$personnel->name}, Day {$day}).");
                        }
                    }
                }
            }
        }
    }

    protected function lookupShiftId($value)
    {
        // 1. Try direct ID first if it's numeric
        if (is_numeric($value)) {
            $shift = Shift::find($value);
            if ($shift) {
                return $shift->id;
            }
        }

        // 2. Try exact slug match (e.g. "pagi" -> "pagi", "shift pagi" -> "shiftpagi")
        $slug = $this->slugify($value);
        if ($this->shifts->has($slug)) {
            return $this->shifts->get($slug);
        }

        // 3. Try partial match (e.g. user typed "pagi", database has "shiftpagi")
        foreach ($this->shifts as $shiftSlug => $id) {
            if (str_contains($shiftSlug, $slug) || str_contains($slug, $shiftSlug)) {
                return $id;
            }
        }

        return null;
    }

    protected function slugify($text)
    {
        // Simple slugify: lowercase and remove all non-alphanumeric
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($text)));
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function startRow(): int
    {
        return 7;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                if ($this->importId) {
                    $filePath = (fn () => $this->currentFile->getLocalPath())->call($event->getReader());
                    $spreadsheet = IOFactory::load($filePath);
                    $totalDataRows = 0;
                    foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
                        $sheet = $spreadsheet->getSheet($sheetIndex);
                        $highestRow = $sheet->getHighestRow();
                        for ($row = 7; $row <= $highestRow; $row++) {
                            $cellValue = trim((string) $sheet->getCell("A$row")->getValue());
                            if (stripos($cellValue, 'DAFTAR REFERENSI') !== false) {
                                break;
                            }
                            if ($cellValue !== '') {
                                $totalDataRows++;
                            }
                        }
                    }

                    Cache::put("import_total_{$this->importId}", $totalDataRows, 3600);
                    Cache::put("import_processed_{$this->importId}", 0, 3600);

                    $progress = [
                        'total' => $totalDataRows,
                        'processed' => 0,
                        'status' => 'processing',
                        'percentage' => 0,
                    ];
                    Cache::put("import_progress_{$this->importId}", $progress, 3600);
                    ImportProgressUpdated::dispatch($this->importId, $progress);
                }
            },
            AfterImport::class => function (AfterImport $event) {
                if ($this->importId) {
                    $total = Cache::get("import_total_{$this->importId}", 0);
                    $progress = [
                        'total' => $total,
                        'processed' => $total,
                        'status' => 'completed',
                        'percentage' => 100,
                    ];
                    Cache::put("import_progress_{$this->importId}", $progress, 3600);
                    ImportProgressUpdated::dispatch($this->importId, $progress);
                }
            },
            ImportFailed::class => function (ImportFailed $event) {
                if ($this->importId) {
                    $progress = [
                        'total' => 0,
                        'processed' => 0,
                        'status' => 'failed',
                        'percentage' => 0,
                        'error' => $event->getException()->getMessage(),
                    ];
                    Cache::put("import_progress_{$this->importId}", $progress, 3600);
                    ImportProgressUpdated::dispatch($this->importId, $progress);
                }
            },
        ];
    }
}
