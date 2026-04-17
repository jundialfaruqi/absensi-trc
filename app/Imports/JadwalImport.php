<?php
 
namespace App\Imports;
 
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
 
class JadwalImport implements ToCollection
{
    protected $month;
    protected $year;
    protected $opdId;
    protected $shifts;
 
    public function __construct($month = null, $year = null, $opdId = null)
    {
        $this->month = $month ?: date('m');
        $this->year = $year ?: date('Y');
        $this->opdId = $opdId;
        
        // Load all shifts into a memory map
        // We'll store them with a "slugged" name for better matching
        $this->shifts = Shift::all()->mapWithKeys(function ($shift) {
            return [$this->slugify($shift->name) => $shift->id];
        });
    }
 
    public function collection(Collection $rows)
    {
        // Skip header row
        $dataRows = $rows->slice(1);
 
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
                    
                    $status = 'SHIFT';
                    $shiftId = $this->lookupShiftId($shiftValue);
 
                    // If no shift found, check if it represents a special status
                    if (!$shiftId) {
                        $upperVal = strtoupper($shiftValue);
                        if (in_array($upperVal, ['LIBUR', 'SAKIT', 'IZIN', 'CUTI'])) {
                            $status = $upperVal;
                        }
                    }
 
                    if ($shiftId || $status !== 'SHIFT') {
                        Jadwal::updateOrCreate(
                            [
                                'personnel_id' => $personnel->id,
                                'tanggal'      => $tanggal,
                            ],
                            [
                                'shift_id' => $status === 'SHIFT' ? $shiftId : null,
                                'status'   => $status,
                                // we don't handle keterangan from Excel yet as the template doesn't have it
                            ]
                        );
                    } else {
                        Log::warning("JadwalImport: Data '{$shiftValue}' tidak dikenali sebagai Shift atau Status (Personnel {$personnel->name}, Day {$day}).");
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
