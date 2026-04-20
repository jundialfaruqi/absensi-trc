<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Kantor;
use App\Models\Opd;
use Illuminate\Validation\Rule;

new #[Title('Manajemen Kantor')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public string $filterOpd = '';

    // Form
    public ?int $kantorId = null;
    public string $name = '';
    public string $opd_id = '';
    public string $alamat = '';
    public float $latitude = 0.507068; // Default Pekanbaru
    public float $longitude = 101.447779;
    public int $radius_meter = 100;
    public bool $is_active = true;

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    #[Computed]
    public function kantors()
    {
        return Kantor::with('opd')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->filterOpd, fn($q) => $q->where('opd_id', $this->filterOpd))
            ->when(!auth()->user()->hasRole('super-admin'), function($q) {
                $userOpdId = auth()->user()->opd()?->id;
                $q->where('opd_id', $userOpdId);
            })
            ->withCount('personnels')
            ->orderBy('name')
            ->paginate($this->perPage);
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

    public function openAddModal(): void
    {
        $this->resetForm();
        if (!auth()->user()->hasRole('super-admin')) {
            $this->opd_id = (string) auth()->user()->opd()?->id;
        }
        $this->dispatch('open-modal', id: 'kantor-modal');
        // Dispatch to map to refresh
        $this->dispatch('init-map', lat: $this->latitude, lng: $this->longitude, radius: $this->radius_meter);
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Kantor::findOrFail($id);
        
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->kantorId = $item->id;
        $this->name = $item->name;
        $this->opd_id = (string) $item->opd_id;
        $this->alamat = $item->alamat ?? '';
        $this->latitude = (float) $item->latitude;
        $this->longitude = (float) $item->longitude;
        $this->radius_meter = $item->radius_meter;
        $this->is_active = (bool) $item->is_active;
        
        $this->dispatch('open-modal', id: 'kantor-modal');
        $this->dispatch('init-map', lat: $this->latitude, lng: $this->longitude, radius: $this->radius_meter);
    }

    public function save(): void
    {
        if (!auth()->user()->hasRole('super-admin')) {
            if ($this->opd_id != auth()->user()->opd()?->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'opd_id' => 'required|exists:opds,id',
            'alamat' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:50|max:10000',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $this->name,
            'opd_id' => $this->opd_id,
            'alamat' => $this->alamat,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meter' => $this->radius_meter,
            'is_active' => $this->is_active,
        ];

        if ($this->kantorId) {
            $kantor = Kantor::findOrFail($this->kantorId);
            $kantor->update($data);
        } else {
            Kantor::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'kantor-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Kantor berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $item = Kantor::findOrFail($id);
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'kantor-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Kantor::findOrFail($this->deleteId);
        
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'kantor-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Kantor berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->kantorId = null;
        $this->name = '';
        $this->opd_id = '';
        $this->alamat = '';
        $this->latitude = 0.507068;
        $this->longitude = 101.447779;
        $this->radius_meter = 100;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterOpd(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
