<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Absensi;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new #[Title('Monitoring Absensi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public string $month = '';
    public string $year = '';

    public function mount(): void
    {
        $this->month = Carbon::now()->format('m');
        $this->year = Carbon::now()->format('Y');
    }

    #[Computed]
    public function stats(): array
    {
        $query = Absensi::whereMonth('tanggal', $this->month)
            ->whereYear('tanggal', $this->year);

        if (!Auth::user()->hasRole('super-admin')) {
            $opdId = Auth::user()->opd()?->id;
            $query->whereHas('personnel', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        return [
            'total_logs' => $query->count(),
            'hadir_tepat_waktu' => (clone $query)->where('status_masuk', 'HADIR')->count(),
            'terlambat' => (clone $query)->where('status_masuk', 'TELAT')->count(),
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

        $paginator = Personnel::with(['absensis' => function ($query) {
                $query->whereYear('tanggal', $this->year)
                      ->whereMonth('tanggal', $this->month);
            }, 'jadwals' => function ($query) {
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

        // Key their data by date for easy lookup in the view
        $paginator->getCollection()->transform(function ($personnel) {
            $personnel->absensi_map = $personnel->absensis->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
            $personnel->jadwal_map = $personnel->jadwals->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));
            return $personnel;
        });

        return $paginator;
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