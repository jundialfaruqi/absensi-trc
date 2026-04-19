<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cuti;
use Illuminate\Validation\Rule;

new #[Title('Manajemen Cuti')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';

    // Form
    public ?int $cutiId = null;
    public string $name = '';
    public string $keterangan = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    #[Computed]
    public function cutis()
    {
        return Cuti::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('keterangan', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'cuti-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Cuti::findOrFail($id);
        
        $this->cutiId = $item->id;
        $this->name = $item->name;
        $this->keterangan = $item->keterangan ?? '';
        
        $this->dispatch('open-modal', id: 'cuti-modal');
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->name,
            'keterangan' => $this->keterangan,
        ];

        if ($this->cutiId) {
            $cuti = Cuti::findOrFail($this->cutiId);
            $cuti->update($data);
        } else {
            Cuti::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'cuti-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Cuti berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'cuti-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Cuti::findOrFail($this->deleteId);
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'cuti-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Cuti berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->cutiId = null;
        $this->name = '';
        $this->keterangan = '';
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
