<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use App\Events\PersonnelVectorUpdated;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAbsensiApiTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminOpdRole;
    protected Role $superAdminRole;
    protected Role $nonAdminRole;
    protected Opd $opd1;
    protected Opd $opd2;
    protected Kantor $kantor1;

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

        $this->kantor1 = Kantor::create([
            'name' => 'Pos Damkar Pusat',
            'opd_id' => $this->opd1->id,
            'latitude' => 0.507068,
            'longitude' => 101.447779,
            'radius_meter' => 150,
        ]);
    }

    protected function createAdminUser(Opd $opd, string $role = 'admin-opd'): array
    {
        $user = User::create([
            'name' => 'Admin ' . $opd->singkatan,
            'email' => 'admin.' . strtolower($opd->singkatan) . '@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($role === 'super-admin' ? $this->superAdminRole : $this->adminOpdRole);
        $user->opds()->attach($opd->id);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateAccessToken($user);

        return [$user, $token];
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/admin/absensi/personnels')->assertStatus(401);
        $this->getJson('/api/v1/admin/absensi/check-status/1')->assertStatus(401);
        $this->postJson('/api/v1/admin/absensi/store', [])->assertStatus(401);
    }

    public function test_admin_opd_only_sees_personnels_from_their_opd(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);

        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        // Personel OPD 1
        Personnel::create([
            'name' => 'Budi Santoso',
            'nik' => '1234567890123456',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'opd_id' => $this->opd1->id,
            'kantor_id' => $this->kantor1->id,
            'penugasan_id' => $penugasan->id,
            'face_descriptor_mobile' => json_encode(array_fill(0, 192, 0.05)),
        ]);

        // Personel OPD 2
        Personnel::create([
            'name' => 'Agus Satpol',
            'nik' => '9876543210987654',
            'email' => 'agus@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/agus.jpg',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $penugasan->id,
            'face_descriptor_mobile' => json_encode(array_fill(0, 192, 0.12)),
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson('/api/v1/admin/absensi/personnels');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.personnels.0.name', 'Budi Santoso')
            ->assertJsonPath('data.personnels.0.kantor.name', 'Pos Damkar Pusat');
    }

    public function test_admin_cannot_check_status_of_other_opd_personnel(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnelOpd2 = Personnel::create([
            'name' => 'Agus Satpol',
            'email' => 'agus2@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/agus.jpg',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $penugasan->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson("/api/v1/admin/absensi/check-status/{$personnelOpd2->id}");

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/v1/admin/absensi/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['personnel_id', 'foto', 'lat', 'lng']);
    }


    public function test_admin_can_update_face_descriptor_mobile_with_valid_data(): void
    {
        Event::fake([PersonnelVectorUpdated::class]);

        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnel = Personnel::create([
            'name' => 'Budi Mobile',
            'nik' => '1234567890123457',
            'email' => 'budi.192@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi192.jpg',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
        ]);

        $valid192 = json_encode(array_fill(0, 192, 0.05));

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson("/api/v1/admin/personnels/{$personnel->id}/face-mobile", [
                'face_descriptor_mobile' => $valid192,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.face_descriptor_mobile_count', 192);

        $this->assertEquals($valid192, $personnel->fresh()->face_descriptor_mobile);

        Event::assertDispatched(PersonnelVectorUpdated::class, function ($event) use ($personnel) {
            return $event->personnel_id === $personnel->id && $event->status === 'ready';
        });
    }

    protected function generateDummyImageBase64(): string
    {
        ob_start();
        $img = imagecreatetruecolor(10, 10);
        imagejpeg($img);
        $dummyImage = ob_get_clean();
        imagedestroy($img);
        return base64_encode($dummyImage);
    }

    public function test_personnel_without_check_in_can_direct_check_out_during_checkout_window(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnel = Personnel::create([
            'name' => 'Budi Shift Pagi',
            'nik' => '1234567890123488',
            'email' => 'budi.shift@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'opd_id' => $this->opd1->id,
            'kantor_id' => $this->kantor1->id,
            'penugasan_id' => $penugasan->id,
        ]);

        $shift = Shift::create([
            'name' => 'Pagi (08:00 - 16:00)',
            'type' => 'shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        Jadwal::create([
            'personnel_id' => $personnel->id,
            'shift_id' => $shift->id,
            'tanggal' => '2026-09-10',
            'status' => 'SHIFT',
        ]);

        // Simulasi waktu saat jam kepulangan (16:15 WIB)
        Carbon::setTestNow(Carbon::parse('2026-09-10 16:15:00'));

        // 1. Cek Status: Harus langsung diizinkan Absen Pulang
        $statusResp = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson("/api/v1/admin/absensi/check-status/{$personnel->id}");

        $statusResp->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('can_attend', true)
            ->assertJsonPath('action_type', 'pulang')
            ->assertJsonPath('data.action_type', 'pulang')
            ->assertJsonPath('data.shift.name', 'Pagi (08:00 - 16:00)');

        // 2. Simpan Presensi Pulang (Direct Check-Out)
        $storeResp = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/v1/admin/absensi/store', [
                'personnel_id' => $personnel->id,
                'foto' => $this->generateDummyImageBase64(),
                'lat' => $this->kantor1->latitude,
                'lng' => $this->kantor1->longitude,
            ]);

        $storeResp->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.action_type', 'pulang')
            ->assertJsonPath('data.status', 'HADIR')
            ->assertJsonPath('data.status_masuk', 'ALPA')
            ->assertJsonPath('data.status_pulang', 'HADIR');

        // 3. Verifikasi Data Tersimpan di Database
        $absensi = Absensi::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', '2026-09-10')
            ->first();

        $this->assertNotNull($absensi);
        $this->assertEquals('HADIR', $absensi->status);
        $this->assertEquals('ALPA', $absensi->status_masuk);
        $this->assertNull($absensi->jam_masuk);
        $this->assertEquals('HADIR', $absensi->status_pulang);
        $this->assertNotNull($absensi->jam_pulang);

        // 4. Scan ulang setelah direct checkout -> status selesai
        $recheckResp = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson("/api/v1/admin/absensi/check-status/{$personnel->id}");

        $recheckResp->assertStatus(200)
            ->assertJsonPath('can_attend', false)
            ->assertJsonPath('action_type', 'selesai');

        Carbon::setTestNow(); // Reset test time
    }

    public function test_night_shift_direct_check_out_next_day_morning(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnel = Personnel::create([
            'name' => 'Budi Shift Malam',
            'nik' => '1234567890123499',
            'email' => 'budi.malam@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'opd_id' => $this->opd1->id,
            'kantor_id' => $this->kantor1->id,
            'penugasan_id' => $penugasan->id,
        ]);

        // Shift Malam: 20:00 s/d 08:00 (lintas hari)
        $shiftMalam = Shift::create([
            'name' => 'Malam (20:00 - 08:00)',
            'type' => 'shift',
            'start_time' => '20:00:00',
            'end_time' => '08:00:00',
        ]);

        // Jadwal kemarin (2026-09-09)
        Jadwal::create([
            'personnel_id' => $personnel->id,
            'shift_id' => $shiftMalam->id,
            'tanggal' => '2026-09-09',
            'status' => 'SHIFT',
        ]);

        // Simulasi hari ini pagi pukul 08:05 WIB (jendela pulang shift kemarin)
        Carbon::setTestNow(Carbon::parse('2026-09-10 08:05:00'));

        // 1. Cek Status: mengenali shift malam kemarin dan siap absen pulang
        $statusResp = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson("/api/v1/admin/absensi/check-status/{$personnel->id}");

        $statusResp->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('can_attend', true)
            ->assertJsonPath('action_type', 'pulang')
            ->assertJsonPath('data.shift.name', 'Malam (20:00 - 08:00)');

        // 2. Simpan Presensi Pulang
        $storeResp = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/v1/admin/absensi/store', [
                'personnel_id' => $personnel->id,
                'foto' => $this->generateDummyImageBase64(),
                'lat' => $this->kantor1->latitude,
                'lng' => $this->kantor1->longitude,
            ]);

        $storeResp->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.action_type', 'pulang')
            ->assertJsonPath('data.tanggal', '2026-09-09')
            ->assertJsonPath('data.status', 'HADIR')
            ->assertJsonPath('data.status_masuk', 'ALPA')
            ->assertJsonPath('data.status_pulang', 'HADIR');

        // 3. Verifikasi Database
        $absensi = Absensi::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', '2026-09-09')
            ->first();

        $this->assertNotNull($absensi);
        $this->assertEquals('HADIR', $absensi->status);
        $this->assertEquals('ALPA', $absensi->status_masuk);
        $this->assertNull($absensi->jam_masuk);
        $this->assertEquals('HADIR', $absensi->status_pulang);
        $this->assertNotNull($absensi->jam_pulang);

        Carbon::setTestNow(); // Reset test time
    }

    public function test_personnel_without_check_in_blocked_during_gap_between_in_and_out(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnel = Personnel::create([
            'name' => 'Budi Siang',
            'nik' => '1234567890123477',
            'email' => 'budi.siang@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'opd_id' => $this->opd1->id,
            'kantor_id' => $this->kantor1->id,
            'penugasan_id' => $penugasan->id,
        ]);

        $shift = Shift::create([
            'name' => 'Pagi (08:00 - 16:00)',
            'type' => 'shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        Jadwal::create([
            'personnel_id' => $personnel->id,
            'shift_id' => $shift->id,
            'tanggal' => '2026-09-10',
            'status' => 'SHIFT',
        ]);

        // Simulasi pukul 12:00 WIB (window in sudah tutup, window out belum buka)
        Carbon::setTestNow(Carbon::parse('2026-09-10 12:00:00'));

        $statusResp = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson("/api/v1/admin/absensi/check-status/{$personnel->id}");

        $statusResp->assertStatus(200)
            ->assertJsonPath('can_attend', false)
            ->assertJsonPath('action_type', 'belum_pulang');

        Carbon::setTestNow();
    }

    public function test_admin_can_get_personnels_paginated_with_stats(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Staf']);

        // Buat 25 personil: 15 punya 192D, 10 tidak punya
        for ($i = 1; $i <= 25; $i++) {
            Personnel::create([
                'name' => 'Personel ' . str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'nik' => '14710100000000' . str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'email' => "p{$i}@example.com",
                'password' => bcrypt('password'),
                'foto' => $i <= 20 ? "personnel/p{$i}.jpg" : '',
                'face_descriptor_mobile' => $i <= 15 ? json_encode(array_fill(0, 192, 0.01)) : null,
                'opd_id' => $this->opd1->id,
                'penugasan_id' => $penugasan->id,
            ]);
        }

        // 1. Test Paginated: per_page=10, page=1
        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson('/api/v1/admin/absensi/personnels?paginate=true&per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total', 25)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.has_more', true)
            ->assertJsonPath('data.stats.total', 25)
            ->assertJsonPath('data.stats.with_photo', 20)
            ->assertJsonPath('data.stats.ready_192', 15)
            ->assertJsonPath('data.stats.missing_192', 10);

        $this->assertCount(10, $response->json('data.personnels'));

        // 2. Test Filter missing_192
        $filterResp = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson('/api/v1/admin/absensi/personnels?paginate=true&filter=missing_192&per_page=15');

        $filterResp->assertStatus(200)
            ->assertJsonPath('data.total', 10);
        $this->assertCount(10, $filterResp->json('data.personnels'));

        // 3. Test Search
        $searchResp = $this->withHeader('Authorization', "Bearer $token1")
            ->getJson('/api/v1/admin/absensi/personnels?paginate=true&search=Personel 05');

        $searchResp->assertStatus(200)
            ->assertJsonPath('data.total', 1);
        $this->assertEquals('Personel 05', $searchResp->json('data.personnels.0.name'));
    }
}

