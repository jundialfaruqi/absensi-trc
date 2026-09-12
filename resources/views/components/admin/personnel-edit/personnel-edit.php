<?php

namespace App\Livewire\Admin\PersonnelEdit;

use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Events\PersonnelPhotoUpdated;

new #[Title('Edit Personnel')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public int $personnelId;

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

    public string $face_descriptor_mobile = '';

    public string $kantor_id = '';

    public bool $wajib_absen_di_lokasi = false;

    public bool $face_recognition = false;

    public string $attendance_type = 'SCHEDULED';

    public bool $auto_create_device = false;

    public bool $has_personal_device = false;

    public string $existing_device_name = '';

    public bool $readyToLoad = false;

    /**
     * @return User|null
     */
    private function user(): ?User
    {
        /** @var User|null */
        return Auth::user();
    }

    public function load()
    {
        $this->readyToLoad = true;
    }

    public function mount(int $id)
    {
        $this->loadPersonnelData($id);
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
        /** @var User|null $user */
        $user = $this->user();
        if ($user && ($user->hasRole('super-admin') || $user->can('edit-personel-all-opd'))) {
            return Opd::query()->orderBy('name', 'asc')->get(['*']);
        } else {
            $userOpdId = $user?->opd()?->id;

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
        /** @var User|null $user */
        $user = $this->user();
        $query = Kantor::query()->orderBy('name', 'asc');
        if (! $user || (! $user->hasRole('super-admin') && ! $user->can('edit-personel-all-opd'))) {
            $query->where('opd_id', '=', $user?->opd()?->id);
        }

        return $query->get(['*']);
    }

    private function canEditPersonnel(?Personnel $personnel): bool
    {
        if (! $personnel) {
            return false;
        }

        /** @var User|null $user */
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // 1. edit-personel-all-opd atau role super-admin
        if ($user->hasRole('super-admin') || $user->can('edit-personel-all-opd')) {
            return true;
        }

        // 2. edit-personel-opd: hanya bisa jika se-OPD
        if ($user->can('edit-personel-opd')) {
            $userOpdId = $user->opd()?->id;

            return ! empty($userOpdId) && ! empty($personnel->opd_id) && (int) $personnel->opd_id === (int) $userOpdId;
        }

        return false;
    }

    private function loadPersonnelData(int $id): void
    {
        $item = Personnel::findOrFail($id);

        if (! $this->canEditPersonnel($item)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data personel ini.');
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
        $this->face_descriptor_mobile = $item->face_descriptor_mobile ?? '';
        $this->kantor_id = (string) $item->kantor_id;
        $this->wajib_absen_di_lokasi = (bool) $item->wajib_absen_di_lokasi;
        $this->face_recognition = (bool) $item->face_recognition;
        $this->attendance_type = (string) $item->attendance_type;

        $device = Device::where('personnel_id', $item->id)->first();
        if ($device) {
            $this->has_personal_device = true;
            $this->existing_device_name = $device->name;
        }
    }

    public function regeneratePin(): void
    {
        $this->pin = $this->generateUniquePin();
    }

    private function generateUniquePin(): string
    {
        do {
            $pin = sprintf('%06d', mt_rand(1, 999999));
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
                Rule::unique('personnels', 'nik')->ignore($this->personnelId),
            ],
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => 'nullable|string|max:13',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('personnels', 'email')->ignore($this->personnelId),
            ],
            'password' => $this->password ? 'nullable|min:8|confirmed' : 'nullable',
            'pin' => [
                'required',
                'string',
                'size:6',
                Rule::unique('personnels', 'pin')->ignore($this->personnelId),
            ],
            'foto' => 'nullable|image|max:2048', // Max 2MB
            'face_descriptor' => 'nullable|string',
            'kantor_id' => 'nullable|exists:kantors,id',
            'attendance_type' => 'required|in:SCHEDULED,FLEXIBLE',
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
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute minimal :min karakter.',
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
            'email' => 'Email',
            'foto' => 'Foto',
            'password' => 'Password',
            'pin' => 'PIN',
            'kantor_id' => 'Kantor',
        ];
    }

    public function updatedFoto()
    {
        if ($this->foto) {
            try {
                $mimeType = $this->foto->getMimeType();
                if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
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

    public function saveFotoDirectly(?string $descriptor = null): void
    {
        $personnel = Personnel::findOrFail($this->personnelId);

        if (! $this->canEditPersonnel($personnel)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data personel ini.');
        }

        if ($this->foto) {
            $path = $this->foto->store('personnel-fotos', 'public');

            if ($this->oldFoto && Storage::disk('public')->exists($this->oldFoto)) {
                Storage::disk('public')->delete($this->oldFoto);
            }

            $updateData = [
                'foto' => $path,
                'face_descriptor_mobile' => null,
            ];

            if ($descriptor) {
                $updateData['face_descriptor'] = $descriptor;
                $this->face_descriptor = $descriptor;
            }

            $personnel->update($updateData);

            PersonnelPhotoUpdated::dispatch(
                $personnel->id,
                $personnel->opd_id,
                $personnel->name,
                asset('storage/' . $path)
            );

            $this->oldFoto = $path;
            $this->reset('foto');
            $this->face_descriptor_mobile = '';

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Foto Tersimpan',
                'message' => 'Foto autentikasi berhasil disimpan ke database.',
            ]);
        }
    }

    public function save()
    {
        $personnel = Personnel::findOrFail($this->personnelId);

        if (! $this->canEditPersonnel($personnel)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data personel ini.');
        }

        /** @var User|null $user */
        $user = $this->user();
        $canEditAll = $user && ($user->hasRole('super-admin') || $user->can('edit-personel-all-opd'));

        if (! $canEditAll) {
            $userOpdId = $user?->opd()?->id;
            if (empty($userOpdId) || (int) $this->opd_id !== (int) $userOpdId) {
                abort(403, 'Anda tidak dapat memindahkan personel ke OPD lain.');
            }
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'nik' => $this->nik ?: null,
            'opd_id' => $this->opd_id,
            'penugasan_id' => $this->penugasan_id,
            'nomor_hp' => $this->nomor_hp,
            'email' => $this->email,
            'pin' => $this->pin,
            'face_descriptor' => $this->face_descriptor ?: null,
            'kantor_id' => $this->kantor_id ?: null,
            'wajib_absen_di_lokasi' => $this->wajib_absen_di_lokasi,
            'face_recognition' => $this->face_recognition,
            'attendance_type' => $this->attendance_type,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->foto) {
            $data['foto'] = $this->foto->store('personnel-fotos', 'public');
            $data['face_descriptor_mobile'] = null;

            if ($this->oldFoto) {
                Storage::disk('public')->delete($this->oldFoto);
            }
        }

        $personnel = Personnel::findOrFail($this->personnelId);
        $personnel->update($data);

        if (isset($data['foto'])) {
            PersonnelPhotoUpdated::dispatch(
                $personnel->id,
                $personnel->opd_id,
                $personnel->name,
                asset('storage/' . $data['foto'])
            );
        }

        $licenseMsg = '';
        if ($this->auto_create_device) {
            $licenseKey = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
            Device::create([
                'opd_id' => $personnel->opd_id,
                'personnel_id' => $personnel->id,
                'name' => 'HP Personal - '.$personnel->name,
                'license_key' => $licenseKey,
                'status' => 'inactive',
            ]);
            $licenseMsg = ' | License Key: '.$licenseKey;
        }

        $this->dispatch('set-pending-toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Personnel berhasil diperbarui.'.$licenseMsg,
        ]);

        return $this->redirectRoute('personnel', [], true, true);
    }
};
