<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Device;
use App\Models\Opd;
use App\Models\Penugasan;
use Illuminate\Support\Facades\Auth;

new #[Title('Maps Real-time')] #[Layout('layouts::admin.app')] class extends Component
{
    public bool $readyToLoad = false;
    public string $search = '';
    public string $filterOpd = '';
    public string $filterPenugasan = '';

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function opds()
    {
        return Auth::user()->hasRole('super-admin') 
            ? Opd::orderBy('name')->get() 
            : [Auth::user()->opd()];
    }

    #[Computed]
    public function penugasans()
    {
        return Penugasan::orderBy('name')->get();
    }

    #[Computed]
    public function devices()
    {
        if (!$this->readyToLoad) return [];

        return Device::with(['personnel', 'opd', 'personnel.penugasan'])
            ->whereNotNull('last_latitude')
            ->whereNotNull('last_longitude')
            ->when(!Auth::user()->hasRole('super-admin'), function ($q) {
                $q->where('opd_id', Auth::user()->opd()?->id);
            })
            ->when($this->filterOpd, function ($q) {
                $q->where('opd_id', $this->filterOpd);
            })
            ->when($this->filterPenugasan, function ($q) {
                $q->whereHas('personnel', function ($pq) {
                    $pq->where('penugasan_id', $this->filterPenugasan);
                });
            })
            ->when($this->search, function ($q) {
                $q->whereHas('personnel', function ($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->get()
            ->map(function ($d) {
                $d->last_seen_human = $d->last_seen_at ? $d->last_seen_at->diffForHumans() : 'Belum aktif';
                return $d;
            });
    }

    #[Computed]
    public function kantors()
    {
        return \App\Models\Kantor::when(!Auth::user()->hasRole('super-admin'), function ($q) {
                $q->where('opd_id', Auth::user()->opd()?->id);
            })
            ->where('is_active', true)
            ->get();
    }

    public function rendered($view)
    {
        $this->dispatch('devices-updated', devices: $this->devices);
        $this->dispatch('kantors-updated', kantors: $this->kantors);
    }
};
