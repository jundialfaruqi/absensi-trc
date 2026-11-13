<?php

use App\Exports\PersonnelRawExport;
use App\Models\Kantor;
use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'manajemen-personel',
        'create-personel-all-opd',
        'create-personel-opd',
        'lihat-personel-all-opd',
        'lihat-personel-opd',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'group' => 'Personel']);
    }

    $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $superAdmin->givePermissionTo([
        'manajemen-personel',
        'create-personel-all-opd',
        'lihat-personel-all-opd',
    ]);

    $adminOpd = Role::firstOrCreate(['name' => 'admin-opd', 'color' => '#3b82f6']);
    $adminOpd->givePermissionTo([
        'manajemen-personel',
        'lihat-personel-opd',
    ]);
});

function seedPersonnelExportData(): array
{
    $opd1 = Opd::create(['name' => 'Badan Penanggulangan Bencana Daerah', 'singkatan' => 'BPBD', 'code' => 'BPBD']);
    $opd2 = Opd::create(['name' => 'Dinas Perhubungan', 'singkatan' => 'DISHUB', 'code' => 'DISHUB']);

    $kantor1 = Kantor::create([
        'name' => 'Markas Komando BPBD',
        'opd_id' => $opd1->id,
        'latitude' => 0.507068,
        'longitude' => 101.447779,
        'radius_meter' => 200,
    ]);

    $kantor2 = Kantor::create([
        'name' => 'Kantor Dishub',
        'opd_id' => $opd2->id,
        'latitude' => 0.510000,
        'longitude' => 101.450000,
        'radius_meter' => 150,
    ]);

    $penugasan = Penugasan::create(['name' => 'Petugas Lapangan']);

    $p1 = Personnel::create([
        'name' => 'Ahmad BPBD',
        'nik' => '1111111111111111',
        'opd_id' => $opd1->id,
        'penugasan_id' => $penugasan->id,
        'kantor_id' => $kantor1->id,
        'nomor_hp' => '081234567890',
        'pin' => '111111',
        'email' => 'ahmad@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    $p2 = Personnel::create([
        'name' => 'Budi Dishub',
        'nik' => '2222222222222222',
        'opd_id' => $opd2->id,
        'penugasan_id' => $penugasan->id,
        'kantor_id' => $kantor2->id,
        'nomor_hp' => '082345678901',
        'pin' => '222222',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'FLEXIBLE',
    ]);

    return compact('opd1', 'opd2', 'kantor1', 'kantor2', 'penugasan', 'p1', 'p2');
}

test('download button and modal are visible to any user with personnel page access even without create permission', function () {
    $data = seedPersonnelExportData();

    $viewer = User::factory()->create();
    $viewer->givePermissionTo(['manajemen-personel', 'lihat-personel-opd']);
    $viewer->opds()->attach($data['opd1']->id);

    $this->actingAs($viewer)
        ->get(route('personnel'))
        ->assertSuccessful()
        ->assertSee('Download')
        ->assertSee('personnel-download-modal')
        ->assertDontSee('Tambah Personnel');
});

test('super admin sees OPD filter dropdown in personnel download modal', function () {
    $data = seedPersonnelExportData();

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('personnel'))
        ->assertSuccessful()
        ->assertSee('Semua OPD')
        ->assertSee($data['opd1']->name)
        ->assertSee($data['opd2']->name);
});

test('super admin can export raw personnel excel filtered or unfiltered', function () {
    Excel::fake();
    $data = seedPersonnelExportData();

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('personnel.export-excel', ['opd_id' => $data['opd1']->id]))
        ->assertSuccessful();

    Excel::assertDownloaded(
        'data_raw_personnel_bpbd_'.date('Ymd_His').'.xlsx',
        function (PersonnelRawExport $export) use ($data) {
            $rows = $export->collection();
            expect($rows)->toHaveCount(1);
            expect($rows->first()->id)->toBe($data['p1']->id);

            $mapped = $export->map($rows->first());
            expect($mapped)->toBe([
                1,
                'Ahmad BPBD',
                'ahmad@example.com',
                'SCHEDULED',
                '081234567890',
                'Petugas Lapangan',
                'Markas Komando BPBD',
                'Badan Penanggulangan Bencana Daerah',
            ]);

            return true;
        }
    );
});

test('non super-admin is strictly scoped to own OPD when exporting excel even if another opd_id is requested', function () {
    Excel::fake();
    $data = seedPersonnelExportData();

    $opdUser = User::factory()->create();
    $opdUser->assignRole('admin-opd');
    $opdUser->opds()->attach($data['opd1']->id);

    $this->actingAs($opdUser)
        ->get(route('personnel.export-excel', ['opd_id' => $data['opd2']->id]))
        ->assertSuccessful();

    Excel::matchByRegex();
    Excel::assertDownloaded(
        '/^data_raw_personnel_bpbd_.*\.xlsx$/',
        function (PersonnelRawExport $export) use ($data) {
            $rows = $export->collection();
            expect($rows)->toHaveCount(1);
            expect($rows->first()->id)->toBe($data['p1']->id);

            return true;
        }
    );
});

test('super admin can export personnel pdf with custom blank columns grouped by OPD', function () {
    $data = seedPersonnelExportData();

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->get(route('personnel.export-pdf', [
            'columns' => ['Setuju', 'Tidak Setuju'],
        ]));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->headers->get('x-filename'))->toMatch('/^daftar_personnel_semua_opd_.*\.pdf$/');

    // Verify rendered PDF view groups by OPD and includes only number, name, and custom headers
    $html = view('reports.personnel-custom-pdf', [
        'groupedPersonnels' => collect([
            $data['opd1']->name => collect([$data['p1']]),
            $data['opd2']->name => collect([$data['p2']]),
        ]),
        'columns' => ['Setuju', 'Tidak Setuju'],
        'opdName' => 'Semua OPD',
        'printedAt' => '13 November 2026 23:00',
    ])->render();

    expect($html)
        ->toContain($data['opd1']->name)
        ->toContain($data['opd2']->name)
        ->toContain('Ahmad BPBD')
        ->toContain('Budi Dishub')
        ->toContain('Setuju')
        ->toContain('Tidak Setuju')
        ->not->toContain('ahmad@example.com')
        ->not->toContain('081234567890');

    expect(substr_count($html, 'class="opd-section page-break"'))->toBe(1);
    expect(substr_count($html, 'class="opd-section "'))->toBe(1);
});

test('personnel pdf export validates required columns and max 5 columns limit', function () {
    seedPersonnelExportData();

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->getJson(route('personnel.export-pdf'))
        ->assertUnprocessable();

    $this->actingAs($admin)
        ->getJson(route('personnel.export-pdf', [
            'columns' => ['K1', 'K2', 'K3', 'K4', 'K5', 'K6'],
        ]))
        ->assertUnprocessable();

    $this->actingAs($admin)
        ->getJson(route('personnel.export-pdf', [
            'columns' => ['Setuju', '   '],
        ]))
        ->assertUnprocessable();
});

test('user without personnel view permissions cannot export excel or pdf', function () {
    seedPersonnelExportData();

    $unauthorized = User::factory()->create();
    $unauthorized->givePermissionTo('manajemen-personel');

    $this->actingAs($unauthorized)
        ->getJson(route('personnel.export-excel'))
        ->assertForbidden();

    $this->actingAs($unauthorized)
        ->getJson(route('personnel.export-pdf', ['columns' => ['Setuju']]))
        ->assertForbidden();
});
