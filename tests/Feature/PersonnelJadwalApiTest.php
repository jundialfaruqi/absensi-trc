<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelJadwalApiTest extends TestCase
{
    use RefreshDatabase;

    protected Opd $opd;
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

        $penugasan = \App\Models\Penugasan::create([
            'name' => 'Tim Reaksi Cepat',
        ]);

        $this->personnel = Personnel::create([
            'opd_id' => $this->opd->id,
            'penugasan_id' => $penugasan->id,
            'name' => 'Budi Jadwal',
            'nik' => '1471012345670009',
            'email' => 'budi.jadwal@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->device = Device::create([
            'opd_id' => $this->opd->id,
            'personnel_id' => $this->personnel->id,
            'name' => 'HP Budi',
            'license_key' => 'JJJJ-KKKK-LLLL',
            'unique_device_id' => 'device_budi_jadwal_id',
            'status' => 'active',
        ]);

        $this->accessToken = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $this->jwtService->generatePersonnelRefreshToken(
            $this->personnel,
            $this->device->id,
            $this->device->unique_device_id
        );
    }

    public function test_jadwal_endpoint_requires_jwt_auth(): void
    {
        $response = $this->getJson('/api/v1/personel/jadwal?month=5&year=2026');
        $response->assertStatus(401);
    }

    public function test_jadwal_endpoint_returns_monthly_calendar_and_shift_details(): void
    {
        $shiftPagi = Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'type' => 'shift',
        ]);

        $shiftOff = Shift::create([
            'name' => 'Libur',
            'start_time' => null,
            'end_time' => null,
            'type' => 'off',
            'keterangan' => 'Hari Libur Rutin',
        ]);

        Jadwal::create([
            'personnel_id' => $this->personnel->id,
            'shift_id' => $shiftPagi->id,
            'tanggal' => '2026-05-10',
            'status' => 'SHIFT',
        ]);

        Jadwal::create([
            'personnel_id' => $this->personnel->id,
            'shift_id' => $shiftOff->id,
            'tanggal' => '2026-05-11',
            'status' => 'LIBUR',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/jadwal?month=5&year=2026');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'month' => 5,
                    'year' => 2026,
                ],
            ]);

        $jadwals = $response->json('data.jadwal');
        $this->assertCount(31, $jadwals);

        // Hari ke-10 (index 9)
        $this->assertEquals('2026-05-10', $jadwals[9]['tanggal']);
        $this->assertEquals('Shift Pagi', $jadwals[9]['shift_name']);
        $this->assertEquals('08:00 - 16:00', $jadwals[9]['shift_hours']);
        $this->assertFalse($jadwals[9]['is_off']);

        // Hari ke-11 (index 10)
        $this->assertEquals('2026-05-11', $jadwals[10]['tanggal']);
        $this->assertEquals('Libur', $jadwals[10]['shift_name']);
        $this->assertNull($jadwals[10]['shift_hours']);
        $this->assertTrue($jadwals[10]['is_off']);

        // Hari ke-1 (index 0 - tidak ada jadwal dibuat)
        $this->assertEquals('2026-05-01', $jadwals[0]['tanggal']);
        $this->assertEquals('-', $jadwals[0]['shift_name']);
        $this->assertNull($jadwals[0]['shift_hours']);
    }
}
