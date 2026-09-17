<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\Shift;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelAbsensiApiTest extends TestCase
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
            'face_descriptor_mobile' => json_encode(array_fill(0, 192, 0.05)),
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
            $this->device->unique_device_id,
            365
        );

        $this->accessToken = $this->jwtService->generatePersonnelAccessToken($this->personnel);
    }

    protected function generateDummyImageBase64(): string
    {
        ob_start();
        $img = imagecreatetruecolor(10, 10);
        imagejpeg($img);
        $dummyImage = ob_get_clean();
        imagedestroy($img);
        return base64_encode($dummyImage);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/personel/absensi/check-status')->assertStatus(401);
        $this->postJson('/api/v1/personel/absensi/store', [])->assertStatus(401);
    }

    public function test_check_status_returns_selesai_after_direct_check_out(): void
    {
        $shift = Shift::create([
            'name' => 'Pagi (08:00 - 16:00)',
            'type' => 'shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        Jadwal::create([
            'personnel_id' => $this->personnel->id,
            'shift_id' => $shift->id,
            'tanggal' => '2026-09-17',
            'status' => 'SHIFT',
        ]);

        // Simulasi waktu jendela pulang (16:15 WIB)
        Carbon::setTestNow(Carbon::parse('2026-09-17 16:15:00'));

        // 1. Sebelum absen pulang: Direct check-out diizinkan
        $resp1 = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/absensi/check-status');

        $resp1->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('can_attend', true)
            ->assertJsonPath('action_type', 'pulang');

        // 2. Simpan presensi pulang (direct check-out)
        $storeResp = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->postJson('/api/v1/personel/absensi/store', [
                'lat' => $this->kantor->latitude,
                'lng' => $this->kantor->longitude,
                'foto' => $this->generateDummyImageBase64(),
            ]);

        $storeResp->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 3. Cek status kembali: Harus SELESAI, can_attend false, bukan Siap Melakukan Presensi Pulang
        $resp2 = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/absensi/check-status');

        $resp2->assertStatus(200)
            ->assertJsonPath('status', 'info')
            ->assertJsonPath('can_attend', false)
            ->assertJsonPath('action_type', 'selesai')
            ->assertJsonPath('message', 'Anda telah menyelesaikan presensi pulang untuk jadwal hari ini.');

        // 4. Mencoba store lagi saat jam_pulang sudah ada harus ditolak dengan HTTP 422
        $storeAgainResp = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->postJson('/api/v1/personel/absensi/store', [
                'lat' => $this->kantor->latitude,
                'lng' => $this->kantor->longitude,
                'foto' => $this->generateDummyImageBase64(),
            ]);

        $storeAgainResp->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Anda sudah menyelesaikan seluruh sesi absensi untuk jadwal ini.');

        Carbon::setTestNow();
    }

    public function test_check_status_returns_selesai_after_normal_in_and_out(): void
    {
        $shift = Shift::create([
            'name' => 'Pagi (08:00 - 16:00)',
            'type' => 'shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        $jadwal = Jadwal::create([
            'personnel_id' => $this->personnel->id,
            'shift_id' => $shift->id,
            'tanggal' => '2026-09-17',
            'status' => 'SHIFT',
        ]);

        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'jadwal_id' => $jadwal->id,
            'tanggal' => '2026-09-17',
            'jam_masuk' => '2026-09-17 07:55:00',
            'jam_pulang' => '2026-09-17 16:05:00',
            'status' => 'HADIR',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-17 16:15:00'));

        $resp = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/absensi/check-status');

        $resp->assertStatus(200)
            ->assertJsonPath('status', 'info')
            ->assertJsonPath('can_attend', false)
            ->assertJsonPath('action_type', 'selesai')
            ->assertJsonPath('message', 'Anda telah menyelesaikan presensi masuk dan pulang untuk jadwal hari ini.');

        Carbon::setTestNow();
    }

    public function test_check_status_returns_selesai_in_flexible_mode(): void
    {
        $this->personnel->update(['attendance_type' => 'FLEXIBLE']);

        Absensi::create([
            'personnel_id' => $this->personnel->id,
            'tanggal' => '2026-09-17',
            'jam_pulang' => '2026-09-17 16:05:00',
            'status' => 'HADIR',
            'status_pulang' => 'HADIR',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-17 16:15:00'));

        $resp = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
            ->getJson('/api/v1/personel/absensi/check-status');

        $resp->assertStatus(200)
            ->assertJsonPath('status', 'info')
            ->assertJsonPath('can_attend', false)
            ->assertJsonPath('action_type', 'selesai')
            ->assertJsonPath('message', 'Anda telah menyelesaikan presensi pulang hari ini (Mode Fleksibel).');

        Carbon::setTestNow();
    }
}
