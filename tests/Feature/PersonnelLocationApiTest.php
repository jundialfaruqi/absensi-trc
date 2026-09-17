<?php

namespace Tests\Feature;

use App\Events\PersonnelLocationUpdated;
use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\PersonnelRefreshToken;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PersonnelLocationApiTest extends TestCase
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

    public function test_can_update_personnel_location_and_broadcast_event(): void
    {
        Event::fake([PersonnelLocationUpdated::class]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'X-Device-Id' => $this->device->unique_device_id,
        ])->postJson('/api/v1/personel/device/location', [
            'latitude' => 0.508123,
            'longitude' => 101.449123,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Lokasi perangkat berhasil diperbarui.',
            ]);

        $this->device->refresh();
        $this->assertEquals(0.508123, (float) $this->device->last_latitude);
        $this->assertEquals(101.449123, (float) $this->device->last_longitude);
        $this->assertNotNull($this->device->last_seen_at);

        Event::assertDispatched(PersonnelLocationUpdated::class, function ($event) {
            return $event->personnel_id === $this->personnel->id
                && $event->latitude == 0.508123
                && $event->longitude == 101.449123
                && $event->device_id === $this->device->id;
        });
    }

    public function test_update_location_validates_coordinates(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'X-Device-Id' => $this->device->unique_device_id,
        ])->postJson('/api/v1/personel/device/location', [
            'latitude' => 'invalid-lat',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_update_location_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/personel/device/location', [
            'latitude' => 0.508123,
            'longitude' => 101.449123,
        ]);

        $response->assertStatus(401);
    }
}
