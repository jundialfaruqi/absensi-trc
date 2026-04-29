<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Personnel;
use App\Models\Opd;
use App\Models\Penugasan;

new #[Title('Manajemen Personnel')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    public bool $readyToLoad = false;
    public int $perPage = 10;
    public string $search = '';

    // Form
    public ?int $personnelId = null;
    public string $name = '';
    public string $nik = '';
    public string $opd_id = '';
    public string $penugasan_id = '';
    public string $nomor_hp = '';
    public $foto;
    public ?string $oldFoto = null;
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $pin = '';
    public string $face_descriptor = '';
    public string $kantor_id = '';
    public bool $wajib_absen_di_lokasi = false;
    public bool $face_recognition = false;

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function personnels()
    {
        if (!$this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        $query = Personnel::with(['opd', 'penugasan', 'kantor'])
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nik', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('pin', 'like', '%' . $this->search . '%');
            }));

        if (!auth()->user()->hasRole('super-admin')) {
            $userOpdId = auth()->user()->opd()?->id;
            $query->where('opd_id', $userOpdId);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    #[Computed]
    public function opds()
    {
        if (auth()->user()->hasRole('super-admin')) {
            return Opd::orderBy('name')->get();
        } else {
            $userOpdId = auth()->user()->opd()?->id;
            return Opd::where('id', $userOpdId)->get();
        }
    }

    #[Computed]
    public function penugasans()
    {
        return Penugasan::orderBy('name')->get();
    }

    #[Computed]
    public function kantors()
    {
        $query = \App\Models\Kantor::orderBy('name');
        if (!auth()->user()->hasRole('super-admin')) {
            $query->where('opd_id', auth()->user()->opd()?->id);
        }
        return $query->get();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        if (!auth()->user()->hasRole('super-admin')) {
            $this->opd_id = (string) auth()->user()->opd()?->id;
        }

        // Generate Unique 6-digit PIN
        $this->pin = $this->generateUniquePin();

        $this->dispatch('open-modal', id: 'personnel-modal');
    }

    private function generateUniquePin(): string
    {
        do {
            $pin = sprintf("%06d", mt_rand(1, 999999));
        } while (Personnel::where('pin', $pin)->exists());

        return $pin;
    }

    public function resetPin(int $id): void
    {
        $item = Personnel::findOrFail($id);
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $newPin = $this->generateUniquePin();
        $item->update(['pin' => $newPin]);

        $this->dispatch('toast', type: 'success', title: 'PIN Direset', message: "PIN baru untuk {$item->name} adalah: {$newPin}");
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Personnel::findOrFail($id);

        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->personnelId = $item->id;
        $this->name = $item->name;
        $this->nik = $item->nik ?? '';
        $this->opd_id = (string) $item->opd_id;
        $this->penugasan_id = (string) $item->penugasan_id;
        $this->nomor_hp = $item->nomor_hp ?? '';
        $this->email = $item->email;
        $this->oldFoto = $item->foto;
        $this->pin = $item->pin ?? '';
        $this->face_descriptor = $item->face_descriptor ?? '';
        $this->kantor_id = (string) $item->kantor_id;
        $this->wajib_absen_di_lokasi = (bool) $item->wajib_absen_di_lokasi;
        $this->face_recognition = (bool) $item->face_recognition;

        $this->dispatch('open-modal', id: 'personnel-modal');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nik' => [
                'required', 'string', 'max:20',
                Rule::unique('personnels')->ignore($this->personnelId),
            ],
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => 'nullable|string|max:30',
            'email' => [
                $this->personnelId ? 'required' : 'nullable', 
                'email', 'max:255',
                Rule::unique('personnels')->ignore($this->personnelId),
            ],
            'foto' => $this->personnelId ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'face_descriptor' => 'nullable|string',
            'kantor_id' => 'nullable|exists:kantors,id',
            'wajib_absen_di_lokasi' => 'boolean',
            'face_recognition' => 'boolean',
            'password' => ($this->personnelId && $this->password) ? 'string|min:8|confirmed' : 'nullable',
            'pin' => [
                'required', 'string', 'digits:6',
                Rule::unique('personnels')->ignore($this->personnelId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Kolom :attribute wajib diisi.',
            'email' => 'Format email tidak valid.',
            'unique' => ':attribute sudah terdaftar.',
            'max' => 'Kolom :attribute maksimal :max karakter.',
            'min' => 'Kolom :attribute minimal :min karakter.',
            'image' => 'File harus berupa gambar.',
            'confirmed' => 'Konfirmasi password tidak cocok.',
            'digits' => 'Kolom :attribute harus berjumlah :digits digit.',
            'exists' => 'Pilihan :attribute tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama',
            'nik' => 'NIK',
            'opd_id' => 'OPD',
            'penugasan_id' => 'Penugasan',
            'nomor_hp' => 'Nomor HP',
            'email' => 'Email',
            'foto' => 'Foto',
            'password' => 'Password',
            'pin' => 'PIN',
            'kantor_id' => 'Kantor',
        ];
    }

    public function save(): void
    {
        // Intercept validation if not super admin to ensure opd_id wasn't tampered
        if (!auth()->user()->hasRole('super-admin')) {
            if ($this->opd_id != auth()->user()->opd()?->id) {
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

        // LOGIKA OTOMATISASI UNTUK PERSONEL BARU
        if (!$this->personnelId) {
            // 1. Generate Email Unik
            $baseEmail = strtolower(str_replace(' ', '', $this->name));
            $email = $baseEmail . '@trc.com';
            $counter = 1;
            while (Personnel::where('email', $email)->exists()) {
                $email = $baseEmail . $counter . '@trc.com';
                $counter++;
            }
            $data['email'] = $email;

            // 2. Generate Password Default (admintrc112_[penugasan])
            $penugasan = Penugasan::find($this->penugasan_id);
            $suffix = $penugasan ? strtolower(str_replace(' ', '', $penugasan->name)) : 'dev';
            $data['password'] = Hash::make('admintrc112_' . $suffix);
        } else {
            // Tetap gunakan email yang diinput jika sedang edit
            $data['email'] = $this->email;
            
            // Update password jika diisi saat edit
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }
        }

        if ($this->foto) {
            $data['foto'] = $this->foto->store('personnel-fotos', 'public');

            if ($this->personnelId && $this->oldFoto) {
                Storage::disk('public')->delete($this->oldFoto);
            }
        }

        if ($this->personnelId) {
            $personnel = Personnel::findOrFail($this->personnelId);
            $personnel->update($data);
        } else {
            Personnel::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'personnel-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Personnel berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $item = Personnel::findOrFail($id);
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'personnel-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Personnel::findOrFail($this->deleteId);

        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($item->foto) {
            Storage::disk('public')->delete($item->foto);
        }
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'personnel-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Personnel berhasil dihapus.');
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

    private function resetForm(): void
    {
        $this->personnelId = null;
        $this->name = '';
        $this->nik = '';
        $this->opd_id = '';
        $this->penugasan_id = '';
        $this->nomor_hp = '';
        $this->foto = null;
        $this->oldFoto = null;
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->pin = '';
        $this->face_descriptor = '';
        $this->kantor_id = '';
        $this->wajib_absen_di_lokasi = false;
        $this->face_recognition = false;
        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
