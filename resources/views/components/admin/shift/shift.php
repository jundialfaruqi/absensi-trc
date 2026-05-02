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
    public string $type = 'shift';
    public string $keterangan = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $color = '#64748b';

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
        $this->type = $item->type;
        $this->keterangan = $item->keterangan;
        $this->start_time = $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '';
        $this->end_time = $item->end_time ? \Carbon\Carbon::parse($item->end_time)->format('H:i') : '';
        $this->color = $item->color ?? '#64748b';

        $this->dispatch('open-modal', id: 'shift-modal');
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:shift,off',
            'keterangan' => 'nullable|string|max:255',
            'start_time' => $this->type === 'shift' ? 'required|date_format:H:i' : 'nullable',
            'end_time' => $this->type === 'shift' ? 'required|date_format:H:i' : 'nullable',
            'color' => 'required|string|max:7',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'keterangan' => $this->keterangan,
            'start_time' => $this->type === 'shift' ? $this->start_time : null,
            'end_time' => $this->type === 'shift' ? $this->end_time : null,
            'color' => $this->color,
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
        $this->type = 'shift';
        $this->keterangan = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->color = '#64748b';
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
