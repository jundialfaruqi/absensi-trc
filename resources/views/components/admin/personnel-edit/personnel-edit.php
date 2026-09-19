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
use App\Events\PersonnelVectorUpdated;
use App\Events\FaceVerificationProcessed;

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

    public $foto_front;
    public $foto_right;
    public $foto_left;
    public $foto_up;
    public string $descriptor_front = '';
    public string $descriptor_right = '';
    public string $descriptor_left = '';
    public string $descriptor_up = '';
    public bool $has_3d_faces = false;
    public array $existing_3d_poses = [];
    public array $existing_3d_photos = [];
    public int $total_adaptations = 0;
    public bool $has_adaptive_biometrics = false;

    public string $face_verification_status = 'UNREGISTERED';
    public ?string $face_verification_notes = null;
    public ?string $face_verified_at = null;
    public ?string $face_verified_by_name = null;
    public string $reject_reason = '';
    public bool $showRejectModal = false;

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
        $embeddings = $item->faceEmbeddings()->get()->keyBy('pose_type');
        $this->existing_3d_poses = $embeddings->keys()->toArray();
        $this->has_3d_faces = count($this->existing_3d_poses) >= 4;
        $this->total_adaptations = (int) $embeddings->sum('adaptation_count');
        $this->has_adaptive_biometrics = $embeddings->whereNotNull('adaptive_descriptor_mobile')->isNotEmpty();

        $this->existing_3d_photos = [];
        foreach (['FRONT', 'RIGHT', 'LEFT', 'UP'] as $pose) {
            if (isset($embeddings[$pose])) {
                $emb = $embeddings[$pose];
                $this->existing_3d_photos[$pose] = [
                    'foto' => $emb->foto ? Storage::url($emb->foto) : null,
                    'has_128d' => !empty($emb->face_descriptor),
                    'has_192d' => !empty($emb->face_descriptor_mobile),
                    'has_adaptive' => !empty($emb->adaptive_descriptor_mobile),
                    'adaptation_count' => (int) $emb->adaptation_count,
                ];
            } else {
                $this->existing_3d_photos[$pose] = null;
            }
        }

        $this->kantor_id = (string) $item->kantor_id;
        $this->wajib_absen_di_lokasi = (bool) $item->wajib_absen_di_lokasi;
        $this->face_recognition = (bool) $item->face_recognition;
        $this->attendance_type = (string) $item->attendance_type;

        $this->face_verification_status = $item->face_verification_status ?? 'UNREGISTERED';
        $this->face_verification_notes = $item->face_verification_notes;
        $this->face_verified_at = $item->face_verified_at ? $item->face_verified_at->format('d/m/Y H:i') : null;
        $this->face_verified_by_name = $item->verifier?->name;

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
                'digits:16',
                Rule::unique('personnels', 'nik')->ignore($this->personnelId),
            ],
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => [
                'required',
                'string',
                'digits_between:10,13',
                Rule::unique('personnels', 'nomor_hp')->ignore($this->personnelId),
            ],
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

        if ($this->has_3d_faces && ($this->foto_front || $this->descriptor_front)) {
            $poses = [
                'FRONT' => ['foto' => $this->foto_front, 'desc' => $this->descriptor_front],
                'RIGHT' => ['foto' => $this->foto_right, 'desc' => $this->descriptor_right],
                'LEFT'  => ['foto' => $this->foto_left,  'desc' => $this->descriptor_left],
                'UP'    => ['foto' => $this->foto_up,    'desc' => $this->descriptor_up],
            ];

            foreach ($poses as $poseType => $dataPose) {
                $embedding = $personnel->faceEmbeddings()->firstOrNew(['pose_type' => $poseType]);
                if (!empty($dataPose['foto'])) {
                    if ($embedding->foto) {
                        Storage::disk('public')->delete($embedding->foto);
                    }
                    $embedding->foto = $dataPose['foto']->store('personnel-fotos/poses', 'public');
                    $embedding->face_descriptor_mobile = null;
                }
                if (!empty($dataPose['desc'])) {
                    $embedding->face_descriptor = $dataPose['desc'];
                }
                $embedding->save();
            }
        }

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

        $this->redirect(route('personnel'), navigate: true);
    }

    public function deleteFaceData(): void
    {
        $personnel = Personnel::findOrFail($this->personnelId);

        if (! $this->canEditPersonnel($personnel)) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data personel ini.');
        }

        // 1. Hapus seluruh berkas foto pose 3D jika ada di storage
        if ($personnel->faceEmbeddings) {
            foreach ($personnel->faceEmbeddings as $embedding) {
                if ($embedding->foto && Storage::disk('public')->exists($embedding->foto)) {
                    Storage::disk('public')->delete($embedding->foto);
                }
            }
            $personnel->faceEmbeddings()->delete();
        }

        // 2. Hapus berkas foto utama jika ada di storage
        if ($personnel->foto && Storage::disk('public')->exists($personnel->foto)) {
            Storage::disk('public')->delete($personnel->foto);
        }

        // 3. Reset kolom biometrik di database
        $personnel->update([
            'foto' => null,
            'face_descriptor' => null,
            'face_descriptor_mobile' => null,
            'face_recognition' => false,
            'face_verification_status' => 'UNREGISTERED',
            'face_verification_notes' => null,
            'face_verified_at' => null,
            'face_verified_by' => null,
        ]);

        // 4. Reset properti Livewire
        $this->oldFoto = null;
        $this->foto = null;
        $this->face_descriptor = '';
        $this->face_descriptor_mobile = '';
        $this->descriptor_front = '';
        $this->descriptor_right = '';
        $this->descriptor_left = '';
        $this->descriptor_up = '';
        $this->foto_front = null;
        $this->foto_right = null;
        $this->foto_left = null;
        $this->foto_up = null;
        $this->has_3d_faces = false;
        $this->existing_3d_poses = [];
        $this->existing_3d_photos = [];
        $this->total_adaptations = 0;
        $this->has_adaptive_biometrics = false;
        $this->face_recognition = false;
        $this->face_verification_status = 'UNREGISTERED';
        $this->face_verification_notes = null;
        $this->face_verified_at = null;
        $this->face_verified_by_name = null;

        // 5. Broadcast event real-time sinkronisasi
        PersonnelVectorUpdated::dispatch(
            $personnel->id,
            $personnel->opd_id,
            'deleted'
        );

        $this->dispatch('face-data-deleted');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Data Wajah Dihapus',
            'message' => 'Seluruh data biometrik wajah (128D, 192D, 3D poses) dan foto personel berhasil dihapus.',
        ]);
    }

    public function approveFaceVerification(): void
    {
        $personnel = Personnel::findOrFail($this->personnelId);

        if (! $this->canEditPersonnel($personnel)) {
            abort(403, 'Anda tidak memiliki izin untuk memverifikasi data personel ini.');
        }

        /** @var User|null $user */
        $user = $this->user();

        $personnel->face_verification_status = 'APPROVED';
        $personnel->face_verification_notes = null;
        $personnel->face_recognition = true;
        $personnel->face_verified_at = now();
        $personnel->face_verified_by = $user?->id;
        $personnel->save();

        $this->face_verification_status = 'APPROVED';
        $this->face_verification_notes = null;
        $this->face_recognition = true;
        $this->face_verified_at = $personnel->face_verified_at->format('d/m/Y H:i');
        $this->face_verified_by_name = $user?->name;

        // Broadcast Real-Time Reverb
        FaceVerificationProcessed::dispatch(
            $personnel->id,
            'APPROVED',
            null,
            $personnel->face_verified_at->toISOString()
        );
        PersonnelVectorUpdated::dispatch(
            $personnel->id,
            $personnel->opd_id,
            'ready'
        );

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Verifikasi Wajah Disetujui',
            'message' => "Verifikasi wajah {$personnel->name} telah berhasil disetujui. Personel sekarang dapat melakukan absensi di aplikasi mobile.",
        ]);
    }

    public function rejectFaceVerification(): void
    {
        $personnel = Personnel::findOrFail($this->personnelId);

        if (! $this->canEditPersonnel($personnel)) {
            abort(403, 'Anda tidak memiliki izin untuk memverifikasi data personel ini.');
        }

        $this->validate([
            'reject_reason' => 'required|string|min:3|max:500',
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi.',
            'reject_reason.min' => 'Alasan penolakan minimal 3 karakter.',
            'reject_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        /** @var User|null $user */
        $user = $this->user();
        $reason = trim($this->reject_reason);

        $personnel->face_verification_status = 'REJECTED';
        $personnel->face_verification_notes = $reason;
        $personnel->face_recognition = false;
        $personnel->face_verified_at = now();
        $personnel->face_verified_by = $user?->id;
        $personnel->save();

        $this->face_verification_status = 'REJECTED';
        $this->face_verification_notes = $reason;
        $this->face_recognition = false;
        $this->face_verified_at = $personnel->face_verified_at->format('d/m/Y H:i');
        $this->face_verified_by_name = $user?->name;
        $this->showRejectModal = false;
        $this->reject_reason = '';

        // Broadcast Real-Time Reverb
        FaceVerificationProcessed::dispatch(
            $personnel->id,
            'REJECTED',
            $reason,
            $personnel->face_verified_at->toISOString()
        );

        $this->dispatch('toast', [
            'type' => 'warning',
            'title' => 'Verifikasi Wajah Ditolak',
            'message' => "Verifikasi biometrik wajah {$personnel->name} ditolak. Personel diminta untuk mengulangi perekaman wajah.",
        ]);
    }

    public function resetAdaptiveBiometrics(\App\Services\AdaptiveFaceLearningService $service): void
    {
        $item = Personnel::findOrFail($this->personnelId);
        $service->resetToMaster($item);
        $this->loadPersonnelData($this->personnelId);

        $this->dispatch('set-pending-toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Template adaptif wajah berhasil direset ke Master Anchor asli.',
        ]);
    }
};
