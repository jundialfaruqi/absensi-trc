<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Shift;

new #[Title('Manajemen Shift')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';

    // Form attributes
    public ?int $shiftId = null;
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';

    // Delete attributes
    public ?int $deleteId = null;
    public string $deleteName = '';

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => Shift::count(),
        ];
    }

    #[Computed]
    public function shifts()
    {
        return Shift::when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'shift-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Shift::findOrFail($id);
        
        $this->shiftId = $item->id;
        $this->name = $item->name;
        $this->start_time = \Carbon\Carbon::parse($item->start_time)->format('H:i');
        $this->end_time = \Carbon\Carbon::parse($item->end_time)->format('H:i');
        
        $this->dispatch('open-modal', id: 'shift-modal');
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];

        if ($this->shiftId) {
            $shift = Shift::findOrFail($this->shiftId);
            $shift->update($data);
        } else {
            Shift::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'shift-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Shift berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'shift-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Shift::findOrFail($this->deleteId);
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'shift-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Shift berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->shiftId = null;
        $this->name = '';
        $this->start_time = '';
        $this->end_time = '';
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