<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    //
public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }
};
