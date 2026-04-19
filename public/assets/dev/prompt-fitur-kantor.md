# Prompt AI Agent — Fitur Manajemen Kantor & Validasi Lokasi Absensi

## Konteks Sistem

Saya memiliki sistem absensi berbasis **Laravel + Livewire 4** (versi terbaru). Stack yang digunakan:

- **Laravel 12**
- **Livewire 4** — sintaks `new #[Layout(...)] class extends Component` (anonymous class, bukan class terpisah)
- **UI: DaisyUI yang sudah dikustomisasi sendiri** — bukan Filament, bukan Blade component pihak ketiga
- **Dua layout berbeda:**
  - Layout absensi (kiosk personel): `layouts.absensi.app`
  - Layout admin (panel manajemen): `layouts::admin.app`

### Konvensi Attribute Livewire yang Wajib Diikuti

**Halaman absensi (personel):**
```php
new #[Layout('layouts.absensi.app')] class extends Component
```

**Halaman admin (CRUD, manajemen):**
```php
new #[Title('Nama Halaman')] #[Layout('layouts::admin.app')] class extends Component
```

> **Penting:** Sebelum menulis kode apapun, baca terlebih dahulu file view yang sudah ada di proyek untuk mengetahui class DaisyUI apa yang digunakan, struktur blade yang dipakai, dan konvensi penamaan property Livewire. Ikuti semua konvensi tersebut secara konsisten.

---

### Referensi Kode yang Sudah Berjalan

Berikut adalah kode Livewire component absensi yang sudah ada — jadikan **acuan konvensi kode, alur logika, dan struktur property**:

```php
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.absensi.app')] class extends Component
{
    // Stepper state: 1: Selection, 2: PIN, 3: Action (In/Out), 4: Result
    public int $step = 1;

    // Step 1: Selection
    public string $search = '';
    public ?int $selectedPersonnelId = null;
    public ?Personnel $selectedPersonnel = null;

    // Step 2: PIN
    public string $pin = '';

    // Step 3: Action state
    public ?Jadwal $activeJadwal = null;
    public ?Absensi $activeAbsensi = null;
    public string $activeDate = '';

    // Step 4: Result
    public bool $isSuccess = false;
    public string $message = '';
    public ?Absensi $lastAbsensi = null;

    // GPS & Image Data (Sent from Client)
    public string $lat = '';
    public string $lng = '';
    public string $imageData = ''; // Base64 selfie

    public function mount()
    {
        $this->resetErrorBag();
    }

    public function selectPersonnel(int $id)   { ... }
    public function verifyPin()                { ... }
    public function prepareActionStep()        { ... }
    public function submitAttendance(string $type, ...) { ... }
    public function resetForm()                { ... }
};
```

---

## BAGIAN 1 — Migration Database

### 1a. Buat migration baru: `create_kantors_table`

```
php artisan make:migration create_kantors_table
```

Kolom yang harus ada:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint unsigned | Primary key, auto increment |
| `opd_id` | bigint unsigned | FK → `opds.id`, `onDelete('cascade')` |
| `name` | varchar(255) | Nama kantor |
| `alamat` | text | Nullable |
| `latitude` | decimal(10,8) | Koordinat GPS |
| `longitude` | decimal(11,8) | Koordinat GPS |
| `radius_meter` | integer | Default: `100` |
| `is_active` | boolean | Default: `true` |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

---

### 1b. Buat migration: `add_kantor_fields_to_personnels_table`

Tambahkan kolom berikut ke tabel `personnels`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `kantor_id` | bigint unsigned, nullable | FK → `kantors.id`, `onDelete('set null')` |
| `wajib_absen_di_lokasi` | boolean | Default: `false` |

---

### 1c. Buat migration: `add_kantor_fields_to_absensis_table`

Tambahkan kolom berikut ke tabel `absensis`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `kantor_id` | bigint unsigned, nullable | FK → `kantors.id`, `onDelete('set null')` — snapshot kantor saat absen |
| `is_within_radius` | boolean, nullable | True jika posisi absen dalam radius kantor |
| `jarak_meter` | integer, nullable | Jarak aktual dalam meter saat absen dilakukan |

> **Catatan:** Gunakan `onDelete('set null')` — data absensi historis tidak boleh hilang jika kantor dihapus di kemudian hari.

---

## BAGIAN 2 — Model & Relasi

### 2a. Buat Model `Kantor` — `app/Models/Kantor.php`

```php
protected $fillable = [
    'opd_id', 'name', 'alamat', 'latitude', 'longitude',
    'radius_meter', 'is_active',
];

// Relasi
public function opd(): BelongsTo         // → App\Models\Opd
public function personnels(): HasMany     // foreign key: kantor_id
public function absensis(): HasMany      // foreign key: kantor_id

// Method helper
public function hitungJarak(float $lat, float $lng): float
// Gunakan formula Haversine. Kembalikan jarak dalam meter (float).

public function isInRadius(float $lat, float $lng): bool
// Return true jika hitungJarak() <= $this->radius_meter
```

**Formula Haversine yang harus digunakan:**
```php
$R = 6371000; // radius bumi dalam meter
$φ1 = deg2rad($this->latitude);
$φ2 = deg2rad($lat);
$Δφ = deg2rad($lat - $this->latitude);
$Δλ = deg2rad($lng - $this->longitude);
$a = sin($Δφ/2) * sin($Δφ/2) +
     cos($φ1) * cos($φ2) *
     sin($Δλ/2) * sin($Δλ/2);
$c = 2 * atan2(sqrt($a), sqrt(1-$a));
return $R * $c;
```

---

### 2b. Update Model `Personnel` — `app/Models/Personnel.php`

- Tambahkan `'kantor_id'` dan `'wajib_absen_di_lokasi'` ke array `$fillable`
- Tambahkan relasi:

```php
public function kantor(): BelongsTo  // → App\Models\Kantor, nullable
```

---

### 2c. Update Model `Absensi` — `app/Models/Absensi.php`

- Tambahkan `'kantor_id'`, `'is_within_radius'`, `'jarak_meter'` ke array `$fillable`
- Tambahkan relasi:

```php
public function kantor(): BelongsTo  // → App\Models\Kantor, nullable
```

---

## BAGIAN 3 — Service Class

Buat file: `app/Services/AbsensiLokasiService.php`

### Method utama:

```php
public function validasiLokasi(Personnel $personnel, float $lat, float $lng): array
```

### Tabel logika yang harus diikuti persis:

| Kondisi | `boleh` | `is_within_radius` | `jarak_meter` | `kantor_id` |
|---|---|---|---|---|
| `kantor_id` personnel = null | `true` | `null` | `null` | `null` |
| Punya kantor, `wajib_absen_di_lokasi = false` | `true` | hasil cek | jarak aktual | id kantor |
| Punya kantor, wajib, **dalam radius** | `true` | `true` | jarak aktual | id kantor |
| Punya kantor, wajib, **luar radius** | `false` | `false` | jarak aktual | id kantor |

### Format return array:

```php
return [
    'boleh'            => bool,
    'is_within_radius' => bool|null,
    'jarak_meter'      => int|null,    // bulatkan ke integer
    'kantor_id'        => int|null,
    'kantor_name'      => string|null, // nama kantor untuk ditampilkan di UI
    'pesan'            => string,
    // Contoh pesan error:
    // "Anda berada 350m dari kantor PUSAT. Maksimal radius adalah 100m."
    // Jika boleh = true, isi pesan dengan string kosong ''
];
```

---

## BAGIAN 4 — Update Livewire Component Absensi

> **Penting:** Jangan ubah logika yang sudah ada (night shift, status TELAT/PC, pengecekan jadwal, dll). Hanya tambahkan integrasi validasi lokasi di posisi yang tepat berikut ini.

### 4a. Tambahkan property baru di class:

```php
public array $infoLokasi = [];
```

### 4b. Tambahkan method baru untuk menerima GPS dari client:

```php
public function terimaCoordsLokasi(float $lat, float $lng): void
{
    $this->lat = (string) $lat;
    $this->lng = (string) $lng;

    if ($this->selectedPersonnel && $this->step === 3) {
        $service = app(\App\Services\AbsensiLokasiService::class);
        $this->infoLokasi = $service->validasiLokasi(
            $this->selectedPersonnel,
            $lat,
            $lng
        );
    }
}
```

### 4c. Tambahkan validasi lokasi di `submitAttendance()`

Sisipkan blok berikut **setelah** update `$this->lat`, `$this->lng`, `$this->imageData` dari client, dan **sebelum** blok `try {...}`:

```php
// Validasi lokasi kantor
$lokasiService = app(\App\Services\AbsensiLokasiService::class);
$lokasiResult = $lokasiService->validasiLokasi(
    $this->selectedPersonnel,
    (float) $this->lat,
    (float) $this->lng
);

if (!$lokasiResult['boleh']) {
    $this->isSuccess = false;
    $this->message = $lokasiResult['pesan'];
    $this->step = 4;
    return;
}
```

### 4d. Tambahkan kolom lokasi ke query database

**Untuk absen MASUK** — tambahkan di dalam array `Absensi::create([...])`:
```php
'kantor_id'        => $lokasiResult['kantor_id'],
'is_within_radius' => $lokasiResult['is_within_radius'],
'jarak_meter'      => $lokasiResult['jarak_meter'],
```

**Untuk absen PULANG** — tambahkan di dalam array `$this->activeAbsensi->update([...])`:
```php
'kantor_id'        => $lokasiResult['kantor_id'],
'is_within_radius' => $lokasiResult['is_within_radius'],
'jarak_meter'      => $lokasiResult['jarak_meter'],
```

---

## BAGIAN 5 — CRUD Kantor (Halaman Admin)

### File yang harus dibuat:

- **Component:** `app/Livewire/Admin/Kantors.php`
- **View:** `resources/views/livewire/admin/kantors.blade.php`

### Attribute Livewire yang wajib digunakan:

```php
new #[Title('Manajemen Kantor')] #[Layout('layouts::admin.app')] class extends Component
```

### Fitur tabel daftar kantor:

- Kolom: Nama Kantor, OPD, Radius (meter), Jumlah Personel, Status (badge Active/Inactive), Aksi (Edit, Hapus)
- Filter by OPD (select dropdown)
- Search by nama kantor (`wire:model.live`)
- Paginasi

### Fitur Modal Tambah/Edit (DaisyUI `modal` + `dialog`):

**Field form:**

| Field | Komponen DaisyUI | Validasi |
|---|---|---|
| Nama Kantor | `input input-bordered` | required |
| Pilih OPD | `select select-bordered` | required |
| Alamat | `textarea textarea-bordered` | nullable |
| Radius (meter) | `input input-bordered` type number | required, min 50, max 10000 |
| Status Aktif | `toggle` | — |
| Latitude | `input input-bordered` readonly | diisi otomatis dari peta |
| Longitude | `input input-bordered` readonly | diisi otomatis dari peta |

**Peta Leaflet.js — load hanya di halaman ini:**
```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
```

**Behavior peta yang harus diimplementasikan:**
- Default view: Indonesia tengah (lat: `-2.5`, lng: `118`, zoom: `5`)
- Klik pada peta → marker muncul → field latitude & longitude terisi otomatis via `@this.set('latitude', ...)` dan `@this.set('longitude', ...)`
- Tampilkan `L.circle()` sebagai visualisasi radius yang berubah ukuran realtime saat input radius diubah
- Jika mode edit dan data sudah punya koordinat → tampilkan marker dan lingkaran sesuai data existing
- Panggil `map.invalidateSize()` setiap kali modal dibuka agar tile peta tidak kosong

### Modal konfirmasi hapus:

Gunakan DaisyUI `modal` konfirmasi — **jangan** gunakan `confirm()` bawaan browser. Tampilkan nama kantor yang akan dihapus di dalam teks konfirmasi.

---

## BAGIAN 6 — Update Form Personel (Halaman Admin)

Temukan file Livewire component dan view form personel yang sudah ada. Pastikan attribute layout yang digunakan tetap sama dengan yang sudah ada. Tambahkan dua field berikut di dalam form, **setelah field OPD**:

### Field 1: Dropdown Kantor

```blade
<div class="form-control">
    <label class="label">
        <span class="label-text">Kantor</span>
    </label>
    <select wire:model.live="kantor_id" class="select select-bordered">
        <option value="">— Tidak ada kantor —</option>
        @foreach($kantors as $kantor)
            <option value="{{ $kantor->id }}">
                {{ $kantor->name }} ({{ $kantor->opd->name }})
            </option>
        @endforeach
    </select>
</div>
```

### Field 2: Toggle Wajib Lokasi (tampil kondisional)

```blade
@if($kantor_id)
<div class="form-control">
    <label class="label cursor-pointer">
        <span class="label-text">Wajib absen dalam radius kantor</span>
        <input type="checkbox" wire:model="wajib_absen_di_lokasi" class="toggle toggle-primary"/>
    </label>
    <label class="label">
        <span class="label-text-alt text-base-content/60">
            Aktifkan jika personel ini harus absen dari dalam area kantor
        </span>
    </label>
</div>
@endif
```

### Validasi tambahan di Livewire:

```php
if ($this->wajib_absen_di_lokasi && !$this->kantor_id) {
    $this->addError(
        'wajib_absen_di_lokasi',
        'Pilih kantor terlebih dahulu jika ingin mewajibkan absen di lokasi.'
    );
}
```

---

## BAGIAN 7 — Info Lokasi di Step 3 Absensi (View)

Di **view Step 3** (halaman pilih Absen Masuk / Absen Pulang), tambahkan blok informasi lokasi yang muncul setelah GPS didapat dari browser.

### JavaScript untuk kirim GPS ke Livewire:

```javascript
// Panggil saat step 3 ditampilkan (gunakan Livewire event atau Alpine x-init)
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        @this.terimaCoordsLokasi(pos.coords.latitude, pos.coords.longitude);
    });
}
```

### Blade untuk menampilkan info lokasi:

```blade
@if(!empty($infoLokasi))
    @if(is_null($infoLokasi['is_within_radius']))
        {{-- Tidak ada kantor terhubung, tidak perlu tampilkan info --}}
    @elseif($infoLokasi['boleh'] && $infoLokasi['is_within_radius'])
        <div class="alert alert-success">
            <span>✓ Anda berada dalam radius kantor
                <strong>{{ $infoLokasi['kantor_name'] }}</strong>
                (±{{ $infoLokasi['jarak_meter'] }}m)
            </span>
        </div>
    @elseif($infoLokasi['boleh'] && !$infoLokasi['is_within_radius'])
        <div class="alert alert-warning">
            <span>⚠ Anda berada di luar radius kantor
                <strong>{{ $infoLokasi['kantor_name'] }}</strong>
                ({{ $infoLokasi['jarak_meter'] }}m). Absensi tetap diperbolehkan.
            </span>
        </div>
    @else
        <div class="alert alert-error">
            <span>✕ {{ $infoLokasi['pesan'] }}</span>
        </div>
    @endif
@endif
```

### Disable tombol absen jika tidak diizinkan:

```blade
<button
    wire:click="submitAttendance('in', ...)"
    class="btn btn-primary"
    @if(!empty($infoLokasi) && $infoLokasi['boleh'] === false) disabled @endif
>
    Absen Masuk
</button>

<button
    wire:click="submitAttendance('out', ...)"
    class="btn btn-secondary"
    @if(!empty($infoLokasi) && $infoLokasi['boleh'] === false) disabled @endif
>
    Absen Pulang
</button>
```

---

## BAGIAN 8 — Badge Lokasi di Riwayat/Detail Absensi (Admin)

Di halaman riwayat atau detail absensi yang sudah ada, tambahkan kolom/badge status lokasi:

| Kondisi | Badge DaisyUI | Teks |
|---|---|---|
| `is_within_radius = true` | `badge badge-success` | Dalam Radius |
| `is_within_radius = false` | `badge badge-error` | Luar Radius |
| `is_within_radius = null` | `badge badge-ghost` | Bebas Lokasi |

```blade
@if($absensi->is_within_radius === true)
    <span class="badge badge-success">Dalam Radius</span>
@elseif($absensi->is_within_radius === false)
    <span class="badge badge-error">Luar Radius</span>
@else
    <span class="badge badge-ghost">Bebas Lokasi</span>
@endif

@if($absensi->jarak_meter !== null)
    <p class="text-xs text-base-content/50 mt-1">
        {{ $absensi->jarak_meter }}m dari kantor
    </p>
@endif
```

---

## Aturan Wajib (Berlaku untuk Semua Bagian)

1. **Sintaks Livewire 4 wajib** — `new #[Title('...')] #[Layout('...')] class extends Component` (anonymous class)
2. **Layout admin** selalu gunakan `layouts::admin.app` dengan attribute `#[Title(...)]` dan `#[Layout(...)]`
3. **Layout absensi** gunakan `layouts.absensi.app` (tanpa `#[Title]`, sesuai yang sudah ada)
4. **Semua teks, label, dan pesan error dalam Bahasa Indonesia**
5. **Semua query gunakan Eloquent** — tidak ada raw SQL
6. **Ikuti class DaisyUI yang sudah dipakai di proyek** — baca view yang sudah ada sebelum menulis kode baru
7. **`onDelete('set null')`** pada FK `kantor_id` di tabel `personnels` dan `absensis`
8. **Leaflet.js** hanya di-load di halaman yang membutuhkan peta, bukan secara global
9. **Jangan ubah logika absensi yang sudah ada** — hanya tambahkan integrasi lokasi di titik yang sudah ditentukan di Bagian 4
10. **Jalankan `php artisan migrate`** setelah semua migration selesai dibuat
