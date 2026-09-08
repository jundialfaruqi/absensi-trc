<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminMyProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminOpdRole;
    protected Role $superAdminRole;
    protected Role $nonAdminRole;
    protected Opd $opd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminOpdRole = Role::create(['name' => 'admin-opd']);
        $this->superAdminRole = Role::create(['name' => 'super-admin']);
        $this->nonAdminRole = Role::create(['name' => 'anggota']);

        $this->opd = Opd::create([
            'name' => 'Badan Penanggulangan Bencana Daerah',
            'singkatan' => 'BPBD',
            'alamat' => 'Jl. HR Soebrantas No. 12, Pekanbaru',
        ]);
    }

    public function test_admin_opd_can_access_my_profile_with_limited_data(): void
    {
        $user = User::create([
            'name' => 'Admin BPBD',
            'email' => 'admin.bpbd@pekanbaru.go.id',
            'password' => Hash::make('password123'),
            'nomor_hp' => '081234567890',
        ]);
        $user->assignRole($this->adminOpdRole);
        $user->opds()->attach($this->opd->id);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin.bpbd@pekanbaru.go.id',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.access_token');

        // Test primary endpoint
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/auth/my-profile');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Admin BPBD')
            ->assertJsonPath('data.email', 'admin.bpbd@pekanbaru.go.id')
            ->assertJsonPath('data.nomor_hp', '081234567890')
            ->assertJsonPath('data.role', 'Admin OPD')
            ->assertJsonPath('data.role_code', 'admin-opd')
            ->assertJsonPath('data.is_super_admin', false)
            ->assertJsonPath('data.opd.name', 'Badan Penanggulangan Bencana Daerah')
            ->assertJsonPath('data.opd.singkatan', 'BPBD')
            ->assertJsonPath('data.opd.alamat', 'Jl. HR Soebrantas No. 12, Pekanbaru');

        // Pastikan tidak ada data yang tidak perlu bocor (misal permissions list, password, dll)
        $data = $response->json('data');
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
        $this->assertArrayNotHasKey('permissions', $data);

        // Test alias endpoint /api/v1/admin/my-profile
        $aliasResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/my-profile');
        $aliasResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_super_admin_can_access_my_profile(): void
    {
        $user = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@pekanbaru.go.id',
            'password' => Hash::make('supersecret'),
        ]);
        $user->assignRole($this->superAdminRole);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'superadmin@pekanbaru.go.id',
            'password' => 'supersecret',
        ]);
        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/auth/my-profile');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.role', 'Super Admin')
            ->assertJsonPath('data.is_super_admin', true)
            ->assertJsonPath('data.opd', null);
    }

    public function test_non_admin_cannot_access_my_profile(): void
    {
        $user = User::create([
            'name' => 'Anggota Reguler',
            'email' => 'anggota@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->nonAdminRole);

        // Attempt login on admin auth should fail
        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'anggota@pekanbaru.go.id',
            'password' => 'password123',
        ]);
        $loginResponse->assertStatus(403);

        // Even with a forged/direct unauthenticated call to my-profile
        $response = $this->getJson('/api/v1/admin/auth/my-profile');
        $response->assertStatus(401);
    }
}
