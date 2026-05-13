<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Konsumsi;

new #[Title('Manajemen Konsumsi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';

    // Form attributes
    public ?int $konsumsiId = null;
    public string $nama = '';

    // Delete attributes
    public ?int $deleteId = null;
    public string $deleteName = '';

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => Konsumsi::count(),
        ];
    }

    #[Computed]
    public function konsumsis()
    {
        return Konsumsi::when($this->search, fn($q) => $q->where('nama', 'like', '%' . $this->search . '%'))
            ->orderBy('nama')
            ->paginate($this->perPage);
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'konsumsi-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Konsumsi::findOrFail($id);

        $this->konsumsiId = $item->id;
        $this->nama = $item->nama;

        $this->dispatch('open-modal', id: 'konsumsi-modal');
    }

    public function save(): void
    {
        $rules = [
            'nama' => 'required|string|max:255|unique:konsumsis,nama,' . $this->konsumsiId,
        ];

        $this->validate($rules);

        $data = [
            'nama' => $this->nama,
        ];

        if ($this->konsumsiId) {
            $konsumsi = Konsumsi::findOrFail($this->konsumsiId);
            $konsumsi->update($data);
        } else {
            Konsumsi::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'konsumsi-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Konsumsi berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $nama): void
    {
        $this->deleteId = $id;
        $this->deleteName = $nama;
        $this->dispatch('open-modal', id: 'konsumsi-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Konsumsi::findOrFail($this->deleteId);
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'konsumsi-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Konsumsi berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->konsumsiId = null;
        $this->nama = '';
        $this->resetErrorBag();
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
