<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFaceVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminOpd1;
    protected User $adminOpd2;
    protected Opd $opd1;
    protected Opd $opd2;
    protected Penugasan $penugasan;
    protected Personnel $personnelOpd1;
    protected Personnel $personnelOpd2;
    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'admin-opd']);

        $this->jwtService = app(JwtService::class);

        $this->opd1 = Opd::create(['name' => 'Badan Penanggulangan Bencana', 'singkatan' => 'BPBD']);
        $this->opd2 = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);
        $this->penugasan = Penugasan::create(['name' => 'Tim Reaksi Cepat']);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->superAdmin->assignRole('super-admin');

        $this->adminOpd1 = User::create([
            'name' => 'Admin BPBD',
            'email' => 'admin.bpbd@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->adminOpd1->assignRole('admin-opd');
        $this->adminOpd1->opds()->attach($this->opd1->id);

        $this->adminOpd2 = User::create([
            'name' => 'Admin DISHUB',
            'email' => 'admin.dishub@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->adminOpd2->assignRole('admin-opd');
        $this->adminOpd2->opds()->attach($this->opd2->id);

        // Buat Personel OPD 1 yang pending verifikasi
        $this->personnelOpd1 = Personnel::create([
            'name' => 'Budi BPBD',
            'nik' => '1471010101010001',
            'email' => 'budi.bpbd@example.com',
            'password' => bcrypt('secret123'),
            'pin' => '123456',
            'nomor_hp' => '081234567890',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan->id,
            'face_verification_status' => 'PENDING',
            'face_recognition' => false,
        ]);

        PersonnelFaceEmbedding::create([
            'personnel_id' => $this->personnelOpd1->id,
            'pose_type' => 'FRONT',
            'face_descriptor_mobile' => json_encode(array_fill(0, 192, 0.1)),
            'foto' => 'personnel-fotos/poses/test_front.jpg',
        ]);

        // Buat Personel OPD 2 yang pending verifikasi
        $this->personnelOpd2 = Personnel::create([
            'name' => 'Joko DISHUB',
            'nik' => '1471020202020002',
            'email' => 'joko.dishub@example.com',
            'password' => bcrypt('secret123'),
            'pin' => '654321',
            'nomor_hp' => '081234567891',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $this->penugasan->id,
            'face_verification_status' => 'PENDING',
            'face_recognition' => false,
        ]);
    }

    public function test_super_admin_can_see_all_pending_face_verifications(): void
    {
        $token = $this->jwtService->generateAccessToken($this->superAdmin);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/face-verifications');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_opd_can_only_see_pending_verifications_from_own_opd(): void
    {
        $token = $this->jwtService->generateAccessToken($this->adminOpd1);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/face-verifications');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->personnelOpd1->id)
            ->assertJsonPath('data.0.name', 'Budi BPBD');
    }

    public function test_admin_opd_cannot_view_or_approve_other_opd_personnel(): void
    {
        $token = $this->jwtService->generateAccessToken($this->adminOpd1);

        // Coba lihat personel OPD 2
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/face-verifications/' . $this->personnelOpd2->id);

        $response->assertStatus(403);

        // Coba approve personel OPD 2
        $approveResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/face-verifications/' . $this->personnelOpd2->id . '/approve');

        $approveResponse->assertStatus(403);
    }

    public function test_admin_can_approve_face_verification_successfully(): void
    {
        $token = $this->jwtService->generateAccessToken($this->adminOpd1);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/face-verifications/' . $this->personnelOpd1->id . '/approve');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'APPROVED');

        $fresh = $this->personnelOpd1->fresh();
        $this->assertEquals('APPROVED', $fresh->face_verification_status);
        $this->assertTrue((bool)$fresh->face_recognition);
        $this->assertNotNull($fresh->face_verified_at);
        $this->assertEquals($this->adminOpd1->id, $fresh->face_verified_by);
    }

    public function test_admin_can_reject_face_verification_with_mandatory_reason(): void
    {
        $token = $this->jwtService->generateAccessToken($this->adminOpd1);

        // Reject tanpa alasan harus ditolak validasi
        $invalidResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/face-verifications/' . $this->personnelOpd1->id . '/reject', []);

        $invalidResponse->assertStatus(422);

        // Reject dengan alasan
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/face-verifications/' . $this->personnelOpd1->id . '/reject', [
                'notes' => 'Foto wajah tidak jelas dan posisi miring. Harap ulangi perekaman.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'REJECTED')
            ->assertJsonPath('data.notes', 'Foto wajah tidak jelas dan posisi miring. Harap ulangi perekaman.');

        $fresh = $this->personnelOpd1->fresh();
        $this->assertEquals('REJECTED', $fresh->face_verification_status);
        $this->assertEquals('Foto wajah tidak jelas dan posisi miring. Harap ulangi perekaman.', $fresh->face_verification_notes);
        $this->assertFalse((bool)$fresh->face_recognition);
    }

    public function test_kordinator_and_admin_absen_are_rejected_with_403(): void
    {
        Role::firstOrCreate(['name' => 'kordinator']);
        Role::firstOrCreate(['name' => 'admin-absen']);

        $kordinator = User::create([
            'name' => 'Kordinator User',
            'email' => 'kordinator.test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $kordinator->assignRole('kordinator');

        $adminAbsen = User::create([
            'name' => 'Admin Absen User',
            'email' => 'adminabsen.test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $adminAbsen->assignRole('admin-absen');

        $tokenKordinator = $this->jwtService->generateAccessToken($kordinator);
        $tokenAdminAbsen = $this->jwtService->generateAccessToken($adminAbsen);

        // Test index endpoint
        $this->withHeader('Authorization', "Bearer $tokenKordinator")
            ->getJson('/api/v1/admin/face-verifications')
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $tokenAdminAbsen")
            ->getJson('/api/v1/admin/face-verifications')
            ->assertStatus(403);

        // Test show endpoint
        $this->withHeader('Authorization', "Bearer $tokenKordinator")
            ->getJson('/api/v1/admin/face-verifications/' . $this->personnelOpd1->id)
            ->assertStatus(403);

        // Test approve endpoint
        $this->withHeader('Authorization', "Bearer $tokenAdminAbsen")
            ->postJson('/api/v1/admin/face-verifications/' . $this->personnelOpd1->id . '/approve')
            ->assertStatus(403);
    }
}
