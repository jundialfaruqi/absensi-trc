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
        'nomor_hp' => '081234567890',
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

test('modal session can be switched and deleteDokumentasi supports passing session', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = Carbon::today()->format('Y-m-d');

    $record = DokumentasiKonsumsi::create([
        'opd_id' => $opd->id,
        'tanggal' => $targetDate,
        'foto_siang' => 'dokumentasi-konsumsi/test_siang.webp',
        'jumlah_siang' => 5,
        'foto_malam' => 'dokumentasi-konsumsi/test_malam.webp',
        'jumlah_malam' => 7,
    ]);

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->set('selectedOpd', $opd->id)
        ->call('openEditKonsumsiModal', $targetDate, 'siang')
        ->assertSet('sesiKonsumsi', 'siang')
        ->assertSeeHtml("selectSesi('siang')")
        ->assertSeeHtml("selectSesi('malam')")
        ->assertSeeHtml("selectSesi('keduanya')")
        ->call('deleteDokumentasi', 'siang')
        ->assertDispatched('toast');

    $record->refresh();
    expect($record->foto_siang)->toBeNull();
    expect($record->jumlah_siang)->toBeNull();
    expect($record->foto_malam)->toBe('dokumentasi-konsumsi/test_malam.webp');
    expect($record->jumlah_malam)->toBe(7);
});

test('modal input jumlah renders dynamic max and onInputJumlah with quota argument', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $personnel = \App\Models\Personnel::create([
        'name' => 'Doni',
        'nomor_hp' => '081234567891',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'doni.jpg',
        'email' => 'doni@example.com',
        'password' => bcrypt('password'),
        'pin' => '654321',
        'attendance_type' => 'SCHEDULED',
    ]);

    $konsumsiSiang = \App\Models\Konsumsi::firstOrCreate(['nama' => 'Siang']);
    $shift = \App\Models\Shift::create([
        'name' => 'Shift Pagi',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
        'type' => 'shift',
        'color' => '#3b82f6',
    ]);
    $shift->konsumsis()->attach([$konsumsiSiang->id]);

    $targetDate = '2026-09-05';

    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $targetDate,
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $targetDate,
        'status' => 'HADIR',
        'jam_masuk' => '08:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->set('selectedOpd', $opd->id)
        ->call('openAddKonsumsiModal', $targetDate, 'siang')
        ->assertSeeHtml("onInputJumlah('siang', \$el, 1)")
        ->assertSeeHtml('max="1"');
});

test('grid cells display 0 in the center and auto calculation in bottom-left corner when undocumented', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $personnel = \App\Models\Personnel::create([
        'name' => 'Budi Santoso',
        'nomor_hp' => '081234567892',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'SCHEDULED',
        'foto' => 'budi.jpg',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
    ]);

    $konsumsiSiang = \App\Models\Konsumsi::create(['nama' => 'Siang', 'opd_id' => $opd->id]);
    $shift = \App\Models\Shift::create([
        'name' => 'Shift Pagi',
        'opd_id' => $opd->id,
        'jam_masuk' => '08:00:00',
        'jam_pulang' => '16:00:00',
        'type' => 'shift',
        'color' => '#3b82f6',
    ]);
    $shift->konsumsis()->attach([$konsumsiSiang->id]);

    $dateWithAbsensiOnly = '2026-09-02';

    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $dateWithAbsensiOnly,
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $dateWithAbsensiOnly,
        'status' => 'HADIR',
        'jam_masuk' => '08:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Test component view
    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi', [
            'startDate' => '2026-09-01',
            'endDate' => '2026-09-02',
            'selectedOpd' => (string) $opd->id,
            'readyToLoad' => true,
        ])
        ->assertOk()
        // Center number should display 0
        ->assertSeeHtml('>0</span>')
        // Auto calculation from attendance should appear in bottom-left corner with title
        ->assertSeeHtml('title="Jumlah dari data absensi: 1"')
        // Total column & Total row should show both real count and auto calculation
        ->assertSeeHtml('title="Total Siang dari perhitungan otomatis: 1"')
        ->assertSeeHtml('title="Total otomatis dari data absensi: 1"')
        ->assertSeeHtml('title="Grand Total dari perhitungan otomatis: 1"')
        // Header summary badges should show both real count and auto calculation
        ->assertSeeHtml('title="Riil: 0 | Otomatis: 1"')
        ->assertSeeHtml('(Auto: 1)');
});

test('personnel with TELAT status (status or status_masuk) is counted in konsumsi calculations', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $personnel = \App\Models\Personnel::create([
        'name' => 'Bambang Tri',
        'nomor_hp' => '081234567893',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'SCHEDULED',
        'foto' => 'bambang.jpg',
        'email' => 'bambang@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
    ]);

    $konsumsiSiang = \App\Models\Konsumsi::create(['nama' => 'Siang', 'opd_id' => $opd->id]);
    $shift = \App\Models\Shift::create([
        'name' => 'Shift Pagi',
        'opd_id' => $opd->id,
        'jam_masuk' => '08:00:00',
        'jam_pulang' => '16:00:00',
        'type' => 'shift',
        'color' => '#3b82f6',
    ]);
    $shift->konsumsis()->attach([$konsumsiSiang->id]);

    $date = '2026-09-10';

    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Data lama dengan status TELAT dan status_masuk TELAT
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'TELAT',
        'status_masuk' => 'TELAT',
        'jam_masuk' => '08:45:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $test = Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi', [
            'startDate' => '2026-09-10',
            'endDate' => '2026-09-10',
            'selectedOpd' => (string) $opd->id,
            'readyToLoad' => true,
        ]);

    $summary = $test->get('monthlySummary');
    expect($summary['daily']['2026-09-10']['auto_siang'])->toBe(1);
    expect($summary['totalAutoSiang'])->toBe(1);

    // Also check getCalculatedKonsumsi (used by upload modal)
    $calculated = $test->instance()->getCalculatedKonsumsi($date);
    expect($calculated['siang'])->toBe(1);
});

test('personnel with status IZIN (even with jam_masuk) is NOT counted in konsumsi calculations', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $personnel = \App\Models\Personnel::create([
        'name' => 'Asep Saepul',
        'nomor_hp' => '081234567894',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'SCHEDULED',
        'foto' => 'asep.jpg',
        'email' => 'asep@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
    ]);

    $konsumsiSiang = \App\Models\Konsumsi::create(['nama' => 'Siang', 'opd_id' => $opd->id]);
    $shift = \App\Models\Shift::create([
        'name' => 'Shift Pagi',
        'opd_id' => $opd->id,
        'jam_masuk' => '08:00:00',
        'jam_pulang' => '16:00:00',
        'type' => 'shift',
        'color' => '#3b82f6',
    ]);
    $shift->konsumsis()->attach([$konsumsiSiang->id]);

    $date = '2026-09-11';

    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Data dengan status IZIN namun memiliki jam_masuk (misal riwayat edit / punch)
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'IZIN',
        'status_masuk' => 'IZIN',
        'jam_masuk' => '08:30:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $test = Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi', [
            'startDate' => '2026-09-11',
            'endDate' => '2026-09-11',
            'selectedOpd' => (string) $opd->id,
            'readyToLoad' => true,
        ]);

    $summary = $test->get('monthlySummary');
    expect($summary['daily']['2026-09-11']['auto_siang'])->toBe(0);
    expect($summary['totalAutoSiang'])->toBe(0);

    $calculated = $test->instance()->getCalculatedKonsumsi($date);
    expect($calculated['siang'])->toBe(0);
});

test('personnel with direct checkout (status HADIR, status_masuk ALPA, jam_pulang filled) is counted in konsumsi calculations', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $personnel = \App\Models\Personnel::create([
        'name' => 'Benny Direct',
        'nomor_hp' => '081234567895',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'SCHEDULED',
        'foto' => 'benny.jpg',
        'email' => 'benny@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
    ]);

    $konsumsiSiang = \App\Models\Konsumsi::create(['nama' => 'Siang', 'opd_id' => $opd->id]);
    $shift = \App\Models\Shift::create([
        'name' => 'Shift Pagi',
        'opd_id' => $opd->id,
        'jam_masuk' => '08:00:00',
        'jam_pulang' => '16:00:00',
        'type' => 'shift',
        'color' => '#3b82f6',
    ]);
    $shift->konsumsis()->attach([$konsumsiSiang->id]);

    $date = '2026-06-04';

    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Data direct checkout: absen pulang saja
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'HADIR',
        'status_masuk' => 'ALPA',
        'status_pulang' => 'HADIR',
        'jam_masuk' => null,
        'jam_pulang' => '16:05:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $test = Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi', [
            'startDate' => '2026-06-04',
            'endDate' => '2026-06-04',
            'selectedOpd' => (string) $opd->id,
            'readyToLoad' => true,
        ]);

    $summary = $test->get('monthlySummary');
    expect($summary['daily']['2026-06-04']['auto_siang'])->toBe(1);
    expect($summary['totalAutoSiang'])->toBe(1);

    $calculated = $test->instance()->getCalculatedKonsumsi($date);
    expect($calculated['siang'])->toBe(1);
});

test('uploading fotoSiang2 and fotoMalam2 in edit modal preserves and saves fotoMalam2 even if sesi is siang', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = '2026-09-08';
    $record = DokumentasiKonsumsi::create([
        'opd_id' => $opd->id,
        'tanggal' => $targetDate,
        'foto_siang' => 'dokumentasi-konsumsi/08-09-2026/existing_siang.webp',
        'jumlah_siang' => 5,
        'foto_malam' => 'dokumentasi-konsumsi/08-09-2026/existing_malam.webp',
        'jumlah_malam' => 5,
    ]);

    $fakeSiang2 = \Illuminate\Http\UploadedFile::fake()->image('siang2.jpg');
    $fakeMalam2 = \Illuminate\Http\UploadedFile::fake()->image('malam2.jpg');

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->set('selectedOpd', (string) $opd->id)
        ->call('openEditKonsumsiModal', $targetDate, 'siang')
        ->assertSet('sesiKonsumsi', 'siang')
        ->set('fotoSiang2', $fakeSiang2)
        ->set('fotoMalam2', $fakeMalam2)
        ->call('saveKonsumsi')
        ->assertHasNoErrors();

    $record->refresh();
    expect($record->foto_siang)->toBe('dokumentasi-konsumsi/08-09-2026/existing_siang.webp');
    expect($record->foto_siang_2)->not->toBeNull();
    expect($record->foto_malam)->toBe('dokumentasi-konsumsi/08-09-2026/existing_malam.webp');
    expect($record->foto_malam_2)->not->toBeNull();
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($record->foto_siang_2);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($record->foto_malam_2);
});

test('uploading all photos with sesi set to siang in create modal saves both siang and malam including foto 2', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = '2026-09-09';

    $fakeSiang = \Illuminate\Http\UploadedFile::fake()->image('siang.jpg');
    $fakeSiang2 = \Illuminate\Http\UploadedFile::fake()->image('siang2.jpg');
    $fakeMalam = \Illuminate\Http\UploadedFile::fake()->image('malam.jpg');
    $fakeMalam2 = \Illuminate\Http\UploadedFile::fake()->image('malam2.jpg');

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->set('selectedOpd', (string) $opd->id)
        ->call('openAddKonsumsiModal', $targetDate, 'siang')
        ->assertSet('sesiKonsumsi', 'siang')
        ->set('jumlahSiang', 3)
        ->set('fotoSiang', $fakeSiang)
        ->set('fotoSiang2', $fakeSiang2)
        ->set('jumlahMalam', 3)
        ->set('fotoMalam', $fakeMalam)
        ->set('fotoMalam2', $fakeMalam2)
        ->call('saveKonsumsi')
        ->assertHasNoErrors();

    $record = DokumentasiKonsumsi::whereDate('tanggal', $targetDate)->first();
    expect($record)->not->toBeNull();
    expect($record->foto_siang)->not->toBeNull();
    expect($record->foto_siang_2)->not->toBeNull();
    expect($record->foto_malam)->not->toBeNull();
    expect($record->foto_malam_2)->not->toBeNull();
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($record->foto_siang);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($record->foto_siang_2);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($record->foto_malam);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($record->foto_malam_2);
});

test('personnel with FLEXIBLE attendance type is automatically counted dynamically based on real attendance hours', function () {
    $opd = Opd::create(['name' => 'Dishub']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Personel 1: Masuk Siang (08:15 - 15:30) -> Siang saja
    $personnelSiang = \App\Models\Personnel::create([
        'name' => 'Deni Siang',
        'nomor_hp' => '081234567801',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'FLEXIBLE',
        'foto' => 'deni1.jpg',
        'email' => 'deni1@example.com',
        'password' => bcrypt('password'),
        'pin' => '123451',
    ]);

    // Personel 2: Masuk Malam (19:30 - 02:00) -> Malam saja
    $personnelMalam = \App\Models\Personnel::create([
        'name' => 'Deni Malam',
        'nomor_hp' => '081234567802',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'FLEXIBLE',
        'foto' => 'deni2.jpg',
        'email' => 'deni2@example.com',
        'password' => bcrypt('password'),
        'pin' => '123452',
    ]);

    // Personel 3: Masuk Siang Pulang Malam (08:00 - 21:30, 13.5 jam) -> Siang & Malam (S+M)
    $personnelBoth = \App\Models\Personnel::create([
        'name' => 'Deni Lembur',
        'nomor_hp' => '081234567803',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'attendance_type' => 'FLEXIBLE',
        'foto' => 'deni3.jpg',
        'email' => 'deni3@example.com',
        'password' => bcrypt('password'),
        'pin' => '123453',
    ]);

    $date = '2026-09-12';

    // Absensi Siang
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnelSiang->id,
        'tanggal' => $date,
        'status' => 'HADIR',
        'status_masuk' => 'HADIR',
        'jam_masuk' => '08:15:00',
        'jam_pulang' => '15:30:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Absensi Malam
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnelMalam->id,
        'tanggal' => $date,
        'status' => 'HADIR',
        'status_masuk' => 'HADIR',
        'jam_masuk' => '19:30:00',
        'jam_pulang' => '02:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Absensi Lembur (Siang & Malam)
    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnelBoth->id,
        'tanggal' => $date,
        'status' => 'HADIR',
        'status_masuk' => 'HADIR',
        'jam_masuk' => '08:00:00',
        'jam_pulang' => '21:30:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $test = Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi', [
            'startDate' => '2026-09-12',
            'endDate' => '2026-09-12',
            'selectedOpd' => (string) $opd->id,
            'readyToLoad' => true,
        ]);

    $summary = $test->get('monthlySummary');
    // Siang: Deni Siang (1) + Deni Lembur (1) = 2
    expect($summary['daily']['2026-09-12']['auto_siang'])->toBe(2);
    // Malam: Deni Malam (1) + Deni Lembur (1) = 2
    expect($summary['daily']['2026-09-12']['auto_malam'])->toBe(2);
    expect($summary['grandTotalAuto'])->toBe(4);

    $calculated = $test->instance()->getCalculatedKonsumsi($date);
    expect($calculated['siang'])->toBe(2);
    expect($calculated['malam'])->toBe(2);

    // Assert nama personel muncul di view
    $test->assertSee('Deni Siang')
        ->assertSee('Deni Malam')
        ->assertSee('Deni Lembur');
});
