<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    //
    public function logout()
    {
        if (Auth::guard('personnel')->check()) {
            Auth::guard('personnel')->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/personnel/login');
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }
};
