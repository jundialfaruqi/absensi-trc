<?php

namespace Tests\Feature;

use App\Models\AdminRefreshToken;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuthJwtTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminOpdRole;
    protected Role $superAdminRole;
    protected Role $devRole;
    protected Opd $opd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminOpdRole = Role::create(['name' => 'admin-opd']);
        $this->superAdminRole = Role::create(['name' => 'super-admin']);
        $this->devRole = Role::create(['name' => 'dev']);

        $this->opd = Opd::create([
            'name' => 'Badan Penanggulangan Bencana Daerah',
            'singkatan' => 'BPBD',
        ]);
    }

    public function test_admin_opd_can_login_and_receive_jwt_and_refresh_token(): void
    {
        $user = User::create([
            'name' => 'Admin BPBD',
            'email' => 'admin.bpbd@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->adminOpdRole);
        $user->opds()->attach($this->opd->id);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin.bpbd@pekanbaru.go.id',
            'password' => 'password123',
            'device_name' => 'Admin Test Phone',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token_type',
                    'access_token',
                    'refresh_token',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'roles',
                        'opd' => ['id', 'name'],
                    ],
                ],
            ]);

        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.access_token'));
        $this->assertNotEmpty($response->json('data.refresh_token'));
        $this->assertDatabaseCount('admin_refresh_tokens', 1);
    }

    public function test_super_admin_can_login_successfully(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pekanbaru.go.id',
            'password' => Hash::make('secret123'),
        ]);
        $user->assignRole($this->superAdminRole);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'superadmin@pekanbaru.go.id',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.is_super_admin', true);
    }

    public function test_user_without_admin_role_is_rejected_with_403(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->devRole);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'user@pekanbaru.go.id',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Akses ditolak. Aplikasi ini khusus untuk Admin OPD dan Super Admin.');
    }

    public function test_invalid_password_returns_401(): void
    {
        $user = User::create([
            'name' => 'Admin OPD',
            'email' => 'admin@pekanbaru.go.id',
            'password' => Hash::make('correct_password'),
        ]);
        $user->assignRole($this->adminOpdRole);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@pekanbaru.go.id',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Email atau kata sandi yang Anda masukkan salah.');
    }

    public function test_form_validation_returns_422_with_inline_errors(): void
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['status', 'message', 'errors' => ['email', 'password']]);
    }

    public function test_can_refresh_access_token_using_valid_refresh_token(): void
    {
        $user = User::create([
            'name' => 'Admin OPD',
            'email' => 'admin@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->adminOpdRole);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@pekanbaru.go.id',
            'password' => 'password123',
        ]);

        $oldAccessToken = $loginResponse->json('data.access_token');
        $refreshToken = $loginResponse->json('data.refresh_token');

        $refreshResponse = $this->postJson('/api/v1/admin/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $refreshResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $newAccessToken = $refreshResponse->json('data.access_token');
        $newRefreshToken = $refreshResponse->json('data.refresh_token');

        $this->assertNotEmpty($newAccessToken);
        $this->assertNotEmpty($newRefreshToken);
        $this->assertNotEquals($refreshToken, $newRefreshToken); // Pastikan token dirotasi
    }

    public function test_me_endpoint_returns_user_profile_when_token_is_valid(): void
    {
        $user = User::create([
            'name' => 'Admin BPBD',
            'email' => 'admin.bpbd@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->adminOpdRole);
        $user->opds()->attach($this->opd->id);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin.bpbd@pekanbaru.go.id',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Admin BPBD')
            ->assertJsonPath('data.opd.name', 'Badan Penanggulangan Bencana Daerah');
    }

    public function test_me_endpoint_rejects_unauthenticated_request(): void
    {
        $response = $this->getJson('/api/v1/admin/auth/me');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    public function test_logout_revokes_refresh_token(): void
    {
        $user = User::create([
            'name' => 'Admin OPD',
            'email' => 'admin@pekanbaru.go.id',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole($this->adminOpdRole);

        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@pekanbaru.go.id',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.access_token');
        $refreshToken = $loginResponse->json('data.refresh_token');

        $logoutResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/auth/logout', [
                'refresh_token' => $refreshToken,
            ]);

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Pastikan token yang sudah dicabut tidak bisa digunakan untuk refresh lagi
        $refreshResponse = $this->postJson('/api/v1/admin/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $refreshResponse->assertStatus(401);
    }
}
