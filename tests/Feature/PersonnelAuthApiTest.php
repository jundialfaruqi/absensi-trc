<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use App\Models\PersonnelRefreshToken;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelAuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected Opd $opd;
    protected Personnel $personnel;
    protected Device $device;
    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = app(JwtService::class);

        $this->opd = Opd::create([
            'name' => 'BPBD Kota Pekanbaru',
            'singkatan' => 'BPBD',
        ]);

        $penugasan = \App\Models\Penugasan::create([
            'name' => 'Tim Reaksi Cepat',
        ]);

        $this->personnel = Personnel::create([
            'opd_id' => $this->opd->id,
            'penugasan_id' => $penugasan->id,
            'name' => 'Budi Santoso',
            'nik' => '1471012345670001',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'foto' => 'personnel/budi.jpg',
            'nomor_hp' => '081234567890',
            'face_recognition' => false,
        ]);

        $this->device = Device::create([
            'opd_id' => $this->opd->id,
            'personnel_id' => $this->personnel->id,
            'name' => 'HP Budi',
            'license_key' => 'TRC-BUDI-1234',
            'unique_device_id' => 'device_original_id',
            'status' => 'active',
        ]);

        $this->jwtService->generatePersonnelRefreshToken(
            $this->personnel,
            $this->device->id,
            $this->device->unique_device_id
        );
    }

    /**
     * Test aktivasi lisensi perdana berhasil dan menghasilkan token JWT.
     */
    public function test_activate_license_success_and_returns_jwt(): void
    {
        $response = $this->postJson('/api/v1/personel/auth/activate', [
            'license_key' => 'TRC-BUDI-1234',
            'device_id' => 'device_phone_1',
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
            'android_version' => '14',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('needs_face_enrollment', true)
            ->assertJsonPath('personnel.id', $this->personnel->id)
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
                'needs_face_enrollment',
                'personnel',
                'device',
            ]);

        // Perangkat harus berubah menjadi device_phone_1 dan aktif
        $this->assertDatabaseHas('devices', [
            'id' => $this->device->id,
            'unique_device_id' => 'device_phone_1',
            'status' => 'active',
        ]);

        // Token refresh harus tersimpan
        $this->assertDatabaseHas('personnel_refresh_tokens', [
            'personnel_id' => $this->personnel->id,
            'unique_device_id' => 'device_phone_1',
            'revoked_at' => null,
        ]);
    }

    /**
     * Test Auto-Takeover: Perangkat kedua aktivasi dengan lisensi yang sama,
     * menyebabkan token perangkat lama otomatis dicabut (revoked).
     */
    public function test_auto_takeover_switches_device_and_revokes_old_tokens(): void
    {
        // 1. Aktivasi di HP Pertama
        $res1 = $this->postJson('/api/v1/personel/auth/activate', [
            'license_key' => 'TRC-BUDI-1234',
            'device_id' => 'hp_lama_alpha',
        ]);
        $res1->assertStatus(200);
        $oldRefreshToken = $res1->json('refresh_token');

        // 2. Aktivasi di HP Kedua (Takeover)
        $res2 = $this->postJson('/api/v1/personel/auth/activate', [
            'license_key' => 'TRC-BUDI-1234',
            'device_id' => 'hp_baru_beta',
        ]);
        $res2->assertStatus(200);
        $newRefreshToken = $res2->json('refresh_token');

        // Pastikan device_id di database sudah beralih ke HP Baru
        $this->assertEquals('hp_baru_beta', $this->device->fresh()->unique_device_id);

        // 3. HP Lama mencoba refresh token -> harus GAGAL (401) karena telah dicabut
        $resOldRefresh = $this->postJson('/api/v1/personel/auth/refresh', [
            'refresh_token' => $oldRefreshToken,
            'device_id' => 'hp_lama_alpha',
        ]);
        $resOldRefresh->assertStatus(401)
            ->assertJsonPath('status', 'error');

        // 4. HP Baru mencoba refresh token -> harus SUKSES (200)
        $resNewRefresh = $this->postJson('/api/v1/personel/auth/refresh', [
            'refresh_token' => $newRefreshToken,
            'device_id' => 'hp_baru_beta',
        ]);
        $resNewRefresh->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['access_token', 'refresh_token']);
    }

    /**
     * Test endpoint /me memerlukan JWT token yang valid.
     */
    public function test_me_endpoint_requires_jwt_and_returns_profile(): void
    {
        // Tanpa token -> 401
        $this->getJson('/api/v1/personel/auth/me')
            ->assertStatus(401);

        // Dengan token valid
        $token = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/personel/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('personnel.id', $this->personnel->id)
            ->assertJsonPath('personnel.name', 'Budi Santoso');
    }

    /**
     * Test perekaman wajah 3D (4 pose) berhasil menyimpan vektor 192D ke database.
     */
    public function test_face_enrollment_stores_4_poses_and_updates_personnel_192d(): void
    {
        $token = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $vec192Front = array_fill(0, 192, 0.05);
        $vec192Right = array_fill(0, 192, 0.06);
        $vec192Left  = array_fill(0, 192, 0.07);
        $vec192Up    = array_fill(0, 192, 0.08);

        $payload = [
            'poses' => [
                ['pose_type' => 'FRONT', 'face_descriptor_mobile' => $vec192Front],
                ['pose_type' => 'RIGHT', 'face_descriptor_mobile' => $vec192Right],
                ['pose_type' => 'LEFT',  'face_descriptor_mobile' => $vec192Left],
                ['pose_type' => 'UP',    'face_descriptor_mobile' => $vec192Up],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/personel/face-enroll', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('has_192d', true)
            ->assertJsonPath('face_recognition_enabled', true);

        // Model Personnel utama harus terisi pose FRONT
        $freshPersonnel = $this->personnel->fresh();
        $this->assertNotNull($freshPersonnel->face_descriptor_mobile);
        $this->assertCount(192, json_decode($freshPersonnel->face_descriptor_mobile, true));
        $this->assertTrue((bool)$freshPersonnel->face_recognition);

        // Tabel personnel_face_embeddings harus memiliki 4 pose
        $this->assertEquals(4, PersonnelFaceEmbedding::where('personnel_id', $this->personnel->id)->count());
    }

    /**
     * Test endpoint face-template mengembalikan master 192D dan 4 poses.
     */
    public function test_face_template_returns_master_and_poses(): void
    {
        $vec192 = array_fill(0, 192, 0.09);
        $this->personnel->update([
            'face_descriptor_mobile' => json_encode($vec192),
            'face_recognition' => true,
        ]);

        PersonnelFaceEmbedding::create([
            'personnel_id' => $this->personnel->id,
            'pose_type' => 'FRONT',
            'face_descriptor_mobile' => json_encode($vec192),
        ]);

        $token = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/personel/face-template');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('personnel_id', $this->personnel->id)
            ->assertJsonPath('total_poses', 1);

        $this->assertCount(192, $response->json('master_descriptor_192'));
    }

    /**
     * Test perangkat yang disuspend atau diblokir langsung ditolak dengan HTTP 403.
     */
    public function test_suspended_device_is_rejected_with_403(): void
    {
        $this->device->update(['status' => 'suspended']);
        $token = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/personel/auth/me');

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    /**
     * Test jika seluruh refresh token dihapus/dicabut, sesi langsung ditolak dengan HTTP 401.
     */
    public function test_revoked_or_empty_refresh_tokens_rejected_with_401(): void
    {
        // Kosongkan refresh token personel
        PersonnelRefreshToken::where('personnel_id', $this->personnel->id)->delete();
        $token = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/personel/auth/me');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }
}
