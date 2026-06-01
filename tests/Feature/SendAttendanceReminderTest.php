<?php

use App\Jobs\SendFcmNotificationJob;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Setting;
use App\Models\Shift;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    putenv('MOBILE_API_KEY=TEST-API-KEY');
});

test('personnel device can update fcm token', function () {
    $opd = Opd::create(['name' => 'Test OPD']);

    $personnel = Personnel::create([
        'id' => 1,
        'name' => 'Personnel 1',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'foto.jpg',
        'email' => 'personnel@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $device = Device::create([
        'opd_id' => $opd->id,
        'personnel_id' => $personnel->id,
        'name' => 'Test Device',
        'license_key' => 'LIC-123456',
        'unique_device_id' => 'DEVICE-123456',
        'status' => 'active',
    ]);

    $token = $device->createToken('device-token')->plainTextToken;

    $response = $this->withHeaders([
        'X-API-KEY' => 'TEST-API-KEY',
        'X-LICENSE-KEY' => 'LIC-123456',
        'X-DEVICE-ID' => 'DEVICE-123456',
        'Authorization' => "Bearer $token",
    ])->postJson('/api/device/fcm-token', [
        'fcm_token' => 'sample-fcm-token-123',
    ]);

    $response->assertSuccessful();
    expect($response->json('status'))->toBe('success');
    expect($personnel->fresh()->fcm_token)->toBe('sample-fcm-token-123');
});

test('global device cannot update fcm token', function () {
    $opd = Opd::create(['name' => 'Test OPD']);

    $device = Device::create([
        'opd_id' => $opd->id,
        'personnel_id' => null, // Global device
        'name' => 'Test Global Device',
        'license_key' => 'LIC-GLOBAL',
        'unique_device_id' => 'DEVICE-GLOBAL',
        'status' => 'active',
    ]);

    $token = $device->createToken('device-token')->plainTextToken;

    $response = $this->withHeaders([
        'X-API-KEY' => 'TEST-API-KEY',
        'X-LICENSE-KEY' => 'LIC-GLOBAL',
        'X-DEVICE-ID' => 'DEVICE-GLOBAL',
        'Authorization' => "Bearer $token",
    ])->postJson('/api/device/fcm-token', [
        'fcm_token' => 'sample-fcm-token-123',
    ]);

    $response->assertStatus(403);
});
test('send check-in attendance reminder sends fcm notification and caches it', function () {
    Setting::set('absensi_masuk_mulai', 30, 'integer');
    Setting::set('absensi_masuk_selesai', 120, 'integer');

    $opd = Opd::create(['name' => 'Test OPD']);

    $personnel = Personnel::create([
        'id' => 1,
        'name' => 'Personnel 1',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'foto.jpg',
        'email' => 'personnel@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
        'fcm_token' => 'mocked-fcm-token-999',
    ]);

    $shift = Shift::create([
        'name' => 'PAGI',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);

    $jadwal = Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => '2026-06-01',
        'status' => 'SHIFT',
    ]);

    // Mock time to 07:45 AM (which is inside check-in window 07:30 to 10:00)
    Carbon::setTestNow(Carbon::parse('2026-06-01 07:45:00'));

    // Mock FcmService
    $mockFcm = $this->mock(FcmService::class);
    $mockFcm->shouldReceive('sendNotification')
        ->once()
        ->with(
            'mocked-fcm-token-999',
            '🚨 Peringatan Absen Masuk!',
            'Anda belum melakukan absen masuk, absen sekarang',
            Mockery::subset([
                'type' => 'attendance_reminder',
                'action' => 'check_in',
            ])
        )
        ->andReturn(true);

    // Assert cache key does not exist yet
    $cacheKey = "attendance_reminder_in_{$personnel->id}_{$jadwal->id}";
    expect(Cache::has($cacheKey))->toBeFalse();

    // Run command
    $this->artisan('attendance:send-reminder')->assertExitCode(0);

    // Assert cache key is now stored (to avoid duplicate reminders)
    expect(Cache::has($cacheKey))->toBeTrue();
});

test('send check-out attendance reminder sends fcm notification and caches it', function () {
    Setting::set('absensi_pulang_mulai', 30, 'integer');
    Setting::set('absensi_pulang_selesai', 120, 'integer');

    $opd = Opd::create(['name' => 'Test OPD']);

    $personnel = Personnel::create([
        'id' => 2,
        'name' => 'Personnel 2',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'foto.jpg',
        'email' => 'personnel2@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
        'fcm_token' => 'mocked-fcm-token-888',
    ]);

    $shift = Shift::create([
        'name' => 'PAGI',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);

    $jadwal = Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => '2026-06-01',
        'status' => 'SHIFT',
    ]);

    // Mock time to 04:15 PM (which is inside check-out window 03:30 PM to 06:00 PM)
    Carbon::setTestNow(Carbon::parse('2026-06-01 16:15:00'));

    // Mock FcmService
    $mockFcm = $this->mock(FcmService::class);
    $mockFcm->shouldReceive('sendNotification')
        ->once()
        ->with(
            'mocked-fcm-token-888',
            '🚨 Peringatan Absen Pulang!',
            'Anda belum melakukan absen pulang, absen sekarang',
            Mockery::subset([
                'type' => 'attendance_reminder',
                'action' => 'check_out',
            ])
        )
        ->andReturn(true);

    // Assert cache key does not exist yet
    $cacheKey = "attendance_reminder_out_{$personnel->id}_{$jadwal->id}";
    expect(Cache::has($cacheKey))->toBeFalse();

    // Run command
    $this->artisan('attendance:send-reminder')->assertExitCode(0);

    // Assert cache key is now stored (to avoid duplicate reminders)
    expect(Cache::has($cacheKey))->toBeTrue();
});

test('send attendance reminder dispatches SendFcmNotificationJob', function () {
    Queue::fake();

    Setting::set('absensi_masuk_mulai', 30, 'integer');
    Setting::set('absensi_masuk_selesai', 120, 'integer');

    $opd = Opd::create(['name' => 'Test OPD']);

    $personnel = Personnel::create([
        'id' => 3,
        'name' => 'Personnel 3',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'foto.jpg',
        'email' => 'personnel3@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
        'fcm_token' => 'mocked-fcm-token-777',
    ]);

    $shift = Shift::create([
        'name' => 'PAGI',
        'type' => 'shift',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);

    $jadwal = Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => '2026-06-01',
        'status' => 'SHIFT',
    ]);

    // Mock time to 07:45 AM
    Carbon::setTestNow(Carbon::parse('2026-06-01 07:45:00'));

    // Run command
    $this->artisan('attendance:send-reminder')->assertExitCode(0);

    // Assert job was dispatched
    Queue::assertPushed(SendFcmNotificationJob::class, function ($job) use ($jadwal) {
        return $job->token === 'mocked-fcm-token-777'
            && $job->title === '🚨 Peringatan Absen Masuk!'
            && $job->body === 'Anda belum melakukan absen masuk, absen sekarang'
            && $job->data['type'] === 'attendance_reminder'
            && $job->data['action'] === 'check_in'
            && $job->data['jadwal_id'] === (string) $jadwal->id;
    });
});
