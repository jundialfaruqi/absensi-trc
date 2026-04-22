<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Setting;

new #[Layout('layouts::admin.app')] #[Title('Pengaturan Sistem')] class extends Component {
    public $registrationEnabled;

    public function mount()
    {
        $this->registrationEnabled = Setting::get('personnel_registration_enabled', true);
    }

    public function updatedRegistrationEnabled($value)
    {
        Setting::set('personnel_registration_enabled', $value, 'boolean');
        $this->dispatch('toast', type: 'success', message: 'Pengaturan pendaftaran personel berhasil diperbarui.');
    }

    public function with()
    {
        return [
            //
        ];
    }
};
