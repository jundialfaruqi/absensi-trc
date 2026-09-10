<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JadwalGenerateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Opd $opd;
    protected Personnel $personnel;
    protected Shift $shiftPagi;
    protected Shift $shiftLibur;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin']);

        $this->opd = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');

        $this->personnel = Personnel::create([
            'name' => 'Fajar Santoso',
            'opd_id' => $this->opd->id,
            'penugasan_id' => 1,
            'foto' => 'fajar.jpg',
            'email' => 'fajar@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'SCHEDULED',
        ]);

        $this->shiftPagi = Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'type' => 'shift',
            'color' => '#10b981',
        ]);

        $this->shiftLibur = Shift::create([
            'name' => 'Libur',
            'type' => 'off',
            'keterangan' => 'LIBUR',
            'color' => '#ef4444',
        ]);
    }

    public function test_jadwal_generate_cycle_links_existing_absensi_without_modifying_other_data(): void
    {
        $this->actingAs($this->admin);

        $startDate = '2026-08-01';
        $endDate = '2026-08-03';

        // Tanggal 1: Memiliki absensi riil dari riwayat sebelumnya
        $origCreated = Carbon::parse('2026-08-01 07:55:00');
        $origUpdated = Carbon::parse('2026-08-01 17:10:00');
        $absensiId1 = DB::table('absensis')->insertGetId([
            'personnel_id' => $this->personnel->id,
            'jadwal_id' => null,
            'tanggal' => '2026-08-01',
            'status' => 'HADIR',
            'jam_masuk' => '07:55:00',
            'jam_pulang' => '17:10:00',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
            'foto_masuk' => 'absensi/2026-08-01/in.jpg',
            'foto_pulang' => 'absensi/2026-08-01/out.jpg',
            'lat_masuk' => 0.507,
            'lng_masuk' => 101.447,
            'created_at' => $origCreated,
            'updated_at' => $origUpdated,
        ]);

        // Tanggal 2 & 3: Belum ada absensi sama sekali

        Livewire::test('admin::jadwal-generate')
            ->set('selectedOpdId', $this->opd->id)
            ->set('selectedPersonnelIds', [$this->personnel->id])
            ->set('generateMode', 'cycle')
            ->set('shiftSequence', [
                ['type' => 'SHIFT', 'shift_id' => $this->shiftPagi->id, 'duration' => 2],
                ['type' => 'OFF', 'shift_id' => $this->shiftLibur->id, 'duration' => 1],
            ])
            ->set('startDate', $startDate)
            ->set('endDate', $endDate)
            ->call('generate')
            ->assertRedirect(route('jadwal'));

        // 1. Verifikasi jadwal tercipta untuk ketiga hari tersebut
        $jadwal1 = Jadwal::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-01')->first();
        $jadwal2 = Jadwal::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-02')->first();
        $jadwal3 = Jadwal::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-03')->first();

        $this->assertNotNull($jadwal1);
        $this->assertNotNull($jadwal2);
        $this->assertNotNull($jadwal3);

        // 2. Verifikasi absensi tanggal 1: jadwal_id terhubung ke jadwal1
        $updatedAbsensi1 = DB::table('absensis')->where('id', $absensiId1)->first();
        $this->assertEquals($jadwal1->id, $updatedAbsensi1->jadwal_id);

        // 3. Verifikasi data absensi tanggal 1 TIDAK BERUBAH sama sekali
        $this->assertEquals('HADIR', $updatedAbsensi1->status);
        $this->assertEquals('HADIR', $updatedAbsensi1->status_masuk);
        $this->assertEquals('HADIR', $updatedAbsensi1->status_pulang);
        $this->assertEquals('07:55:00', $updatedAbsensi1->jam_masuk);
        $this->assertEquals('17:10:00', $updatedAbsensi1->jam_pulang);
        $this->assertEquals('absensi/2026-08-01/in.jpg', $updatedAbsensi1->foto_masuk);
        $this->assertEquals('absensi/2026-08-01/out.jpg', $updatedAbsensi1->foto_pulang);

        // 4. Verifikasi timestamps tanggal 1 TIDAK BERUBAH
        $this->assertEquals(
            $origCreated->toDateTimeString(),
            Carbon::parse($updatedAbsensi1->created_at)->toDateTimeString()
        );
        $this->assertEquals(
            $origUpdated->toDateTimeString(),
            Carbon::parse($updatedAbsensi1->updated_at)->toDateTimeString()
        );

        // 5. Verifikasi tanggal 2 & 3 absensi placeholder default dibuat
        $absensi2 = Absensi::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-02')->first();
        $this->assertNotNull($absensi2);
        $this->assertEquals($jadwal2->id, $absensi2->jadwal_id);
        $this->assertEquals('ALPA', $absensi2->status);

        $absensi3 = Absensi::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-03')->first();
        $this->assertNotNull($absensi3);
        $this->assertEquals($jadwal3->id, $absensi3->jadwal_id);
        $this->assertEquals('LIBUR', $absensi3->status);
    }

    public function test_jadwal_generate_weekly_links_existing_absensi_without_modifying_other_data(): void
    {
        $this->actingAs($this->admin);

        $startDate = '2026-08-03'; // Senin
        $endDate = '2026-08-04'; // Selasa

        $origCreated = Carbon::parse('2026-08-03 08:00:00');
        $origUpdated = Carbon::parse('2026-08-03 17:00:00');
        $absensiId = DB::table('absensis')->insertGetId([
            'personnel_id' => $this->personnel->id,
            'jadwal_id' => null,
            'tanggal' => '2026-08-03',
            'status' => 'HADIR',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
            'created_at' => $origCreated,
            'updated_at' => $origUpdated,
        ]);

        $weeklyConfig = [
            0 => ['type' => 'OFF', 'shift_id' => $this->shiftLibur->id],
            1 => ['type' => 'SHIFT', 'shift_id' => $this->shiftPagi->id],
            2 => ['type' => 'SHIFT', 'shift_id' => $this->shiftPagi->id],
            3 => ['type' => 'SHIFT', 'shift_id' => $this->shiftPagi->id],
            4 => ['type' => 'SHIFT', 'shift_id' => $this->shiftPagi->id],
            5 => ['type' => 'SHIFT', 'shift_id' => $this->shiftPagi->id],
            6 => ['type' => 'OFF', 'shift_id' => $this->shiftLibur->id],
        ];

        Livewire::test('admin::jadwal-generate')
            ->set('selectedOpdId', $this->opd->id)
            ->set('selectedPersonnelIds', [$this->personnel->id])
            ->set('generateMode', 'weekly')
            ->set('weeklyConfig', $weeklyConfig)
            ->set('startDate', $startDate)
            ->set('endDate', $endDate)
            ->call('generate')
            ->assertRedirect(route('jadwal'));

        $jadwal = Jadwal::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-03')->first();
        $this->assertNotNull($jadwal);

        $updatedAbsensi = DB::table('absensis')->where('id', $absensiId)->first();
        $this->assertEquals($jadwal->id, $updatedAbsensi->jadwal_id);
        $this->assertEquals('HADIR', $updatedAbsensi->status);
        $this->assertEquals(
            $origUpdated->toDateTimeString(),
            Carbon::parse($updatedAbsensi->updated_at)->toDateTimeString()
        );
    }

    public function test_jadwal_generate_quota_links_existing_absensi_without_modifying_other_data(): void
    {
        $this->actingAs($this->admin);

        $startDate = '2026-08-05';
        $endDate = '2026-08-05';

        $origCreated = Carbon::parse('2026-08-05 08:15:00');
        $origUpdated = Carbon::parse('2026-08-05 16:45:00');
        $absensiId = DB::table('absensis')->insertGetId([
            'personnel_id' => $this->personnel->id,
            'jadwal_id' => null,
            'tanggal' => '2026-08-05',
            'status' => 'HADIR',
            'jam_masuk' => '08:15:00',
            'jam_pulang' => '16:45:00',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
            'created_at' => $origCreated,
            'updated_at' => $origUpdated,
        ]);

        Livewire::test('admin::jadwal-generate')
            ->set('selectedOpdId', $this->opd->id)
            ->set('selectedPersonnelIds', [$this->personnel->id])
            ->set('generateMode', 'quota')
            ->set('quotaConfig', [$this->shiftPagi->id => 1])
            ->set('startDate', $startDate)
            ->set('endDate', $endDate)
            ->call('generate')
            ->assertRedirect(route('jadwal'));

        $jadwal = Jadwal::where('personnel_id', $this->personnel->id)->whereDate('tanggal', '2026-08-05')->first();
        $this->assertNotNull($jadwal);

        $updatedAbsensi = DB::table('absensis')->where('id', $absensiId)->first();
        $this->assertEquals($jadwal->id, $updatedAbsensi->jadwal_id);
        $this->assertEquals('HADIR', $updatedAbsensi->status);
        $this->assertEquals(
            $origUpdated->toDateTimeString(),
            Carbon::parse($updatedAbsensi->updated_at)->toDateTimeString()
        );
    }
}

