<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts::personnel.dashboard.app')] #[Title('Dashboard Personnel')] class extends Component {
    public $personnel;

    public function mount()
    {
        $this->personnel = Auth::guard('personnel')->user();
        
        if (!$this->personnel) {
            return $this->redirect('/personnel/login', navigate: true);
        }

        // Load relationships for complete data
        $this->personnel->load(['opd', 'penugasan', 'kantor']);
    }

    public function with()
    {
        $todayJadwal = \App\Models\Jadwal::where('personnel_id', $this->personnel->id)
            ->whereDate('tanggal', \Carbon\Carbon::today())
            ->with('shift')
            ->first();

        return [
            'todayJadwal' => $todayJadwal,
        ];
    }

    public function logout()
    {
        Auth::guard('personnel')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirect('/personnel/login', navigate: true);
    }
};
