<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PinAttemptLog;
use App\Models\Opd;
use Illuminate\Support\Facades\Auth;

new #[Title('Log Percobaan PIN')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 15;
    public string $search = '';
    public ?int $selectedOpdId = null;
    public ?string $selectedStatus = null;
    public bool $showConfirmModal = false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $this->selectedOpdId = $user->opd()?->id;
        }
    }

    #[Computed]
    public function logs()
    {
        return PinAttemptLog::with(['personnel', 'personnel.opd'])
            ->when($this->selectedOpdId, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('opd_id', $this->selectedOpdId);
                });
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('ip_address', 'like', '%' . $this->search . '%');
            })
            ->orderBy('attempted_at', 'desc')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function opds()
    {
        return Opd::orderBy('name')->get();
    }

    public function clearOldLogs()
    {
        if (!Auth::user()->hasRole('super-admin')) {
            $this->dispatch('toast', message: 'Anda tidak memiliki izin!', type: 'error');
            return;
        }
        $this->showConfirmModal = true;
    }

    public function confirmClearOldLogs()
    {
        if (!Auth::user()->hasRole('super-admin')) {
            $this->showConfirmModal = false;
            return;
        }

        $count = PinAttemptLog::where('attempted_at', '<', now()->subDays(30))->delete();
        $this->showConfirmModal = false;

        if ($count > 0) {
            $this->dispatch('toast', message: "Berhasil membersihkan {$count} log lama.", type: 'success');
        } else {
            $this->dispatch('toast', message: "Tidak ada log yang berumur lebih dari 30 hari.", type: 'info');
        }
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedSelectedOpdId() { $this->resetPage(); }
    public function updatedSelectedStatus() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }
};
