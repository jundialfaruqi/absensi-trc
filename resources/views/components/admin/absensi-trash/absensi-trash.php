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

    // For bulk selection (Opsi B)
    public array $selectedIds = [];
    public bool $selectAll = false;

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
        $existing = Absensi::where('personnel_id', $absensi->personnel_id)
            ->where('tanggal', $absensi->tanggal)
            ->first();

        if ($existing) {
            // Jika record yang ada masih default (belum diisi), hapus lalu restore yang lama
            if (!$existing->jam_masuk && !$existing->jam_pulang && !$existing->foto_masuk && !$existing->foto_pulang) {
                $existing->forceDelete();
            } else {
                $this->dispatch('toast', type: 'error', message: 'Gagal mengembalikan: sudah ada data absensi yang sudah diisi untuk personnel ini pada tanggal yang sama.');
                return;
            }
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

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->trashedAbsensis->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds()
    {
        $currentIds = $this->trashedAbsensis->pluck('id')->map(fn($id) => (string)$id)->toArray();
        if (count($currentIds) > 0 && count(array_intersect($this->selectedIds, $currentIds)) === count($currentIds)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function resetSelection()
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function confirmBulkForceDelete()
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'Silakan pilih data absensi yang ingin dihapus.');
            return;
        }
        $this->dispatch('open-modal', id: 'bulk-force-delete-modal');
    }

    public function executeBulkForceDelete()
    {
        if (empty($this->selectedIds)) return;

        $absensis = Absensi::onlyTrashed()->whereIn('id', $this->selectedIds)->get();

        foreach ($absensis as $absensi) {
            if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
                Storage::disk('public')->delete($absensi->foto_masuk);
            }
            if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
                Storage::disk('public')->delete($absensi->foto_pulang);
            }
            $absensi->forceDelete();
        }

        $this->resetSelection();
        $this->dispatch('close-modal', id: 'bulk-force-delete-modal');
        $this->dispatch('toast', type: 'success', message: 'Data absensi terpilih berhasil dihapus permanen.');
    }

    public function confirmEmptyTrash()
    {
        $count = Absensi::onlyTrashed()->count();
        if ($count === 0) {
            $this->dispatch('toast', type: 'error', message: 'Kotak sampah sudah kosong.');
            return;
        }
        $this->dispatch('open-modal', id: 'empty-trash-modal');
    }

    public function executeEmptyTrash()
    {
        $absensis = Absensi::onlyTrashed()->get();

        foreach ($absensis as $absensi) {
            if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
                Storage::disk('public')->delete($absensi->foto_masuk);
            }
            if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
                Storage::disk('public')->delete($absensi->foto_pulang);
            }
            $absensi->forceDelete();
        }

        $this->resetSelection();
        $this->dispatch('close-modal', id: 'empty-trash-modal');
        $this->dispatch('toast', type: 'success', message: 'Semua data absensi di kotak sampah berhasil dihapus permanen.');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedFilterOpd()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedFilterDate()
    {
        $this->resetPage();
        $this->resetSelection();
    }
};
