<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Setting;
use Livewire\WithPagination;

new #[Layout('layouts::admin.app')] #[Title('Pengaturan Sistem')] class extends Component {
    use WithPagination;
    public $registrationEnabled;
    public $webAbsensiActive;
    public $masukMulai;
    public $masukSelesai;
    public $pulangMulai;
    public $pulangSelesai;
    public $pinMaxAttempts;
    public $pinLock5;
    public $pinLock10;
    
    // APK Information Settings
    public $apkVersion;
    public $apkReleaseDate;
    public $apkDescription;
    public $apkWhatsNew = []; 
    public $apkOptionalMessage;

    // Storage Cleanup Settings
    public $hapusDariTanggal;
    public $hapusSampaiTanggal;
    public $ukuranTerhitung;
    public $confirmHapusText;

    protected $messages = [
        'hapusDariTanggal.required' => 'Tanggal awal harus diisi.',
        'hapusDariTanggal.date' => 'Format tanggal tidak valid.',
        'hapusSampaiTanggal.required' => 'Tanggal akhir harus diisi.',
        'hapusSampaiTanggal.date' => 'Format tanggal tidak valid.',
        'hapusSampaiTanggal.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
    ];


    public function addWhatsNewPoint()
    {
        $this->apkWhatsNew[] = '';
    }

    public function removeWhatsNewPoint($index)
    {
        unset($this->apkWhatsNew[$index]);
        $this->apkWhatsNew = array_values($this->apkWhatsNew); // Re-index array
    }

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
        
        $this->loadApkSettings();
    }

    public function loadApkSettings()
    {
        $latest = \App\Models\ApkRelease::latestRelease();
        
        if ($latest) {
            $this->apkVersion = $latest->version;
            $this->apkReleaseDate = $latest->release_date?->format('Y-m-d');
            $this->apkDescription = $latest->description;
            $this->apkWhatsNew = $latest->whats_new ?? [];
            $this->apkOptionalMessage = $latest->optional_message;
        } else {
            // Load Legacy APK Settings
            $this->apkVersion = Setting::get('apk_version', 'v1.2.0');
            $this->apkReleaseDate = now()->format('Y-m-d');
            $this->apkDescription = Setting::get('apk_description', 'Rilis terbaru dengan penguatan sistem keamanan perangkat.');
            
            $whatsNew = Setting::get('apk_whats_new');
            if ($whatsNew) {
                $this->apkWhatsNew = is_array(json_decode($whatsNew, true)) ? json_decode($whatsNew, true) : [$whatsNew];
            } else {
                $this->apkWhatsNew = [
                    'Keamanan Berlapis: Autentikasi digital (Enkripsi Kunci Dinamis) yang diperbarui otomatis setiap 30 hari.',
                    'Blokir Real-time: Perangkat yang dihapus/suspend otomatis terkunci dari akses sistem.',
                    'Bebas PIN: Menghapus modul PIN yang tidak terpakai untuk mempercepat performa.',
                    'Monitoring Aktivitas: Pelacakan waktu aktif terakhir perangkat (Last Seen) di database.'
                ];
            }
            $this->apkOptionalMessage = Setting::get('apk_optional_message', '');
        }
    }

    public function openApkModal()
    {
        $this->dispatch('open-modal', id: 'apk-modal');
    }

    public function closeApkModal()
    {
        $this->dispatch('close-modal', id: 'apk-modal');
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

    public function saveApkSettings()
    {
        $this->validate([
            'apkVersion' => 'required',
            'apkReleaseDate' => 'required|date',
        ]);

        \App\Models\ApkRelease::create([
            'version' => $this->apkVersion,
            'release_date' => $this->apkReleaseDate,
            'description' => $this->apkDescription,
            'whats_new' => array_values(array_filter($this->apkWhatsNew)),
            'optional_message' => $this->apkOptionalMessage,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Informasi rilis APK baru berhasil disimpan.');
        $this->dispatch('close-modal', 'apk-modal');
    }

    public function hitungUkuran()
    {
        $this->validate([
            'hapusDariTanggal' => 'required|date',
            'hapusSampaiTanggal' => 'required|date|after_or_equal:hapusDariTanggal',
        ]);

        $start = \Carbon\Carbon::parse($this->hapusDariTanggal);
        $end = \Carbon\Carbon::parse($this->hapusSampaiTanggal);
        
        $totalSize = 0;
        
        // Iterate through dates
        $current = $start->copy();
        while ($current->lte($end)) {
            $folderName = 'absensi/' . $current->format('Y-m-d');
            if (Storage::disk('public')->exists($folderName)) {
                $files = Storage::disk('public')->allFiles($folderName);
                foreach ($files as $file) {
                    $totalSize += Storage::disk('public')->size($file);
                }
            }
            $current->addDay();
        }

        // Format size
        if ($totalSize >= 1073741824) {
            $this->ukuranTerhitung = number_format($totalSize / 1073741824, 2) . ' GB';
        } elseif ($totalSize >= 1048576) {
            $this->ukuranTerhitung = number_format($totalSize / 1048576, 2) . ' MB';
        } elseif ($totalSize >= 1024) {
            $this->ukuranTerhitung = number_format($totalSize / 1024, 2) . ' KB';
        } else {
            $this->ukuranTerhitung = $totalSize . ' bytes';
        }
    }

    public function konfirmasiHapus()
    {
        $this->validate([
            'hapusDariTanggal' => 'required|date',
            'hapusSampaiTanggal' => 'required|date|after_or_equal:hapusDariTanggal',
        ]);
        
        $this->confirmHapusText = '';
        $this->dispatch('open-modal', id: 'delete-photo-modal');
    }

    public function hapusPermanen()
    {
        if ($this->confirmHapusText !== 'HAPUS') {
            $this->dispatch('toast', type: 'error', message: 'Konfirmasi teks tidak valid.');
            return;
        }

        $this->validate([
            'hapusDariTanggal' => 'required|date',
            'hapusSampaiTanggal' => 'required|date|after_or_equal:hapusDariTanggal',
        ]);

        $start = \Carbon\Carbon::parse($this->hapusDariTanggal);
        $end = \Carbon\Carbon::parse($this->hapusSampaiTanggal);
        
        $deletedCount = 0;
        
        // Iterate through dates
        $current = $start->copy();
        while ($current->lte($end)) {
            $folderName = 'absensi/' . $current->format('Y-m-d');
            if (Storage::disk('public')->exists($folderName)) {
                Storage::disk('public')->deleteDirectory($folderName);
                $deletedCount++;
            }
            $current->addDay();
        }

        $this->dispatch('close-modal', id: 'delete-photo-modal');
        $this->dispatch('toast', type: 'success', message: "Berhasil menghapus folder foto untuk $deletedCount hari.");
        $this->ukuranTerhitung = '';
        $this->confirmHapusText = '';
    }

    public function with()
    {
        return [
            'apkReleases' => \App\Models\ApkRelease::orderByDesc('release_date')
                ->orderByDesc('id')
                ->paginate(10)
        ];
    }
};
