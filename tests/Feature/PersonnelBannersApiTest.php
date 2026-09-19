<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\Device;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelBannersApiTest extends TestCase
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
            'nomor_hp' => '08123456789',
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

    public function test_can_fetch_active_banners(): void
    {
        $admin = User::factory()->create();

        Berita::create([
            'judul' => 'Pengumuman Apel Pagi Gabungan',
            'slug' => 'pengumuman-apel-pagi-gabungan',
            'deskripsi' => 'Seluruh personel diwajibkan mengikuti apel...',
            'isi' => 'Konten lengkap berita apel pagi...',
            'gambar' => 'berita/apel.jpg',
            'kategori' => 'Pengumuman',
            'created_by' => $admin->id,
            'is_banner_active' => true,
        ]);

        Berita::create([
            'judul' => 'Berita Lama Non Aktif',
            'slug' => 'berita-lama-non-aktif',
            'deskripsi' => 'Berita yang sudah tidak dijadikan banner...',
            'isi' => 'Konten...',
            'kategori' => 'Berita',
            'created_by' => $admin->id,
            'is_banner_active' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'X-Device-Id' => $this->device->unique_device_id,
        ])->getJson('/api/v1/personel/banners');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Daftar banner aktif berhasil dimuat.',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'judul' => 'Pengumuman Apel Pagi Gabungan',
                'slug' => 'pengumuman-apel-pagi-gabungan',
                'isi' => 'Konten lengkap berita apel pagi...',
                'kategori' => 'Pengumuman',
            ]);
    }

    public function test_can_fetch_single_banner_detail(): void
    {
        $admin = User::factory()->create();

        $berita = Berita::create([
            'judul' => 'Detail Informasi Pelatihan TRC',
            'slug' => 'detail-informasi-pelatihan-trc',
            'deskripsi' => 'Ringkasan info pelatihan...',
            'isi' => '<p>Ini adalah isi lengkap materi pelatihan TRC di lapangan.</p>',
            'gambar' => 'berita/pelatihan.jpg',
            'kategori' => 'Pelatihan',
            'created_by' => $admin->id,
            'is_banner_active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'X-Device-Id' => $this->device->unique_device_id,
        ])->getJson('/api/v1/personel/banners/' . $berita->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Detail artikel berhasil dimuat.',
                'data' => [
                    'id' => $berita->id,
                    'judul' => 'Detail Informasi Pelatihan TRC',
                    'slug' => 'detail-informasi-pelatihan-trc',
                    'deskripsi' => 'Ringkasan info pelatihan...',
                    'isi' => '<p>Ini adalah isi lengkap materi pelatihan TRC di lapangan.</p>',
                    'kategori' => 'Pelatihan',
                ],
            ]);
    }
}
