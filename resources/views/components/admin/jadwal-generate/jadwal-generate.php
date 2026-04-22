<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
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
    public int $step = 1;
    
    // Step 1: OPD Selection
    public ?int $selectedOpdId = null;

    // Step 2: Personnel Selection
    public array $selectedPersonnelIds = [];
    public bool $selectAll = false;

    // Step 3: Shift Sequence
    // Each item: ['type' => 'SHIFT|LIBUR', 'shift_id' => null, 'duration' => 1]
    public array $shiftSequence = [
        ['type' => 'SHIFT', 'shift_id' => '', 'duration' => 1]
    ];

    // Step 4: Date Range
    public string $startDate;
    public string $endDate;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $this->selectedOpdId = $user->opd()?->id;
            $this->step = 2; // Skip Step 1 for non-super-admins
        }
        
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
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
        $this->shiftSequence[] = ['type' => 'SHIFT', 'shift_id' => '', 'duration' => 1];
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
        $personnelCount = count($this->selectedPersonnelIds);
        
        // Construct full cycle
        $fullCycle = [];
        foreach ($this->shiftSequence as $seq) {
            for ($i = 0; $i < $seq['duration']; $i++) {
                $fullCycle[] = [
                    'status' => $seq['type'],
                    'shift_id' => $seq['type'] === 'SHIFT' ? $seq['shift_id'] : null
                ];
            }
        }
        
        $cycleLength = count($fullCycle);
        if ($cycleLength === 0) {
            $this->dispatch('toast', type: 'error', message: 'Siklus shift tidak valid.');
            return;
        }

        // Process in groups of 2 as per user request example (staggered)
        // Group size 2, each group offset by 1 day
        foreach ($this->selectedPersonnelIds as $index => $pId) {
            $offset = (int) floor($index / 2);
            
            $dayCounter = 0;
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $cycleIndex = ($dayCounter + $offset) % $cycleLength;
                $config = $fullCycle[$cycleIndex];

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
