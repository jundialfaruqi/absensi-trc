<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\DokumentasiKonsumsi;
use App\Models\Jadwal;
use App\Models\Konsumsi;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use App\Services\JwtService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDokumentasiApiTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminOpdRole;
    protected Opd $opd1;
    protected Shift $shiftPagi;
    protected Shift $shiftMalam;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->adminOpdRole = Role::create(['name' => 'admin-opd']);

        $this->opd1 = Opd::create([
            'name' => 'Badan Penanggulangan Bencana Daerah',
            'singkatan' => 'BPBD',
            'alamat' => 'Jl. Sudirman No. 1',
        ]);

        $konsumsiSiang = Konsumsi::create(['nama' => 'Siang']);
        $konsumsiMalam = Konsumsi::create(['nama' => 'Malam']);

        $this->shiftPagi = Shift::create([
            'name' => 'Shift Pagi',
            'type' => 'shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);
        $this->shiftPagi->konsumsis()->attach($konsumsiSiang->id);

        $this->shiftMalam = Shift::create([
            'name' => 'Shift Malam',
            'type' => 'shift',
            'start_time' => '20:00:00',
            'end_time' => '08:00:00',
        ]);
        $this->shiftMalam->konsumsis()->attach($konsumsiMalam->id);
    }

    protected function createAdminUser(Opd $opd): array
    {
        $user = User::create([
            'name' => 'Admin ' . $opd->singkatan,
            'email' => 'admin.' . strtolower($opd->singkatan) . '@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->adminOpdRole);
        $user->opds()->attach($opd->id);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateAccessToken($user);

        return ['user' => $user, 'token' => $token];
    }

    public function test_admin_can_check_dokumentasi_quota_and_existing_records(): void
    {
        $admin = $this->createAdminUser($this->opd1);
        $today = Carbon::now()->format('Y-m-d');

        $penugasan = Penugasan::create(['name' => 'Regu 1']);

        // Personel 1 hadir shift pagi (konsumsi siang)
        $p1 = Personnel::create([
            'name' => 'Personel Satu',
            'nik' => '1111111111111111',
            'email' => 'personel1@trc.com',
            'nomor_hp' => '081234567890',
            'pin' => '123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
            'password' => Hash::make('password'),
        ]);
        Jadwal::create([
            'personnel_id' => $p1->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
        ]);
        Absensi::create([
            'personnel_id' => $p1->id,
            'tanggal' => $today,
            'status' => 'HADIR',
            'jam_masuk' => '07:55:00',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->getJson('/api/v1/admin/dokumentasi/check?tanggal=' . $today);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.calculated_siang', 1)
            ->assertJsonPath('data.calculated_malam', 0)
            ->assertJsonPath('data.existing_record', null);
    }

    public function test_admin_can_upload_dokumentasi_successfully(): void
    {
        $admin = $this->createAdminUser($this->opd1);
        $today = Carbon::now()->format('Y-m-d');

        $penugasan = Penugasan::create(['name' => 'Regu 1']);
        $p1 = Personnel::create([
            'name' => 'Personel Satu',
            'nik' => '1111111111111111',
            'email' => 'personel1@trc.com',
            'nomor_hp' => '081234567890',
            'pin' => '123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
            'password' => Hash::make('password'),
        ]);
        Jadwal::create([
            'personnel_id' => $p1->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
        ]);
        Absensi::create([
            'personnel_id' => $p1->id,
            'tanggal' => $today,
            'status' => 'HADIR',
            'jam_masuk' => '07:55:00',
        ]);

        $foto1 = UploadedFile::fake()->image('makan_siang_1.jpg');
        $foto2 = UploadedFile::fake()->image('makan_siang_2.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson('/api/v1/admin/dokumentasi', [
                'tanggal' => $today,
                'shift' => 'siang',
                'jumlah_porsi' => 1,
                'keterangan' => 'Konsumsi makan siang posko utama',
                'foto1' => $foto1,
                'foto2' => $foto2,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.shift', 'siang')
            ->assertJsonPath('data.jumlah_porsi', 1);

        $this->assertDatabaseHas('dokumentasi_konsumsis', [
            'opd_id' => $this->opd1->id,
            'jumlah_siang' => 1,
            'keterangan' => 'Konsumsi makan siang posko utama',
        ]);
    }

    public function test_upload_fails_when_porsi_exceeds_calculated_quota(): void
    {
        $admin = $this->createAdminUser($this->opd1);
        $today = Carbon::now()->format('Y-m-d');

        $foto1 = UploadedFile::fake()->image('makan_siang_1.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson('/api/v1/admin/dokumentasi', [
                'tanggal' => $today,
                'shift' => 'siang',
                'jumlah_porsi' => 10, // Kuota 0 karena tidak ada yang hadir
                'foto1' => $foto1,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_upload_fails_when_shift_is_already_documented(): void
    {
        $admin = $this->createAdminUser($this->opd1);
        $today = Carbon::now()->format('Y-m-d');

        DokumentasiKonsumsi::create([
            'opd_id' => $this->opd1->id,
            'tanggal' => $today,
            'jumlah_siang' => 5,
            'foto_siang' => 'dokumentasi-konsumsi/test/siang.jpg',
        ]);

        $foto1 = UploadedFile::fake()->image('makan_siang_1.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson('/api/v1/admin/dokumentasi', [
                'tanggal' => $today,
                'shift' => 'siang',
                'jumlah_porsi' => 5,
                'foto1' => $foto1,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_upload_fails_when_file_is_png(): void
    {
        $admin = $this->createAdminUser($this->opd1);
        $today = Carbon::now()->format('Y-m-d');

        $penugasan = Penugasan::create(['name' => 'Regu 1']);
        $p1 = Personnel::create([
            'name' => 'Personel Satu',
            'nik' => '1111111111111111',
            'email' => 'personel1@trc.com',
            'nomor_hp' => '081234567890',
            'pin' => '123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
            'password' => Hash::make('password'),
        ]);
        Jadwal::create([
            'personnel_id' => $p1->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
        ]);
        Absensi::create([
            'personnel_id' => $p1->id,
            'tanggal' => $today,
            'status' => 'HADIR',
            'jam_masuk' => '07:55:00',
        ]);

        $fotoPng = UploadedFile::fake()->create('makan_siang.png', 50, 'image/png');

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson('/api/v1/admin/dokumentasi', [
                'tanggal' => $today,
                'shift' => 'siang',
                'jumlah_porsi' => 1,
                'foto1' => $fotoPng,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Format foto utama harus JPEG, JPG, atau WebP.');
    }

    public function test_upload_succeeds_with_webp_image(): void
    {
        $admin = $this->createAdminUser($this->opd1);
        $today = Carbon::now()->format('Y-m-d');

        $penugasan = Penugasan::create(['name' => 'Regu 1']);
        $p1 = Personnel::create([
            'name' => 'Personel Satu',
            'nik' => '1111111111111111',
            'email' => 'personel1@trc.com',
            'nomor_hp' => '081234567890',
            'pin' => '123456',
            'opd_id' => $this->opd1->id,
            'penugasan_id' => $penugasan->id,
            'password' => Hash::make('password'),
        ]);
        Jadwal::create([
            'personnel_id' => $p1->id,
            'shift_id' => $this->shiftPagi->id,
            'tanggal' => $today,
        ]);
        Absensi::create([
            'personnel_id' => $p1->id,
            'tanggal' => $today,
            'status' => 'HADIR',
            'jam_masuk' => '07:55:00',
        ]);

        $fotoWebp = UploadedFile::fake()->create('makan_siang.webp', 45, 'image/webp');

        $response = $this->withHeader('Authorization', 'Bearer ' . $admin['token'])
            ->postJson('/api/v1/admin/dokumentasi', [
                'tanggal' => $today,
                'shift' => 'siang',
                'jumlah_porsi' => 1,
                'foto1' => $fotoWebp,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);
    }
}

