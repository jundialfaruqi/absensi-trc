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

    public function logout()
    {
        Auth::guard('personnel')->logout();
        Session::invalidate();
        Session::regenerateToken();

        return $this->redirect('/personnel/login', navigate: true);
    }
};
