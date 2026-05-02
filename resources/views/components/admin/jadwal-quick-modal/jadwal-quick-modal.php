<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Jadwal;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    // Quick Add Properties
    public $quickPersonnelId;
    public string $quickDate = '';
    public $quickShiftId;
    public $quickStatus = 'SHIFT'; // 'SHIFT' or 'OFF'
    public $quickKeterangan = '';
    
    // Substitution Properties
    public string $swapTargetPersonnelId = '';
    public string $swapWarning = '';
    public $activeTab = 'quick'; // 'quick' or 'swap'

    protected $listeners = [
        'openQuickEdit' => 'open'
    ];

    public function open($personnelId, $date): void
    {
        $this->quickPersonnelId = $personnelId;
        $this->quickDate = $date;

        $jadwal = Jadwal::with('shift')->where('personnel_id', $personnelId)
            ->where('tanggal', $date)
            ->first();

        $this->quickShiftId = $jadwal ? $jadwal->shift_id : '';
        
        if ($jadwal) {
            if ($jadwal->shift) {
                $this->quickStatus = strtoupper($jadwal->shift->type); // 'SHIFT' or 'OFF'
            } else {
                $this->quickStatus = $jadwal->status === 'SHIFT' ? 'SHIFT' : 'OFF';
            }
        } else {
            $this->quickStatus = 'SHIFT';
        }
        
        $this->quickKeterangan = $jadwal ? $jadwal->keterangan : '';

        // Reset Swap
        $this->swapTargetPersonnelId = '';
        $this->activeTab = 'quick';
        $this->swapWarning = '';

        $this->dispatch('open-modal', id: 'quick-add-modal');
    }

    public function saveQuickJadwal(): void
    {
        $rules = [
            'quickStatus' => 'required|in:SHIFT,OFF',
            'quickKeterangan' => 'nullable|string',
            'quickShiftId' => 'required|exists:shifts,id',
        ];

        $this->validate($rules, [
            'quickShiftId.required' => 'Pilih shift/status terlebih dahulu.',
        ]);

        $personnel = Personnel::findOrFail($this->quickPersonnelId);
        if (!Auth::user()->hasRole('super-admin') && $personnel->opd_id !== Auth::user()->opd()?->id) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Anda tidak memiliki hak akses untuk personnel ini.');
            return;
        }

        $selectedShift = \App\Models\Shift::find($this->quickShiftId);

        $jadwal = Jadwal::updateOrCreate(
            ['personnel_id' => $this->quickPersonnelId, 'tanggal' => $this->quickDate],
            [
                'shift_id'   => $this->quickShiftId,
                'status'     => $this->quickStatus === 'OFF' ? ($selectedShift->keterangan ?? 'OFF') : 'SHIFT',
                'keterangan' => $this->quickKeterangan,
                'is_manual'  => false,
            ]
        );

        $absensi = \App\Models\Absensi::updateOrCreate(
            ['personnel_id' => $this->quickPersonnelId, 'tanggal' => $this->quickDate],
            [
                'jadwal_id' => $jadwal->id,
                'status' => $this->quickStatus === 'SHIFT' ? 'ALFA' : ($selectedShift->keterangan ?? 'OFF'),
                'status_masuk' => $this->quickStatus === 'SHIFT' ? 'ALFA' : ($selectedShift->keterangan ?? 'OFF'),
                'status_pulang' => $this->quickStatus === 'SHIFT' ? 'ALFA' : ($selectedShift->keterangan ?? 'OFF'),
            ]
        );

        $this->dispatch('close-modal', id: 'quick-add-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Jadwal berhasil disimpan.');
        $this->dispatch('refreshJadwal');
    }

    public function updatedQuickStatus(): void
    {
        $this->quickShiftId = null;
    }

    public function updatedActiveTab()
    {
        $this->swapWarning = '';
    }

    #[Computed]
    public function shifts()
    {
        $type = strtolower($this->quickStatus);
        return \App\Models\Shift::where('type', $type)->orderBy('name')->get();
    }

    #[Computed]
    public function originPersonnel()
    {
        if (!$this->quickPersonnelId) return null;
        return Personnel::find($this->quickPersonnelId);
    }

    #[Computed]
    public function originJadwal()
    {
        if (!$this->quickPersonnelId || !$this->quickDate) return null;
        return Jadwal::with('shift')
            ->where('personnel_id', $this->quickPersonnelId)
            ->whereDate('tanggal', $this->quickDate)
            ->first();
    }

    #[Computed]
    public function availableSubstitutes()
    {
        if (!$this->quickPersonnelId || !$this->quickDate) return collect();

        $originJadwal = Jadwal::where('personnel_id', $this->quickPersonnelId)->where('tanggal', $this->quickDate)->first();
        if (!$originJadwal || $originJadwal->status !== 'SHIFT' || !$originJadwal->shift) return collect();
        
        $targetShift = $originJadwal->shift;
        $isTargetNight = stripos($targetShift->name, 'malam') !== false || (Carbon::parse($targetShift->start_time)->hour >= 18 || Carbon::parse($targetShift->start_time)->hour < 4);
        $isTargetDay = !$isTargetNight;

        $user = Auth::user();
        $todayStr = $this->quickDate;
        $yesterday = Carbon::parse($todayStr)->subDay()->format('Y-m-d');
        $tomorrow = Carbon::parse($todayStr)->addDay()->format('Y-m-d');

        $candidates = Personnel::where('id', '!=', $this->quickPersonnelId)
            ->when(!$user->hasRole('super-admin'), function($q) use ($user) {
                $q->where('opd_id', $user->opd()?->id);
            })
            ->whereHas('jadwals', function($q) use ($todayStr) {
                $q->whereDate('tanggal', $todayStr)->where('status', 'LIBUR');
            })
            ->whereDoesntHave('jadwals', function($q) use ($todayStr) {
                $q->whereDate('tanggal', $todayStr)->where('is_manual', true);
            })
            ->get();

        return $candidates->filter(function($p) use ($yesterday, $tomorrow, $isTargetNight, $isTargetDay) {
            $jPrev = Jadwal::where('personnel_id', $p->id)->where('tanggal', $yesterday)->first();
            $jNext = Jadwal::where('personnel_id', $p->id)->where('tanggal', $tomorrow)->first();

            $isNight = function($j) {
                if (!$j || $j->status !== 'SHIFT' || !$j->shift) return false;
                $s = $j->shift;
                return stripos($s->name, 'malam') !== false || (Carbon::parse($s->start_time)->hour >= 18 || Carbon::parse($s->start_time)->hour < 4);
            };
            $isWork = fn($j) => $j && $j->status === 'SHIFT';

            if ($isTargetDay && $isNight($jPrev)) return false;
            if ($isTargetNight && $isWork($jNext) && !$isNight($jNext)) return false;
            if ($isTargetNight && $isWork($jPrev) && !$isNight($jPrev)) return false;

            return true;
        });
    }

    public function updatedSwapTargetPersonnelId()
    {
        $this->checkSwapCollision();
    }

    public function updatedQuickDate() { $this->checkSwapCollision(); }

    protected function checkSwapCollision()
    {
        $this->swapWarning = '';
        if (!$this->quickPersonnelId || !$this->swapTargetPersonnelId || !$this->quickDate) return;

        $target = Personnel::find($this->swapTargetPersonnelId);
        if (!$target) return;

        $warnings = [];
        $isNight = function($j) {
            if (!$j || $j->status !== 'SHIFT' || !$j->shift) return false;
            $s = $j->shift;
            return stripos($s->name, 'malam') !== false || (Carbon::parse($s->start_time)->hour >= 18 || Carbon::parse($s->start_time)->hour < 4);
        };
        $isWork = fn($j) => $j && $j->status === 'SHIFT';
        $getJadwal = fn($pId, $date) => Jadwal::where('personnel_id', $pId)->where('tanggal', $date)->first();

        $shiftA_Today = $getJadwal($this->quickPersonnelId, $this->quickDate);
        if ($isWork($shiftA_Today)) {
            $prevDayB = Carbon::parse($this->quickDate)->subDay()->format('Y-m-d');
            if ($isNight($getJadwal($this->swapTargetPersonnelId, $prevDayB))) {
                $warnings[] = "<b>{$target->name}</b> baru saja shift Malam kemarin ({$prevDayB}), terlalu lelah untuk masuk lagi hari ini.";
            }
            if ($isNight($shiftA_Today)) {
                $nextDayB = Carbon::parse($this->quickDate)->addDay()->format('Y-m-d');
                if ($isWork($getJadwal($this->swapTargetPersonnelId, $nextDayB))) {
                    $warnings[] = "<b>{$target->name}</b> akan shift Malam hari ini dan langsung kerja lagi besok ({$nextDayB}).";
                }
            }
        }

        if (!empty($warnings)) $this->swapWarning = implode("<br>", $warnings);
    }

    public function executeSwapGuling()
    {
        if (!$this->swapTargetPersonnelId) {
            $this->dispatch('toast', type: 'error', message: 'Silakan pilih personel pengganti.');
            return;
        }

        $jadwalA = Jadwal::where('personnel_id', $this->quickPersonnelId)->where('tanggal', $this->quickDate)->first();
        $targetJadwal = Jadwal::updateOrCreate(
            ['personnel_id' => $this->swapTargetPersonnelId, 'tanggal' => $this->quickDate],
            ['shift_id' => $jadwalA->shift_id, 'status' => 'SHIFT', 'is_manual' => true, 'keterangan' => 'Menggantikan ' . Personnel::find($this->quickPersonnelId)->name]
        );

        $jadwalA->update(['status' => 'LIBUR', 'shift_id' => null, 'is_manual' => true, 'keterangan' => 'Digantikan oleh ' . Personnel::find($this->swapTargetPersonnelId)->name]);

        \App\Models\Absensi::updateOrCreate(['personnel_id' => $this->swapTargetPersonnelId, 'tanggal' => $this->quickDate], ['jadwal_id' => $targetJadwal->id, 'status' => 'ALFA', 'status_masuk' => 'ALFA', 'status_pulang' => 'ALFA']);
        \App\Models\Absensi::updateOrCreate(['personnel_id' => $this->quickPersonnelId, 'tanggal' => $this->quickDate], ['jadwal_id' => $jadwalA->id, 'status' => 'LIBUR', 'status_masuk' => 'LIBUR', 'status_pulang' => 'LIBUR']);

        $this->dispatch('close-modal', id: 'quick-add-modal');
        $this->dispatch('toast', type: 'success', message: 'Proses tukar guling berhasil.');
        $this->dispatch('refreshJadwal');
    }
};
