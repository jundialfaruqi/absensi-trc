<?php

namespace App\Livewire\Admin\AbsensiInput;

use App\Models\Absensi;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Kantor;
use App\Models\Personnel;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Input Absen')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public int|string|null $personnelId = null;

    public string $tanggal = '';

    public int|string|null $shiftId = null;

    public string $sesi = 'keduanya'; // masuk | pulang | keduanya

    public string $jamMasuk = '';

    public string $jamPulang = '';

    public string $statusMasuk = 'HADIR'; // HADIR | TELAT

    public string $statusPulang = 'HADIR'; // HADIR | PC

    public int|string|null $kantorId = null;

    public ?string $lat = null;

    public ?string $lng = null;

    public bool $useProfilePhoto = true;

    public $fotoMasuk = null;

    public $fotoPulang = null;

    public int $uploadIteration = 0;

    public ?string $keterangan = null;

    // Context info dari server untuk kombinasi (personnelId, tanggal)
    public string $attendanceType = 'SCHEDULED';

    public bool $needsShiftSelection = false;

    public string $activeJadwalStatus = 'NONE'; // NONE | SHIFT | OFF | FLEXIBLE

    public ?string $activeShiftName = null;

    public ?string $activeShiftStart = null;

    public ?string $activeShiftEnd = null;

    public ?array $existingSummary = null;

    public function mount(): void
    {
        if (! Auth::user()?->can('absensi-input')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $this->tanggal = Carbon::now()->format('Y-m-d');
    }

    private function scopedOpdId(): ?int
    {
        $user = Auth::user();
        if (! $user || $user->hasRole('super-admin')) {
            return null;
        }

        return $user->opd()?->id;
    }

    #[Computed]
    public function personnelsData(): array
    {
        $opdId = $this->scopedOpdId();

        return Personnel::query()
            ->with([
                'opd:id,name',
                'kantor:id,name,latitude,longitude,radius_meter',
                'devices' => fn ($q) => $q->orderByDesc('status')->orderByDesc('id'),
            ])
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->orderBy('name')
            ->get()
            ->map(function (Personnel $p) {
                $activeDevice = $p->devices->firstWhere('status', 'active') ?? $p->devices->first();
                $deviceName = $activeDevice
                    ? ($activeDevice->name ?: trim("{$activeDevice->brand} {$activeDevice->model}"))
                    : 'Mobile Personel';

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'nik' => $p->nik,
                    'opd_name' => $p->opd?->name ?? '-',
                    'attendance_type' => $p->attendance_type ?? 'SCHEDULED',
                    'kantor_id' => $p->kantor_id,
                    'kantor_name' => $p->kantor?->name,
                    'kantor_lat' => $p->kantor?->latitude !== null ? (string) $p->kantor->latitude : null,
                    'kantor_lng' => $p->kantor?->longitude !== null ? (string) $p->kantor->longitude : null,
                    'has_foto' => ! empty($p->foto),
                    'foto_url' => $p->foto ? asset('storage/'.$p->foto) : null,
                    'device_name' => $deviceName ?: 'Mobile Personel',
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function shiftsData(): array
    {
        return Shift::query()
            ->where('type', '!=', 'off')
            ->orderBy('name')
            ->get()
            ->map(fn (Shift $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'keterangan' => $s->keterangan,
                'start_time' => $s->start_time ? Carbon::parse($s->start_time)->format('H:i') : null,
                'end_time' => $s->end_time ? Carbon::parse($s->end_time)->format('H:i') : null,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function kantorsData(): array
    {
        $opdId = $this->scopedOpdId();

        return Kantor::query()
            ->where('is_active', true)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->orderBy('name')
            ->get()
            ->map(fn (Kantor $k) => [
                'id' => $k->id,
                'name' => $k->name,
                'latitude' => (string) $k->latitude,
                'longitude' => (string) $k->longitude,
                'radius_meter' => (int) $k->radius_meter,
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function toleransiMenit(): int
    {
        return (int) Setting::get('absensi_masuk_toleransi', 15);
    }

    public function selectPersonnel(?int $personnelId): void
    {
        $this->personnelId = $personnelId;
        $this->resetErrorBag();
        $this->loadTargetContext(resetLocation: true);
    }

    public function updatedTanggal(): void
    {
        $this->resetErrorBag();
        if ($this->personnelId) {
            $this->loadTargetContext(resetLocation: false);
        }
    }

    public function loadTargetContext(bool $resetLocation = false): void
    {
        if (! $this->personnelId || ! $this->tanggal) {
            $this->needsShiftSelection = false;
            $this->activeJadwalStatus = 'NONE';
            $this->activeShiftName = null;
            $this->activeShiftStart = null;
            $this->activeShiftEnd = null;
            $this->existingSummary = null;
            $this->shiftId = null;

            return;
        }

        $opdId = $this->scopedOpdId();
        $personnel = Personnel::with('kantor')
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->find($this->personnelId);

        if (! $personnel) {
            $this->personnelId = null;

            return;
        }

        $this->attendanceType = $personnel->attendance_type ?? 'SCHEDULED';

        if ($resetLocation) {
            $this->kantorId = $personnel->kantor_id;
            $this->lat = $personnel->kantor?->latitude !== null ? (string) $personnel->kantor->latitude : null;
            $this->lng = $personnel->kantor?->longitude !== null ? (string) $personnel->kantor->longitude : null;
        }

        $jadwal = Jadwal::with('shift')
            ->where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $this->tanggal)
            ->first();

        $existing = Absensi::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $this->tanggal)
            ->first();

        if ($existing) {
            $this->existingSummary = [
                'status' => $existing->status,
                'jam_masuk' => $existing->jam_masuk ? Carbon::parse($existing->jam_masuk)->format('H:i') : null,
                'status_masuk' => $existing->status_masuk,
                'jam_pulang' => $existing->jam_pulang ? Carbon::parse($existing->jam_pulang)->format('H:i') : null,
                'status_pulang' => $existing->status_pulang,
            ];
        } else {
            $this->existingSummary = null;
        }

        $isWorkingJadwal = $jadwal && $jadwal->shift && $jadwal->shift->type !== 'off';

        if ($this->attendanceType === 'FLEXIBLE') {
            $this->needsShiftSelection = false;
            $this->activeJadwalStatus = 'FLEXIBLE';
            $this->shiftId = $isWorkingJadwal ? $jadwal->shift_id : null;
            $this->activeShiftName = $isWorkingJadwal ? $jadwal->shift->name : 'Fleksibel';
            $this->activeShiftStart = $isWorkingJadwal && $jadwal->shift->start_time
                ? Carbon::parse($jadwal->shift->start_time)->format('H:i')
                : null;
            $this->activeShiftEnd = $isWorkingJadwal && $jadwal->shift->end_time
                ? Carbon::parse($jadwal->shift->end_time)->format('H:i')
                : null;
        } elseif ($isWorkingJadwal) {
            $this->needsShiftSelection = false;
            $this->activeJadwalStatus = 'SHIFT';
            $this->shiftId = $jadwal->shift_id;
            $this->activeShiftName = $jadwal->shift->name.($jadwal->shift->keterangan ? " ({$jadwal->shift->keterangan})" : '');
            $this->activeShiftStart = $jadwal->shift->start_time ? Carbon::parse($jadwal->shift->start_time)->format('H:i') : null;
            $this->activeShiftEnd = $jadwal->shift->end_time ? Carbon::parse($jadwal->shift->end_time)->format('H:i') : null;
        } else {
            // SCHEDULED tanpa jadwal atau jadwal OFF/LIBUR -> blokir sampai admin pilih shift
            $this->needsShiftSelection = true;
            $this->activeJadwalStatus = ($jadwal && $jadwal->shift && $jadwal->shift->type === 'off') ? 'OFF' : 'NONE';
            $this->shiftId = null;
            $this->activeShiftName = $jadwal?->shift?->name;
            $this->activeShiftStart = null;
            $this->activeShiftEnd = null;
        }

        // Smart default sesi & jam berdasarkan data absensi eksisting atau shift
        if ($existing && $existing->jam_masuk && ! $existing->jam_pulang) {
            $this->sesi = 'pulang';
        } elseif ($existing && ! $existing->jam_masuk && $existing->jam_pulang) {
            $this->sesi = 'masuk';
        } else {
            $this->sesi = 'keduanya';
        }

        if ($existing && $existing->jam_masuk) {
            $this->jamMasuk = Carbon::parse($existing->jam_masuk)->format('H:i');
            $this->statusMasuk = in_array($existing->status_masuk, ['HADIR', 'TELAT']) ? $existing->status_masuk : 'HADIR';
        } elseif ($this->activeShiftStart) {
            $this->jamMasuk = $this->activeShiftStart;
            $this->statusMasuk = 'HADIR';
        } else {
            $this->jamMasuk = '';
            $this->statusMasuk = 'HADIR';
        }

        if ($existing && $existing->jam_pulang) {
            $this->jamPulang = Carbon::parse($existing->jam_pulang)->format('H:i');
            $this->statusPulang = in_array($existing->status_pulang, ['HADIR', 'PC']) ? $existing->status_pulang : 'HADIR';
        } elseif ($this->activeShiftEnd) {
            $this->jamPulang = $this->activeShiftEnd;
            $this->statusPulang = 'HADIR';
        } else {
            $this->jamPulang = '';
            $this->statusPulang = 'HADIR';
        }

        $this->dispatch('absensi-context-updated', [
            'sesi' => $this->sesi,
            'shiftId' => $this->shiftId,
            'jamMasuk' => $this->jamMasuk,
            'jamPulang' => $this->jamPulang,
            'statusMasuk' => $this->statusMasuk,
            'statusPulang' => $this->statusPulang,
            'kantorId' => $this->kantorId,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);
    }

    public function updatedFotoMasuk(): void
    {
        if ($this->fotoMasuk) {
            $this->validateOnly('fotoMasuk', [
                'fotoMasuk' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:512'],
            ], [
                'fotoMasuk.image' => 'File foto masuk harus berupa gambar.',
                'fotoMasuk.mimes' => 'Format foto masuk harus JPEG, JPG, PNG, atau WebP.',
                'fotoMasuk.max' => 'Ukuran foto masuk terkompresi terlalu besar (maks. 512KB).',
            ]);
        }
    }

    public function updatedFotoPulang(): void
    {
        if ($this->fotoPulang) {
            $this->validateOnly('fotoPulang', [
                'fotoPulang' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:512'],
            ], [
                'fotoPulang.image' => 'File foto pulang harus berupa gambar.',
                'fotoPulang.mimes' => 'Format foto pulang harus JPEG, JPG, PNG, atau WebP.',
                'fotoPulang.max' => 'Ukuran foto pulang terkompresi terlalu besar (maks. 512KB).',
            ]);
        }
    }

    public function removeFoto(string $type): void
    {
        if ($type === 'masuk') {
            $this->fotoMasuk = null;
        } elseif ($type === 'pulang') {
            $this->fotoPulang = null;
        }
        $this->uploadIteration++;
    }

    public function clearUploadedPhotos(): void
    {
        $this->fotoMasuk = null;
        $this->fotoPulang = null;
        $this->uploadIteration++;
    }

    public function resetForm(): void
    {
        $this->personnelId = null;
        $this->tanggal = Carbon::now()->format('Y-m-d');
        $this->shiftId = null;
        $this->sesi = 'keduanya';
        $this->jamMasuk = '';
        $this->jamPulang = '';
        $this->statusMasuk = 'HADIR';
        $this->statusPulang = 'HADIR';
        $this->kantorId = null;
        $this->lat = null;
        $this->lng = null;
        $this->useProfilePhoto = true;
        $this->fotoMasuk = null;
        $this->fotoPulang = null;
        $this->keterangan = null;
        $this->attendanceType = 'SCHEDULED';
        $this->needsShiftSelection = false;
        $this->activeJadwalStatus = 'NONE';
        $this->activeShiftName = null;
        $this->activeShiftStart = null;
        $this->activeShiftEnd = null;
        $this->existingSummary = null;
        $this->uploadIteration++;
        $this->resetValidation();
        $this->resetErrorBag();
        $this->dispatch('absensi-input-reset');
    }

    public function save(): void
    {
        if (! Auth::user()?->can('absensi-input')) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        $rules = [
            'personnelId' => ['required', 'integer', 'exists:personnels,id'],
            'tanggal' => ['required', 'date'],
            'sesi' => ['required', 'in:masuk,pulang,keduanya'],
            'kantorId' => ['nullable', 'integer', 'exists:kantors,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];

        if (in_array($this->sesi, ['masuk', 'keduanya'])) {
            $rules['jamMasuk'] = ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'];
            $rules['statusMasuk'] = ['required', 'in:HADIR,TELAT'];
            $rules['fotoMasuk'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512'];
        }

        if (in_array($this->sesi, ['pulang', 'keduanya'])) {
            $rules['jamPulang'] = ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'];
            $rules['statusPulang'] = ['required', 'in:HADIR,PC'];
            $rules['fotoPulang'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512'];
        }

        $this->validate($rules, [
            'personnelId.required' => 'Personel wajib dipilih.',
            'tanggal.required' => 'Tanggal absensi wajib diisi.',
            'sesi.required' => 'Sesi absensi wajib dipilih.',
            'jamMasuk.required' => 'Jam masuk wajib diisi.',
            'jamMasuk.regex' => 'Format jam masuk tidak valid.',
            'jamPulang.required' => 'Jam pulang wajib diisi.',
            'jamPulang.regex' => 'Format jam pulang tidak valid.',
        ]);

        $personnel = Personnel::with(['kantor', 'devices'])->findOrFail($this->personnelId);

        $opdId = $this->scopedOpdId();
        if ($opdId && $personnel->opd_id !== $opdId) {
            throw ValidationException::withMessages([
                'personnelId' => 'Anda tidak memiliki akses untuk menginput absensi personel dari OPD lain.',
            ]);
        }

        $targetDate = Carbon::parse($this->tanggal)->format('Y-m-d');

        // Resolusi Jadwal & Shift (blokir jika SCHEDULED tanpa jadwal/OFF dan belum pilih shift)
        $jadwal = Jadwal::with('shift')
            ->where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $targetDate)
            ->first();

        $shift = ($jadwal && $jadwal->shift && $jadwal->shift->type !== 'off') ? $jadwal->shift : null;

        if (($personnel->attendance_type ?? 'SCHEDULED') !== 'FLEXIBLE') {
            if (! $shift) {
                if (! $this->shiftId) {
                    throw ValidationException::withMessages([
                        'shiftId' => 'Personel tidak memiliki jadwal shift aktif pada tanggal ini. Silakan pilih shift terlebih dahulu.',
                    ]);
                }

                $selectedShift = Shift::where('id', $this->shiftId)
                    ->where('type', '!=', 'off')
                    ->first();

                if (! $selectedShift) {
                    throw ValidationException::withMessages([
                        'shiftId' => 'Shift yang dipilih tidak valid atau merupakan shift libur.',
                    ]);
                }

                $shift = $selectedShift;
                $jadwal = Jadwal::updateOrCreate(
                    ['personnel_id' => $personnel->id, 'tanggal' => $targetDate],
                    [
                        'shift_id' => $shift->id,
                        'status' => 'SHIFT',
                        'is_manual' => true,
                    ]
                );
            }
        }

        // Resolusi Lokasi Kantor & Koordinat
        $kantor = $this->kantorId
            ? Kantor::when($opdId, fn ($q) => $q->where('opd_id', $opdId))->find($this->kantorId)
            : $personnel->kantor;

        $resolvedLat = ($this->lat !== null && $this->lat !== '')
            ? (float) $this->lat
            : ($kantor?->latitude !== null ? (float) $kantor->latitude : null);

        $resolvedLng = ($this->lng !== null && $this->lng !== '')
            ? (float) $this->lng
            : ($kantor?->longitude !== null ? (float) $kantor->longitude : null);

        $isWithinRadius = null;
        $jarakMeter = null;
        if ($kantor && $resolvedLat !== null && $resolvedLng !== null) {
            $jarak = $kantor->hitungJarak($resolvedLat, $resolvedLng);
            $jarakMeter = (int) round($jarak);
            $isWithinRadius = $jarak <= $kantor->radius_meter;
        }

        // Resolusi Device Terdaftar Personel (sama persis seperti real mobile upload)
        $registeredDevice = Device::where('personnel_id', $personnel->id)
            ->where('status', 'active')
            ->latest()
            ->first()
            ?? Device::where('personnel_id', $personnel->id)->latest()->first();

        $platform = 'android';
        $deviceName = $registeredDevice
            ? ($registeredDevice->name ?: trim("{$registeredDevice->brand} {$registeredDevice->model}"))
            : 'Mobile Personel';
        if (empty($deviceName)) {
            $deviceName = 'Mobile Personel';
        }
        $uniqueDeviceId = $registeredDevice
            ? (string) $registeredDevice->id
            : ('personnel-'.$personnel->id);

        DB::transaction(function () use (
            $personnel,
            $targetDate,
            $jadwal,
            $kantor,
            $resolvedLat,
            $resolvedLng,
            $isWithinRadius,
            $jarakMeter,
            $platform,
            $deviceName,
            $uniqueDeviceId
        ) {
            $absensi = Absensi::where('personnel_id', $personnel->id)
                ->whereDate('tanggal', $targetDate)
                ->first();

            if (! $absensi) {
                $absensi = new Absensi([
                    'personnel_id' => $personnel->id,
                    'tanggal' => $targetDate,
                ]);
            }

            $absensi->jadwal_id = $jadwal?->id;
            $absensi->status = 'HADIR';
            $absensi->is_location_anomaly = false;
            $absensi->anomaly_reason = null;

            if ($this->keterangan !== null && $this->keterangan !== '') {
                $absensi->keterangan = $this->keterangan;
            }

            if (in_array($this->sesi, ['masuk', 'keduanya'])) {
                $formattedMasuk = Carbon::parse($this->jamMasuk)->format('H:i:s');
                $fotoMasukPath = $this->resolveAndStorePhoto($this->fotoMasuk, $personnel, $targetDate, 'in');

                $absensi->kantor_id = $kantor?->id;
                $absensi->jam_masuk = $formattedMasuk;
                $absensi->status_masuk = $this->statusMasuk;
                if ($fotoMasukPath !== null) {
                    $absensi->foto_masuk = $fotoMasukPath;
                }
                $absensi->lat_masuk = $resolvedLat;
                $absensi->lng_masuk = $resolvedLng;
                $absensi->is_within_radius = $isWithinRadius;
                $absensi->jarak_meter = $jarakMeter;
                $absensi->platform_masuk = $platform;
                $absensi->device_name_masuk = $deviceName;
                $absensi->unique_device_id_masuk = $uniqueDeviceId;

                // Bersihkan placeholder status_pulang jika sebelumnya hanya placeholder ALPA/LIBUR/OFF tanpa jam pulang
                if ($this->sesi === 'masuk' && empty($absensi->jam_pulang) && in_array($absensi->status_pulang, ['ALPA', 'LIBUR', 'OFF'])) {
                    $absensi->status_pulang = null;
                }
            }

            if (in_array($this->sesi, ['pulang', 'keduanya'])) {
                $formattedPulang = Carbon::parse($this->jamPulang)->format('H:i:s');
                $fotoPulangPath = $this->resolveAndStorePhoto($this->fotoPulang, $personnel, $targetDate, 'out');

                // Jika langsung pulang tanpa jam_masuk (direct check-out), tandai status_masuk ALPA seperti mobile
                if ($this->sesi === 'pulang' && empty($absensi->jam_masuk) && (empty($absensi->status_masuk) || in_array($absensi->status_masuk, ['LIBUR', 'OFF']))) {
                    $absensi->status_masuk = 'ALPA';
                }

                $absensi->kantor_id_pulang = $kantor?->id;
                $absensi->jam_pulang = $formattedPulang;
                $absensi->status_pulang = $this->statusPulang;
                if ($fotoPulangPath !== null) {
                    $absensi->foto_pulang = $fotoPulangPath;
                }
                $absensi->lat_pulang = $resolvedLat;
                $absensi->lng_pulang = $resolvedLng;
                $absensi->is_within_radius_pulang = $isWithinRadius;
                $absensi->jarak_meter_pulang = $jarakMeter;
                $absensi->platform_pulang = $platform;
                $absensi->device_name_pulang = $deviceName;
                $absensi->unique_device_id_pulang = $uniqueDeviceId;
            }

            $absensi->save();
        });

        $savedName = $personnel->name;
        $savedDate = Carbon::parse($targetDate)->translatedFormat('d F Y');

        $this->resetForm();

        $this->dispatch('toast', type: 'success', message: "Absensi {$savedName} tanggal {$savedDate} berhasil disimpan.");
    }

    private function resolveAndStorePhoto($uploadedFile, Personnel $personnel, string $targetDate, string $prefix): ?string
    {
        $folderPath = 'absensi/'.$targetDate;
        $baseName = $prefix.'_'.$personnel->id.'_'.time().'_'.Str::random(8);

        if (! $this->useProfilePhoto && ($uploadedFile instanceof UploadedFile || (is_object($uploadedFile) && method_exists($uploadedFile, 'storeAs')))) {
            $ext = strtolower($uploadedFile->getClientOriginalExtension() ?: 'webp');
            $stored = $uploadedFile->storeAs($folderPath, "{$baseName}.{$ext}", 'public');

            return $stored ?: null;
        }

        if ($this->useProfilePhoto && ! empty($personnel->foto) && Storage::disk('public')->exists($personnel->foto)) {
            $rawBytes = Storage::disk('public')->get($personnel->foto);
            if (! $rawBytes) {
                return null;
            }

            $gdImg = @imagecreatefromstring($rawBytes);
            if ($gdImg) {
                ob_start();
                imagejpeg($gdImg, null, 80);
                $cleanJpeg = ob_get_clean();
                imagedestroy($gdImg);

                if ($cleanJpeg) {
                    $fileName = "{$folderPath}/{$baseName}.jpg";
                    Storage::disk('public')->put($fileName, $cleanJpeg);

                    return $fileName;
                }
            }

            $origExt = strtolower(pathinfo($personnel->foto, PATHINFO_EXTENSION) ?: 'jpg');
            $fileName = "{$folderPath}/{$baseName}.{$origExt}";
            Storage::disk('public')->put($fileName, $rawBytes);

            return $fileName;
        }

        return null;
    }
};
