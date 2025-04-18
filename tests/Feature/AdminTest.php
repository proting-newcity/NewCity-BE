<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pemerintah;
use App\Models\Institusi;
use App\Models\Admin;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private const PATH_STORE = '/api/pemerintah';
    private const PATH_UPDATE = '/api/pemerintah';
    private const PATH_UBAH_PASSWORD = '/api/reset-password';

    /** @test */
    public function store_pemerintah_requires_authentication()
    {
        $response = $this->postJson(self::PATH_STORE, [
            'name'         => 'John Doe',
            'username'     => 'johndoe',
            'phone'        => '12345679',
            'password'     => 'Password1',
            'status'       => true,
            'institusi_id' => 1,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function store_pemerintah_validation_error()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(self::PATH_STORE, [
            'name'   => 'John',
            'username' => 'johndoe',
            'phone'    => '12345678',
            'status'   => true,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function store_pemerintah_success()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        Event::fake();
        Institusi::factory()->create(['id' => 1, 'name' => 'Test Institusi']);
        $file = UploadedFile::fake()->image('foto.jpg');

        $payload = [
            'name'         => 'John Doe',
            'username'     => 'johndoe',
            'phone'        => '123456789',
            'password'     => 'Password1',
            'status'       => true,
            'institusi_id' => 1,
            'foto'         => $file,
        ];

        $response = $this->post(self::PATH_STORE, $payload);

        $response->assertStatus(204);
        $this->assertDatabaseHas('user', ['username' => 'johndoe']);
        $this->assertTrue(Hash::check('Password1', User::where('username', 'johndoe')->first()->password));
        $this->assertDatabaseHas('pemerintah', ['phone' => '123456789']);

        Event::assertDispatched(Registered::class);
    }

    /** @test */
    public function update_pemerintah_validation_error()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(self::PATH_UPDATE . '/1', [
            'username' => str_repeat('a', 300),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('username');
    }

    /** @test */
    public function update_pemerintah_not_found()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        Institusi::factory()->create(['id' => 1, 'name' => 'TestInstitusi']);

        $payload = [
            'name'         => 'Name',
            'username'     => 'user',
            'phone'        => '123',
            'status'       => true,
            'institusi_id' => 1,
        ];

        $response = $this->postJson(self::PATH_UPDATE . '/999', $payload);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'User or Pemerintah not found']);
    }

    /** @test */
    public function update_pemerintah_success()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        Institusi::factory()->create(['id' => 1, 'name' => 'Test Institusi']);
        $userToUpdate = User::factory()->create([
            'password' => Hash::make('Password1'),
            'foto'     => 'old/path.jpg',
        ]);
        Pemerintah::create([
            'id'           => $userToUpdate->id,
            'status'       => false,
            'phone'        => '0000000',
            'institusi_id' => 1,
        ]);

        $file = UploadedFile::fake()->image('newfoto.jpg');
        $payload = [
            'name'         => 'New Name',
            'username'     => 'newusername',
            'phone'        => '9999999',
            'password'     => 'NewPassword1',
            'status'       => true,
            'institusi_id' => 1,
            'foto'         => $file,
        ];

        $response = $this->post(self::PATH_UPDATE . '/' . $userToUpdate->id, $payload);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User and Pemerintah updated successfully']);

        $updatedUser = User::find($userToUpdate->id);
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('newusername', $updatedUser->username);
        $this->assertTrue(Hash::check('NewPassword1', $updatedUser->password));
        $this->assertDatabaseHas('pemerintah', ['id' => $userToUpdate->id, 'phone' => '9999999', 'status' => true]);
    }

    /** @test */
    public function ubah_password_validation_error()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(self::PATH_UBAH_PASSWORD, [
            'username'     => '',
            'new_password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['username', 'new_password']);
    }

    /** @test */
    public function ubah_password_user_not_found()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(self::PATH_UBAH_PASSWORD, [
            'username'     => 'nonexistent',
            'new_password' => 'Password1',
        ]);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'User not found']);
    }

    /** @test */
    public function ubah_password_success()
    {
        $user = User::factory()->create();
        Admin::factory()->create(['id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $userToChange = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('Password1'),
        ]);

        $response = $this->postJson(self::PATH_UBAH_PASSWORD, [
            'username'     => 'testuser',
            'new_password' => 'NewPassword1',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password updated successfully']);

        $this->assertTrue(Hash::check('NewPassword1', User::find($userToChange->id)->password));
    }
}
