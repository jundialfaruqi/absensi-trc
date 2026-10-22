<?php

use App\Models\User;
use App\Models\Opd;
use App\Models\Shift;
use App\Models\Personnel;
use App\Models\Jadwal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JadwalImport;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $perm = Permission::firstOrCreate(['name' => 'manajemen-jadwal-import', 'group' => 'Jadwal']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($perm);
});

test('user without permission cannot access jadwal import', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('jadwal.import'))
        ->assertForbidden();
});

test('super admin can access jadwal import page', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get(route('jadwal.import'))
        ->assertSuccessful()
        ->assertSee('Import Jadwal');
});

test('uploading excel file auto-detects target period and updates month and year properties', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $excelPath = '/Users/macprom4/Herd/absensi-trc/template_jadwal_2026_10.xlsx';
    if (! file_exists($excelPath)) {
        $this->markTestSkipped('Sample template excel file not found');
    }

    $file = UploadedFile::fake()->createWithContent(
        'template_jadwal_2026_10.xlsx',
        file_get_contents($excelPath)
    );

    // Initialized with September 2026 (09)
    $component = Livewire::actingAs($user)
        ->withQueryParams(['month' => '09', 'year' => '2026'])
        ->test('admin::jadwal-import')
        ->assertSet('month', '09')
        ->assertSet('year', '2026')
        ->set('file', $file);

    // After uploading October file, month must automatically switch to 10
    $component->assertSet('month', '10')
        ->assertSet('year', '2026')
        ->assertSet('detectedMonth', '10')
        ->assertSet('detectedYear', '2026')
        ->assertSet('detectedPeriod', 'Oktober 2026')
        ->assertSet('detectedDays', 31)
        ->assertSee('Target Periode Impor')
        ->assertSee('Oktober 2026')
        ->assertSee('Terdeteksi Otomatis');
});

test('safety guard blocks import if period was manually mismatched', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $excelPath = '/Users/macprom4/Herd/absensi-trc/template_jadwal_2026_10.xlsx';
    if (! file_exists($excelPath)) {
        $this->markTestSkipped('Sample template excel file not found');
    }

    $file = UploadedFile::fake()->createWithContent(
        'template_jadwal_2026_10.xlsx',
        file_get_contents($excelPath)
    );

    Livewire::actingAs($user)
        ->test('admin::jadwal-import')
        ->set('file', $file)
        ->set('month', '09') // force mismatch back to September
        ->call('import')
        ->assertHasErrors(['file']);
});

test('import executes queued import with correctly detected month and year', function () {
    Excel::fake();

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $excelPath = '/Users/macprom4/Herd/absensi-trc/template_jadwal_2026_10.xlsx';
    if (! file_exists($excelPath)) {
        $this->markTestSkipped('Sample template excel file not found');
    }

    $file = UploadedFile::fake()->createWithContent(
        'template_jadwal_2026_10.xlsx',
        file_get_contents($excelPath)
    );

    Livewire::actingAs($user)
        ->withQueryParams(['month' => '09', 'year' => '2026'])
        ->test('admin::jadwal-import')
        ->set('file', $file)
        ->call('import');

    Excel::matchByRegex();
    Excel::assertQueued('/^imports\/.+/', function (JadwalImport $import) {
        $ref = new ReflectionClass($import);
        $monthProp = $ref->getProperty('month');
        $monthProp->setAccessible(true);
        $yearProp = $ref->getProperty('year');
        $yearProp->setAccessible(true);

        return $monthProp->getValue($import) === '10' && $yearProp->getValue($import) === '2026';
    });
});

test('getImportProgress returns cached progress reliably', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $importId = 'test-uuid-123';
    Illuminate\Support\Facades\Cache::put("import_progress_{$importId}", [
        'total' => 100,
        'processed' => 50,
        'status' => 'processing',
        'percentage' => 50,
    ], 3600);

    Livewire::actingAs($user)
        ->test('admin::jadwal-import')
        ->set('importId', $importId)
        ->call('getImportProgress')
        ->assertReturned([
            'total' => 100,
            'processed' => 50,
            'status' => 'processing',
            'percentage' => 50,
        ]);
});

test('dispatchProgress handles broadcasting error gracefully without throwing exception', function () {
    $import = new JadwalImport('10', '2026', null, false, 'test-resilient-id');
    $ref = new ReflectionClass($import);
    $method = $ref->getMethod('dispatchProgress');
    $method->setAccessible(true);

    // Call with valid progress array — should not throw even if reverb/pusher is offline
    $method->invoke($import, [
        'total' => 10,
        'processed' => 10,
        'status' => 'completed',
        'percentage' => 100,
    ]);

    $cached = Illuminate\Support\Facades\Cache::get('import_progress_test-resilient-id');
    expect($cached['status'])->toBe('completed');
    expect($cached['percentage'])->toBe(100);
});

