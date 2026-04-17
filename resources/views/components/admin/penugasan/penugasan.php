<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Penugasan;

new #[Title('Manajemen Penugasan')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';

    // Form
    public ?int $penugasanId = null;
    public string $name = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function mount(): void
    {
        //
    }

    #[Computed]
    public function stats(): array
    {
        $total = Penugasan::count();

        return [
            'total' => $total,
        ];
    }

    #[Computed]
    public function penugasans()
    {
        return Penugasan::when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'penugasan-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Penugasan::findOrFail($id);
        
        $this->penugasanId = $item->id;
        $this->name = $item->name;
        
        $this->dispatch('open-modal', id: 'penugasan-modal');
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ], [], [
            'name' => 'Nama Penugasan',
        ]);

        if ($this->penugasanId) {
            Penugasan::findOrFail($this->penugasanId)->update(['name' => $this->name]);
        } else {
            Penugasan::create(['name' => $this->name]);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'penugasan-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Penugasan berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'penugasan-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Penugasan::findOrFail($this->deleteId);
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'penugasan-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Penugasan berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->penugasanId = null;
        $this->name = '';
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