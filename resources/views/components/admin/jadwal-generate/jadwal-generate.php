<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Personnel;
use App\Models\Opd;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

new #[Title('Generate Jadwal Otomatis')] #[Layout('layouts::admin.app')] class extends Component
{
    #[Url]
    public int $step = 1;
    
    // Step 1: OPD Selection
    #[Url]
    public ?int $selectedOpdId = null;

    // Step 2: Personnel Selection
    #[Url]
    public array $selectedPersonnelIds = [];
    public bool $selectAll = false;

    // Step 3: Shift Sequence
    // Each item: ['type' => 'SHIFT|LIBUR', 'shift_id' => null, 'duration' => 1, 'count' => 1]
    #[Url]
    public array $shiftSequence = [
        ['type' => 'SHIFT', 'shift_id' => '', 'duration' => 1, 'count' => 1]
    ];

    // Step 4: Date Range
    #[Url]
    public string $startDate;
    #[Url]
    public string $endDate;

    public function mount()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin');

        if (!$isSuperAdmin) {
            $this->selectedOpdId = $user->opd()?->id;
            if ($this->step === 1) {
                $this->step = 2;
            }
        }
        
        if (empty($this->startDate)) {
            $this->startDate = date('Y-m-01');
        }
        if (empty($this->endDate)) {
            $this->endDate = date('Y-m-t');
        }
    }

    #[Computed]
    public function opds()
    {
        return Opd::orderBy('name')->get();
    }

    #[Computed]
    public function personnels()
    {
        if (!$this->selectedOpdId) return collect();
        return Personnel::where('opd_id', $this->selectedOpdId)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function shifts()
    {
        return Shift::orderBy('name')->get();
    }

    public function nextStep()
    {
        if ($this->step == 1) {
            if (!$this->selectedOpdId) {
                $this->dispatch('toast', type: 'error', message: 'Silakan pilih OPD terlebih dahulu.');
                return;
            }
        }
        
        if ($this->step == 2) {
            if (empty($this->selectedPersonnelIds)) {
                $this->dispatch('toast', type: 'error', message: 'Silakan pilih minimal satu personel.');
                return;
            }
        }

        if ($this->step == 3) {
            foreach ($this->shiftSequence as $seq) {
                if ($seq['type'] === 'SHIFT' && empty($seq['shift_id'])) {
                    $this->dispatch('toast', type: 'error', message: 'Silakan pilih shift untuk semua entri SHIFT.');
                    return;
                }
                if ($seq['duration'] < 1) {
                    $this->dispatch('toast', type: 'error', message: 'Durasi minimal adalah 1 hari.');
                    return;
                }
                if (($seq['count'] ?? 1) < 1) {
                    $this->dispatch('toast', type: 'error', message: 'Jumlah personel minimal adalah 1.');
                    return;
                }
            }
        }

        $this->step++;
    }

    public function prevStep()
    {
        $user = Auth::user();
        if ($this->step == 2 && !$user->hasRole('super-admin')) {
            return; // Cannot go back to OPD selection if not super-admin
        }
        $this->step--;
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedPersonnelIds = $this->personnels->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedPersonnelIds = [];
        }
    }

    public function addSequence()
    {
        $this->shiftSequence[] = ['type' => 'SHIFT', 'shift_id' => '', 'duration' => 1, 'count' => 1];
    }

    public function removeSequence($index)
    {
        if (count($this->shiftSequence) > 1) {
            unset($this->shiftSequence[$index]);
            $this->shiftSequence = array_values($this->shiftSequence);
        }
    }

    public function generate()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $period = CarbonPeriod::create($this->startDate, $this->endDate);
        
        // 1. Construct the Daily Cycle Configuration
        // A cycle is a list of days, where each day has a specific shift type.
        $dailyCycle = [];
        foreach ($this->shiftSequence as $seq) {
            for ($d = 0; $d < ($seq['duration'] ?? 1); $d++) {
                $dailyCycle[] = [
                    'status' => $seq['type'],
                    'shift_id' => $seq['type'] === 'SHIFT' ? $seq['shift_id'] : null,
                    'count' => $seq['count'] ?? 1
                ];
            }
        }
        
        $cycleLength = count($dailyCycle);
        if ($cycleLength === 0) {
            $this->dispatch('toast', type: 'error', message: 'Siklus shift tidak valid.');
            return;
        }

        // 2. Map Personnel to Starting Day Offsets
        // We fill the slots of the cycle day-by-day.
        // Personnel 0 & 1 take slots for Day 0, Personnel 2 & 3 take Day 1, etc.
        $personnelDayOffsets = [];
        $slotCounter = 0;
        $totalPersonnel = count($this->selectedPersonnelIds);
        
        // Create a map of "Personnel Index -> Cycle Day Offset"
        // We iterate through the cycle and assign personnel to each day's slots
        $pIdx = 0;
        $tempCycleIndex = 0;
        while ($pIdx < $totalPersonnel) {
            $dayConfig = $dailyCycle[$tempCycleIndex % $cycleLength];
            for ($c = 0; $c < $dayConfig['count'] && $pIdx < $totalPersonnel; $c++) {
                $personnelDayOffsets[$pIdx] = $tempCycleIndex % $cycleLength;
                $pIdx++;
            }
            $tempCycleIndex++;
        }

        // 3. Generate Schedule
        foreach ($this->selectedPersonnelIds as $index => $pId) {
            $startOffset = $personnelDayOffsets[$index] ?? 0;
            
            $dayCounter = 0;
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // The current position in the cycle for this person
                $cycleIndex = ($dayCounter + $startOffset) % $cycleLength;
                $config = $dailyCycle[$cycleIndex];

                // Create/Update Jadwal
                $jadwal = Jadwal::updateOrCreate(
                    ['personnel_id' => $pId, 'tanggal' => $dateStr],
                    ['status' => $config['status'], 'shift_id' => $config['shift_id']]
                );

                // Create/Update Absensi Placeholder
                Absensi::updateOrCreate(
                    ['personnel_id' => $pId, 'tanggal' => $dateStr],
                    [
                        'jadwal_id' => $jadwal->id,
                        'status' => $config['status'] === 'LIBUR' ? 'LIBUR' : 'ALFA'
                    ]
                );

                $dayCounter++;
            }
        }

        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Jadwal otomatis berhasil digenerate.');
        return $this->redirectRoute('jadwal', navigate: true);
    }
};
