<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelFcmTokenApiTest extends TestCase
{
    use RefreshDatabase;

    protected Opd $opd;
    protected Kantor $kantor;
    protected Personnel $personnel;
    protected Device $device;
    protected JwtService $jwtService;
    protected string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = app(JwtService::class);

        $this->opd = Opd::create([
            'name' => 'BPBD Kota Pekanbaru',
            'singkatan' => 'BPBD',
        ]);

        $this->kantor = Kantor::create([
            'name' => 'Markas Komando BPBD',
            'opd_id' => $this->opd->id,
            'latitude' => 0.507068,
            'longitude' => 101.447779,
            'radius_meter' => 200,
        ]);

        $penugasan = Penugasan::create([
            'name' => 'Tim Reaksi Cepat',
        ]);

        $this->personnel = Personnel::create([
            'opd_id' => $this->opd->id,
            'kantor_id' => $this->kantor->id,
            'penugasan_id' => $penugasan->id,
            'name' => 'Ahmad Personel',
            'nik' => '1471012345670002',
            'email' => 'ahmad@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->device = Device::create([
            'opd_id' => $this->opd->id,
            'personnel_id' => $this->personnel->id,
            'name' => 'HP Ahmad',
            'license_key' => 'AAAA-BBBB-CCCC',
            'unique_device_id' => 'device_ahmad_id',
            'status' => 'active',
        ]);

        $this->jwtService->generatePersonnelRefreshToken(
            $this->personnel,
            $this->device->id,
            $this->device->unique_device_id
        );

        $this->accessToken = $this->jwtService->generatePersonnelAccessToken($this->personnel);
    }

    public function test_can_update_fcm_token_via_dedicated_endpoint(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'X-Device-Id' => $this->device->unique_device_id,
        ])->postJson('/api/v1/personel/device/fcm-token', [
            'fcm_token' => 'test-fcm-token-12345',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'FCM Token berhasil diperbarui.',
            ]);

        $this->personnel->refresh();
        $this->assertEquals('test-fcm-token-12345', $this->personnel->fcm_token);
    }

    public function test_activate_license_accepts_fcm_token(): void
    {
        $response = $this->postJson('/api/v1/personel/auth/activate', [
            'license_key' => 'AAAA-BBBB-CCCC',
            'device_id' => 'device_new_id',
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
            'fcm_token' => 'fcm-token-from-activation',
        ]);

        $response->assertStatus(200);

        $this->personnel->refresh();
        $this->assertEquals('fcm-token-from-activation', $this->personnel->fcm_token);
    }

    public function test_update_fcm_token_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/personel/device/fcm-token', [
            'fcm_token' => 'test-fcm-token-12345',
        ]);

        $response->assertStatus(401);
    }
}
