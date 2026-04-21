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
            ['name' => 'manajemen-absensi', 'group' => 'Overview'],
            ['name' => 'manajemen-permohonan-cuti', 'group' => 'Overview'],
            ['name' => 'manajemen-personel', 'group' => 'Overview'],
            ['name' => 'manajemen-opd', 'group' => 'Data'],
            ['name' => 'manajemen-penugasan', 'group' => 'Data'],
            ['name' => 'manajemen-kantor', 'group' => 'Data'],
            ['name' => 'manajemen-shift', 'group' => 'Data'],
            ['name' => 'manajemen-jadwal', 'group' => 'Data'],
            ['name' => 'manajemen-master-cuti', 'group' => 'Data'],
            ['name' => 'manajemen-jadwal-import', 'group' => 'Data'],
            ['name' => 'manajemen-user', 'group' => 'Settings'],
            ['name' => 'manajemen-role-permission', 'group' => 'Settings'],
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create($permission);
        }

        // 2. Seed Roles
        $superAdminRole = \Spatie\Permission\Models\Role::create(['name' => 'super-admin', 'color' => 'error']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin', 'color' => 'primary']);

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
