<?php

namespace App\Livewire\Admin\PersonnelCreate;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Kantor;

new #[Title('Tambah Personnel')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $nik = '';
    public string $opd_id = '';
    public string $penugasan_id = '';
    public string $nomor_hp = '';
    public $foto;
    public string $pin = '';
    public string $face_descriptor = '';
    public string $kantor_id = '';
    public bool $wajib_absen_di_lokasi = false;
    public bool $face_recognition = false;

    public function mount()
    {
        if (!Auth::user()->hasRole('super-admin')) {
            $this->opd_id = (string) Auth::user()->opd()?->id;
        }

        $this->pin = $this->generateUniquePin();
        $this->face_recognition = true;
    }

    public function updatedKantorId($value)
    {
        if ($value === '') {
            $this->wajib_absen_di_lokasi = false;
        } else {
            $this->wajib_absen_di_lokasi = true;
        }
    }

    #[Computed]
    public function opds()
    {
        if (Auth::user()->hasRole('super-admin')) {
            return Opd::query()->orderBy('name', 'asc')->get(['*']);
        } else {
            $userOpdId = Auth::user()->opd()?->id;
            return Opd::query()->where('id', '=', $userOpdId)->get(['*']);
        }
    }

    #[Computed]
    public function penugasans()
    {
        return Penugasan::query()->orderBy('name', 'asc')->get(['*']);
    }

    #[Computed]
    public function kantors()
    {
        $query = Kantor::query()->orderBy('name', 'asc');
        if (!Auth::user()->hasRole('super-admin')) {
            $query->where('opd_id', '=', Auth::user()->opd()?->id);
        }
        return $query->get(['*']);
    }

    public function regeneratePin(): void
    {
        $this->pin = $this->generateUniquePin();
    }

    private function generateUniquePin(): string
    {
        do {
            $pin = sprintf("%06d", mt_rand(1, 999999));
        } while (Personnel::query()->where('pin', '=', $pin)->exists());

        return $pin;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nik' => [
                'required',
                'string',
                'max:16',
                Rule::unique('personnels', 'nik'),
            ],
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => 'nullable|string|max:13',
            'pin' => [
                'required',
                'string',
                'size:6',
                Rule::unique('personnels', 'pin'),
            ],
            'foto' => 'required|image|max:2048', // Max 2MB
            'face_descriptor' => 'nullable|string',
            'kantor_id' => 'nullable|exists:kantors,id',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'max' => ':attribute maksimal :max karakter.',
            'size' => ':attribute harus :size karakter.',
            'image' => 'File harus berupa gambar.',
            'exists' => ':attribute tidak valid.',
            'email' => 'Format email tidak valid.',
            'foto.required' => 'Foto autentikasi wajib diunggah.',
            'face_descriptor.required' => 'Wajah harus dideteksi terlebih dahulu.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'nik' => 'NIK / NIP',
            'opd_id' => 'OPD',
            'penugasan_id' => 'Penugasan',
            'nomor_hp' => 'Nomor HP',
            'foto' => 'Foto',
            'pin' => 'PIN',
            'kantor_id' => 'Kantor',
        ];
    }

    public function updatedFoto()
    {
        if ($this->foto) {
            try {
                $mimeType = $this->foto->getMimeType();
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
                    $this->reset('foto');
                    $this->addError('foto', 'File yang diunggah bukan merupakan gambar yang valid.');
                    return;
                }
            } catch (\Exception $e) {
                $this->reset('foto');
                $this->addError('foto', 'File yang diunggah tidak dapat dibaca atau rusak.');
                return;
            }
        }

        $this->validateOnly('foto');
    }

    public function save()
    {
        if (!Auth::user()->hasRole('super-admin')) {
            if ($this->opd_id != Auth::user()->opd()?->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'nik' => $this->nik ?: null,
            'opd_id' => $this->opd_id,
            'penugasan_id' => $this->penugasan_id,
            'nomor_hp' => $this->nomor_hp,
            'pin' => $this->pin,
            'face_descriptor' => $this->face_descriptor ?: null,
            'kantor_id' => $this->kantor_id ?: null,
            'wajib_absen_di_lokasi' => $this->wajib_absen_di_lokasi,
            'face_recognition' => $this->face_recognition,
        ];

        // Generate Email Unik
        $baseEmail = strtolower(str_replace(' ', '', $this->name));
        $email = $baseEmail . '@trc.com';
        $counter = 1;
        while (Personnel::query()->where('email', '=', $email)->exists()) {
            $email = $baseEmail . $counter . '@trc.com';
            $counter++;
        }
        $data['email'] = $email;

        // Generate Password Default
        $penugasan = Penugasan::query()->find($this->penugasan_id);
        $suffix = $penugasan ? strtolower(str_replace(' ', '', $penugasan->name)) : 'dev';
        $data['password'] = Hash::make('admintrc112_' . $suffix);

        if ($this->foto) {
            $data['foto'] = $this->foto->store('personnel-fotos', 'public');
        }

        Personnel::create($data);

        $this->dispatch('set-pending-toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Personnel berhasil ditambahkan.'
        ]);
        return $this->redirectRoute('personnel', [], true, true);
    }
};
