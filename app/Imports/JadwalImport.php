<?php
 
namespace App\Imports;
 
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
 
use Illuminate\Support\Facades\Storage;
 
class JadwalImport implements ToCollection
{
    protected $month;
    protected $year;
    protected $opdId;
    protected $shifts;
    protected $shouldReset;
 
    public function __construct($month = null, $year = null, $opdId = null, $shouldReset = false)
    {
        $this->month = $month ?: date('m');
        $this->year = $year ?: date('Y');
        $this->opdId = $opdId;
        $this->shouldReset = $shouldReset;
        
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
        // 1. Get all attendance records to delete photos
        $absensis = \App\Models\Absensi::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function($q) {
                if ($this->opdId) {
                    $q->where('opd_id', $this->opdId);
                }
            })
            ->get();

        foreach ($absensis as $absensi) {
            if ($absensi->foto_masuk) {
                Storage::disk('public')->delete($absensi->foto_masuk);
            }
            if ($absensi->foto_pulang) {
                Storage::disk('public')->delete($absensi->foto_pulang);
            }
            $absensi->delete();
        }

        // 2. Delete schedules
        \App\Models\Jadwal::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function($q) {
                if ($this->opdId) {
                    $q->where('opd_id', $this->opdId);
                }
            })
            ->delete();
    }
 
    public function collection(Collection $rows)
    {
        // Skip branding, instructions, and double header rows (Rows 1-6)
        $dataRows = $rows->slice(6);
 
        foreach ($dataRows as $row) {
            // Convert row to array to ensure we can use numeric indices reliably
            $rowData = $row instanceof Collection ? $row->toArray() : $row;
            
            $personnelId = $rowData[0] ?? null; // ID Personnel is in first column
            if (is_null($personnelId) || trim($personnelId) === '') continue;
 
            $personnel = Personnel::when($this->opdId, function($q) {
                    $q->where('opd_id', $this->opdId);
                })
                ->find($personnelId);
            if (!$personnel) {
                Log::warning("JadwalImport: Personnel with ID {$personnelId} not found or unauthorized for current OPD context.");
                continue;
            }
 
            // Iterate through date columns starting from index 2 (Day 1)
            $dayCount = Carbon::create($this->year, $this->month, 1)->daysInMonth;
            
            for ($day = 1; $day <= $dayCount; $day++) {
                $colIndex = $day + 1; // Col 0=ID, 1=Name, 2=Day 1...
                $shiftValue = $rowData[$colIndex] ?? null;
                
                // Trimming and checking if it's actually filled
                $shiftValue = trim((string)$shiftValue);
 
                if ($shiftValue !== '') {
                    $tanggal = Carbon::create($this->year, $this->month, $day)->format('Y-m-d');
                    
                    $shiftId = $this->lookupShiftId($shiftValue);
                    $sObj = $shiftId ? Shift::find($shiftId) : null;

                    if ($sObj) {
                        $status = $sObj->type === 'off' ? ($sObj->keterangan ?? 'OFF') : 'SHIFT';
                        $absensiStatus = $sObj->type === 'off' ? ($sObj->keterangan ?? 'OFF') : 'ALFA';

                        $jadwal = Jadwal::updateOrCreate(
                            [
                                'personnel_id' => $personnel->id,
                                'tanggal'      => $tanggal,
                            ],
                            [
                                'shift_id' => $shiftId,
                                'status'   => $status,
                                'is_manual' => false,
                            ]
                        );

                        // CREATE/UPDATE ABSENSI
                        \App\Models\Absensi::updateOrCreate(
                            [
                                'personnel_id' => $personnel->id,
                                'tanggal'      => $tanggal,
                            ],
                            [
                                'jadwal_id' => $jadwal->id,
                                'status'    => $absensiStatus,
                                'status_masuk' => $absensiStatus,
                                'status_pulang' => $absensiStatus,
                            ]
                        );
                    } else {
                        // Fallback logic for literal "LIBUR" if no shift matches
                        if (strtoupper($shiftValue) === 'LIBUR') {
                            $jadwal = Jadwal::updateOrCreate(
                                [
                                    'personnel_id' => $personnel->id,
                                    'tanggal'      => $tanggal,
                                ],
                                [
                                    'shift_id' => null,
                                    'status'   => 'LIBUR',
                                    'is_manual' => false,
                                ]
                            );

                            \App\Models\Absensi::updateOrCreate(
                                [
                                    'personnel_id' => $personnel->id,
                                    'tanggal'      => $tanggal,
                                ],
                                [
                                    'jadwal_id' => $jadwal->id,
                                    'status'    => 'LIBUR',
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
            if ($shift) return $shift->id;
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
}
