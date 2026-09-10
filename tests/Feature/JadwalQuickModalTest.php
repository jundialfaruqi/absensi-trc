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
use Tests\TestCase;

class JadwalQuickModalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Opd $opd;
    protected Personnel $personnel;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->opd = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);

        \Spatie\Permission\Models\Role::create(['name' => 'super-admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');

        $this->personnel = Personnel::create([
            'name' => 'Budi Santoso',
            'opd_id' => $this->opd->id,
            'penugasan_id' => 1,
            'foto' => 'budi.jpg',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'pin' => '123456',
            'attendance_type' => 'SCHEDULED',
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'type' => 'shift',
            'color' => '#10b981',
        ]);
    }

    public function test_quick_modal_can_set_jadwal_and_links_existing_absensi_without_modifying_other_data(): void
    {
        $this->actingAs($this->admin);

        $date = '2026-08-15';
        $originalCreatedAt = Carbon::parse('2026-08-15 08:05:00');
        $originalUpdatedAt = Carbon::parse('2026-08-15 17:05:00');

        // Simulasi data absen dari mode Flexible sebelumnya: jadwal_id null, status HADIR
        $absensiId = DB::table('absensis')->insertGetId([
            'personnel_id' => $this->personnel->id,
            'jadwal_id' => null,
            'tanggal' => $date,
            'status' => 'HADIR',
            'jam_masuk' => '08:05:00',
            'jam_pulang' => '17:05:00',
            'status_masuk' => 'HADIR',
            'status_pulang' => 'HADIR',
            'foto_masuk' => 'absensi/2026-08-15/in_140.jpg',
            'foto_pulang' => 'absensi/2026-08-15/out_140.jpg',
            'lat_masuk' => 0.5070677,
            'lng_masuk' => 101.447779,
            'created_at' => $originalCreatedAt,
            'updated_at' => $originalUpdatedAt,
        ]);

        // Jalankan Livewire component jadwal-quick-modal
        Livewire::test('admin::jadwal-quick-modal')
            ->call('open', personnelId: $this->personnel->id, date: $date)
            ->set('quickStatus', 'SHIFT')
            ->set('quickShiftId', $this->shift->id)
            ->call('saveQuickJadwal')
            ->assertDispatched('toast')
            ->assertDispatched('close-modal', id: 'quick-add-modal')
            ->assertDispatched('refreshJadwal');

        // 1. Verifikasi tabel jadwals terisi
        $jadwal = Jadwal::where('personnel_id', $this->personnel->id)
            ->whereDate('tanggal', $date)
            ->first();

        $this->assertNotNull($jadwal);
        $this->assertEquals($this->shift->id, $jadwal->shift_id);
        $this->assertEquals('SHIFT', $jadwal->status);

        // 2. Verifikasi tabel absensis: jadwal_id terisi foreign key jadwal baru
        $updatedAbsensi = DB::table('absensis')->where('id', $absensiId)->first();

        $this->assertNotNull($updatedAbsensi);
        $this->assertEquals($jadwal->id, $updatedAbsensi->jadwal_id);

        // 3. Verifikasi data absensi TIDAK TERUBAH sedikitpun
        $this->assertEquals('HADIR', $updatedAbsensi->status);
        $this->assertEquals('HADIR', $updatedAbsensi->status_masuk);
        $this->assertEquals('HADIR', $updatedAbsensi->status_pulang);
        $this->assertEquals('08:05:00', $updatedAbsensi->jam_masuk);
        $this->assertEquals('17:05:00', $updatedAbsensi->jam_pulang);
        $this->assertEquals('absensi/2026-08-15/in_140.jpg', $updatedAbsensi->foto_masuk);
        $this->assertEquals('absensi/2026-08-15/out_140.jpg', $updatedAbsensi->foto_pulang);

        // 4. Verifikasi timestamps (created_at dan updated_at) TIDAK TERUBAH
        $this->assertEquals(
            $originalCreatedAt->toDateTimeString(),
            Carbon::parse($updatedAbsensi->created_at)->toDateTimeString()
        );
        $this->assertEquals(
            $originalUpdatedAt->toDateTimeString(),
            Carbon::parse($updatedAbsensi->updated_at)->toDateTimeString()
        );
    }

    public function test_quick_modal_creates_new_placeholder_absensi_when_none_exists(): void
    {
        $this->actingAs($this->admin);

        $date = '2026-08-20';

        Livewire::test('admin::jadwal-quick-modal')
            ->call('open', personnelId: $this->personnel->id, date: $date)
            ->set('quickStatus', 'SHIFT')
            ->set('quickShiftId', $this->shift->id)
            ->call('saveQuickJadwal')
            ->assertDispatched('toast')
            ->assertDispatched('close-modal', id: 'quick-add-modal')
            ->assertDispatched('refreshJadwal');

        $jadwal = Jadwal::where('personnel_id', $this->personnel->id)
            ->whereDate('tanggal', $date)
            ->first();

        $this->assertNotNull($jadwal);

        $absensi = Absensi::where('personnel_id', $this->personnel->id)
            ->whereDate('tanggal', $date)
            ->first();

        $this->assertNotNull($absensi);
        $this->assertEquals($jadwal->id, $absensi->jadwal_id);
        $this->assertEquals('ALPA', $absensi->status);
        $this->assertEquals('ALPA', $absensi->status_masuk);
        $this->assertEquals('ALPA', $absensi->status_pulang);
        $this->assertNull($absensi->jam_masuk);
        $this->assertNull($absensi->jam_pulang);
    }
}

