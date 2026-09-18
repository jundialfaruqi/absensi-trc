<?php

namespace Tests\Feature;

use App\Events\PersonnelPhotoUpdated;
use App\Events\PersonnelVectorUpdated;
use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPersonnelApiTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminOpdRole;
    protected Role $superAdminRole;
    protected Opd $opd1;
    protected Opd $opd2;
    protected Penugasan $penugasan1;
    protected Penugasan $penugasan2;
    protected Kantor $kantor1;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Event::fake([PersonnelPhotoUpdated::class, PersonnelVectorUpdated::class]);

        $this->adminOpdRole = Role::create(['name' => 'admin-opd']);
        $this->superAdminRole = Role::create(['name' => 'super-admin']);

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

        $this->penugasan1 = Penugasan::create([
            'name' => 'Rescue Lapangan',
        ]);

        $this->penugasan2 = Penugasan::create([
            'name' => 'Patroli Ketertiban',
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

        return ['user' => $user, 'token' => $token];
    }

    public function test_admin_opd_can_list_only_personnel_in_their_opd(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        Personnel::create([
            'name' => 'Budi Santoso',
            'nik' => '1234567890123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081211111111',
            'email' => 'budi@trc.com',
            'pin' => '111111',
            'password' => Hash::make('password'),
        ]);

        Personnel::create([
            'name' => 'Siti Rahma',
            'nik' => '9876543210987654',
            'opd_id' => $this->opd2->id,
            'penugasan_id' => $this->penugasan2->id,
            'nomor_hp' => '081222222222',
            'email' => 'siti@trc.com',
            'pin' => '222222',
            'password' => Hash::make('password'),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->getJson('/api/v1/admin/personnels');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budi Santoso');
    }

    public function test_search_filters_strictly_by_personnel_name_only(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        Personnel::create([
            'name' => 'Ahmad Fauzi',
            'nik' => '1111222233334444',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081233333333',
            'email' => 'ahmad@trc.com',
            'pin' => '123456',
            'password' => Hash::make('password'),
        ]);

        Personnel::create([
            'name' => 'Budi Setiawan',
            'nik' => '5555666677778888',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081244444444',
            'email' => 'budi.setiawan@trc.com',
            'pin' => '654321',
            'password' => Hash::make('password'),
        ]);

        // Search for 'Ahmad' -> matches Ahmad Fauzi
        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->getJson('/api/v1/admin/personnels?search=Ahmad');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Ahmad Fauzi');

        // Search by NIK should NOT match because search is by name only
        $responseNik = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->getJson('/api/v1/admin/personnels?search=55556666');

        $responseNik->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_get_form_options(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->getJson('/api/v1/admin/personnels/form-options');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data.opds')
            ->assertJsonCount(1, 'data.kantors');
        $this->assertGreaterThanOrEqual(2, count($response->json('data.penugasans')));
    }

    public function test_get_personnel_detail(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $personnel = Personnel::create([
            'name' => 'Eko Prasetyo',
            'nik' => '1231231231231234',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'kantor_id' => $this->kantor1->id,
            'nomor_hp' => '081255555555',
            'email' => 'eko@trc.com',
            'pin' => '555666',
            'password' => Hash::make('password'),
            'attendance_type' => 'SCHEDULED',
            'wajib_absen_di_lokasi' => true,
        ]);

        Device::create([
            'opd_id' => $this->opd1->id,
            'personnel_id' => $personnel->id,
            'name' => 'HP Personal - Eko',
            'license_key' => 'ABCD-EFGH-IJKL',
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->getJson("/api/v1/admin/personnels/{$personnel->id}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Eko Prasetyo')
            ->assertJsonPath('data.pin', '555666')
            ->assertJsonPath('data.has_personal_device', true)
            ->assertJsonPath('data.license_key', 'ABCD-EFGH-IJKL');
    }

    public function test_create_personnel_with_auto_pin_and_device(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $payload = [
            'name' => 'Dimas Anggara',
            'nik' => '3201123456780001',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'kantor_id' => $this->kantor1->id,
            'nomor_hp' => '081234567890',
            'attendance_type' => 'SCHEDULED',
            'wajib_absen_di_lokasi' => 1,
            'auto_create_device' => 1,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson('/api/v1/admin/personnels', $payload);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Dimas Anggara')
            ->assertJsonPath('data.email', 'dimasanggara@trc.com');

        $this->assertDatabaseHas('personnels', [
            'name' => 'Dimas Anggara',
            'nik' => '3201123456780001',
            'opd_id' => $this->opd1->id,
            'nomor_hp' => '081234567890',
        ]);

        $this->assertDatabaseHas('devices', [
            'opd_id' => $this->opd1->id,
            'name' => 'HP Personal - Dimas Anggara',
        ]);
    }

    public function test_update_personnel(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $personnel = Personnel::create([
            'name' => 'Rian Hidayat',
            'nik' => '3201123456780002',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081266666666',
            'email' => 'rian@trc.com',
            'pin' => '123123',
            'password' => Hash::make('password'),
        ]);

        $payload = [
            'name' => 'Rian Hidayat Putra',
            'nik' => '3201123456780002',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081266666666',
            'email' => 'rian.putra@trc.com',
            'pin' => '999888',
            'attendance_type' => 'FLEXIBLE',
            'wajib_absen_di_lokasi' => 0,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson("/api/v1/admin/personnels/{$personnel->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('personnels', [
            'id' => $personnel->id,
            'name' => 'Rian Hidayat Putra',
            'nomor_hp' => '081266666666',
            'email' => 'rian.putra@trc.com',
            'pin' => '999888',
            'attendance_type' => 'FLEXIBLE',
        ]);
    }

    public function test_delete_face_data(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $personnel = Personnel::create([
            'name' => 'Doni Siregar',
            'nik' => '3201123456780003',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081277777777',
            'email' => 'doni@trc.com',
            'pin' => '444555',
            'foto' => 'personnel-fotos/doni.jpg',
            'face_descriptor_mobile' => json_encode(array_fill(0, 192, 0.123)),
            'face_recognition' => true,
            'password' => Hash::make('password'),
        ]);

        Storage::disk('public')->put('personnel-fotos/doni.jpg', 'dummy');

        PersonnelFaceEmbedding::create([
            'personnel_id' => $personnel->id,
            'pose_type' => 'FRONT',
            'foto' => 'personnel-fotos/poses/doni_front.jpg',
            'face_descriptor_mobile' => json_encode(array_fill(0, 192, 0.123)),
        ]);
        Storage::disk('public')->put('personnel-fotos/poses/doni_front.jpg', 'dummy');

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->deleteJson("/api/v1/admin/personnels/{$personnel->id}/face-data");

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $personnel->refresh();
        $this->assertNull($personnel->foto);
        $this->assertNull($personnel->face_descriptor_mobile);
        $this->assertFalse((bool)$personnel->face_recognition);
        $this->assertCount(0, $personnel->faceEmbeddings);
        Storage::disk('public')->assertMissing('personnel-fotos/doni.jpg');
        Storage::disk('public')->assertMissing('personnel-fotos/poses/doni_front.jpg');
    }

    public function test_delete_personnel_cascades_devices_and_files(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $personnel = Personnel::create([
            'name' => 'Hendra Saputra',
            'nik' => '3201123456780004',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081288888888',
            'email' => 'hendra@trc.com',
            'pin' => '777888',
            'foto' => 'personnel-fotos/hendra.jpg',
            'password' => Hash::make('password'),
        ]);

        Device::create([
            'opd_id' => $this->opd1->id,
            'personnel_id' => $personnel->id,
            'name' => 'HP Personal - Hendra',
            'license_key' => 'ZXCV-BNMM-LKJH',
            'status' => 'inactive',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->deleteJson("/api/v1/admin/personnels/{$personnel->id}");

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('personnels', ['id' => $personnel->id]);
        $this->assertDatabaseMissing('devices', ['personnel_id' => $personnel->id]);
    }

    public function test_enroll_3d_face_poses(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $personnel = Personnel::create([
            'name' => 'Bayu Pratama',
            'nik' => '3201123456780005',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081299999999',
            'email' => 'bayu@trc.com',
            'pin' => '333222',
            'password' => Hash::make('password'),
        ]);

        $poses = [
            [
                'pose_type' => 'FRONT',
                'face_descriptor_mobile' => array_fill(0, 192, 0.5),
            ],
            [
                'pose_type' => 'RIGHT',
                'face_descriptor_mobile' => array_fill(0, 192, 0.6),
            ],
            [
                'pose_type' => 'LEFT',
                'face_descriptor_mobile' => array_fill(0, 192, 0.7),
            ],
            [
                'pose_type' => 'UP',
                'face_descriptor_mobile' => array_fill(0, 192, 0.8),
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson("/api/v1/admin/personnels/{$personnel->id}/face-enroll", [
                'poses' => $poses,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('has_192d', true)
            ->assertJsonPath('face_recognition_enabled', true)
            ->assertJsonCount(4, 'saved_poses');

        $personnel->refresh();
        $this->assertTrue((bool)$personnel->face_recognition);
        $this->assertNotNull($personnel->face_descriptor_mobile);
        $this->assertCount(4, $personnel->faceEmbeddings);
    }

    public function test_enroll_3d_face_poses_does_not_overwrite_existing_2d_photo(): void
    {
        $admin = $this->createAdminUser($this->opd1);

        $personnel = Personnel::create([
            'name' => 'Citra Lestari',
            'nik' => '3201123456780006',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $this->penugasan1->id,
            'nomor_hp' => '081288888888',
            'email' => 'citra@trc.com',
            'pin' => '444333',
            'foto' => 'personnel-fotos/existing_2d_photo.jpg',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
            'password' => Hash::make('password'),
        ]);

        $poses = [
            [
                'pose_type' => 'FRONT',
                'face_descriptor_mobile' => array_fill(0, 192, 0.5),
            ],
            [
                'pose_type' => 'RIGHT',
                'face_descriptor_mobile' => array_fill(0, 192, 0.6),
            ],
            [
                'pose_type' => 'LEFT',
                'face_descriptor_mobile' => array_fill(0, 192, 0.7),
            ],
            [
                'pose_type' => 'UP',
                'face_descriptor_mobile' => array_fill(0, 192, 0.8),
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson("/api/v1/admin/personnels/{$personnel->id}/face-enroll", [
                'poses' => $poses,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $personnel->refresh();
        // Master 2D photo and 2D descriptor must NOT be overwritten
        $this->assertEquals('personnel-fotos/existing_2d_photo.jpg', $personnel->foto);
        $this->assertEquals(json_encode(array_fill(0, 128, 0.1)), $personnel->face_descriptor);
        // Mobile 192D biometric and embeddings must be updated properly
        $this->assertNotNull($personnel->face_descriptor_mobile);
        $this->assertCount(4, $personnel->faceEmbeddings);
        $this->assertTrue((bool)$personnel->face_recognition);
    }
}
