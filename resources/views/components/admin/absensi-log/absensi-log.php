<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Absensi;
use App\Models\Opd;
use Illuminate\Support\Facades\Auth;

new #[Title('Log Aktifitas Absensi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public ?int $selectedOpdId = null;

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
        return Absensi::with(['personnel', 'editor', 'personnel.opd'])
            ->whereNotNull('edited_by_user_id')
            ->when($this->selectedOpdId, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('opd_id', $this->selectedOpdId);
                });
            })
            ->when($this->search, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('edited_at', 'desc')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function opds()
    {
        return Opd::orderBy('name')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedOpdId()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
};
