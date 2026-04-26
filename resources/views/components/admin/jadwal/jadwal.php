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

    protected $listeners = [
        'refreshJadwal' => '$refresh'
    ];

    public function mount(): void
    {
        if (!$this->month) $this->month = Carbon::now()->format('m');
        if (!$this->year) $this->year = Carbon::now()->format('Y');
    }

    public function load()
    {
        $this->readyToLoad = true;
    }

    public function openQuickAdd($personnelId, $date): void
    {
        $this->dispatch('openQuickEdit', personnelId: $personnelId, date: $date)->to('admin::jadwal-quick-modal');
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
