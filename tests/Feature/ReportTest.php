<?php

use App\Models\DokumentasiKonsumsi;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed basic permissions and roles
    $permission = Permission::create(['name' => 'manajemen-absensi', 'group' => 'Absensi']);
    $permKonsumsi = Permission::create(['name' => 'lihat-dokumentasi-konsumsi', 'group' => 'Konsumsi']);
    $superAdminRole = Role::create(['name' => 'super-admin', 'color' => '#ef4444']);
    $superAdminRole->givePermissionTo($permission);
    $superAdminRole->givePermissionTo($permKonsumsi);
});

test('unauthenticated users cannot export PDF', function () {
    $response = $this->get(route('absensi.export-pdf', [
        'month' => 5,
        'year' => 2026,
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-31',
    ]));

    $response->assertRedirect('/login');
});

test('authenticated super-admin can export PDF with default parameters', function () {
    $opd = Opd::create(['name' => 'Test OPD']);

    $personnel = Personnel::create([
        'name' => 'John Doe',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'john.jpg',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(route('absensi.export-pdf', [
        'month' => '05',
        'year' => '2026',
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-31',
        'paperSize' => 'a4',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('x-filename', 'rekap_absensi_01-05-2026_31-05-2026.pdf');
    expect($response->headers->get('content-disposition'))->toContain('attachment; filename=rekap_absensi_01-05-2026_31-05-2026.pdf');
});

test('handles f4 custom paper size correctly', function () {
    $opd = Opd::create(['name' => 'Test OPD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(route('absensi.export-pdf', [
        'month' => '05',
        'year' => '2026',
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-31',
        'paperSize' => 'f4',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('restricts date range to maximum 31 days to prevent memory exhaustion', function () {
    $opd = Opd::create(['name' => 'Test OPD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // June has 30 days, July has 31. May 1st to June 30th is 60 days.
    $response = $this->actingAs($user)->get(route('absensi.export-pdf', [
        'month' => '05',
        'year' => '2026',
        'startDate' => '2026-05-01',
        'endDate' => '2026-06-30', // 60 days range
        'paperSize' => 'a4',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('super-admin filters by opd_id when selected', function () {
    $opd1 = Opd::create(['name' => 'OPD Satu']);
    $opd2 = Opd::create(['name' => 'OPD Dua']);

    Personnel::create([
        'name' => 'John Doe',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'foto' => 'john.jpg',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    Personnel::create([
        'name' => 'Jane Doe',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'foto' => 'jane.jpg',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
        'pin' => '123457',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Export with opd_id filter pointing to opd1
    $response = $this->actingAs($user)->get(route('absensi.export-pdf', [
        'month' => '05',
        'year' => '2026',
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-31',
        'paperSize' => 'a4',
        'opd_id' => $opd1->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('dinas attendance renders as D and is not counted as Hadir in PDF report', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan']);
    $personnel = Personnel::create([
        'name' => 'Ahmad Dani',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'ahmad.jpg',
        'email' => 'ahmad@example.com',
        'password' => bcrypt('password'),
        'pin' => '654321',
        'attendance_type' => 'SCHEDULED',
    ]);

    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-05-01',
        'status' => 'DINAS',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(route('absensi.export-pdf', [
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-01',
        'paperSize' => 'a4',
        'opd_id' => $opd->id,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Also assert directly on the rendered Blade view to verify D display, JML = 0, and Hadir count = 0
    $personnel->absensi_map = collect([
        '2026-05-01' => (object) ['status' => 'DINAS'],
    ]);
    $personnel->jadwal_map = collect([
        '2026-05-01' => (object) ['status' => 'SHIFT'],
    ]);

    $html = view('reports.absensi-pdf', [
        'personnels' => collect([$personnel]),
        'dates' => ['2026-05-01'],
        'month' => 5,
        'year' => 2026,
        'monthName' => 'Mei',
        'opdName' => $opd->name,
    ])->render();

    expect($html)->toContain('>D<');
    expect($html)->toContain('D: Dinas');
    expect($html)->toContain('<td class="summary-column">0</td>');
});

test('excluded shifts display *H and are not counted towards Hadir in PDF report', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan']);
    $personnel = Personnel::create([
        'name' => 'Bambang',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'bambang.jpg',
        'email' => 'bambang@example.com',
        'password' => bcrypt('password'),
        'pin' => '998877',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(route('absensi.export-pdf', [
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-02',
        'paperSize' => 'a4',
        'opd_id' => $opd->id,
        'excluded_shifts' => [99],
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test blade view rendering with 2 days: Day 1 (Shift 10, Hadir), Day 2 (Shift 99, Hadir -> *H)
    $personnel->absensi_map = collect([
        '2026-05-01' => (object) ['status' => 'HADIR'],
        '2026-05-02' => (object) ['status' => 'HADIR'],
    ]);
    $personnel->jadwal_map = collect([
        '2026-05-01' => (object) ['shift_id' => 10, 'status' => 'SHIFT'],
        '2026-05-02' => (object) ['shift_id' => 99, 'status' => 'SHIFT'],
    ]);

    $html = view('reports.absensi-pdf', [
        'personnels' => collect([$personnel]),
        'dates' => ['2026-05-01', '2026-05-02'],
        'month' => 5,
        'year' => 2026,
        'monthName' => 'Mei',
        'opdName' => $opd->name,
        'excludedShiftIds' => [99],
    ])->render();

    expect($html)->toContain('>H<');
    expect($html)->toContain('>*<u>H</u><');
    // JML = 2 (two shifts scheduled)
    expect($html)->toContain('<td class="summary-column">2</td>');
    // H = 1 (only Day 1 counted, Day 2 *H is excluded, has highlight-hadir class!)
    expect($html)->toContain('<td class="summary-column highlight-hadir">1</td>');
    // Footer legend
    expect($html)->toContain('*<u>H</u>: Hadir (Shift Dikecualikan)');
});

test('sakit, izin, and cuti attendance render correctly and are NOT counted as Hadir in PDF report', function () {
    $opd = Opd::create(['name' => 'Dinas Kesehatan']);
    $personnel = Personnel::create([
        'name' => 'Dr. Rina',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'rina.jpg',
        'email' => 'rina@example.com',
        'password' => bcrypt('password'),
        'pin' => '123123',
        'attendance_type' => 'SCHEDULED',
    ]);

    $personnel->absensi_map = collect([
        '2026-05-01' => (object) ['status' => 'SAKIT'],
        '2026-05-02' => (object) ['status' => 'IZIN'],
        '2026-05-03' => (object) ['status' => 'CUTI'],
    ]);
    $personnel->jadwal_map = collect([
        '2026-05-01' => (object) ['status' => 'SHIFT'],
        '2026-05-02' => (object) ['status' => 'SHIFT'],
        '2026-05-03' => (object) ['status' => 'SHIFT'],
    ]);

    $html = view('reports.absensi-pdf', [
        'personnels' => collect([$personnel]),
        'dates' => ['2026-05-01', '2026-05-02', '2026-05-03'],
        'month' => 5,
        'year' => 2026,
        'monthName' => 'Mei',
        'opdName' => $opd->name,
    ])->render();

    expect($html)->toContain('>S<');
    expect($html)->toContain('>I<');
    expect($html)->toContain('>C<');
    expect($html)->toContain('<td class="summary-column ">0</td>');
    expect($html)->toContain('>Lainnya</th>');
    expect($html)->toContain('>HSK</th>');
    expect($html)->toContain('>I</th>');
    expect($html)->toContain('>S</th>');
    expect($html)->toContain('>C</th>');
});

test('personnel with flexible attendance type has JML equal to Hadir in PDF report', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan']);
    $personnel = Personnel::create([
        'name' => 'Fajar Flex',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'fajar.jpg',
        'email' => 'fajar@example.com',
        'password' => bcrypt('password'),
        'pin' => '654987',
        'attendance_type' => 'FLEXIBLE',
    ]);

    $personnel->absensi_map = collect([
        '2026-08-01' => (object) ['status' => 'HADIR'],
        '2026-08-02' => (object) ['status' => 'HADIR'],
        '2026-08-03' => (object) ['status' => 'TELAT'],
    ]);
    $personnel->jadwal_map = collect([]);

    $html = view('reports.absensi-pdf', [
        'personnels' => collect([$personnel]),
        'dates' => ['2026-08-01', '2026-08-02', '2026-08-03'],
        'month' => 8,
        'year' => 2026,
        'monthName' => 'Agustus',
        'opdName' => $opd->name,
    ])->render();

    // JML column should be 3 (equal to Hadir)
    expect($html)->toContain('<td class="summary-column">3</td>');
    expect($html)->toContain('<td class="summary-column ">3</td>');
});

test('authenticated user can export konsumsi PDF with default rekap only', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('x-filename', 'rekap_konsumsi_01-09-2026_05-09-2026.pdf');
});

test('export konsumsi PDF view renders sections conditionally based on includeRekap and includeRincian', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $dates = ['2026-09-01', '2026-09-02'];
    $dailySummary = [
        '2026-09-01' => ['siang' => 5, 'malam' => 5, 'total' => 10],
        '2026-09-02' => ['siang' => 6, 'malam' => 4, 'total' => 10],
    ];

    // Case 1: Only Rekap
    $htmlRekapOnly = view('reports.konsumsi-pdf', [
        'personnels' => collect([]),
        'dates' => $dates,
        'dailySummary' => $dailySummary,
        'totalSiangAll' => 11,
        'totalMalamAll' => 9,
        'grandTotalAll' => 20,
        'month' => 9,
        'year' => 2026,
        'monthName' => 'September',
        'opdName' => $opd->name,
        'includeRekap' => true,
        'includeRincian' => false,
    ])->render();

    expect($htmlRekapOnly)->toContain('Rekapitulasi Jumlah Porsi Konsumsi (September 2026)');
    expect($htmlRekapOnly)->not->toContain('Rincian Konsumsi Per Personel');

    // Case 2: Only Rincian
    $htmlRincianOnly = view('reports.konsumsi-pdf', [
        'personnels' => collect([]),
        'dates' => $dates,
        'dailySummary' => $dailySummary,
        'totalSiangAll' => 11,
        'totalMalamAll' => 9,
        'grandTotalAll' => 20,
        'month' => 9,
        'year' => 2026,
        'monthName' => 'September',
        'opdName' => $opd->name,
        'includeRekap' => false,
        'includeRincian' => true,
    ])->render();

    expect($htmlRincianOnly)->not->toContain('Rekapitulasi Jumlah Porsi Konsumsi');
    expect($htmlRincianOnly)->toContain('Rincian Konsumsi Per Personel');

    // Case 3: Both
    $htmlBoth = view('reports.konsumsi-pdf', [
        'personnels' => collect([]),
        'dates' => $dates,
        'dailySummary' => $dailySummary,
        'totalSiangAll' => 11,
        'totalMalamAll' => 9,
        'grandTotalAll' => 20,
        'month' => 9,
        'year' => 2026,
        'monthName' => 'September',
        'opdName' => $opd->name,
        'includeRekap' => true,
        'includeRincian' => true,
    ])->render();

    expect($htmlBoth)->toContain('I. Rekapitulasi Jumlah Porsi Konsumsi (September 2026)');
    expect($htmlBoth)->toContain('II. Rincian Konsumsi Per Personel');
});

test('export konsumsi PDF filename dynamically changes based on selected sections', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Both selected
    $resBoth = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'include_rekap' => 1,
        'include_rincian' => 1,
    ]));
    $resBoth->assertHeader('x-filename', 'rekap_dan_rincian_konsumsi_01-09-2026_05-09-2026.pdf');

    // Only rincian selected
    $resRincian = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'include_rekap' => 0,
        'include_rincian' => 1,
    ]));
    $resRincian->assertHeader('x-filename', 'rincian_konsumsi_personel_01-09-2026_05-09-2026.pdf');

    // Only dokumentasi selected
    $resDok = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'include_rekap' => 0,
        'include_rincian' => 0,
        'include_dokumentasi' => 1,
    ]));
    $resDok->assertHeader('x-filename', 'dokumentasi_foto_konsumsi_01-09-2026_05-09-2026.pdf');

    // All three selected
    $resAll = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'include_rekap' => 1,
        'include_rincian' => 1,
        'include_dokumentasi' => 1,
    ]));
    $resAll->assertHeader('x-filename', 'laporan_lengkap_konsumsi_01-09-2026_05-09-2026.pdf');

    // Rekap + Dokumentasi selected
    $resRekapDok = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'include_rekap' => 1,
        'include_rincian' => 0,
        'include_dokumentasi' => 1,
    ]));
    $resRekapDok->assertHeader('x-filename', 'rekap_dan_dokumentasi_konsumsi_01-09-2026_05-09-2026.pdf');

    // Rincian + Dokumentasi selected
    $resRincianDok = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-05',
        'include_rekap' => 0,
        'include_rincian' => 1,
        'include_dokumentasi' => 1,
    ]));
    $resRincianDok->assertHeader('x-filename', 'rincian_dan_dokumentasi_konsumsi_01-09-2026_05-09-2026.pdf');
});

test('export konsumsi PDF view renders dokumentasi foto section per date correctly', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $dates = ['2026-09-01'];

    $dokumentasiList = [
        [
            'date' => '2026-09-01',
            'tanggalFormatted' => '01 September 2026',
            'bulanTahun' => 'September 2026',
            'jumlah_siang' => 20,
            'jumlah_malam' => 15,
            'foto_siang' => null,
            'foto_siang_2' => null,
            'foto_malam' => null,
            'foto_malam_2' => null,
            'has_foto' => false,
            'keterangan' => 'Uji Coba',
        ],
    ];

    $html = view('reports.konsumsi-pdf', [
        'personnels' => collect([]),
        'dates' => $dates,
        'dailySummary' => ['2026-09-01' => ['siang' => 20, 'malam' => 15, 'total' => 35]],
        'totalSiangAll' => 20,
        'totalMalamAll' => 15,
        'grandTotalAll' => 35,
        'month' => 9,
        'year' => 2026,
        'monthName' => 'September',
        'opdName' => $opd->name,
        'includeRekap' => false,
        'includeRincian' => false,
        'includeDokumentasi' => true,
        'dokumentasiList' => $dokumentasiList,
    ])->render();

    // Verify Title & Subheader
    expect($html)->toContain('Dokumentasi Makan Minum Petugas Lapangan Bulan September 2026');
    expect($html)->toContain('Tanggal 01 September 2026');

    // Verify Siang & Malam columns
    expect($html)->toContain('Siang : 20 bungkus');
    expect($html)->toContain('Malam : 15 Bungkus');

    // Rekap/Rincian header should NOT be present when only dokumentasi is selected
    expect($html)->not->toContain('REKAPITULASI DOKUMENTASI KONSUMSI MAKAN MINUM');
});

test('authenticated user can export dokumentasi foto PDF from database', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    DokumentasiKonsumsi::create([
        'opd_id' => $opd->id,
        'tanggal' => '2026-09-01',
        'jumlah_siang' => 25,
        'jumlah_malam' => 18,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('dokumentasi-konsumsi.export-pdf', [
        'startDate' => '2026-09-01',
        'endDate' => '2026-09-01',
        'include_rekap' => 0,
        'include_rincian' => 0,
        'include_dokumentasi' => 1,
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('x-filename', 'dokumentasi_foto_konsumsi_01-09-2026_01-09-2026.pdf');
});

