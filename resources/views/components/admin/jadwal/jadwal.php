<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new #[Title('Manajemen Jadwal')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public string $month = '';
    public string $year = '';

    public ?int $deleteId = null;

    // Quick Add Properties
    public $quickPersonnelId;
    public $quickDate;
    public $quickShiftId;
    public $quickStatus = 'SHIFT';
    public $quickKeterangan = '';

    public function mount(): void
    {
        $this->month = Carbon::now()->format('m');
        $this->year = Carbon::now()->format('Y');
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

        Jadwal::updateOrCreate(
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
    public function stats(): array
    {
        $query = Jadwal::whereMonth('tanggal', $this->month)
            ->whereYear('tanggal', $this->year);

        if (!Auth::user()->hasRole('super-admin')) {
            $opdId = Auth::user()->opd()?->id;
            $query->whereHas('personnel', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        return [
            'total' => $query->count(),
            'personnel_count' => $query->distinct('personnel_id')->count('personnel_id'),
        ];
    }

    #[Computed]
    public function dates(): array
    {
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
                $query->whereYear('tanggal', $this->year)
                      ->whereMonth('tanggal', $this->month)
                      ->with('shift');
            }, 'penugasan'])
            ->when(!Auth::user()->hasRole('super-admin'), function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            })
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        // Key their jadwals by date for easy lookup
        $paginator->getCollection()->transform(function ($personnel) {
            $personnel->jadwal_map = $personnel->jadwals->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));
            return $personnel;
        });

        return $paginator;
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