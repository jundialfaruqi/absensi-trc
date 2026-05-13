<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Absensi;
use App\Models\Opd;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new #[Title('Kotak Sampah Absensi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;
    public int $perPage = 10;
    #[Url]
    public string $search = '';
    #[Url]
    public ?int $filterOpd = null;
    #[Url]
    public string $filterDate = '';

    // For delete confirmation modal
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function trashedAbsensis()
    {
        if (!$this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        return Absensi::onlyTrashed()
            ->with(['personnel', 'personnel.opd', 'deleter'])
            ->when(strlen($this->search) >= 3, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterOpd, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('opd_id', $this->filterOpd);
                });
            })
            ->when($this->filterDate, function ($query) {
                $query->whereDate('tanggal', $this->filterDate);
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function opds()
    {
        return Opd::orderBy('name')->get();
    }

    public function restore($id)
    {
        $absensi = Absensi::onlyTrashed()->findOrFail($id);

        // Cek apakah sudah ada data absensi aktif untuk personnel + tanggal yang sama
        $exists = Absensi::where('personnel_id', $absensi->personnel_id)
            ->where('tanggal', $absensi->tanggal)
            ->exists();

        if ($exists) {
            $this->dispatch('toast', type: 'error', message: 'Gagal mengembalikan: sudah ada data absensi baru untuk personnel ini pada tanggal yang sama.');
            return;
        }

        $absensi->update(['deleted_by_user_id' => null]);
        $absensi->restore();

        $this->dispatch('toast', type: 'success', message: 'Data absensi berhasil dikembalikan.');
    }

    public function confirmForceDelete($id, $name)
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'force-delete-modal');
    }

    public function executeForceDelete()
    {
        if (!$this->deleteId) return;

        $absensi = Absensi::onlyTrashed()->findOrFail($this->deleteId);

        // Hapus foto dari storage
        if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
            Storage::disk('public')->delete($absensi->foto_masuk);
        }
        if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
            Storage::disk('public')->delete($absensi->foto_pulang);
        }

        // Hapus permanen dari database
        $absensi->forceDelete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'force-delete-modal');
        $this->dispatch('toast', type: 'success', message: 'Data absensi dan foto berhasil dihapus permanen.');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedFilterOpd()
    {
        $this->resetPage();
    }

    public function updatedFilterDate()
    {
        $this->resetPage();
    }
};
