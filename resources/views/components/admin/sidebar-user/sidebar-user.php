<?php
 
use Livewire\Component;
use Livewire\Attributes\On;
 
new class extends Component
{
    #[On('profile-updated')]
    public function refresh()
    {
        // Just refresh the component
    }
 
    public function render()
    {
        return view('components.admin.sidebar-user.sidebar-user');
    }
};
