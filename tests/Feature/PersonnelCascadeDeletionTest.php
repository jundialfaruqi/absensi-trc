<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\PersonnelFaceEmbedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonnelCascadeDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_personnel_deletes_associated_devices_and_3d_pose_photos(): void
    {
        Storage::fake('public');

        $opd = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);

        // 1. Buat file dummy di storage
        $mainPhoto = 'personnel-fotos/main_test.jpg';
        $frontPhoto = 'personnel-fotos/poses/front_test.jpg';
        $rightPhoto = 'personnel-fotos/poses/right_test.jpg';
        $leftPhoto = 'personnel-fotos/poses/left_test.jpg';
        $upPhoto = 'personnel-fotos/poses/up_test.jpg';

        Storage::disk('public')->put($mainPhoto, 'dummy content');
        Storage::disk('public')->put($frontPhoto, 'dummy content');
        Storage::disk('public')->put($rightPhoto, 'dummy content');
        Storage::disk('public')->put($leftPhoto, 'dummy content');
        Storage::disk('public')->put($upPhoto, 'dummy content');

        // 2. Buat record personnel
        $personnel = Personnel::create([
            'name' => 'Budi Santoso',
            'nik' => '1234567890123456',
            'opd_id' => $opd->id,
            'penugasan_id' => 1,
            'pin' => '123456',
            'foto' => $mainPhoto,
            'email' => 'budi.test@example.com',
            'password' => bcrypt('password'),
            'attendance_type' => 'SCHEDULED',
        ]);

        // 3. Buat 4 pose 3D
        PersonnelFaceEmbedding::create([
            'personnel_id' => $personnel->id,
            'pose_type' => 'FRONT',
            'foto' => $frontPhoto,
        ]);
        PersonnelFaceEmbedding::create([
            'personnel_id' => $personnel->id,
            'pose_type' => 'RIGHT',
            'foto' => $rightPhoto,
        ]);
        PersonnelFaceEmbedding::create([
            'personnel_id' => $personnel->id,
            'pose_type' => 'LEFT',
            'foto' => $leftPhoto,
        ]);
        PersonnelFaceEmbedding::create([
            'personnel_id' => $personnel->id,
            'pose_type' => 'UP',
            'foto' => $upPhoto,
        ]);

        // 4. Buat Device terhubung
        $device = Device::create([
            'opd_id' => $opd->id,
            'personnel_id' => $personnel->id,
            'name' => 'HP Budi',
            'license_key' => 'TEST-1234-KEY1',
            'status' => 'active',
        ]);

        $deviceId = $device->id;
        $personnelId = $personnel->id;

        // Pastikan data awal ada
        $this->assertDatabaseHas('personnels', ['id' => $personnelId]);
        $this->assertDatabaseHas('devices', ['id' => $deviceId, 'personnel_id' => $personnelId]);
        $this->assertDatabaseHas('personnel_face_embeddings', ['personnel_id' => $personnelId, 'pose_type' => 'FRONT']);
        $this->assertDatabaseHas('personnel_face_embeddings', ['personnel_id' => $personnelId, 'pose_type' => 'RIGHT']);
        $this->assertTrue(Storage::disk('public')->exists($mainPhoto));
        $this->assertTrue(Storage::disk('public')->exists($frontPhoto));
        $this->assertTrue(Storage::disk('public')->exists($rightPhoto));
        $this->assertTrue(Storage::disk('public')->exists($leftPhoto));
        $this->assertTrue(Storage::disk('public')->exists($upPhoto));

        // 5. Eksekusi hapus personel
        $personnel->delete();

        // 6. Verifikasi database
        $this->assertDatabaseMissing('personnels', ['id' => $personnelId]);
        $this->assertDatabaseMissing('devices', ['id' => $deviceId]);
        $this->assertDatabaseMissing('personnel_face_embeddings', ['personnel_id' => $personnelId]);

        // 7. Verifikasi file storage terhapus
        $this->assertFalse(Storage::disk('public')->exists($mainPhoto));
        $this->assertFalse(Storage::disk('public')->exists($frontPhoto));
        $this->assertFalse(Storage::disk('public')->exists($rightPhoto));
        $this->assertFalse(Storage::disk('public')->exists($leftPhoto));
        $this->assertFalse(Storage::disk('public')->exists($upPhoto));
    }
}
