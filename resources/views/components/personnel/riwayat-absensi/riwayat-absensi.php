<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;

new #[Layout('layouts::personnel.dashboard.app')] #[Title('Riwayat Absensi')] class extends Component {
    public $personnel;

    public function mount()
    {
        $this->personnel = Auth::guard('personnel')->user();
        if (!$this->personnel) {
            return $this->redirect('/personnel/login', navigate: true);
        }
    }

    public function getRiwayatProperty()
    {
        return Absensi::where('personnel_id', $this->personnel->id)
            ->with(['jadwal.shift'])
            ->orderBy('tanggal', 'desc')
            ->limit(50)
            ->get();
    }
};
