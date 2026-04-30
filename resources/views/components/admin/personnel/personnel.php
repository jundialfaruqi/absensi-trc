<?php

namespace App\Livewire\Admin\Personnel;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use App\Models\Personnel;
use App\Models\Opd;

new #[Title('Manajemen Personnel')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;
    public int $perPage = 10;
    
    #[Url]
    public string $search = '';

    #[Url]
    public string $selectedOpd = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function personnels()
    {
        if (!$this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        $query = Personnel::with(['opd', 'penugasan', 'kantor'])
            ->join('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select('personnels.*')
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->where('personnels.name', 'like', '%' . $this->search . '%')
                    ->orWhere('personnels.nik', 'like', '%' . $this->search . '%')
                    ->orWhere('personnels.email', 'like', '%' . $this->search . '%')
                    ->orWhere('personnels.pin', 'like', '%' . $this->search . '%');
            }))
            ->when($this->selectedOpd, fn($q) => $q->where('personnels.opd_id', $this->selectedOpd));

        if (!auth()->user()->hasRole('super-admin')) {
            $userOpdId = auth()->user()->opd()?->id;
            $query->where('personnels.opd_id', $userOpdId);
        }

        return $query->orderBy('opds.name')->orderBy('personnels.name')->paginate($this->perPage);
    }

    #[Computed]
    public function opds()
    {
        if (auth()->user()->hasRole('super-admin')) {
            return Opd::orderBy('name')->get();
        } else {
            $userOpdId = auth()->user()->opd()?->id;
            return Opd::where('id', $userOpdId)->get();
        }
    }

    public function resetPin(int $id): void
    {
        $item = Personnel::findOrFail($id);
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $newPin = $this->generateUniquePin();
        $item->update(['pin' => $newPin]);

        $this->dispatch('toast', type: 'success', title: 'PIN Direset', message: "PIN baru untuk {$item->name} adalah: {$newPin}");
    }

    private function generateUniquePin(): string
    {
        do {
            $pin = sprintf("%06d", mt_rand(1, 999999));
        } while (Personnel::where('pin', $pin)->exists());

        return $pin;
    }

    public function confirmDelete(int $id, string $name): void
    {
        $item = Personnel::findOrFail($id);
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'personnel-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Personnel::findOrFail($this->deleteId);

        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($item->foto) {
            Storage::disk('public')->delete($item->foto);
        }
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'personnel-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Personnel berhasil dihapus.');
    }

    public function goToAdd()
    {
        return $this->redirectRoute('personnel.tambah', navigate: true);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
