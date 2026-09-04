<?php

use App\Models\Opd;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'manajemen-absensi', 'group' => 'Absensi']);
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $superAdminRole->givePermissionTo($permission);
});

test('unauthenticated users cannot export Excel', function () {
    $response = $this->get(route('absensi.export-excel', [
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-10',
    ]));

    $response->assertRedirect('/login');
});

test('authenticated super-admin can export Excel and download .xlsx file', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);

    Personnel::create([
        'name' => 'Budi Santoso',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'budi.jpg',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(route('absensi.export-excel', [
        'startDate' => '2026-05-01',
        'endDate' => '2026-05-10',
    ]));

    $response->assertSuccessful();
    $response->assertHeader('content-disposition');
    expect($response->headers->get('content-disposition'))->toContain('attachment; filename=rekap_absensi_2026-05-01_2026-05-10.xlsx');
});

test('absensi export creates separate sheets per OPD', function () {
    $opd1 = Opd::create(['name' => 'Dinas Kesehatan', 'singkatan' => 'DINKES']);
    $opd2 = Opd::create(['name' => 'Satuan Polisi Pamong Praja', 'singkatan' => 'SATPOL PP']);

    Personnel::create([
        'name' => 'Petugas Dinkes',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'foto' => 'dinkes.jpg',
        'email' => 'dinkes@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    Personnel::create([
        'name' => 'Petugas Satpol',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'foto' => 'satpol.jpg',
        'email' => 'satpol@example.com',
        'password' => bcrypt('password'),
        'pin' => '654321',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user);

    $export = new AbsensiExport('2026-05-01', '2026-05-05');
    $sheets = $export->sheets();

    expect(count($sheets))->toBe(2);
    expect($sheets[0]->title())->toBe('DINKES');
    expect($sheets[1]->title())->toBe('SATPOL PP');
});

test('absensi export filters by opd_id when selected', function () {
    $opd1 = Opd::create(['name' => 'Dinas Kesehatan', 'singkatan' => 'DINKES']);
    $opd2 = Opd::create(['name' => 'Satuan Polisi Pamong Praja', 'singkatan' => 'SATPOL PP']);

    Personnel::create([
        'name' => 'Petugas Dinkes',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'foto' => 'dinkes.jpg',
        'email' => 'dinkes@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    Personnel::create([
        'name' => 'Petugas Satpol',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'foto' => 'satpol.jpg',
        'email' => 'satpol@example.com',
        'password' => bcrypt('password'),
        'pin' => '654321',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user);

    $export = new AbsensiExport('2026-05-01', '2026-05-05', null, (string)$opd1->id);
    $sheets = $export->sheets();

    expect(count($sheets))->toBe(1);
    expect($sheets[0]->title())->toBe('DINKES');
});

test('absensi export date header cells are formatted with 00 and suppress number stored as text warning', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);

    Personnel::create([
        'name' => 'Petugas Dishub',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'dishub.jpg',
        'email' => 'dishub@example.com',
        'password' => bcrypt('password'),
        'pin' => '999888',
        'attendance_type' => 'SCHEDULED',
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $this->actingAs($user);

    // Export to temporary file and inspect cells with PhpSpreadsheet
    $tempPath = storage_path('framework/testing/test_absensi.xlsx');
    if (!file_exists(dirname($tempPath))) {
        mkdir(dirname($tempPath), 0777, true);
    }

    Excel::store(new AbsensiExport('2026-09-01', '2026-09-05', null, (string)$opd->id), 'test_absensi.xlsx', 'local');

    $storedFile = storage_path('app/private/test_absensi.xlsx');
    if (!file_exists($storedFile)) {
        $storedFile = storage_path('app/test_absensi.xlsx');
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($storedFile);
    $sheet = $spreadsheet->getSheet(0);

    // Assert OPD name row is positioned above month row with black bg and white text
    expect((string)$sheet->getCell('A4')->getValue())->toContain('DINAS PERHUBUNGAN');
    expect($sheet->getCell('A4')->getStyle()->getAlignment()->getHorizontal())->toBe(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    expect(strtoupper($sheet->getCell('A4')->getStyle()->getFill()->getStartColor()->getRGB()))->toBe('000000');
    expect(strtoupper($sheet->getCell('A4')->getStyle()->getFont()->getColor()->getRGB()))->toBe('FFFFFF');

    // Assert month row is below OPD row
    expect((string)$sheet->getCell('A5')->getValue())->toContain('BULAN');

    // Assert row 7 contains day names (e.g. Sel, Rab, Kam)
    expect($sheet->getCell('B7')->getValue())->toBe('Sel');

    // Assert row 8 contains date numbers as numeric with '00' format (displayed as 01 in Excel, no Number Stored as Text warning)
    expect($sheet->getCell('B8')->getValue())->toBe(1);
    expect($sheet->getCell('B8')->getDataType())->toBe(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    expect($sheet->getCell('B8')->getStyle()->getNumberFormat()->getFormatCode())->toBe('00');

    // Clean up temporary stored file
    if (file_exists($storedFile)) {
        unlink($storedFile);
    }
});

test('telat attendance is displayed as H and counted as Hadir in Excel, footer excludes Telat', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);

    $personnel = Personnel::create([
        'name' => 'Budi Santoso',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'budi.jpg',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    \Illuminate\Support\Facades\DB::table('absensis')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-09-01',
        'status' => 'TELAT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $this->actingAs($user);

    Excel::store(new AbsensiExport('2026-09-01', '2026-09-01', null, (string)$opd->id), 'test_telat.xlsx', 'local');

    $storedFile = storage_path('app/private/test_telat.xlsx');
    if (!file_exists($storedFile)) {
        $storedFile = storage_path('app/test_telat.xlsx');
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($storedFile);
    $sheet = $spreadsheet->getSheet(0);

    $rows = $sheet->toArray();
    // find row containing 'Budi Santoso'
    $budiRow = null;
    foreach ($rows as $idx => $r) {
        if (in_array('Budi Santoso', $r)) {
            $budiRow = $r;
            break;
        }
    }
    expect($budiRow)->not->toBeNull();
    // Budi Santoso is in column 0, date 'H' is in column 1
    expect($budiRow[0])->toBe('Budi Santoso');
    expect($budiRow[1])->toBe('H');
    // Hadir count is in column 3
    expect((int)$budiRow[3])->toBe(1);

    // Footer Keterangan should NOT contain T: Telat
    $allText = implode(' ', array_map(fn($r) => implode(' ', array_filter($r)), $rows));
    expect($allText)->toContain('H: Hadir');
    expect($allText)->not->toContain('T: Telat');

    if (file_exists($storedFile)) {
        unlink($storedFile);
    }
});

test('dinas attendance is displayed as D in Excel but NOT counted as Hadir', function () {
    $opd = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB']);

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
        'tanggal' => '2026-09-01',
        'status' => 'DINAS',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shift = \App\Models\Shift::create([
        'name' => 'Shift Pagi',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'type' => 'shift',
        'color' => '#10b981',
    ]);

    \Illuminate\Support\Facades\DB::table('jadwals')->insert([
        'personnel_id' => $personnel->id,
        'tanggal' => '2026-09-01',
        'shift_id' => $shift->id,
        'status' => 'SHIFT',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $this->actingAs($user);

    Excel::store(new AbsensiExport('2026-09-01', '2026-09-01', null, (string)$opd->id), 'test_dinas.xlsx', 'local');

    $storedFile = storage_path('app/private/test_dinas.xlsx');
    if (!file_exists($storedFile)) {
        $storedFile = storage_path('app/test_dinas.xlsx');
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($storedFile);
    $sheet = $spreadsheet->getSheet(0);

    $rows = $sheet->toArray();
    $ahmadRow = null;
    foreach ($rows as $r) {
        if (in_array('Ahmad Dani', $r)) {
            $ahmadRow = $r;
            break;
        }
    }
    expect($ahmadRow)->not->toBeNull();
    // Ahmad Dani in column 0, date displays 'D' in column 1
    expect($ahmadRow[0])->toBe('Ahmad Dani');
    expect($ahmadRow[1])->toBe('D');
    // JML count is in column 2, should be 0 (DINAS is not counted in JML)
    expect((int)$ahmadRow[2])->toBe(0);
    // Hadir count is in column 3, should be 0 (DINAS is not counted as hadir)
    expect((int)$ahmadRow[3])->toBe(0);

    // Footer Keterangan should contain D: Dinas
    $allText = implode(' ', array_map(fn($r) => implode(' ', array_filter($r)), $rows));
    expect($allText)->toContain('D: Dinas');

    if (file_exists($storedFile)) {
        unlink($storedFile);
    }
});

