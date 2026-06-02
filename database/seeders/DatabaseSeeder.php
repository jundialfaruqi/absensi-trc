<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Seed Permissions with Groups
        $permissions = [
            // Absensi
            ['name' => 'halaman-absensi', 'group' => 'Absensi'],
            ['name' => 'lihat-log-absensi', 'group' => 'Absensi'],
            ['name' => 'manajemen-absensi', 'group' => 'Absensi'],
            ['name' => 'manajemen-absensi-anomaly', 'group' => 'Absensi'],
            ['name' => 'reset-absen', 'group' => 'Absensi'],

            // APK
            ['name' => 'download-apk', 'group' => 'APK'],

            // Berita
            ['name' => 'manajemen-berita', 'group' => 'Berita'],

            // Cuti
            ['name' => 'manajemen-master-cuti', 'group' => 'Cuti'],
            ['name' => 'manajemen-permohonan-cuti', 'group' => 'Cuti'],

            // Dashboard
            ['name' => 'lihat-dashboard', 'group' => 'Dashboard'],
            ['name' => 'lihat-dashboard-opd', 'group' => 'Dashboard'],

            // Jadwal
            ['name' => 'manajemen-jadwal', 'group' => 'Jadwal'],
            ['name' => 'manajemen-jadwal-import', 'group' => 'Jadwal'],

            // Kantor
            ['name' => 'manajemen-kantor', 'group' => 'Kantor'],

            // Konsumsi
            ['name' => 'manajemen-konsumsi', 'group' => 'Konsumsi'],

            // Kotak Sampah
            ['name' => 'manajemen-kotak-sampah-absensi', 'group' => 'Kotak Sampah'],

            // Maps
            ['name' => 'manajemen-maps', 'group' => 'Maps'],

            // OPD
            ['name' => 'manajemen-opd', 'group' => 'OPD'],

            // Pengaturan
            ['name' => 'manajemen-pengaturan', 'group' => 'Pengaturan'],

            // Pengguna
            ['name' => 'manajemen-user', 'group' => 'Pengguna'],

            // Penugasan
            ['name' => 'manajemen-penugasan', 'group' => 'Penugasan'],

            // Perangkat
            ['name' => 'manajemen-perangkat', 'group' => 'Perangkat'],

            // Personel
            ['name' => 'manajemen-personel', 'group' => 'Personel'],

            // Role & Permission
            ['name' => 'manajemen-role-permission', 'group' => 'Role & Permission'],

            // Shift
            ['name' => 'manajemen-shift', 'group' => 'Shift'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Reset cached roles and permissions since WithoutModelEvents is used in this seeder
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Seed Roles
        $devRole = Role::create(['name' => 'dev', 'color' => '#8b5cf6']);
        $superAdminRole = Role::create(['name' => 'super-admin', 'color' => '#ef4444']);
        $adminOpdRole = Role::create(['name' => 'admin-opd', 'color' => '#3b82f6']);
        $adminAbsenRole = Role::create(['name' => 'admin-absen', 'color' => '#64748b']);

        // 3. Assign specific permissions to dev
        $devRole->givePermissionTo([
            'manajemen-absensi-anomaly',
            'lihat-dashboard',
            'manajemen-konsumsi',
            'manajemen-kotak-sampah-absensi',
            'manajemen-opd',
            'manajemen-pengaturan',
            'manajemen-user',
            'manajemen-penugasan',
            'manajemen-role-permission',
        ]);

        // 4. Assign specific permissions to super-admin
        $superAdminRole->givePermissionTo([
            'manajemen-absensi',
            'manajemen-permohonan-cuti',
            'manajemen-personel',
            'manajemen-jadwal',
            'manajemen-jadwal-import',
            'download-apk',
            'manajemen-berita',
            'manajemen-master-cuti',
            'lihat-dashboard',
            'manajemen-kantor',
            'manajemen-maps',
            'manajemen-perangkat',
            'manajemen-shift',
            'manajemen-role-permission',
            'manajemen-user',
        ]);

        // 5. Assign specific permissions to admin-opd
        $adminOpdRole->givePermissionTo([
            'manajemen-absensi',
            'manajemen-permohonan-cuti',
            'lihat-dashboard-opd',
            'manajemen-jadwal',
            'manajemen-jadwal-import',
            'manajemen-personel',
        ]);

        // 6. Assign specific permissions to admin-absen
        $adminAbsenRole->givePermissionTo([
            'halaman-absensi',
            'lihat-dashboard',
        ]);

        // 7. Create Super Admin User
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mail.com',
            'password' => bcrypt('admin123'),
        ]);

        $user->assignRole('super-admin');

        // 8. Default Settings
        Setting::set('personnel_registration_enabled', false, 'boolean');

        // 9. Seed OPD
        $this->call(OpdSeeder::class);

        // 10. Seed Shift
        $this->call(ShiftSeeder::class);

        // 11. Seed Personnel
        $this->call(PersonnelSeeder::class);
    }
}
