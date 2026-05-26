<?php

use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    putenv('MOBILE_API_KEY=TEST-API-KEY');
});

test('clock in fails because yesterday is treated as night shift buffer when it is OFF', function () {
    // 1. Set global settings
    Setting::set('absensi_masuk_mulai', 60, 'integer');
    Setting::set('absensi_masuk_selesai', 120, 'integer');
    Setting::set('absensi_pulang_mulai', 0, 'integer');
    Setting::set('absensi_pulang_selesai', 120, 'integer');

    // 2. Create OPD & Personnel
    $opd = Opd::create([
        'name' => 'Test OPD',
    ]);

    $personnel = Personnel::create([
        'id' => 1,
        'name' => 'Personnel 1',
        'opd_id' => $opd->id,
        'penugasan_id' => 1, // seeded default
        'foto' => 'foto.jpg',
        'email' => 'personnel@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    // 3. Create Device for authorization
    $device = Device::create([
        'opd_id' => $opd->id,
        'personnel_id' => $personnel->id,
        'name' => 'Test Device',
        'license_key' => 'LIC-123456',
        'unique_device_id' => 'DEVICE-123456',
        'status' => 'active',
    ]);

    // 4. Mock time to today at 07:10 AM
    $todayDate = '2026-05-26';
    $yesterdayDate = '2026-05-25';
    Carbon::setTestNow(Carbon::parse("$todayDate 07:10:00"));

    // 5. Create Yesterday's OFF/LIBUR Schedule
    $offShift = Shift::create([
        'name' => 'OFF',
        'type' => 'off',
        'keterangan' => 'LIBUR',
        'start_time' => null,
        'end_time' => null,
    ]);

    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $offShift->id,
        'tanggal' => $yesterdayDate,
        'status' => 'LIBUR',
    ]);

    // 6. Create Today's 24-Hour Shift
    $shift24 = Shift::create([
        'name' => '24',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '08:00:00',
    ]);

    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift24->id,
        'tanggal' => $todayDate,
        'status' => 'SHIFT',
    ]);

    // 7. Request attendance status with valid auth headers
    $response = $this->withHeaders([
        'X-API-KEY' => 'TEST-API-KEY',
        'X-LICENSE-KEY' => 'LIC-123456',
        'X-DEVICE-ID' => 'DEVICE-123456',
    ])->getJson("/api/personnels/check-status/{$personnel->id}");

    // Assert successful response
    $response->assertSuccessful();

    $data = $response->json();
    expect($data['status'])->toBe('success');
    expect($data['data']['shift_id'])->toBe($shift24->id);
});
