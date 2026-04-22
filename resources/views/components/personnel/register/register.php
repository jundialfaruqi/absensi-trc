<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use App\Models\Personnel;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts::personnel.register.app')] #[Title('Pendaftaran Personnel')] class extends Component {
    use WithFileUploads;

    public $name;
    public $email;
    public $nomor_hp;
    public $opd_id;
    public $penugasan_id;
    public $password;
    public $password_confirmation;
    public $pin;
    public $foto;

    public function mount()
    {
        if (!Setting::get('personnel_registration_enabled', true)) {
            session()->flash('error', 'Pendaftaran personel saat ini sedang ditutup.');
            return $this->redirect('/personnel/login', navigate: true);
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:personnels,email',
            'nomor_hp' => 'required|string|max:20|unique:personnels,nomor_hp',
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'password' => 'required|string|min:8|confirmed',
            'pin' => 'required|digits:4',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2048KB = 2MB
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar dalam sistem.',
            'nomor_hp.required' => 'Nomor HP wajib diisi.',
            'nomor_hp.unique' => 'Nomor HP sudah terdaftar dalam sistem.',
            'opd_id.required' => 'Silakan pilih OPD Anda.',
            'opd_id.exists' => 'OPD yang dipilih tidak valid.',
            'penugasan_id.required' => 'Silakan pilih penugasan Anda.',
            'penugasan_id.exists' => 'Penugasan yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'pin.required' => 'PIN wajib diisi.',
            'pin.digits' => 'PIN harus terdiri dari 4 digit angka.',
            'foto.required' => 'Foto profil wajib diunggah.',
            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Hanya format JPG, JPEG, dan PNG yang diperbolehkan.',
            'foto.max' => 'Ukuran foto maksimal adalah 2000 KB.',
        ];
    }

    public function register()
    {
        if (!Setting::get('personnel_registration_enabled', true)) {
            $this->addError('registration', 'Maaf, pendaftaran personel telah ditutup oleh admin.');
            return;
        }

        $this->validate();

        // Additional strict MIME check for security
        $mimeType = $this->foto->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
            $this->addError('foto', 'File yang diunggah bukan merupakan gambar yang valid.');
            return;
        }

        try {
            $fotoPath = $this->foto->store('personnels', 'public');

            Personnel::create([
                'name' => $this->name,
                'email' => $this->email,
                'nomor_hp' => $this->nomor_hp,
                'opd_id' => $this->opd_id,
                'penugasan_id' => $this->penugasan_id,
                'password' => Hash::make($this->password),
                'pin' => Hash::make($this->pin),
                'foto' => $fotoPath,
                'wajib_absen_di_lokasi' => true,
            ]);

            $this->dispatch('registration-success');
        } catch (\Exception $e) {
            $this->dispatch('registration-failed', message: 'Terjadi kesalahan sistem. Silakan coba lagi nanti.');
        }
    }

    public function with()
    {
        return [
            'opds' => Opd::all(),
            'penugasans' => Penugasan::all(),
        ];
    }
};
