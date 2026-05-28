<?php

use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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
        if (! $this->readyToLoad) {
            return [];
        }

        return Device::with([
            'personnel:id,name,foto,penugasan_id,opd_id',
            'personnel.penugasan:id,name',
            'opd:id,name,singkatan',
        ])
            ->whereNotNull('last_latitude')
            ->whereNotNull('last_longitude')
            ->when(! Auth::user()->hasRole('super-admin'), function ($q) {
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
                    $pq->where('name', 'like', '%'.$this->search.'%');
                });
            })
            // Hanya ambil kolom yang diperlukan untuk peta (hilangkan data sensitif)
            ->select([
                'id', 'opd_id', 'personnel_id', 'name',
                'last_latitude', 'last_longitude', 'last_seen_at',
            ])
            ->get()
            ->map(function ($d) {
                $d->last_seen_human = $d->last_seen_at ? $d->last_seen_at->diffForHumans() : 'Belum aktif';
                $d->is_online = $d->last_seen_at && $d->last_seen_at->diffInMinutes() < 1;

                return $d;
            })
            ->sortByDesc('is_online')
            ->values();
    }

    #[Computed]
    public function totalOnline(): int
    {
        if (! $this->readyToLoad) {
            return 0;
        }

        return $this->devices->filter(fn ($d) => $d->is_online)->count();
    }

    #[Computed]
    public function kantors()
    {
        return Kantor::when(! Auth::user()->hasRole('super-admin'), function ($q) {
            $q->where('opd_id', Auth::user()->opd()?->id);
        })
            ->where('is_active', true)
            ->get();
    }

    public function rendered($view)
    {
        // Hanya kirim data ke Javascript setelah load() selesai dipanggil
        // Ini mencegah data kosong dikirim saat halaman pertama dimuat
        if ($this->readyToLoad) {
            $this->dispatch('devices-updated', devices: $this->devices);
            $this->dispatch('kantors-updated', kantors: $this->kantors);
        }
    }
};
