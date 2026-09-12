<?php

use App\Models\User;
use App\Models\Opd;
use App\Models\Shift;
use App\Models\Konsumsi;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\DokumentasiKonsumsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $permission = Permission::firstOrCreate(['name' => 'upload-dokumentasi', 'group' => 'Dokumentasi']);
    $kordinatorRole = Role::firstOrCreate(['name' => 'kordinator', 'color' => '#10b981']);
    $kordinatorRole->givePermissionTo($permission);
});

test('guest is redirected to login when accessing upload dokumentasi', function () {
    $this->get(route('upload-dokumentasi'))
        ->assertRedirect(route('login'));
});

test('user without upload-dokumentasi permission receives 403 forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('upload-dokumentasi'))
        ->assertForbidden();
});

test('user with upload-dokumentasi permission can access page and see form components', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $this->actingAs($user)
        ->get(route('upload-dokumentasi'))
        ->assertOk()
        ->assertSee('Upload Dokumentasi');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->assertSet('readyToLoad', true)
        ->assertSet('tanggal', Carbon::today()->format('Y-m-d'))
        ->assertSet('shift', '')
        ->assertSet('jumlah_porsi', 0)
        ->assertSee('Tanggal Dokumentasi')
        ->assertSee('Pilih Shift')
        ->assertSeeHtml('-- Pilih Shift --');
});

test('validation requires tanggal, shift, and foto1', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', '')
        ->set('shift', '')
        ->set('foto1', null)
        ->call('save')
        ->assertHasErrors(['tanggal', 'shift', 'foto1']);
});

test('validation fails when jumlah_porsi exceeds calculated max quota', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');
    $fakeImage = UploadedFile::fake()->image('test.jpg');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 10) // Max is 0 because no personnels present
        ->set('foto1', $fakeImage)
        ->call('save')
        ->assertHasErrors(['jumlah_porsi']);
});

test('can upload documentation for shift siang and save to database and storage', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');

    // Create Shift and Konsumsi
    $shift = Shift::create([
        'name' => 'P',
        'type' => 'shift',
        'keterangan' => 'PAGI',
        'start_time' => '08:00',
        'end_time' => '20:00',
        'color' => '#22c55e',
    ]);
    $konsumsi = Konsumsi::create(['nama' => 'Siang']);
    $shift->konsumsis()->attach($konsumsi->id);

    // Create OPD, Penugasan, Personnel, Jadwal, Absensi Hadir
    $opd = Opd::create(['name' => 'DISKOMINFO']);
    $penugasan = \App\Models\Penugasan::create(['name' => 'Call Taker']);
    $personnel = Personnel::create([
        'name' => 'John Doe',
        'nik' => '1234567890123456',
        'opd_id' => $opd->id,
        'penugasan_id' => $penugasan->id,
        'foto' => 'personnel/test.jpg',
        'email' => 'john@mail.com',
        'password' => bcrypt('password'),
        'pin' => bcrypt('123456'),
    ]);
    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => $targetDate,
    ]);
    Absensi::create([
        'personnel_id' => $personnel->id,
        'tanggal' => $targetDate,
        'status' => 'HADIR',
        'jam_masuk' => '07:55:00',
    ]);

    $foto1 = UploadedFile::fake()->image('siang1.jpg');
    $foto2 = UploadedFile::fake()->image('siang2.jpg');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->assertSet('jumlah_porsi', 1)
        ->set('foto1', $foto1)
        ->set('foto2', $foto2)
        ->set('keterangan', 'Dokumentasi makan siang aman')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('upload-dokumentasi'));

    $record = DokumentasiKonsumsi::whereDate('tanggal', $targetDate)->first();
    expect($record)->not->toBeNull()
        ->and($record->jumlah_siang)->toBe(1)
        ->and($record->keterangan)->toBe('Dokumentasi makan siang aman')
        ->and($record->foto_siang)->not->toBeNull()
        ->and($record->foto_siang_2)->not->toBeNull();

    Storage::disk('public')->assertExists($record->foto_siang);
    Storage::disk('public')->assertExists($record->foto_siang_2);
});

test('uploading shift malam preserves existing shift siang documentation', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');

    // Existing record with shift siang
    $existing = DokumentasiKonsumsi::create([
        'tanggal' => $targetDate,
        'opd_id' => null,
        'jumlah_siang' => 15,
        'foto_siang' => 'dokumentasi-konsumsi/test/siang.jpg',
        'created_by' => $user->id,
    ]);

    $fotoMalam1 = UploadedFile::fake()->image('malam1.jpg');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'malam')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $fotoMalam1)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('upload-dokumentasi'));

    $record = DokumentasiKonsumsi::whereDate('tanggal', $targetDate)->first();
    expect($record)->not->toBeNull()
        ->and($record->foto_siang)->toBe('dokumentasi-konsumsi/test/siang.jpg')
        ->and($record->jumlah_siang)->toBe(15)
        ->and($record->foto_malam)->not->toBeNull()
        ->and($record->jumlah_malam)->toBe(0);

    Storage::disk('public')->assertExists($record->foto_malam);
});

test('resetForm clears all input fields and resets validation', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $fakeImage = UploadedFile::fake()->image('test.jpg');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 5)
        ->set('keterangan', 'Catatan test')
        ->set('foto1', $fakeImage)
        ->call('resetForm')
        ->assertSet('shift', '')
        ->assertSet('jumlah_porsi', 0)
        ->assertSet('keterangan', '')
        ->assertSet('foto1', null)
        ->assertSet('foto2', null);
});

test('validation fails when photo size exceeds 2048KB', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    // Create file over 2048KB (e.g. 2500KB)
    $largeImage = UploadedFile::fake()->create('large.jpg', 2500, 'image/jpeg');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $largeImage)
        ->call('save')
        ->assertHasErrors(['foto1' => 'max']);
});

test('validation passes with compressed webp image within 2048KB', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $webpImage = UploadedFile::fake()->create('compressed.webp', 95, 'image/webp');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $webpImage)
        ->call('save')
        ->assertHasNoErrors(['foto1']);
});

test('shows inline error when selected shift already has documentation on date', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');

    // Create existing documentation for shift siang
    DokumentasiKonsumsi::create([
        'tanggal' => $targetDate,
        'opd_id' => null,
        'jumlah_siang' => 10,
        'foto_siang' => 'dokumentasi-konsumsi/test/siang.jpg',
        'created_by' => $user->id,
    ]);

    // Selecting shift siang should immediately show error on shift
    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->assertHasErrors(['shift'])
        // Switching to shift malam (which has no documentation yet) should clear error
        ->set('shift', 'malam')
        ->assertHasNoErrors(['shift']);
});

test('save is blocked with error if documentation for shift already exists', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');

    DokumentasiKonsumsi::create([
        'tanggal' => $targetDate,
        'opd_id' => null,
        'jumlah_siang' => 10,
        'foto_siang' => 'dokumentasi-konsumsi/test/siang.jpg',
        'created_by' => $user->id,
    ]);

    $foto = UploadedFile::fake()->create('test.webp', 80, 'image/webp');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $foto)
        ->call('save')
        ->assertHasErrors(['shift']);
});

test('validation fails when jumlah_porsi is negative or non-integer', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');
    $foto = UploadedFile::fake()->create('test.webp', 80, 'image/webp');

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', -5)
        ->set('foto1', $foto)
        ->call('save')
        ->assertHasErrors(['jumlah_porsi' => 'min']);

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 'abc')
        ->set('foto1', $foto)
        ->call('save')
        ->assertHasErrors(['jumlah_porsi' => 'integer']);
});

test('validation fails when photo mime type is not jpeg, jpg, png, or webp', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');
    $invalidPdf = UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf');
    $invalidPhp = UploadedFile::fake()->create('shell.php', 10, 'text/x-php');

    // Test PDF rejection
    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $invalidPdf)
        ->call('save')
        ->assertHasErrors(['foto1' => 'image']);

    // Test PHP script rejection
    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $invalidPhp)
        ->call('save')
        ->assertHasErrors(['foto1' => 'image']);
});

test('validation passes when uploading valid jpeg, jpg, png, or webp files directly within 2048KB', function (string $extension, string $mime) {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');
    $file = UploadedFile::fake()->image("sample.{$extension}")->size(500);

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $file)
        ->assertHasNoErrors(['foto1']);
})->with([
    ['jpg', 'image/jpeg'],
    ['jpeg', 'image/jpeg'],
    ['png', 'image/png'],
    ['webp', 'image/webp'],
]);

test('validation rejects dangerous script disguised with image extension (e.g. php script renamed to shell.jpg)', function () {
    $user = User::factory()->create();
    $user->assignRole('kordinator');

    $targetDate = Carbon::today()->format('Y-m-d');

    // Create a temporary file containing PHP shell script
    $tmpPath = tempnam(sys_get_temp_dir(), 'sec_test_');
    file_put_contents($tmpPath, '<?php system($_GET["cmd"] ?? "whoami"); ?>');

    // Disguise file as jpg with image/jpeg mime header (simulating Burp Suite manipulation)
    $disguisedFile = new class($tmpPath, 'shell.jpg', 'image/jpeg', null, true) extends UploadedFile {
        public $name = 'shell.jpg';
    };

    Livewire::actingAs($user)
        ->test('admin::upload-dokumentasi')
        ->call('load')
        ->set('tanggal', $targetDate)
        ->set('shift', 'siang')
        ->set('jumlah_porsi', 0)
        ->set('foto1', $disguisedFile)
        ->call('save')
        ->assertHasErrors(['foto1' => 'image']);

    if (file_exists($tmpPath)) {
        @unlink($tmpPath);
    }
});


