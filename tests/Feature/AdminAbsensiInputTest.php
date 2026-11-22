<?php

use App\Models\Absensi;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $permission = Permission::firstOrCreate(['name' => 'absensi-input', 'group' => 'Absensi']);
    $role = Role::firstOrCreate(['name' => 'admin-input', 'color' => '#3b82f6']);
    $role->givePermissionTo($permission);
});

function createTestPersonnel(array $overrides = []): Personnel
{
    static $seq = 1;
    $seq++;

    return Personnel::create(array_merge([
        'name' => "Personel {$seq}",
        'nik' => str_pad((string) $seq, 16, '1', STR_PAD_LEFT),
        'email' => "personel{$seq}@example.com",
        'password' => bcrypt('password'),
        'pin' => '111111',
        'foto' => 'sample.jpg',
        'attendance_type' => 'SCHEDULED',
    ], $overrides));
}

test('guest is redirected to login and user without absensi-input gets 403', function () {
    $this->get(route('absensi.input'))
        ->assertRedirect(route('login'));

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)
        ->get(route('absensi.input'))
        ->assertForbidden();
});

test('authorized user sees form immediately without tracking classes and sidebar link points to input absen', function () {
    $user = User::factory()->create();
    $user->assignRole('admin-input');

    $this->actingAs($user)
        ->get(route('absensi.input'))
        ->assertOk()
        ->assertSee('Input Absen')
        ->assertSee('Simpan Data')
        ->assertSee(route('absensi.input'), false);

    $componentHtml = Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->assertOk()
        ->html();

    expect($componentHtml)->not->toContain('tracking-widest');
    expect($componentHtml)->not->toContain('tracking-wider');
    expect($componentHtml)->not->toContain('tracking-wide');
    expect($componentHtml)->not->toContain('tracking-tight');
    expect($componentHtml)->toContain(':disabled="useProfilePhoto"');
});

test('scheduled personnel without active shift is blocked until shift is selected, then saves jadwal and absensi', function () {
    $opd = Opd::create(['name' => 'Dinas Kominfo']);
    $penugasan = Penugasan::create(['name' => 'Operasional']);
    $kantor = Kantor::create([
        'opd_id' => $opd->id,
        'name' => 'Kantor Walikota',
        'latitude' => 0.50706770,
        'longitude' => 101.44777930,
        'radius_meter' => 100,
        'is_active' => true,
    ]);
    $personnel = createTestPersonnel([
        'name' => 'Budi Santoso',
        'nik' => '1471010101010001',
        'opd_id' => $opd->id,
        'penugasan_id' => $penugasan->id,
        'kantor_id' => $kantor->id,
        'attendance_type' => 'SCHEDULED',
    ]);
    $device = Device::create([
        'opd_id' => $opd->id,
        'personnel_id' => $personnel->id,
        'name' => 'Samsung Galaxy A55',
        'brand' => 'Samsung',
        'model' => 'A55',
        'license_key' => 'LIC-TEST-001',
        'status' => 'active',
    ]);
    $shift = Shift::create([
        'name' => 'Pagi',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin-input');
    $user->opds()->attach($opd->id);

    $tanggal = '2026-11-18';
    $fotoMasuk = UploadedFile::fake()->image('masuk.webp');
    $fotoPulang = UploadedFile::fake()->image('pulang.webp');

    // Tanpa jadwal & tanpa pilih shift -> diblokir validasi shiftId
    Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->call('selectPersonnel', $personnel->id)
        ->set('tanggal', $tanggal)
        ->assertSet('needsShiftSelection', true)
        ->set('sesi', 'keduanya')
        ->set('jamMasuk', '08:00')
        ->set('jamPulang', '15:30')
        ->call('save')
        ->assertHasErrors(['shiftId']);

    // Pilih shift, isi status PC, upload WebP masuk & pulang -> berhasil, reset form & toast
    Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->call('selectPersonnel', $personnel->id)
        ->set('tanggal', $tanggal)
        ->set('shiftId', $shift->id)
        ->set('sesi', 'keduanya')
        ->set('jamMasuk', '07:55')
        ->set('statusMasuk', 'HADIR')
        ->set('jamPulang', '15:30')
        ->set('statusPulang', 'PC')
        ->set('useProfilePhoto', false)
        ->set('fotoMasuk', $fotoMasuk)
        ->set('fotoPulang', $fotoPulang)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('toast')
        ->assertDispatched('absensi-input-reset')
        ->assertSet('personnelId', null);

    $jadwal = Jadwal::where('personnel_id', $personnel->id)
        ->whereDate('tanggal', $tanggal)
        ->first();
    expect($jadwal)->not->toBeNull();
    expect($jadwal->shift_id)->toBe($shift->id);

    $absensi = Absensi::where('personnel_id', $personnel->id)
        ->whereDate('tanggal', $tanggal)
        ->first();
    expect($absensi)->not->toBeNull();
    expect($absensi->jadwal_id)->toBe($jadwal->id);
    expect($absensi->status)->toBe('HADIR');
    expect($absensi->status_masuk)->toBe('HADIR');
    expect($absensi->status_pulang)->toBe('PC');
    expect($absensi->kantor_id)->toBe($kantor->id);
    expect($absensi->kantor_id_pulang)->toBe($kantor->id);
    expect((bool) $absensi->is_within_radius)->toBeTrue();
    expect($absensi->jarak_meter)->toBe(0);
    expect($absensi->platform_masuk)->toBe('android');
    expect($absensi->platform_pulang)->toBe('android');
    expect($absensi->unique_device_id_masuk)->toBe((string) $device->id);
    expect($absensi->device_name_masuk)->toBe('Samsung Galaxy A55');
    expect($absensi->edited_by_user_id)->toBeNull();
    expect($absensi->original_status_masuk)->toBeNull();
    expect($absensi->foto_masuk)->toStartWith("absensi/{$tanggal}/in_{$personnel->id}_");
    expect($absensi->foto_pulang)->toStartWith("absensi/{$tanggal}/out_{$personnel->id}_");
    Storage::disk('public')->assertExists($absensi->foto_masuk);
    Storage::disk('public')->assertExists($absensi->foto_pulang);
});

test('uses personnel profile photo in exact real absensi folder and naming format when no photo is uploaded', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan']);
    $penugasan = Penugasan::create(['name' => 'Lapangan']);
    $kantor = Kantor::create([
        'opd_id' => $opd->id,
        'name' => 'Terminal',
        'latitude' => 0.51000000,
        'longitude' => 101.45000000,
        'radius_meter' => 100,
        'is_active' => true,
    ]);

    $profilePath = UploadedFile::fake()->image('profil.jpg', 200, 200)->store('personnel-fotos', 'public');
    $personnel = createTestPersonnel([
        'name' => 'Siti Aminah',
        'nik' => '1471020202020002',
        'opd_id' => $opd->id,
        'penugasan_id' => $penugasan->id,
        'kantor_id' => $kantor->id,
        'foto' => $profilePath,
        'attendance_type' => 'FLEXIBLE',
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin-input');
    $user->opds()->attach($opd->id);

    $tanggal = Carbon::today()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->call('selectPersonnel', $personnel->id)
        ->set('tanggal', $tanggal)
        ->assertSet('needsShiftSelection', false)
        ->set('sesi', 'keduanya')
        ->set('jamMasuk', '08:00')
        ->set('jamPulang', '16:00')
        ->set('useProfilePhoto', true)
        ->call('save')
        ->assertHasNoErrors();

    $absensi = Absensi::where('personnel_id', $personnel->id)->first();
    expect($absensi)->not->toBeNull();
    expect($absensi->foto_masuk)->toStartWith("absensi/{$tanggal}/in_{$personnel->id}_");
    expect($absensi->foto_pulang)->toStartWith("absensi/{$tanggal}/out_{$personnel->id}_");
    expect($absensi->foto_masuk)->not->toBe($absensi->foto_pulang);
    Storage::disk('public')->assertExists($absensi->foto_masuk);
    Storage::disk('public')->assertExists($absensi->foto_pulang);
});

test('automatically updates and completes existing absensi record without wiping existing session data', function () {
    $opd = Opd::create(['name' => 'Dinas Sosial']);
    $penugasan = Penugasan::create(['name' => 'Sekretariat']);
    $kantor = Kantor::create([
        'opd_id' => $opd->id,
        'name' => 'Kantor Dinsos',
        'latitude' => 0.52000000,
        'longitude' => 101.46000000,
        'radius_meter' => 100,
        'is_active' => true,
    ]);
    $shift = Shift::create([
        'name' => 'Reguler',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);
    $personnel = createTestPersonnel([
        'name' => 'Andi Wijaya',
        'nik' => '1471030303030003',
        'opd_id' => $opd->id,
        'penugasan_id' => $penugasan->id,
        'kantor_id' => $kantor->id,
        'attendance_type' => 'SCHEDULED',
    ]);
    $tanggal = '2026-11-18';
    $jadwal = Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => $tanggal,
        'status' => 'SHIFT',
    ]);

    Absensi::create([
        'personnel_id' => $personnel->id,
        'jadwal_id' => $jadwal->id,
        'kantor_id' => $kantor->id,
        'tanggal' => $tanggal,
        'status' => 'HADIR',
        'jam_masuk' => '07:50:00',
        'status_masuk' => 'HADIR',
        'foto_masuk' => 'absensi/2026-11-18/in_existing.jpg',
        'platform_masuk' => 'android',
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin-input');
    $user->opds()->attach($opd->id);

    Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->call('selectPersonnel', $personnel->id)
        ->set('tanggal', $tanggal)
        ->assertSet('sesi', 'pulang')
        ->set('jamPulang', '16:05')
        ->set('statusPulang', 'HADIR')
        ->call('save')
        ->assertHasNoErrors();

    $absensi = Absensi::where('personnel_id', $personnel->id)->whereDate('tanggal', $tanggal)->first();
    expect($absensi->jam_masuk->format('H:i:s'))->toBe('07:50:00');
    expect($absensi->foto_masuk)->toBe('absensi/2026-11-18/in_existing.jpg');
    expect($absensi->jam_pulang->format('H:i:s'))->toBe('16:05:00');
    expect($absensi->status_pulang)->toBe('HADIR');
    expect(Absensi::where('personnel_id', $personnel->id)->count())->toBe(1);
});

test('non super-admin cannot input absensi for personnel from another opd', function () {
    $opdA = Opd::create(['name' => 'OPD A']);
    $opdB = Opd::create(['name' => 'OPD B']);
    $penugasan = Penugasan::create(['name' => 'Umum']);

    $userA = User::factory()->create();
    $userA->assignRole('admin-input');
    $userA->opds()->attach($opdA->id);

    $personnelB = createTestPersonnel([
        'name' => 'Personel B',
        'nik' => '1471040404040004',
        'opd_id' => $opdB->id,
        'penugasan_id' => $penugasan->id,
        'attendance_type' => 'FLEXIBLE',
    ]);

    Livewire::actingAs($userA)
        ->test('admin::absensi-input')
        ->set('personnelId', $personnelB->id)
        ->set('tanggal', '2026-11-18')
        ->set('sesi', 'masuk')
        ->set('jamMasuk', '08:00')
        ->call('save')
        ->assertHasErrors(['personnelId']);
});

test('absensi input component defaults late tolerance to 30 minutes', function () {
    $user = User::factory()->create();
    $user->assignRole('admin-input');

    Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->assertSet('toleransiMenit', 30);
});
