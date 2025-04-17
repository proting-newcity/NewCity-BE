<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Institusi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    private const PATH_REGISTER = '/api/register';
    private const FIELD_PASSWORD = 'Password123!';

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_can_register_as_masyarakat()
    {
        $response = $this->postJson(self::PATH_REGISTER, [
            'name' => 'John Doe',
            'username' => 'johndoe123',
            'password' => self::FIELD_PASSWORD,
            'password_confirmation' => self::FIELD_PASSWORD,
            'role' => 'masyarakat',
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('user', [
            'username' => 'johndoe123',
        ]);

        $this->assertDatabaseHas('masyarakat', [
            'id' => User::where('username', 'johndoe123')->first()->id,
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_can_register_as_pemerintah_with_institusi()
    {
        $institusi = Institusi::factory()->create();

        $response = $this->postJson(self::PATH_REGISTER, [
            'name' => 'Jane Doe',
            'username' => 'janedoe456',
            'password' => self::FIELD_PASSWORD,
            'password_confirmation' => self::FIELD_PASSWORD,
            'role' => 'pemerintah',
            'institusi_id' => $institusi->id,
        ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('user', ['username' => 'janedoe456']);
        $this->assertDatabaseHas('pemerintah', [
            'id' => User::where('username', 'janedoe456')->first()->id,
            'institusi_id' => $institusi->id,
        ]);

        $this->assertAuthenticated();
    }

    public function test_registration_requires_all_fields()
    {
        $response = $this->postJson(self::PATH_REGISTER, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'username', 'password']);
    }

    public function test_username_must_be_unique()
    {
        User::factory()->create(['username' => 'duplicateuser']);

        $response = $this->postJson(self::PATH_REGISTER, [
            'name' => 'Someone',
            'username' => 'duplicateuser',
            'password' => self::FIELD_PASSWORD,
            'password_confirmation' => self::FIELD_PASSWORD,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_pemerintah_registration_requires_institusi_id()
    {
        $response = $this->postJson(self::PATH_REGISTER, [
            'name' => 'Gov User',
            'username' => 'govuser',
            'password' => self::FIELD_PASSWORD,
            'password_confirmation' => self::FIELD_PASSWORD,
            'role' => 'pemerintah',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['institusi_id']);
    }

    public function test_user_can_upload_profile_photo()
    {
        // Use Storage::fake to simulate the public disk
        Storage::fake('public');

        // Create a fake image file
        $file = UploadedFile::fake()->image('avatar.jpg');

        // Send a POST request with the fake image file
        $response = $this->postJson(self::PATH_REGISTER, [
            'name' => 'Photo User',
            'username' => 'photouser',
            'password' => self::FIELD_PASSWORD,
            'password_confirmation' => self::FIELD_PASSWORD,
            'role' => 'masyarakat',
            'foto' => $file,
        ]);

        // Assert the response was successful
        $response->assertNoContent();

        // Retrieve the user from the database
        $user = User::where('username', 'photouser')->first();

        // Assert that the user has a foto field and it's not null
        $this->assertNotNull($user->foto);
    }
}
