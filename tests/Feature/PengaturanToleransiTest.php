<?php

use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $perm = Permission::firstOrCreate(['name' => 'manajemen-pengaturan', 'group' => 'Pengaturan']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($perm);
});

test('pengaturan sistem defaults masukToleransi to 30 and renders client-side shortcuts up to 60 minutes', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Livewire::actingAs($user)
        ->test('admin::pengaturan')
        ->assertOk()
        ->assertSet('masukToleransi', 30)
        ->assertSee('Toleransi Keterlambatan Absen Masuk (Global)')
        ->assertSee('shortcuts: [0, 10, 15, 20, 30, 45, 60]', false)
        ->assertSee('selectShortcut(item)', false)
        ->assertSee('max="60"', false);
});

test('pengaturan sistem validates masukToleransi max 60 and saves valid setting', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Livewire::actingAs($user)
        ->test('admin::pengaturan')
        ->set('masukToleransi', 61)
        ->call('saveTimeSettings')
        ->assertHasErrors(['masukToleransi']);

    Livewire::actingAs($user)
        ->test('admin::pengaturan')
        ->set('masukToleransi', -1)
        ->call('saveTimeSettings')
        ->assertHasErrors(['masukToleransi']);

    Livewire::actingAs($user)
        ->test('admin::pengaturan')
        ->set('masukToleransi', 45)
        ->call('saveTimeSettings')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success', message: 'Batasan waktu absensi berhasil disimpan.');

    expect(Setting::get('absensi_masuk_toleransi'))->toBe(45);
});

test('dynamic global tolerance setting is respected by mobile absensi api and admin absensi input', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $inputPerm = Permission::firstOrCreate(['name' => 'absensi-input', 'group' => 'Absensi']);
    $user->givePermissionTo($inputPerm);

    Livewire::actingAs($user)
        ->test('admin::pengaturan')
        ->set('masukToleransi', 45)
        ->call('saveTimeSettings')
        ->assertHasNoErrors();

    Livewire::actingAs($user)
        ->test('admin::absensi-input')
        ->assertSet('toleransiMenit', 45);

    $opd = Opd::create(['name' => 'BPBD', 'singkatan' => 'BPBD']);
    $kantor = Kantor::create([
        'name' => 'Markas',
        'opd_id' => $opd->id,
        'latitude' => 0.507068,
        'longitude' => 101.447779,
        'radius_meter' => 200,
    ]);
    $penugasan = Penugasan::create(['name' => 'TRC']);
    $personnel = Personnel::create([
        'opd_id' => $opd->id,
        'kantor_id' => $kantor->id,
        'penugasan_id' => $penugasan->id,
        'name' => 'Budi',
        'nik' => '1471012345679999',
        'email' => 'budi.tol@example.com',
        'password' => bcrypt('password'),
    ]);

    $device = Device::create([
        'opd_id' => $opd->id,
        'personnel_id' => $personnel->id,
        'name' => 'HP Budi',
        'license_key' => 'LIC-TOL-001',
        'unique_device_id' => 'device_budi_tol',
        'status' => 'active',
    ]);

    $jwt = app(JwtService::class);
    $jwt->generatePersonnelRefreshToken($personnel, $device->id, $device->unique_device_id, 365);
    $token = $jwt->generatePersonnelAccessToken($personnel);

    $shift = Shift::create([
        'name' => 'Pagi',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);

    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => '2026-11-22',
        'status' => 'SHIFT',
    ]);

    ob_start();
    $img = imagecreatetruecolor(10, 10);
    imagejpeg($img);
    $dummy = base64_encode(ob_get_clean());
    imagedestroy($img);

    // 08:45:59 masih HADIR saat toleransi 45 menit
    Carbon::setTestNow(Carbon::parse('2026-11-22 08:45:59'));
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/personel/absensi/store', [
            'lat' => 0.507068,
            'lng' => 101.447779,
            'foto' => $dummy,
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.status_masuk', 'HADIR');

    // 08:46:00 pada tanggal berikutnya menjadi TELAT
    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => '2026-11-23',
        'status' => 'SHIFT',
    ]);

    Carbon::setTestNow(Carbon::parse('2026-11-23 08:46:00'));
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/personel/absensi/store', [
            'lat' => 0.507068,
            'lng' => 101.447779,
            'foto' => $dummy,
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.status_masuk', 'TELAT');

    Carbon::setTestNow();
});
