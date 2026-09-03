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
