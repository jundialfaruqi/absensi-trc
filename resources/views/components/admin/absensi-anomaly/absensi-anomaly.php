<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Absensi;
use App\Models\Opd;
use Illuminate\Support\Facades\Auth;

new #[Title('Anomali Lokasi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public ?int $selectedOpdId = null;
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $this->selectedOpdId = $user->opd()?->id;
        }
    }

    #[Computed]
    public function anomalies()
    {
        return Absensi::with(['personnel', 'personnel.opd', 'kantor'])
            ->where('is_location_anomaly', true)
            ->when($this->selectedOpdId, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('opd_id', $this->selectedOpdId);
                });
            })
            ->when($this->search, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('tanggal', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('tanggal', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function totalAnomalies()
    {
        return Absensi::where('is_location_anomaly', true)
            ->when($this->selectedOpdId, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('opd_id', $this->selectedOpdId);
                });
            })
            ->count();
    }

    #[Computed]
    public function opds()
    {
        return Opd::orderBy('name')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedOpdId()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }
};
