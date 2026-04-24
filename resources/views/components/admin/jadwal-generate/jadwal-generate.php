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
    public int $peoplePerRegu = 2;
    public bool $useRegu = false;

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

    // Template Management
    public ?int $selectedTemplateId = null;
    public bool $saveAsTemplate = false;
    public string $templateName = '';

    // Generate Mode: 'cycle' (Rolling), 'weekly' (Fixed), or 'quota' (Smart)
    #[Url]
    public string $generateMode = 'cycle';

    // Step 3 Weekly: [dayIndex => ['type' => 'SHIFT|LIBUR', 'shift_id' => '']]
    public array $weeklyConfig = [];

    // Step 3 Quota: [shift_id => headcount]
    public array $quotaConfig = [];

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

        // Initialize Weekly Config if empty
        if (empty($this->weeklyConfig)) {
            for ($i = 0; $i < 7; $i++) {
                $this->weeklyConfig[$i] = ['type' => 'SHIFT', 'shift_id' => ''];
            }
        }

        // Initialize Quota Config
        if (empty($this->quotaConfig)) {
            foreach ($this->shifts as $s) {
                $this->quotaConfig[$s->id] = 1; // Default 1 person per shift
            }
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

    #[Computed]
    public function templates()
    {
        return \App\Models\ShiftCycleTemplate::where('opd_id', $this->selectedOpdId)
            ->orWhereNull('opd_id')
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedTemplateId($value)
    {
        if ($value) {
            $template = \App\Models\ShiftCycleTemplate::find($value);
            if ($template) {
                $this->generateMode = $template->mode ?? 'cycle';
                if ($this->generateMode === 'cycle') {
                    $this->shiftSequence = $template->sequence;
                } elseif ($this->generateMode === 'weekly') {
                    $this->weeklyConfig = $template->sequence;
                } else {
                    $this->quotaConfig = $template->sequence;
                }
                $this->dispatch('toast', type: 'success', message: 'Template "' . $template->name . '" berhasil dimuat.');
            }
        }
    }

    public function saveCurrentAsTemplate()
    {
        $this->validate([
            'templateName' => 'required|min:3|max:50',
        ]);

        $sequence = $this->shiftSequence;
        if ($this->generateMode === 'weekly') $sequence = $this->weeklyConfig;
        if ($this->generateMode === 'quota') $sequence = $this->quotaConfig;

        \App\Models\ShiftCycleTemplate::create([
            'name' => $this->templateName,
            'opd_id' => $this->selectedOpdId,
            'mode' => $this->generateMode,
            'sequence' => $sequence
        ]);

        $this->templateName = '';
        $this->saveAsTemplate = false;
        $this->dispatch('toast', type: 'success', message: 'Template berhasil disimpan.');
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
            if ($this->generateMode === 'cycle') {
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
            } elseif ($this->generateMode === 'weekly') {
                foreach ($this->weeklyConfig as $day => $config) {
                    if ($config['type'] === 'SHIFT' && empty($config['shift_id'])) {
                        $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$day];
                        $this->dispatch('toast', type: 'error', message: "Silakan pilih shift untuk hari $dayName.");
                        return;
                    }
                }
            }
        }

        if ($this->step == 3 && $this->generateMode === 'quota') {
            $totalNeeded = array_sum($this->quotaConfig);
            if ($totalNeeded > count($this->selectedPersonnelIds)) {
                $this->dispatch('toast', type: 'error', message: "Kebutuhan personel ($totalNeeded) melebihi jumlah personel yang dipilih (" . count($this->selectedPersonnelIds) . ").");
                return;
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

        // Logic for Rolling Cycle
        if ($this->generateMode === 'cycle') {
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

            // Map Personnel to Starting Day Offsets
            foreach ($this->selectedPersonnelIds as $index => $pId) {
                if ($this->useRegu) {
                    $reguIndex = (int) floor($index / max(1, $this->peoplePerRegu));
                    $reguName = 'Regu ' . ($reguIndex + 1);
                    Personnel::where('id', $pId)->update(['regu' => $reguName]);
                    $startOffset = $reguIndex % $cycleLength;
                } else {
                    Personnel::where('id', $pId)->update(['regu' => null]);
                    $startOffset = $index % $cycleLength;
                }

                $dayCounter = 0;
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $cycleIndex = ($dayCounter + $startOffset) % $cycleLength;
                    $config = $dailyCycle[$cycleIndex];

                    $jadwal = Jadwal::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $dateStr],
                        [
                            'status' => $config['status'], 
                            'shift_id' => $config['shift_id'],
                            'is_manual' => false,
                            'keterangan' => null
                        ]
                    );

                    Absensi::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $dateStr],
                        [
                            'jadwal_id' => $jadwal->id,
                            'status' => $config['status'] === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_masuk' => $config['status'] === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_pulang' => $config['status'] === 'LIBUR' ? 'LIBUR' : 'ALFA',
                        ]
                    );

                    $dayCounter++;
                }
            }
        } 
        // Logic for Fixed Weekly
        elseif ($this->generateMode === 'weekly') {
            foreach ($this->selectedPersonnelIds as $pId) {
                // In Weekly mode, we clear regu by default as they all follow the same pattern
                Personnel::where('id', $pId)->update(['regu' => null]);

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)
                    $config = $this->weeklyConfig[$dayOfWeek];

                    $jadwal = Jadwal::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $dateStr],
                        [
                            'status' => $config['type'], 
                            'shift_id' => $config['type'] === 'SHIFT' ? $config['shift_id'] : null,
                            'is_manual' => false,
                            'keterangan' => null
                        ]
                    );

                    Absensi::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $dateStr],
                        [
                            'jadwal_id' => $jadwal->id,
                            'status' => $config['type'] === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_masuk' => $config['type'] === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_pulang' => $config['type'] === 'LIBUR' ? 'LIBUR' : 'ALFA',
                        ]
                    );
                }
            }
        } 
        // Logic for Smart Quota (Fairness + Weekend Balance)
        else {
            $pIds = $this->selectedPersonnelIds;
            $stats = [];
            foreach ($pIds as $pId) {
                $stats[$pId] = [
                    'work_days' => 0,
                    'weekend_offs' => 0,
                    'consecutive_work' => 0,
                    'last_shift_id' => null,
                    'last_status' => null
                ];
            }

            // Get Night Shift IDs for recovery rule
            $nightShiftIds = $this->shifts->filter(fn($s) => 
                stripos($s->name, 'malam') !== false || 
                (Carbon::parse($s->start_time)->hour >= 18 || Carbon::parse($s->start_time)->hour < 4)
            )->pluck('id')->toArray();

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $isWeekend = in_array($date->dayOfWeek, [0, 6]); // 0=Sun, 6=Sat

                // 1. Calculate Eligibility & Scores
                $pool = [];
                foreach ($pIds as $pId) {
                    $s = &$stats[$pId];
                    $eligible = true;
                    $score = $s['work_days'] * 10; // Primary score: total work days

                    // Rule: Recovery from Night Shift (No work today if last shift was night)
                    if (in_array($s['last_shift_id'], $nightShiftIds) && $s['last_status'] === 'SHIFT') {
                        $eligible = false;
                    }

                    // Rule: Max 6 days consecutive work
                    if ($s['consecutive_work'] >= 6) {
                        $eligible = false;
                    }

                    // Weekend Fairness adjustment
                    if ($isWeekend) {
                        // People with FEWER weekend offs so far get a HUGE penalty to their work score
                        // effectively pushing them to the bottom of the "work" list so they are chosen for LIBUR
                        $score += (10 - $s['weekend_offs']) * 100;
                    }

                    $pool[] = [
                        'id' => $pId,
                        'eligible' => $eligible,
                        'score' => $score + (rand(0, 9) / 10) // Small random for tie-breaking
                    ];
                }

                // 2. Sort pool by score (Ascending: low score = prioritize for WORK)
                // Filter eligible first
                $eligiblePool = array_filter($pool, fn($p) => $p['eligible']);
                usort($eligiblePool, fn($a, $b) => $a['score'] <=> $b['score']);
                $eligiblePids = array_column($eligiblePool, 'id');

                // 3. Fill Shifts
                $assignedToday = [];
                foreach ($this->quotaConfig as $shiftId => $count) {
                    for ($i = 0; $i < $count; $i++) {
                        if (!empty($eligiblePids)) {
                            $pId = array_shift($eligiblePids);
                            $assignedToday[$pId] = $shiftId;
                        }
                    }
                }

                // 4. Update Database & Stats
                foreach ($pIds as $pId) {
                    $s = &$stats[$pId];
                    $status = isset($assignedToday[$pId]) ? 'SHIFT' : 'LIBUR';
                    $shiftId = $assignedToday[$pId] ?? null;

                    $jadwal = Jadwal::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $dateStr],
                        ['status' => $status, 'shift_id' => $shiftId, 'is_manual' => false, 'keterangan' => null]
                    );

                    Absensi::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $dateStr],
                        [
                            'jadwal_id' => $jadwal->id,
                            'status' => $status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_masuk' => $status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_pulang' => $status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                        ]
                    );

                    // Update local stats for next day
                    if ($status === 'SHIFT') {
                        $s['work_days']++;
                        $s['consecutive_work']++;
                    } else {
                        $s['consecutive_work'] = 0;
                        if ($isWeekend) $s['weekend_offs']++;
                    }
                    $s['last_shift_id'] = $shiftId;
                    $s['last_status'] = $status;
                }
            }
            
            // Clear regu in Quota mode
            Personnel::whereIn('id', $pIds)->update(['regu' => null]);
        }

        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Jadwal otomatis berhasil digenerate.');
        return $this->redirectRoute('jadwal', navigate: true);
    }
};
