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
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mail.com',
            'password' => bcrypt('admin123'),
        ]);

        $user->assignRole('super-admin');

        $user->givePermissionTo('manajemen-user');
        $user->givePermissionTo('manajemen-role-permission');
        $user->givePermissionTo('manajemen-opd');
        $user->givePermissionTo('manajemen-kantor');
        $user->givePermissionTo('manajemen-personnel');
        $user->givePermissionTo('manajemen-penugasan');
    }
}
