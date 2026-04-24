<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new #[Title('Manajemen Jadwal')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;
    public int $perPage = 10;
    
    #[Url]
    public string $search = '';
    
    #[Url]
    public string $month = '';
    
    #[Url]
    public string $year = '';

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    public ?int $deleteId = null;

    // Quick Add Properties
    public $quickPersonnelId;
    public string $quickDate = '';
    public $quickShiftId;
    public $quickStatus = 'SHIFT';
    public $quickKeterangan = '';
    
    // Substitution Properties (Simplified)
    public string $swapTargetPersonnelId = '';
    public string $swapWarning = '';
    public $activeTab = 'quick'; // 'quick' or 'swap'

    public function mount(): void
    {
        if (!$this->month) $this->month = Carbon::now()->format('m');
        if (!$this->year) $this->year = Carbon::now()->format('Y');
    }

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function shifts()
    {
        return \App\Models\Shift::orderBy('name')->get();
    }

    public function openQuickAdd($personnelId, $date): void
    {
        $this->quickPersonnelId = $personnelId;
        $this->quickDate = $date;

        // Find existing jadwal for this date if any
        $jadwal = Jadwal::where('personnel_id', $personnelId)
            ->where('tanggal', $date)
            ->first();

        $this->quickShiftId = $jadwal ? $jadwal->shift_id : '';
        $this->quickStatus = $jadwal ? $jadwal->status : 'SHIFT';
        $this->quickKeterangan = $jadwal ? $jadwal->keterangan : '';

        // Reset Swap
        $this->swapTargetPersonnelId = '';
        $this->paybackOptions = [];
        $this->selectedPaybackDate = '';
        $this->activeTab = 'quick';
        $this->swapWarning = ''; // Reset warning

        $this->dispatch('open-modal', id: 'quick-add-modal');
    }

    public function saveQuickJadwal(): void
    {
        $rules = [
            'quickStatus' => 'required|string',
            'quickKeterangan' => 'nullable|string',
        ];

        if ($this->quickStatus === 'SHIFT') {
            $rules['quickShiftId'] = 'required|exists:shifts,id';
        } else {
            $rules['quickShiftId'] = 'nullable';
        }

        $this->validate($rules, [
            'quickShiftId.required' => 'Pilih shift terlebih dahulu.',
        ]);

        // Authorization Check
        $personnel = \App\Models\Personnel::findOrFail($this->quickPersonnelId);
        if (!Auth::user()->hasRole('super-admin') && $personnel->opd_id !== Auth::user()->opd()?->id) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Anda tidak memiliki hak akses untuk personnel ini.');
            return;
        }

        $jadwal = Jadwal::updateOrCreate(
            [
                'personnel_id' => $this->quickPersonnelId,
                'tanggal'      => $this->quickDate,
            ],
            [
                'shift_id'   => $this->quickStatus === 'SHIFT' ? $this->quickShiftId : null,
                'status'     => $this->quickStatus,
                'keterangan' => $this->quickKeterangan,
                'is_manual'  => false,
            ]
        );

        // Also create/update the Absensi record as a placeholder
        // Only if it doesn't have actual clock-in data yet, OR we want to force status (like LIBUR)
        $absensi = \App\Models\Absensi::where('personnel_id', $this->quickPersonnelId)
            ->where('tanggal', $this->quickDate)
            ->first();

        if (!$absensi || in_array($absensi->status, ['ALFA', 'LIBUR'])) {
             \App\Models\Absensi::updateOrCreate(
                [
                    'personnel_id' => $this->quickPersonnelId,
                    'tanggal'      => $this->quickDate,
                ],
                [
                    'jadwal_id' => $jadwal->id,
                    'status' => $this->quickStatus === 'LIBUR' ? 'LIBUR' : 'ALFA',
                    'status_masuk' => $this->quickStatus === 'LIBUR' ? 'LIBUR' : 'ALFA',
                    'status_pulang' => $this->quickStatus === 'LIBUR' ? 'LIBUR' : 'ALFA',
                ]
            );
        } else {
            // Just update the jadwal_id link if it already has clock-in data
            $absensi->update(['jadwal_id' => $jadwal->id]);
        }

        $this->dispatch('close-modal', id: 'quick-add-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Jadwal berhasil disimpan.');
    }

    public function updatedActiveTab()
    {
        $this->swapWarning = '';
    }


    #[Computed]
    public function originPersonnel()
    {
        if (!$this->quickPersonnelId) return null;
        return \App\Models\Personnel::find($this->quickPersonnelId);
    }

    #[Computed]
    public function originJadwal()
    {
        if (!$this->quickPersonnelId || !$this->quickDate) return null;
        return \App\Models\Jadwal::with('shift')
            ->where('personnel_id', $this->quickPersonnelId)
            ->whereDate('tanggal', $this->quickDate)
            ->first();
    }

    #[Computed]
    public function availableSubstitutes()
    {
        if (!$this->quickPersonnelId || !$this->quickDate) return collect();

        $originPersonnel = \App\Models\Personnel::find($this->quickPersonnelId);
        $originJadwal = Jadwal::where('personnel_id', $this->quickPersonnelId)->where('tanggal', $this->quickDate)->first();
        
        if (!$originJadwal || $originJadwal->status !== 'SHIFT' || !$originJadwal->shift) return collect();
        
        $targetShift = $originJadwal->shift;
        $isTargetNight = stripos($targetShift->name, 'malam') !== false || (\Carbon\Carbon::parse($targetShift->start_time)->hour >= 18 || \Carbon\Carbon::parse($targetShift->start_time)->hour < 4);
        $isTargetDay = !$isTargetNight;

        $user = Auth::user();
        $todayStr = $this->quickDate;
        $yesterday = Carbon::parse($todayStr)->subDay()->format('Y-m-d');
        $tomorrow = Carbon::parse($todayStr)->addDay()->format('Y-m-d');

        $candidates = \App\Models\Personnel::where('id', '!=', $this->quickPersonnelId)
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

        // Secondary filtering for rest safety (Malam <-> Siang)
        return $candidates->filter(function($p) use ($yesterday, $tomorrow, $isTargetNight, $isTargetDay) {
            $jPrev = Jadwal::where('personnel_id', $p->id)->where('tanggal', $yesterday)->first();
            $jNext = Jadwal::where('personnel_id', $p->id)->where('tanggal', $tomorrow)->first();

            $isNight = function($j) {
                if (!$j || $j->status !== 'SHIFT' || !$j->shift) return false;
                $s = $j->shift;
                return stripos($s->name, 'malam') !== false || (\Carbon\Carbon::parse($s->start_time)->hour >= 18 || \Carbon\Carbon::parse($s->start_time)->hour < 4);
            };
            $isWork = fn($j) => $j && $j->status === 'SHIFT';

            // Rule: Malam -> Siang TIDAK BOLEH
            // 1. Jika Target Shift adalah SIANG, cek apakah kemarin calon ini MALAM
            if ($isTargetDay && $isNight($jPrev)) return false;

            // 2. Jika Target Shift adalah MALAM, cek apakah besok calon ini SIANG/KERJA
            if ($isTargetNight && $isWork($jNext) && !$isNight($jNext)) return false;

            // 3. Tambahan: Siang -> Malam TIDAK BOLEH
            // Jika kemarin calon ini SIANG, dan hari ini dia ambil MALAM
            if ($isTargetNight && $isWork($jPrev) && !$isNight($jPrev)) return false;

            return true;
        });
    }

    public function updatedSwapTargetPersonnelId()
    {
        $this->checkSwapCollision();
    }

    public function updatedQuickDate() { $this->checkSwapCollision(); }
    public function updatedSelectedPaybackDate() { $this->checkSwapCollision(); }

    protected function checkSwapCollision()
    {
        $this->swapWarning = '';
        if (!$this->quickPersonnelId || !$this->swapTargetPersonnelId || !$this->quickDate) return;

        $target = \App\Models\Personnel::find($this->swapTargetPersonnelId);
        if (!$target) return;

        $warnings = [];

        // Helper functions
        $isNight = function($j) {
            if (!$j || $j->status !== 'SHIFT' || !$j->shift) return false;
            $s = $j->shift;
            return stripos($s->name, 'malam') !== false || (\Carbon\Carbon::parse($s->start_time)->hour >= 18 || \Carbon\Carbon::parse($s->start_time)->hour < 4);
        };
        $isWork = fn($j) => $j && $j->status === 'SHIFT';
        $getJadwal = fn($pId, $date) => Jadwal::where('personnel_id', $pId)->where('tanggal', $date)->first();

        // ANALISIS UNTUK PENGGANTI (TARGET) - Akan mengambil shift A di hari ini
        $shiftA_Today = $getJadwal($this->quickPersonnelId, $this->quickDate);
        if ($isWork($shiftA_Today)) {
            // Cek apakah H-1 (sebelum tukar) adalah Malam bagi si Pengganti
            $prevDayB = Carbon::parse($this->quickDate)->subDay()->format('Y-m-d');
            if ($isNight($getJadwal($this->swapTargetPersonnelId, $prevDayB))) {
                $warnings[] = "<b>{$target->name}</b> baru saja shift Malam kemarin ({$prevDayB}), terlalu lelah untuk masuk lagi hari ini.";
            }
            // Cek apakah hari ini (tukar) adalah Malam dan besok dia harus masuk
            if ($isNight($shiftA_Today)) {
                $nextDayB = Carbon::parse($this->quickDate)->addDay()->format('Y-m-d');
                if ($isWork($getJadwal($this->swapTargetPersonnelId, $nextDayB))) {
                    $warnings[] = "<b>{$target->name}</b> akan shift Malam hari ini dan langsung kerja lagi besok ({$nextDayB}).";
                }
            }
        }

        if (!empty($warnings)) {
            $this->swapWarning = implode("<br>", $warnings);
        }
    }

    public function executeSwapGuling()
    {
        if (!$this->swapTargetPersonnelId) {
            $this->dispatch('toast', type: 'error', message: 'Silakan pilih personel pengganti.');
            return;
        }

        $originPersonnel = \App\Models\Personnel::find($this->quickPersonnelId);
        $targetPersonnel = \App\Models\Personnel::find($this->swapTargetPersonnelId);

        // 1. Ambil Jadwal Asli si Pemohon
        $jadwalA = Jadwal::where('personnel_id', $this->quickPersonnelId)->where('tanggal', $this->quickDate)->first();
        
        // 2. Ambil (atau buat) Jadwal si Pengganti
        $jadwalC = Jadwal::where('personnel_id', $this->swapTargetPersonnelId)->where('tanggal', $this->quickDate)->first();

        if (!$jadwalA || $jadwalA->status !== 'SHIFT') {
            $this->dispatch('toast', type: 'error', message: 'Hanya personel yang sedang bertugas yang bisa digantikan.');
            return;
        }

        // Simpan data shift asli
        $originalShiftId = $jadwalA->shift_id;

        // A. Proses Pemohon: Jadi LIBUR (Izin/Digantikan)
        $jadwalA->update([
            'status' => 'LIBUR',
            'shift_id' => null,
            'is_manual' => true,
            'keterangan' => 'Digantikan oleh ' . $targetPersonnel->name
        ]);

        // B. Proses Pengganti: Jadi SHIFT (Mengambil tugas A)
        $jadwalC = Jadwal::updateOrCreate(
            ['personnel_id' => $this->swapTargetPersonnelId, 'tanggal' => $this->quickDate],
            [
                'status' => 'SHIFT',
                'shift_id' => $originalShiftId,
                'is_manual' => true,
                'keterangan' => 'Substitusi: Menggantikan ' . $originPersonnel->name
            ]
        );

        // Sync Absensi records
        foreach ([$this->quickPersonnelId, $this->swapTargetPersonnelId] as $pId) {
            $j = Jadwal::where('personnel_id', $pId)->where('tanggal', $this->quickDate)->first();
            if ($j) {
                \App\Models\Absensi::updateOrCreate(
                    ['personnel_id' => $pId, 'tanggal' => $this->quickDate],
                    [
                        'jadwal_id' => $j->id,
                        'status' => $j->status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                        'status_masuk' => $j->status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                        'status_pulang' => $j->status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                    ]
                );
            }
        }

        $this->swapWarning = '';
        $this->dispatch('close-modal', id: 'quick-add-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Substitusi personel berhasil diproses.');
    }

    #[Computed]
    public function dates(): array
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);

            // Safety cap: max 31 days
            if ($start->diffInDays($end) > 31) {
                $end = $start->copy()->addDays(31);
            }

            $dates = [];
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                $start->addDay();
            }
            return $dates;
        }

        if ($this->startDate) {
            return [$this->startDate];
        }

        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = Carbon::create($this->year, $this->month, $i)->format('Y-m-d');
        }
        return $dates;
    }

    #[Computed]
    public function personnels()
    {
        if (!$this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        $opdId = Auth::user()->opd()?->id;

        $paginator = \App\Models\Personnel::with(['jadwals' => function ($query) {
                if ($this->startDate && $this->endDate) {
                    $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
                } elseif ($this->startDate) {
                    $query->whereDate('tanggal', $this->startDate);
                } else {
                    $query->whereYear('tanggal', $this->year)
                          ->whereMonth('tanggal', $this->month);
                }
                $query->with('shift');
            }, 'penugasan'])
            ->when(!Auth::user()->hasRole('super-admin'), function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            })
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderByRaw('LENGTH(regu) ASC, regu ASC')
            ->orderBy('name')
            ->paginate($this->perPage);

        // Key their data by date for easy lookup in the view
        $paginator->getCollection()->transform(function ($personnel) {
            $personnel->jadwal_map = $personnel->jadwals->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));
            return $personnel;
        });

        return $paginator;
    }

    public function updatedStartDate($value)
    {
        if ($value) {
            $date = Carbon::parse($value);
            $this->month = $date->format('m');
            $this->year = $date->format('Y');
        }
        $this->resetPage();
    }

    public function updatedEndDate($value)
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('open-modal', id: 'jadwal-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Jadwal::findOrFail($this->deleteId);

        // Authorization Check
        if (!Auth::user()->hasRole('super-admin') && $item->personnel->opd_id !== Auth::user()->opd()?->id) {
             throw new \Exception('Unauthorized');
        }

        $item->delete();

        $this->deleteId = null;
        $this->dispatch('close-modal', id: 'jadwal-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Jadwal berhasil dihapus.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
