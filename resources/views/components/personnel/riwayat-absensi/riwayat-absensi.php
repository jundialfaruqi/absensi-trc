<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use Carbon\Carbon;

new #[Layout('layouts::personnel.dashboard.app')] #[Title('Riwayat Absensi')] class extends Component {
    use WithPagination;

    public $personnel;
    public $month;
    public $year;

    public function mount()
    {
        $this->personnel = Auth::guard('personnel')->user();
        if (!$this->personnel) {
            return $this->redirect('/personnel/login', navigate: true);
        }

        $this->month = date('m');
        $this->year = date('Y');
    }

    public function updated($property)
    {
        if (in_array($property, ['month', 'year'])) {
            $this->resetPage();
        }
    }

    public function getRiwayatProperty()
    {
        return Absensi::where('personnel_id', $this->personnel->id)
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->where('tanggal', '<=', now()->format('Y-m-d'))
            ->with(['jadwal.shift'])
            ->orderBy('tanggal', 'desc')
            ->paginate(10);
    }
};
