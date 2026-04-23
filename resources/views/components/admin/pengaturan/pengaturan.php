<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Setting;

new #[Layout('layouts::admin.app')] #[Title('Pengaturan Sistem')] class extends Component {
    public $registrationEnabled;
    public $webAbsensiActive;
    public $masukMulai;
    public $masukSelesai;
    public $pulangMulai;
    public $pulangSelesai;
    public $pinMaxAttempts;
    public $pinLock5;
    public $pinLock10;

    public function mount()
    {
        $this->registrationEnabled = Setting::get('personnel_registration_enabled', true);
        $this->webAbsensiActive = Setting::get('web_absensi_active', true);
        $this->masukMulai = Setting::get('absensi_masuk_mulai', 30);
        $this->masukSelesai = Setting::get('absensi_masuk_selesai', 120);
        $this->pulangMulai = Setting::get('absensi_pulang_mulai', 30);
        $this->pulangSelesai = Setting::get('absensi_pulang_selesai', 120);
        $this->pinMaxAttempts = Setting::get('pin_max_attempts', 5);
        $this->pinLock5 = Setting::get('pin_lock_duration_5', 5);
        $this->pinLock10 = Setting::get('pin_lock_duration_10', 15);
    }

    public function updatedRegistrationEnabled($value)
    {
        Setting::set('personnel_registration_enabled', $value, 'boolean');
        $this->dispatch('toast', type: 'success', message: 'Pengaturan pendaftaran personel diperbarui.');
    }

    public function updatedWebAbsensiActive($value)
    {
        Setting::set('web_absensi_active', $value, 'boolean');
        $this->dispatch('toast', type: 'success', message: 'Status Web Absensi diperbarui.');
    }

    public function saveTimeSettings()
    {
        Setting::set('absensi_masuk_mulai', $this->masukMulai, 'integer');
        Setting::set('absensi_masuk_selesai', $this->masukSelesai, 'integer');
        Setting::set('absensi_pulang_mulai', $this->pulangMulai, 'integer');
        Setting::set('absensi_pulang_selesai', $this->pulangSelesai, 'integer');

        $this->dispatch('toast', type: 'success', message: 'Batasan waktu absensi berhasil disimpan.');
    }

    public function saveSecuritySettings()
    {
        Setting::set('pin_max_attempts', $this->pinMaxAttempts, 'integer');
        Setting::set('pin_lock_duration_5', $this->pinLock5, 'integer');
        Setting::set('pin_lock_duration_10', $this->pinLock10, 'integer');

        $this->dispatch('toast', type: 'success', message: 'Pengaturan keamanan PIN berhasil disimpan.');
    }

    public function with()
    {
        return [
            //
        ];
    }
};
