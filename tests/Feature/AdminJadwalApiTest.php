<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminJadwalApiTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminOpdRole;
    protected Role $superAdminRole;
    protected Role $nonAdminRole;
    protected Opd $opd1;
    protected Opd $opd2;
    protected Shift $shiftPagi;
    protected Penugasan $penugasan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminOpdRole = Role::create(['name' => 'admin-opd']);
        $this->superAdminRole = Role::create(['name' => 'super-admin']);
        $this->nonAdminRole = Role::create(['name' => 'anggota']);

        $this->opd1 = Opd::create([
            'name' => 'Dinas Pemadam Kebakaran',
            'singkatan' => 'Damkar',
            'alamat' => 'Jl. Cempaka No. 5',
        ]);

        $this->opd2 = Opd::create([
            'name' => 'Satuan Polisi Pamong Praja',
            'singkatan' => 'Satpol PP',
            'alamat' => 'Jl. Cut Nyak Dien No. 1',
        ]);

        $this->shiftPagi = Shift::create([
            'name' => 'Shift Pagi',
            'type' => 'shift',
            'keterangan' => 'Pagi',
            'start_time' => '2026-09-08 07:30:00',
            'end_time' => '2026-09-08 15:30:00',
        ]);

        $this->penugasan = Penugasan::create([
            'name' => 'Petugas Lapangan',
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/admin/jadwal');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_jadwal(): void
    {
        $user = User::create([
            'name' => 'Anggota Regular',
            'email' => 'anggota@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->nonAdminRole);

        // Generate token langsung dengan JwtService untuk menguji middleware jwt.admin
        $jwtService = app(JwtService::class);
        $token = $jwtService->generateAccessToken($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/jadwal');

        $response->assertStatus(403);
    }

    public function test_admin_opd_only_sees_their_own_opd_data(): void
    {
        // Personel OPD 1 (Damkar)
        $personnel1 = Personnel::create([
            'name' => 'Petugas Damkar 1',
            'foto' => 'personnels/avatar1.jpg',
            'email' => 'damkar1@pekanbaru.go.id',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan->id,
            'attendance_type' => 'SHIFT',
        ]);

        // Personel OPD 2 (Satpol PP)
        $personnel2 = Personnel::create([
            'name' => 'Petugas Satpol 1',
            'foto' => 'personnels/avatar2.jpg',
            'email' => 'satpol1@pekanbaru.go.id',
            'password' => bcrypt('password'),
            'pin' => '654321',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $this->penugasan->id,
            'attendance_type' => 'SHIFT',
        ]);

        $today = Carbon::today()->format('Y-m-d');

        $jadwal1 = \App\Models\Jadwal::create([
            'personnel_id' => $personnel1->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
            'status' => 'active',
        ]);

        $jadwal2 = \App\Models\Jadwal::create([
            'personnel_id' => $personnel2->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
            'status' => 'active',
        ]);

        // Absensi OPD 1
        Absensi::create([
            'personnel_id' => $personnel1->id,
            'jadwal_id' => $jadwal1->id,
            'tanggal' => $today,
            'status' => 'HADIR',
            'jam_masuk' => '07:25:00',
            'status_masuk' => 'TEPAT WAKTU',
        ]);

        // Absensi OPD 2
        Absensi::create([
            'personnel_id' => $personnel2->id,
            'jadwal_id' => $jadwal2->id,
            'tanggal' => $today,
            'status' => 'ALPA',
        ]);

        // Login sebagai Admin OPD 1
        $admin = User::create([
            'name' => 'Admin Damkar',
            'email' => 'admin.damkar@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole($this->adminOpdRole);
        $admin->opds()->attach($this->opd1->id);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin.damkar@pekanbaru.go.id',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/jadwal?tanggal=$today");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_super_admin', false)
            ->assertJsonPath('data.opd_name', 'Dinas Pemadam Kebakaran')
            ->assertJsonPath('data.stats.total_required', 1)
            ->assertJsonPath('data.stats.total_hadir', 1)
            ->assertJsonPath('data.stats.total_alpa', 0);

        // Pastikan hanya personel Damkar yang muncul di activities
        $activities = $response->json('data.activities');
        $this->assertCount(1, $activities);
        $this->assertEquals('Petugas Damkar 1', $activities[0]['personnel']['name']);
    }

    public function test_super_admin_sees_all_opds_data(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $personnel1 = Personnel::create([
            'name' => 'Petugas Damkar 1',
            'foto' => 'personnels/avatar1.jpg',
            'email' => 'damkar1@pekanbaru.go.id',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan->id,
            'attendance_type' => 'SHIFT',
        ]);

        $personnel2 = Personnel::create([
            'name' => 'Petugas Satpol 1',
            'foto' => 'personnels/avatar2.jpg',
            'email' => 'satpol1@pekanbaru.go.id',
            'password' => bcrypt('password'),
            'pin' => '654321',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $this->penugasan->id,
            'attendance_type' => 'SHIFT',
        ]);

        $jadwal1 = \App\Models\Jadwal::create([
            'personnel_id' => $personnel1->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
            'status' => 'active',
        ]);

        $jadwal2 = \App\Models\Jadwal::create([
            'personnel_id' => $personnel2->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
            'status' => 'active',
        ]);

        Absensi::create([
            'personnel_id' => $personnel1->id,
            'jadwal_id' => $jadwal1->id,
            'tanggal' => $today,
            'status' => 'HADIR',
        ]);

        Absensi::create([
            'personnel_id' => $personnel2->id,
            'jadwal_id' => $jadwal2->id,
            'tanggal' => $today,
            'status' => 'ALPA',
        ]);

        // Login sebagai Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pekanbaru.go.id',
            'password' => Hash::make('supersecret'),
        ]);
        $superAdmin->assignRole($this->superAdminRole);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'superadmin@pekanbaru.go.id',
            'password' => 'supersecret',
        ]);
        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/jadwal?tanggal=$today");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_super_admin', true)
            ->assertJsonPath('data.stats.total_required', 2)
            ->assertJsonPath('data.stats.total_hadir', 1)
            ->assertJsonPath('data.stats.total_alpa', 1);

        $activities = $response->json('data.activities');
        $this->assertCount(2, $activities);
    }
}
