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
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    public string $filterStartDate = '';

    public string $filterEndDate = '';

    #[Url]
    public string $selectedOpd = '';

    public string $paperSize = 'a4';

    protected $listeners = [
        'refreshAbsensi' => '$refresh'
    ];

    public function mount(): void
    {
        // Set default rentang tanggal: dari kemarin sampai 10 hari ke depan
        if (!$this->startDate) {
            $this->startDate = Carbon::yesterday()->format('Y-m-d');
        }

        if (!$this->endDate) {
            $this->endDate = Carbon::yesterday()->addDays(10)->format('Y-m-d');
        }

        $this->filterStartDate = $this->startDate;
        $this->filterEndDate = $this->endDate;
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

        $start = Carbon::yesterday();
        $end = Carbon::yesterday()->addDays(10);
        $dates = [];
        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
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
                }
                $query->with('kantor');
            }, 'jadwals' => function ($query) {
                if ($this->startDate && $this->endDate) {
                    $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
                } elseif ($this->startDate) {
                    $query->whereDate('tanggal', $this->startDate);
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

    public function applyFilter(): void
    {
        if (!$this->filterStartDate && $this->filterEndDate) {
            $this->filterStartDate = $this->filterEndDate;
        } elseif ($this->filterStartDate && !$this->filterEndDate) {
            $this->filterEndDate = $this->filterStartDate;
        }

        if ($this->filterStartDate && $this->filterEndDate) {
            if ($this->filterStartDate > $this->filterEndDate) {
                $temp = $this->filterStartDate;
                $this->filterStartDate = $this->filterEndDate;
                $this->filterEndDate = $temp;
            }

            $start = Carbon::parse($this->filterStartDate);
            $end = Carbon::parse($this->filterEndDate);
            if ($start->diffInDays($end) > 31) {
                $this->filterEndDate = $start->copy()->addDays(31)->format('Y-m-d');
            }
        }

        $this->startDate = $this->filterStartDate;
        $this->endDate = $this->filterEndDate;

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        // Kembalikan ke default
        $this->startDate = Carbon::yesterday()->format('Y-m-d');
        $this->endDate = Carbon::yesterday()->addDays(10)->format('Y-m-d');

        $this->filterStartDate = $this->startDate;
        $this->filterEndDate = $this->endDate;

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

    #[Computed]
    public function shifts()
    {
        return \App\Models\Shift::where('type', '!=', 'off')
            ->with('konsumsis')
            ->orderBy('name')
            ->get();
    }
};
