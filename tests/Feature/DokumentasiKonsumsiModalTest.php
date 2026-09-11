<?php

use App\Models\User;
use App\Models\Opd;
use App\Models\DokumentasiKonsumsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'lihat-dokumentasi-konsumsi', 'group' => 'Dokumentasi Konsumsi']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($permission);
});

test('dokumentasi konsumsi component renders skeleton loading and instant modal hooks', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->assertOk()
        ->assertSeeHtml('openKonsumsiModalInstantly')
        ->assertSeeHtml('isKonsumsiModalLoading')
        ->assertSeeHtml('isKonsumsiModalOpen')
        ->assertSeeHtml('closeKonsumsiModal')
        ->assertSeeHtml('submitWithCrop')
        ->assertSeeHtml("cropRatioSiang: 'original'");
});

test('openAddKonsumsiModal opens modal and sets default create properties', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = Carbon::today()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->assertSet('showAddModal', false)
        ->call('openAddKonsumsiModal', $targetDate, 'siang')
        ->assertSet('showAddModal', true)
        ->assertSet('modalMode', 'create')
        ->assertSet('uploadTanggal', $targetDate)
        ->assertSet('sesiKonsumsi', 'siang')
        ->call('closeAddKonsumsiModal')
        ->assertSet('showAddModal', false);
});

test('openEditKonsumsiModal opens modal with edit mode and loads existing data', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = Carbon::today()->format('Y-m-d');

    DokumentasiKonsumsi::create([
        'opd_id' => $opd->id,
        'tanggal' => $targetDate,
        'foto_siang' => 'dokumentasi-konsumsi/test_siang.webp',
        'jumlah_siang' => 5,
    ]);

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->set('selectedOpd', $opd->id)
        ->call('openEditKonsumsiModal', $targetDate, 'siang')
        ->assertSet('showAddModal', true)
        ->assertSet('modalMode', 'edit')
        ->assertSet('uploadTanggal', $targetDate)
        ->assertSet('sesiKonsumsi', 'siang')
        ->assertSet('existingFotoSiang', 'dokumentasi-konsumsi/test_siang.webp')
        ->assertSeeHtml("cropRatioSiang === 'original' ? 'object-contain' : 'object-cover'")
        ->assertSeeHtml("x-text=\"getBadgeRatioText('fotoSiang')\"")
        ->assertSeeHtml("rotatePhoto('fotoSiang')")
        ->assertSeeHtml("toggleFlipPhoto('fotoSiang', 'H')")
        ->assertSeeHtml("toggleFlipPhoto('fotoSiang', 'V')")
        ->assertSeeHtml("resetOrientation('fotoSiang')")
        ->call('closeAddKonsumsiModal')
        ->assertSet('showAddModal', false);
});

test('monthlySummary synchronizes totals by prioritizing dokumentasi_konsumsi and falling back to absensi', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $personnel = \App\Models\Personnel::create([
        'name' => 'Budi Santoso',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'budi.jpg',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $konsumsiSiang = \App\Models\Konsumsi::firstOrCreate(['nama' => 'Siang']);
    $konsumsiMalam = \App\Models\Konsumsi::firstOrCreate(['nama' => 'Malam']);

    $shift = \App\Models\Shift::create([
        'name' => 'Shift 24 Jam',
        'start_time' => '08:00:00',
        'end_time' => '08:00:00',
        'type' => 'shift',
        'color' => '#10b981',
    ]);
    $shift->konsumsis()->attach([$konsumsiSiang->id, $konsumsiMalam->id]);

    // Tanggal 1: Ada absensi (otomatis siang=1, malam=1), tapi di-override oleh dokumentasi_konsumsi (siang=20, malam=15)
    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-09-01',
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-09-01',
        'status' => 'HADIR',
        'jam_masuk' => '08:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DokumentasiKonsumsi::create([
        'opd_id' => $opd->id,
        'tanggal' => '2026-09-01',
        'jumlah_siang' => 20,
        'jumlah_malam' => 15,
        'created_by' => $user->id,
    ]);

    // Tanggal 2: Ada absensi (otomatis siang=1, malam=1), TIDAK ADA dokumentasi_konsumsi -> pakai otomatis
    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-09-02',
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-09-02',
        'status' => 'HADIR',
        'jam_masuk' => '08:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $test = Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi', [
            'startDate' => '2026-09-01',
            'endDate' => '2026-09-02',
            'selectedOpd' => (string) $opd->id,
            'readyToLoad' => true,
        ]);

    $summary = $test->get('monthlySummary');

    // Tanggal 1 pakai nilai riil dari dokumentasi_konsumsi
    expect($summary['daily']['2026-09-01']['siang'])->toBe(20);
    expect($summary['daily']['2026-09-01']['malam'])->toBe(15);
    expect($summary['daily']['2026-09-01']['total'])->toBe(35);
    expect($summary['daily']['2026-09-01']['auto_siang'])->toBe(1);
    expect($summary['daily']['2026-09-01']['auto_malam'])->toBe(1);

    // Tanggal 2 tetap pakai nilai otomatis absensi
    expect($summary['daily']['2026-09-02']['siang'])->toBe(1);
    expect($summary['daily']['2026-09-02']['malam'])->toBe(1);
    expect($summary['daily']['2026-09-02']['total'])->toBe(2);

    // Akumulasi total keseluruhan
    expect($summary['totalSiang'])->toBe(21); // 20 + 1
    expect($summary['totalMalam'])->toBe(16); // 15 + 1
    expect($summary['grandTotal'])->toBe(37); // 35 + 2
});

