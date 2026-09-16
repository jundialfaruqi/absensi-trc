<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use App\Models\PersonnelFaceLearningLog;
use App\Models\User;
use App\Services\AdaptiveFaceLearningService;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdaptiveFaceLearningTest extends TestCase
{
    use RefreshDatabase;

    protected Opd $opd;
    protected Kantor $kantor;
    protected User $admin;
    protected Personnel $personnel;
    protected string $adminToken;
    protected array $unitVector1;
    protected array $unitVector2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin-opd']);
        Role::create(['name' => 'super-admin']);

        $this->opd = Opd::create([
            'name' => 'Badan Penanggulangan Bencana',
            'singkatan' => 'BPBD',
        ]);

        $this->kantor = Kantor::create([
            'name' => 'Markas Komando BPBD',
            'opd_id' => $this->opd->id,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meter' => 100,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Komandan Regu',
            'email' => 'danru@bpbd.go.id',
        ]);
        $this->admin->assignRole('admin-opd');
        $this->admin->opds()->attach($this->opd->id);

        $jwtService = app(JwtService::class);
        $this->adminToken = $jwtService->generateAccessToken($this->admin);

        // Vector 1 (unit vector 192-D)
        $this->unitVector1 = array_fill(0, 192, 0.0);
        $this->unitVector1[0] = 1.0;

        // Vector 2 (slightly rotated: ~0.90 similarity to vector 1)
        // 0.90 * e_0 + sqrt(1 - 0.90^2) * e_1
        $this->unitVector2 = array_fill(0, 192, 0.0);
        $this->unitVector2[0] = 0.90;
        $this->unitVector2[1] = sqrt(1.0 - 0.90 * 0.90);

        $penugasan = \App\Models\Penugasan::create(['name' => 'Petugas Lapangan']);

        // Personnel dengan Master Anchor
        $this->personnel = Personnel::create([
            'name' => 'Agus Prajurit',
            'nik' => '1234567890123456',
            'email' => 'agus@bpbd.go.id',
            'password' => bcrypt('secret123'),
            'opd_id' => $this->opd->id,
            'kantor_id' => $this->kantor->id,
            'penugasan_id' => $penugasan->id,
            'foto' => 'personnel-fotos/test.jpg',
            'face_descriptor_mobile' => json_encode($this->unitVector1),
            'face_recognition' => true,
        ]);

        PersonnelFaceEmbedding::create([
            'personnel_id' => $this->personnel->id,
            'pose_type' => 'FRONT',
            'face_descriptor_mobile' => json_encode($this->unitVector1),
        ]);
    }

    public function test_gate_1_rejects_confidence_below_85_percent(): void
    {
        $service = app(AdaptiveFaceLearningService::class);

        $result = $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $this->unitVector2,
            confidenceScore: 0.82 // 82% < 85%
        );

        $this->assertFalse($result['adapted']);
        $this->assertStringContainsString('Gate 1', $result['reason']);
        $this->assertDatabaseCount('personnel_face_learning_logs', 0);
    }

    public function test_gate_3_rejects_unstable_geometry(): void
    {
        $service = app(AdaptiveFaceLearningService::class);

        $result = $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $this->unitVector2,
            confidenceScore: 0.92,
            eulerAngles: ['yaw' => 25.0, 'pitch' => 5.0, 'roll' => 2.0] // Yaw > 15 deg
        );

        $this->assertFalse($result['adapted']);
        $this->assertStringContainsString('Gate 3', $result['reason']);
    }

    public function test_gate_4_drift_guard_rejects_excessive_drift(): void
    {
        $service = app(AdaptiveFaceLearningService::class);

        // Vector orthogonal (sim = 0.0 to master vector)
        $orthogonalVector = array_fill(0, 192, 0.0);
        $orthogonalVector[10] = 1.0;

        // Force previous adaptive vector already at margin
        $embedding = $this->personnel->faceEmbeddings()->where('pose_type', 'FRONT')->first();
        // Set previous template far away
        $farVector = array_fill(0, 192, 0.0);
        $farVector[0] = 0.65;
        $farVector[1] = sqrt(1.0 - 0.65 * 0.65);
        $embedding->update(['adaptive_descriptor_mobile' => json_encode($farVector)]);

        $result = $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $orthogonalVector,
            confidenceScore: 0.95
        );

        $this->assertFalse($result['adapted']);
        $this->assertStringContainsString('Gate 4', $result['reason']);
    }

    public function test_valid_adaptation_passes_and_increments_counter(): void
    {
        $service = app(AdaptiveFaceLearningService::class);

        $result = $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $this->unitVector2,
            confidenceScore: 0.89,
            poseType: 'FRONT'
        );

        $this->assertTrue($result['adapted']);
        $this->assertEquals(1, $result['adaptation_count']);
        $this->assertGreaterThanOrEqual(0.70, $result['drift_to_master']);

        $embedding = $this->personnel->faceEmbeddings()->where('pose_type', 'FRONT')->first()->fresh();
        $this->assertNotNull($embedding->adaptive_descriptor_mobile);
        $this->assertEquals(1, $embedding->adaptation_count);
        $this->assertNotNull($embedding->last_adapted_at);

        $this->assertDatabaseHas('personnel_face_learning_logs', [
            'personnel_id' => $this->personnel->id,
            'pose_type' => 'FRONT',
            'adaptation_index' => 1,
        ]);
    }

    public function test_gate_2_rate_limits_adaptation_to_once_per_day(): void
    {
        $service = app(AdaptiveFaceLearningService::class);

        // First adaptation succeeds
        $res1 = $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $this->unitVector2,
            confidenceScore: 0.90
        );
        $this->assertTrue($res1['adapted']);

        // Second adaptation on the same day is rejected
        $res2 = $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $this->unitVector2,
            confidenceScore: 0.92
        );
        $this->assertFalse($res2['adapted']);
        $this->assertStringContainsString('Gate 2', $res2['reason']);
    }

    public function test_reset_to_master_clears_adaptive_template(): void
    {
        $service = app(AdaptiveFaceLearningService::class);

        // Run adaptation first
        $service->attemptAdaptation(
            personnel: $this->personnel,
            capturedDescriptor: $this->unitVector2,
            confidenceScore: 0.90
        );

        $embedding = $this->personnel->faceEmbeddings()->where('pose_type', 'FRONT')->first()->fresh();
        $this->assertEquals(1, $embedding->adaptation_count);

        // Execute reset
        $resetResult = $service->resetToMaster($this->personnel);
        $this->assertTrue($resetResult['success']);

        $embedding->refresh();
        $this->assertNull($embedding->adaptive_descriptor_mobile);
        $this->assertEquals(0, $embedding->adaptation_count);
        $this->assertNull($embedding->last_adapted_at);
    }

    public function test_api_reset_face_learning_endpoint(): void
    {
        // Set an active adaptation
        $embedding = $this->personnel->faceEmbeddings()->where('pose_type', 'FRONT')->first();
        $embedding->update([
            'adaptive_descriptor_mobile' => json_encode($this->unitVector2),
            'adaptation_count' => 3,
            'last_adapted_at' => Carbon::now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson("/api/v1/admin/personnels/{$this->personnel->id}/reset-face-learning");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $embedding->refresh();
        $this->assertNull($embedding->adaptive_descriptor_mobile);
        $this->assertEquals(0, $embedding->adaptation_count);
    }

    public function test_api_personnels_returns_adaptive_metadata(): void
    {
        $embedding = $this->personnel->faceEmbeddings()->where('pose_type', 'FRONT')->first();
        $embedding->update([
            'adaptive_descriptor_mobile' => json_encode($this->unitVector2),
            'adaptation_count' => 5,
            'last_adapted_at' => Carbon::now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/v1/admin/absensi/personnels?search=Agus");

        $response->assertStatus(200);
        $data = $response->json('data.personnels.0');

        $this->assertEquals('Agus Prajurit', $data['name']);
        $this->assertTrue($data['has_adaptive']);
        $this->assertEquals(5, $data['total_adaptations']);
        $this->assertEquals(5, $data['multi_face_descriptors'][0]['adaptation_count']);
        $this->assertNotNull($data['multi_face_descriptors'][0]['adaptive_descriptor_mobile']);
    }
}
