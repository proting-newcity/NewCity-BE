<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    private const PATH_LOGIN = '/api/login';
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials_and_get_role_masyarakat()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        Masyarakat::factory()->create(['id' => $user->id]);

        $response = $this->postJson(self::PATH_LOGIN, [
            'username' => $user->username,
            'password' => 'password123',
            'always_signed_in' => true,
            'role' => 'masyarakat',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login success',
                'token_type' => 'Bearer',
                'role' => 'masyarakat',
            ]);

        $this->assertArrayHasKey('access_token', $response->json());
    }

    public function test_login_returns_invalid_credentials_for_wrong_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson(self::PATH_LOGIN, [
            'username' => $user->username,
            'password' => 'wrongpassword',
            'always_signed_in' => true,
            'role' => 'masyarakat',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid login credentials']);
    }

    public function test_login_sets_correct_role_for_pemerintah_and_admin()
    {
        $pemerintahUser = User::factory()->create(['password' => bcrypt('secret')]);
        Pemerintah::factory()->create(['id' => $pemerintahUser->id]);

        $adminUser = User::factory()->create(['password' => bcrypt('secret')]);
        Admin::factory()->create(['id' => $adminUser->id]);

        $this->postJson(self::PATH_LOGIN, [
            'username' => $pemerintahUser->username,
            'password' => 'secret',
            'always_signed_in' => true,
            'role' => 'pemerintah',
        ])->assertJson(['role' => 'pemerintah']);

        $this->postJson(self::PATH_LOGIN, [
            'username' => $adminUser->username,
            'password' => 'secret',
            'always_signed_in' => true,
            'role' => 'admin',
        ])->assertJson(['role' => 'admin']);
    }

    public function test_login_requires_all_fields()
    {
        $response = $this->postJson(self::PATH_LOGIN, []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username', 'password', 'always_signed_in']);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertNoContent();

        $this->assertGuest();
    }
}
