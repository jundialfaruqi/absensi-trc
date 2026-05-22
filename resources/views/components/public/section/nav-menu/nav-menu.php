<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('welcome')] class extends Component
{
    public function render()
    {
        return view('components.public.section.nav-menu.nav-menu');
    }
};
