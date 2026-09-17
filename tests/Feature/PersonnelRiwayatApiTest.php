<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelRiwayatApiTest extends TestCase
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

        $this->accessToken = $this->jwtService->generatePersonnelAccessToken($this->personnel);

        $this->jwtService->generatePersonnelRefreshToken(
            $this->personnel,
            $this->device->id,
            $this->device->unique_device_id
        );
    }

    public function test_riwayat_endpoint_returns_monthly_records_and_summary(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'type' => 'shift',
        ]);

        $jadwal = Jadwal::create([
            'personnel_id' => $this->personnel->id,
            'shift_id' => $shift->id,
            'tanggal' => '2026-04-15',
        ]);

        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'jadwal_id' => $jadwal->id,
            'tanggal' => '2026-04-15',
            'jam_masuk' => '07:55:00',
            'status_masuk' => 'HADIR',
            'jam_pulang' => '17:05:00',
            'status_pulang' => 'HADIR',
            'status' => 'HADIR',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/absensi/riwayat?month=4&year=2026');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'month' => 4,
                    'year' => 2026,
                    'summary' => [
                        'total_hari' => 30,
                        'total_hadir' => 1,
                        'total_terlambat' => 0,
                        'total_alpa' => 0,
                    ],
                ],
            ]);

        $this->assertCount(30, $response->json('data.riwayat'));
        // Hari ke-15 (index 14)
        $this->assertEquals('Shift Pagi', $response->json('data.riwayat.14.shift_name'));
        $this->assertEquals('HADIR', $response->json('data.riwayat.14.status'));
        // Hari ke-1 (index 0: tidak ada absensi)
        $this->assertNull($response->json('data.riwayat.0.jam_masuk'));
        $this->assertNull($response->json('data.riwayat.0.jam_pulang'));
    }

    public function test_dinas_is_counted_in_total_libur_not_in_total_izin(): void
    {
        // 1. Buat data absensi: 1 IZIN, 1 SAKIT, 1 CUTI, 1 DINAS, 1 LIBUR
        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'tanggal' => '2026-04-01',
            'status' => 'IZIN',
        ]);
        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'tanggal' => '2026-04-02',
            'status' => 'SAKIT',
        ]);
        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'tanggal' => '2026-04-03',
            'status' => 'CUTI',
        ]);
        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'tanggal' => '2026-04-04',
            'status' => 'DINAS',
        ]);
        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'tanggal' => '2026-04-05',
            'status' => 'LIBUR',
        ]);

        // Cek Riwayat API
        $responseRiwayat = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/absensi/riwayat?month=4&year=2026');

        $responseRiwayat->assertStatus(200);
        $summary = $responseRiwayat->json('data.summary');
        
        // total_izin HANYA menghitung IZIN, SAKIT, CUTI (harus 3)
        $this->assertEquals(3, $summary['total_izin']);
        // total_libur harus menghitung LIBUR dan DINAS (minimal 2)
        $this->assertGreaterThanOrEqual(2, $summary['total_libur']);

        // Cek Dashboard Summary API
        $responseDashboard = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/dashboard/summary?month=4&year=2026');

        $responseDashboard->assertStatus(200);
        $dashData = $responseDashboard->json('data');

        $this->assertEquals(3, $dashData['izin_count']);
        $this->assertEquals(2, $dashData['libur_count']);
    }
}
