<?php

namespace Tests\Feature;

use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_admin_can_update_face_descriptor_512_with_valid_data(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnel = Personnel::create([
            'name' => 'Budi Santoso',
            'nik' => '1234567890123456',
            'email' => 'budi.512@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
        ]);

        $valid512 = json_encode(array_fill(0, 512, 0.042));

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson("/api/v1/admin/personnels/{$personnel->id}/face-512", [
                'face_descriptor_512' => $valid512,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.face_descriptor_512_count', 512);

        $this->assertEquals($valid512, $personnel->fresh()->face_descriptor_512);
    }

    public function test_update_face_descriptor_512_rejects_invalid_count_or_malformed_json(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnel = Personnel::create([
            'name' => 'Budi Santoso',
            'nik' => '1234567890123456',
            'email' => 'budi.invalid@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
        ]);

        // Test 1: Only 128 elements instead of 512
        $invalid128 = json_encode(array_fill(0, 128, 0.042));
        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson("/api/v1/admin/personnels/{$personnel->id}/face-512", [
                'face_descriptor_512' => $invalid128,
            ]);
        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Test 2: Arbitrary string injection (anti-vulnerability)
        $responseInject = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson("/api/v1/admin/personnels/{$personnel->id}/face-512", [
                'face_descriptor_512' => "MALICIOUS_PAYLOAD'; DROP TABLE personnels; --",
            ]);
        $responseInject->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_admin_cannot_update_face_descriptor_512_of_other_opd(): void
    {
        [$admin1, $token1] = $this->createAdminUser($this->opd1);
        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        $personnelOpd2 = Personnel::create([
            'name' => 'Agus Satpol',
            'email' => 'agus.other@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/agus.jpg',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $penugasan->id,
        ]);

        $valid512 = json_encode(array_fill(0, 512, 0.042));

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson("/api/v1/admin/personnels/{$personnelOpd2->id}/face-512", [
                'face_descriptor_512' => $valid512,
            ]);

        $response->assertStatus(403);
    }
}
