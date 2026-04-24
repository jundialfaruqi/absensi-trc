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
    public $quickDate;
    public $quickShiftId;
    public $quickStatus = 'SHIFT';
    public $quickKeterangan = '';
    
    // Swap Properties
    public $swapTargetPersonnelId = '';
    public $paybackOptions = [];
    public $selectedPaybackDate = '';
    public $activeTab = 'quick'; // 'quick' or 'swap'

    public function mount(): void
    {
        if (!$this->month) $this->month = Carbon::now()->format('m');
        if (!$this->year) $this->year = Carbon::now()->format('Y');
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

    public function deleteQuickJadwal(): void
    {
        // Authorization Check
        $personnel = \App\Models\Personnel::findOrFail($this->quickPersonnelId);
        if (!Auth::user()->hasRole('super-admin') && $personnel->opd_id !== Auth::user()->opd()?->id) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Anda tidak memiliki hak akses untuk personnel ini.');
            return;
        }

        $jadwal = Jadwal::where('personnel_id', $this->quickPersonnelId)
            ->where('tanggal', $this->quickDate)
            ->first();

        if ($jadwal) {
            $jadwal->delete();
            $this->dispatch('close-modal', id: 'quick-add-modal');
            $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Jadwal berhasil dihapus.');
        }
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

        $user = Auth::user();
        $originPersonnel = \App\Models\Personnel::find($this->quickPersonnelId);
        $today = \Carbon\Carbon::parse($this->quickDate);
        $yesterday = $today->copy()->subDay()->format('Y-m-d');
        $todayStr = $today->format('Y-m-d');
        $tomorrow = $today->copy()->addDay()->format('Y-m-d');

        $query = \App\Models\Personnel::where('id', '!=', $this->quickPersonnelId)
            ->where('regu', '!=', $originPersonnel->regu);

        if (!$user->hasRole('super-admin')) {
            $opdId = $user->opd()?->id;
            $query->where('opd_id', $opdId);
        }

        return $query->whereHas('jadwals', function($q) use ($todayStr) {
                $q->whereDate('tanggal', $todayStr)
                  ->where('status', 'LIBUR');
            })
            ->where(function($q) use ($yesterday) {
                // A. Kemarin Masuk (Normal Day 1)
                $q->whereHas('jadwals', function($sq) use ($yesterday) {
                    $sq->whereDate('tanggal', $yesterday)
                       ->where('status', 'SHIFT');
                })
                // B. Jika kemarin kosong (awal periode), tampilkan saja agar mendukung pola libur 1-hari
                ->orWhereDoesntHave('jadwals', function($sq) use ($yesterday) {
                    $sq->whereDate('tanggal', $yesterday);
                });
            })
            ->whereDoesntHave('jadwals', function($q) use ($todayStr) {
                $q->whereDate('tanggal', $todayStr)
                  ->where('is_manual', true);
            })
            ->get();
    }

    public function updatedSwapTargetPersonnelId($value)
    {
        if (!$value) {
            $this->paybackOptions = [];
            return;
        }

        $options = [];
        $startDate = Carbon::parse($this->quickDate)->addDay();
        $endDate = Carbon::parse($this->quickDate)->addDays(30);

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Syarat Payback: 
            // 1. Personel A (peminta) LIBUR di tanggal ini
            // 2. Personel C (target) MASUK (SHIFT) di tanggal ini
            
            $jadwalA = Jadwal::where('personnel_id', $this->quickPersonnelId)
                ->whereDate('tanggal', $dateStr)
                ->first();
            
            $jadwalC = Jadwal::where('personnel_id', $value)
                ->whereDate('tanggal', $dateStr)
                ->first();

            if ($jadwalA && $jadwalA->status === 'LIBUR' && $jadwalC && $jadwalC->status === 'SHIFT') {
                $options[] = [
                    'date' => $dateStr,
                    'label' => $date->translatedFormat('l, d M Y'),
                    'shift_name' => $jadwalC->shift?->name ?? 'SHIFT'
                ];
            }
            
            if (count($options) >= 5) break; // Limit 5 options
        }

        $this->paybackOptions = $options;
        $this->selectedPaybackDate = !empty($options) ? $options[0]['date'] : '';
    }

    public function executeSwapGuling()
    {
        if (!$this->swapTargetPersonnelId || !$this->selectedPaybackDate) {
            $this->dispatch('toast', type: 'error', message: 'Silakan pilih pengganti dan tanggal bayar.');
            return;
        }

        $targetPersonnel = \App\Models\Personnel::find($this->swapTargetPersonnelId);
        $originPersonnel = \App\Models\Personnel::find($this->quickPersonnelId);

        // 1. Data Hari Ini (Tanggal Tukar)
        $jadwalA_Today = Jadwal::where('personnel_id', $this->quickPersonnelId)->where('tanggal', $this->quickDate)->first();
        $jadwalC_Today = Jadwal::where('personnel_id', $this->swapTargetPersonnelId)->where('tanggal', $this->quickDate)->first();

        // 2. Data Hari Esok (Tanggal Bayar)
        $jadwalA_Payback = Jadwal::where('personnel_id', $this->quickPersonnelId)->where('tanggal', $this->selectedPaybackDate)->first();
        $jadwalC_Payback = Jadwal::where('personnel_id', $this->swapTargetPersonnelId)->where('tanggal', $this->selectedPaybackDate)->first();

        if (!$jadwalA_Today || !$jadwalC_Payback) {
             $this->dispatch('toast', type: 'error', message: 'Data jadwal tidak lengkap untuk pertukaran.');
             return;
        }

        // Simpan state asli
        $stateA_Today = ['shift_id' => $jadwalA_Today->shift_id, 'status' => $jadwalA_Today->status];
        $stateC_Payback = ['shift_id' => $jadwalC_Payback->shift_id, 'status' => $jadwalC_Payback->status];

        // --- PROSES TUKAR ---
        
        // A. Hari Ini: A Libur, C Masuk (Gantikan A)
        $jadwalA_Today->update([
            'status' => 'LIBUR', 
            'shift_id' => null, 
            'is_manual' => true,
            'keterangan' => 'Tukar Shift dengan ' . $targetPersonnel->name
        ]);
        $jadwalC_Today->update([
            'status' => $stateA_Today['status'], 
            'shift_id' => $stateA_Today['shift_id'], 
            'is_manual' => true,
            'keterangan' => 'Gantikan ' . $originPersonnel->name
        ]);

        // B. Hari Bayar: C Libur, A Masuk (Bayar Hutang)
        $jadwalC_Payback->update([
            'status' => 'LIBUR', 
            'shift_id' => null, 
            'is_manual' => true,
            'keterangan' => 'Libur (Bayar Hutang ke ' . $originPersonnel->name . ')'
        ]);
        $jadwalA_Payback->update([
            'status' => $stateC_Payback['status'], 
            'shift_id' => $stateC_Payback['shift_id'], 
            'is_manual' => true,
            'keterangan' => 'Masuk (Bayar Hutang ke ' . $targetPersonnel->name . ')'
        ]);

        // Sync Absensi records
        foreach ([$this->quickDate, $this->selectedPaybackDate] as $date) {
            foreach ([$this->quickPersonnelId, $this->swapTargetPersonnelId] as $pId) {
                $j = Jadwal::where('personnel_id', $pId)->where('tanggal', $date)->first();
                if ($j) {
                    \App\Models\Absensi::updateOrCreate(
                        ['personnel_id' => $pId, 'tanggal' => $date],
                        [
                            'jadwal_id' => $j->id,
                            'status' => $j->status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_masuk' => $j->status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                            'status_pulang' => $j->status === 'LIBUR' ? 'LIBUR' : 'ALFA',
                        ]
                    );
                }
            }
        }

        $this->dispatch('close-modal', id: 'quick-add-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Tukar Shift berhasil diproses.');
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
