<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new #[Title('Monitoring Absensi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;
    public bool $readyToLoad = false;

    #[Url]
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

    #[Url]
    public string $selectedOpd = '';

    public string $paperSize = 'a4';

    protected $listeners = [
        'refreshAbsensi' => '$refresh'
    ];

    public function mount(): void
    {
        // Set default 7 hari: dari kemarin sampai 5 hari ke depan
        if (!$this->startDate) {
            $this->startDate = Carbon::yesterday()->format('Y-m-d');
        }

        if (!$this->endDate) {
            $this->endDate = Carbon::yesterday()->addDays(10)->format('Y-m-d');
        }

        // Sinkronkan filter dropdown bulan/tahun bawaan
        if (!$this->month) $this->month = Carbon::parse($this->startDate)->format('m');
        if (!$this->year) $this->year = Carbon::parse($this->startDate)->format('Y');
    }

    #[Computed]
    public function isDefaultDateFilter(): bool
    {
        $defaultStart = Carbon::yesterday()->format('Y-m-d');
        $defaultEnd = Carbon::yesterday()->addDays(10)->format('Y-m-d');

        return $this->startDate === $defaultStart && $this->endDate === $defaultEnd;
    }

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function dates(): array
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);

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

        $paginator = Personnel::with(['absensis' => function ($query) {
                if ($this->startDate && $this->endDate) {
                    $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
                } elseif ($this->startDate) {
                    $query->whereDate('tanggal', $this->startDate);
                } else {
                    $query->whereYear('tanggal', $this->year)
                          ->whereMonth('tanggal', $this->month);
                }
                $query->with('kantor');
            }, 'jadwals' => function ($query) {
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
                $q->where('personnels.opd_id', $opdId);
            })
            ->when(Auth::user()->hasRole('super-admin') && $this->selectedOpd, function ($q) {
                $q->where('personnels.opd_id', $this->selectedOpd);
            })
            ->when($this->search, function ($q) {
                $q->where('personnels.name', 'like', '%' . $this->search . '%');
            })
            ->join('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select([
                'personnels.id',
                'personnels.name',
                'personnels.foto',
                'personnels.regu',
                'personnels.attendance_type',
                'personnels.opd_id',
                'personnels.penugasan_id',
            ])
            ->orderBy('opds.name')
            ->orderByRaw('LENGTH(personnels.regu) ASC, personnels.regu ASC')
            ->orderBy('personnels.name')
            ->paginate($this->perPage);

        $paginator->getCollection()->transform(function ($personnel) {
            $personnel->absensi_map = $personnel->absensis->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
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
        // Kembalikan ke default 7 hari
        $this->startDate = Carbon::yesterday()->format('Y-m-d');
        $this->endDate = Carbon::yesterday()->addDays(10)->format('Y-m-d');

        $this->resetPage();
    }

    #[Renderless]
    public function editAbsensi($personnelId, $tanggal)
    {
        $this->dispatch('openEditAbsensi', personnelId: $personnelId, tanggal: $tanggal);
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

    #[Computed]
    public function opds()
    {
        // Perbaikan Poin 5: Optimasi select tabel referensi
        return \App\Models\Opd::select('id', 'name')->orderBy('name')->get();
    }
};
