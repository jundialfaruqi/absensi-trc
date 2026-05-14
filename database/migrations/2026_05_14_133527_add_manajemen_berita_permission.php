<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah permission sudah ada
        $permission = Permission::where('name', 'manajemen-berita')->first();
        
        if (!$permission) {
            $permission = Permission::create([
                'name' => 'manajemen-berita',
                'group' => 'Berita'
            ]);
        }

        // Assign ke super-admin
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'manajemen-berita')->first();
        if ($permission) {
            // Revoke dari super-admin dulu
            $superAdmin = Role::where('name', 'super-admin')->first();
            if ($superAdmin) {
                $superAdmin->revokePermissionTo($permission);
            }
            $permission->delete();
        }
    }
};
