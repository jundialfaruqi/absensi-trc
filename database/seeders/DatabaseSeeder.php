<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Permissions with Groups
        $permissions = [
            ['name' => 'manajemen-absensi', 'group' => 'Absensi'],
            ['name' => 'manajemen-permohonan-cuti', 'group' => 'Cuti'],
            ['name' => 'manajemen-personel', 'group' => 'Personel'],
            ['name' => 'manajemen-opd', 'group' => 'OPD'],
            ['name' => 'manajemen-penugasan', 'group' => 'Penugasan'],
            ['name' => 'manajemen-kantor', 'group' => 'Kantor'],
            ['name' => 'manajemen-shift', 'group' => 'Shift'],
            ['name' => 'manajemen-jadwal', 'group' => 'Jadwal'],
            ['name' => 'manajemen-master-cuti', 'group' => 'Cuti'],
            ['name' => 'manajemen-jadwal-import', 'group' => 'Jadwal'],
            ['name' => 'manajemen-user', 'group' => 'Pengguna'],
            ['name' => 'manajemen-role-permission', 'group' => "Role & Permission"],
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create($permission);
        }

        // 2. Seed Roles
        $superAdminRole = \Spatie\Permission\Models\Role::create(['name' => 'super-admin', 'color' => '#ef4444']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin-opd', 'color' => '#ef4444']);

        // 3. Assign all permissions to super-admin
        $superAdminRole->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // 4. Create Super Admin User
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mail.com',
            'password' => bcrypt('admin123'),
        ]);

        $user->assignRole('super-admin');
    }
}
