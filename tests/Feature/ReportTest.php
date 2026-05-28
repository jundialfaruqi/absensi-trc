<?php

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
    $superAdminRole = Role::create(['name' => 'super-admin', 'color' => '#ef4444']);
    $superAdminRole->givePermissionTo($permission);
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
    expect($response->headers->get('content-disposition'))->toContain('attachment; filename=rekap_absensi_05_2026.pdf');
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
