<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAbsensiApiEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'manajemen-absensi', 'group' => 'Absensi']);
        Permission::firstOrCreate(['name' => 'edit-absensi-all-opd', 'group' => 'Absensi']);
        Permission::firstOrCreate(['name' => 'edit-absensi-opd', 'group' => 'Absensi']);
        Permission::firstOrCreate(['name' => 'reset-absen', 'group' => 'Absensi']);
        $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
        $role->givePermissionTo(Permission::all());
    }

    public function test_get_edit_data_returns_personnel_and_attendance_info(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $opd = Opd::create(['name' => 'BPBD Pekanbaru', 'code' => 'BPBD']);
        $personnel = Personnel::create([
            'name' => 'Budi Santoso',
            'nik' => '1234567890123456',
            'opd_id' => $opd->id,
            'penugasan_id' => 1,
            'foto' => 'budi.jpg',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'SHIFT',
        ]);

        $shift = Shift::create([
            'name' => 'P1',
            'type' => 'shift',
            'keterangan' => 'PAGI',
            'start_time' => '08:00',
            'end_time' => '20:00',
            'color' => '#22c55e',
        ]);

        $date = '2026-08-15';
        Jadwal::create([
            'personnel_id' => $personnel->id,
            'shift_id' => $shift->id,
            'tanggal' => $date,
            'status' => 'KERJA',
        ]);

        $cuti = Cuti::create(['name' => 'Cuti Tahunan']);

        $jwtService = app(\App\Services\JwtService::class);
        $token = $jwtService->generateAccessToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/absensi/edit-data?personnel_id={$personnel->id}&tanggal={$date}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.personnel.name', 'Budi Santoso')
            ->assertJsonPath('data.jadwal.shift_name', 'P1 (PAGI)')
            ->assertJsonPath('data.jadwal.jam_masuk', '08:00')
            ->assertJsonPath('data.jadwal.jam_pulang', '20:00')
            ->assertJsonPath('data.is_edited', false)
            ->assertJsonFragment(['name' => 'Cuti Tahunan']);
    }

    public function test_save_edit_data_updates_attendance_and_sets_original_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
        $personnel = Personnel::create([
            'name' => 'Siti Aminah',
            'nik' => '1234567890123457',
            'opd_id' => $opd->id,
            'penugasan_id' => 1,
            'foto' => 'siti.jpg',
            'email' => 'siti@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'SHIFT',
        ]);

        $date = '2026-08-16';
        $token = app(\App\Services\JwtService::class)->generateAccessToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/absensi/edit-data', [
                'personnel_id' => $personnel->id,
                'tanggal' => $date,
                'status_masuk' => 'HADIR',
                'status_pulang' => 'HADIR',
                'jam_masuk' => '08:00',
                'jam_pulang' => '16:00',
                'alasan_edit' => 'Koreksi kehadiran admin untuk uji coba',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $saved = Absensi::where('personnel_id', $personnel->id)->whereDate('tanggal', $date)->first();
        $this->assertNotNull($saved);
        $this->assertEquals('HADIR', $saved->status);
        $this->assertEquals('HADIR', $saved->status_masuk);
        $this->assertEquals('HADIR', $saved->status_pulang);
        $this->assertEquals('ALPA', $saved->original_status_masuk);
        $this->assertEquals('Koreksi kehadiran admin untuk uji coba', $saved->alasan_edit);
    }

    public function test_reset_to_original_reverts_edited_attendance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
        $personnel = Personnel::create([
            'name' => 'Ahmad Dani',
            'nik' => '1234567890123458',
            'opd_id' => $opd->id,
            'penugasan_id' => 1,
            'foto' => 'ahmad.jpg',
            'email' => 'ahmad@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'SHIFT',
        ]);

        $date = '2026-08-17';
        $absensi = Absensi::create([
            'personnel_id' => $personnel->id,
            'tanggal' => $date,
            'status' => 'HADIR',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'original_status_masuk' => 'ALPA',
            'original_status_pulang' => 'ALPA',
            'alasan_edit' => 'Diedit admin',
            'edited_by_user_id' => $user->id,
            'edited_at' => now(),
        ]);

        $token = app(\App\Services\JwtService::class)->generateAccessToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/absensi/reset-original', [
                'absensi_id' => $absensi->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $refreshed = $absensi->fresh();
        $this->assertEquals('ALPA', $refreshed->status);
        $this->assertNull($refreshed->status_masuk);
        $this->assertNull($refreshed->original_status_masuk);
        $this->assertNull($refreshed->alasan_edit);
    }

    public function test_reset_absensi_moves_to_trash(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
        $personnel = Personnel::create([
            'name' => 'Rudi',
            'nik' => '1234567890123459',
            'opd_id' => $opd->id,
            'penugasan_id' => 1,
            'foto' => 'rudi.jpg',
            'email' => 'rudi@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'SHIFT',
        ]);

        $date = '2026-08-18';
        $absensi = Absensi::create([
            'personnel_id' => $personnel->id,
            'tanggal' => $date,
            'status' => 'HADIR',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
        ]);

        $token = app(\App\Services\JwtService::class)->generateAccessToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/absensi/reset-absen', [
                'absensi_id' => $absensi->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('absensis', ['id' => $absensi->id]);
    }

    public function test_get_edit_data_returns_flexible_schedule_without_shift_time_fallback(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        // Create default shift that would normally be picked by fallback
        Shift::create([
            'name' => 'P1',
            'type' => 'shift',
            'keterangan' => 'PAGI',
            'start_time' => '08:00',
            'end_time' => '20:00',
            'color' => '#22c55e',
        ]);

        $opd = Opd::create(['name' => 'Dinkes', 'code' => 'DINKES']);
        $personnel = Personnel::create([
            'name' => 'Dokter Andi',
            'nik' => '9999888877776666',
            'opd_id' => $opd->id,
            'penugasan_id' => 1,
            'email' => 'andi@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'FLEXIBLE',
        ]);

        $date = '2026-08-20';
        $token = app(\App\Services\JwtService::class)->generateAccessToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/absensi/edit-data?personnel_id={$personnel->id}&tanggal={$date}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.personnel.attendance_type', 'FLEXIBLE')
            ->assertJsonPath('data.jadwal.shift_name', 'Fleksibel')
            ->assertJsonPath('data.jadwal.jam_masuk', null)
            ->assertJsonPath('data.jadwal.jam_pulang', null);
    }
}
