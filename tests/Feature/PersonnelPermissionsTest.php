<?php

use App\Models\Opd;
use App\Models\Penugasan;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed standard permissions
    $permissions = [
        'manajemen-personel',
        'create-personel-all-opd',
        'create-personel-opd',
        'edit-personel-all-opd',
        'edit-personel-opd',
        'lihat-personel-all-opd',
        'lihat-personel-opd',
        'delete-personel-all-opd',
        'delete-personel-opd',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'group' => 'Personel']);
    }

    $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $superAdmin->givePermissionTo([
        'manajemen-personel',
        'create-personel-all-opd',
        'edit-personel-all-opd',
        'lihat-personel-all-opd',
        'delete-personel-all-opd',
    ]);

    $adminOpd = Role::firstOrCreate(['name' => 'admin-opd', 'color' => '#3b82f6']);
    $adminOpd->givePermissionTo([
        'manajemen-personel',
        'create-personel-opd',
        'edit-personel-opd',
        'lihat-personel-opd',
        'delete-personel-opd',
    ]);
});

test('user with manajemen-personel and lihat-personel-all-opd can view all personnel across all OPDs', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);

    $user = User::factory()->create();
    $user->givePermissionTo(['manajemen-personel', 'lihat-personel-all-opd']);
    $user->opds()->attach($opd1->id);

    $p1 = Personnel::create([
        'name' => 'Andi BKPSDM',
        'nik' => '1111111111111111',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'pin' => '111111',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    $p2 = Personnel::create([
        'name' => 'Budi DISDIK',
        'nik' => '2222222222222222',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'pin' => '222222',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    Livewire::actingAs($user)
        ->test('admin::personnel')
        ->call('load')
        ->assertSee('Andi BKPSDM')
        ->assertSee('Budi DISDIK')
        ->assertSee('Semua OPD (Filter)');
});

test('user with manajemen-personel and lihat-personel-opd only views personnel from own OPD', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);

    $user = User::factory()->create();
    $user->givePermissionTo(['manajemen-personel', 'lihat-personel-opd']);
    $user->opds()->attach($opd1->id);

    $p1 = Personnel::create([
        'name' => 'Andi BKPSDM',
        'nik' => '1111111111111111',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'pin' => '111111',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    $p2 = Personnel::create([
        'name' => 'Budi DISDIK',
        'nik' => '2222222222222222',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'pin' => '222222',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    Livewire::actingAs($user)
        ->test('admin::personnel')
        ->call('load')
        ->assertSee('Andi BKPSDM')
        ->assertDontSee('Budi DISDIK')
        ->assertDontSee('Semua OPD (Filter)');
});

test('user without lihat permissions cannot view any personnel data', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);

    $user = User::factory()->create();
    $user->givePermissionTo(['manajemen-personel']);
    $user->opds()->attach($opd1->id);

    Personnel::create([
        'name' => 'Andi BKPSDM',
        'nik' => '1111111111111111',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'pin' => '111111',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    Livewire::actingAs($user)
        ->test('admin::personnel')
        ->call('load')
        ->assertDontSee('Andi BKPSDM');
});

test('user with create-personel-all-opd can create personnel for any OPD', function () {
    Storage::fake('public');

    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);
    $penugasan = Penugasan::create(['name' => 'TRC Staff', 'description' => 'Staff TRC']);

    $user = User::factory()->create();
    $user->givePermissionTo(['create-personel-all-opd']);
    $user->opds()->attach($opd1->id);

    $foto = UploadedFile::fake()->image('personnel.jpg');

    Livewire::actingAs($user)
        ->test('admin::personnel-create')
        ->set('name', 'Pegawai Baru Disdik')
        ->set('nik', '3333333333333333')
        ->set('opd_id', $opd2->id)
        ->set('penugasan_id', $penugasan->id)
        ->set('nomor_hp', '081234567890')
        ->set('foto', $foto)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('personnel'));

    expect(Personnel::where('nik', '3333333333333333')->first())
        ->not->toBeNull()
        ->opd_id->toBe($opd2->id);
});

test('user with create-personel-opd can only create personnel for own OPD', function () {
    Storage::fake('public');

    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);
    $penugasan = Penugasan::create(['name' => 'TRC Staff', 'description' => 'Staff TRC']);

    $user = User::factory()->create();
    $user->givePermissionTo(['create-personel-opd']);
    $user->opds()->attach($opd1->id);

    $foto = UploadedFile::fake()->image('personnel.jpg');

    // Trying to create for different OPD should abort 403
    Livewire::actingAs($user)
        ->test('admin::personnel-create')
        ->set('name', 'Pegawai Ilegal')
        ->set('nik', '4444444444444444')
        ->set('opd_id', $opd2->id)
        ->set('penugasan_id', $penugasan->id)
        ->set('nomor_hp', '081234567890')
        ->set('foto', $foto)
        ->call('save')
        ->assertStatus(403);

    // Creating for own OPD should succeed
    Livewire::actingAs($user)
        ->test('admin::personnel-create')
        ->set('name', 'Pegawai Legal')
        ->set('nik', '5555555555555555')
        ->set('opd_id', $opd1->id)
        ->set('penugasan_id', $penugasan->id)
        ->set('nomor_hp', '081234567890')
        ->set('foto', $foto)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('personnel'));

    expect(Personnel::where('nik', '5555555555555555')->first())
        ->not->toBeNull()
        ->opd_id->toBe($opd1->id);
});

test('user with edit-personel-all-opd can edit personnel of any OPD', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);
    $penugasan = Penugasan::create(['name' => 'TRC Staff', 'description' => 'Staff TRC']);

    $user = User::factory()->create();
    $user->givePermissionTo(['edit-personel-all-opd']);
    $user->opds()->attach($opd1->id);

    $personnel = Personnel::create([
        'name' => 'Nama Lama',
        'nik' => '6666666666666666',
        'opd_id' => $opd2->id,
        'penugasan_id' => $penugasan->id,
        'pin' => '666666',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    Livewire::actingAs($user)
        ->test('admin::personnel-edit', ['id' => $personnel->id])
        ->set('name', 'Nama Baru Disdik')
        ->call('save')
        ->assertHasNoErrors();

    expect($personnel->fresh()->name)->toBe('Nama Baru Disdik');
});

test('user with edit-personel-opd can edit own OPD personnel but is forbidden for other OPD', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);
    $penugasan = Penugasan::create(['name' => 'TRC Staff', 'description' => 'Staff TRC']);

    $user = User::factory()->create();
    $user->givePermissionTo(['edit-personel-opd']);
    $user->opds()->attach($opd1->id);

    $pOther = Personnel::create([
        'name' => 'Pegawai OPD Lain',
        'nik' => '7777777777777777',
        'opd_id' => $opd2->id,
        'penugasan_id' => $penugasan->id,
        'pin' => '777777',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    $pOwn = Personnel::create([
        'name' => 'Pegawai OPD Sendiri',
        'nik' => '8888888888888888',
        'opd_id' => $opd1->id,
        'penugasan_id' => $penugasan->id,
        'pin' => '888888',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    // Forbidden on other OPD
    Livewire::actingAs($user)
        ->test('admin::personnel-edit', ['id' => $pOther->id])
        ->assertStatus(403);

    // Allowed on own OPD
    Livewire::actingAs($user)
        ->test('admin::personnel-edit', ['id' => $pOwn->id])
        ->set('name', 'Pegawai OPD Sendiri Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect($pOwn->fresh()->name)->toBe('Pegawai OPD Sendiri Updated');
});

test('user with delete-personel-all-opd can delete personnel from any OPD', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);

    $user = User::factory()->create();
    $user->givePermissionTo(['manajemen-personel', 'lihat-personel-all-opd', 'delete-personel-all-opd']);
    $user->opds()->attach($opd1->id);

    $personnel = Personnel::create([
        'name' => 'Untuk Dihapus All',
        'nik' => '9999999999999991',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'pin' => '999991',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    Livewire::actingAs($user)
        ->test('admin::personnel')
        ->call('load')
        ->call('confirmDelete', $personnel->id, $personnel->name)
        ->call('executeDelete')
        ->assertDispatched('toast');

    expect(Personnel::find($personnel->id))->toBeNull();
});

test('user with delete-personel-opd can delete own OPD personnel but not other OPD', function () {
    $opd1 = Opd::create(['name' => 'Badan Kepegawaian', 'code' => 'BKPSDM']);
    $opd2 = Opd::create(['name' => 'Dinas Pendidikan', 'code' => 'DISDIK']);

    $user = User::factory()->create();
    $user->givePermissionTo(['manajemen-personel', 'lihat-personel-opd', 'delete-personel-opd']);
    $user->opds()->attach($opd1->id);

    $pOther = Personnel::create([
        'name' => 'Pegawai OPD Lain',
        'nik' => '9999999999999992',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'pin' => '999992',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    $pOwn = Personnel::create([
        'name' => 'Pegawai OPD Sendiri',
        'nik' => '9999999999999993',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'pin' => '999993',
        'foto' => 'sample.jpg',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'attendance_type' => 'SCHEDULED',
    ]);

    // Forbidden to confirm delete on other OPD
    Livewire::actingAs($user)
        ->test('admin::personnel')
        ->call('load')
        ->call('confirmDelete', $pOther->id, $pOther->name)
        ->assertStatus(403);

    // Allowed on own OPD
    Livewire::actingAs($user)
        ->test('admin::personnel')
        ->call('load')
        ->call('confirmDelete', $pOwn->id, $pOwn->name)
        ->call('executeDelete')
        ->assertDispatched('toast');

    expect(Personnel::find($pOwn->id))->toBeNull()
        ->and(Personnel::find($pOther->id))->not->toBeNull();
});
