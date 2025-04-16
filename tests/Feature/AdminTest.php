<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use App\Models\Pemerintah;
use App\Models\Institusi;
use App\Http\Controllers\AdminController;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Storage::fake('public');
    }

    /**
     * Create a partial mock of the AdminController to override helper methods.
     */
    private function getControllerMock(array $additionalMethods = [])
    {
        return $this->getMockBuilder(AdminController::class)
            ->onlyMethods(array_merge(['checkRole', 'uploadImage', 'deleteImage'], $additionalMethods))
            ->getMock();
    }

    /**
     * Test storePemerintah returns unauthorized when the role check fails.
     */
    public function testStorePemerintahUnauthorized()
    {
        $controller = $this->getControllerMock();
        $controller->method('checkRole')->with("admin")->willReturn(false);

        $request = Request::create('/store-pemerintah', 'POST', [
            'name'     => 'John Doe',
            'username' => 'johndoe',
            'phone'    => '123456789',
            'password' => 'Password1',
            'status'   => true,
        ]);

        $response = $controller->storePemerintah($request);
        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('You are not authorized!', $data['error']);
    }

    /**
     * Test storePemerintah returns validation error with missing data.
     */
    public function testStorePemerintahValidationError()
    {
        $controller = $this->getControllerMock();
        $controller->method('checkRole')->with("admin")->willReturn(true);

        $request = Request::create('/store-pemerintah', 'POST', [
            'name'     => 'John Doe',
            'username' => 'johndoe',
            'phone'    => '123456789',
            'status'   => true,
        ]);

        $response = $controller->storePemerintah($request);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test storePemerintah works correctly when valid data is passed.
     */
    public function testStorePemerintahSuccess()
    {
        $controller = $this->getControllerMock();
        $controller->method('checkRole')->with("admin")->willReturn(true);
        $controller->method('uploadImage')->willReturn('fake/path/image.jpg');

        Event::fake();

        $password = 'Password1';

        $file = UploadedFile::fake()->image('foto.jpg');

        $institusi = Institusi::factory()->create(['name' => 'Test Institusi', 'id' => 1]);

        $request = Request::create('/store-pemerintah', 'POST', [
            'name'       => 'John Doe',
            'username'   => 'johndoe',
            'phone'      => '123456789',
            'password'   => $password,
            'status'     => true,
            'institusi_id' => 1,
        ], [], ['foto' => $file]);

        $response = $controller->storePemerintah($request);
        // Expecting a noContent response (HTTP 204)
        $this->assertEquals(204, $response->getStatusCode());

        // Check that the user is created in the database.
        $user = User::where('username', 'johndoe')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($password, $user->password));

        // Check the associated Pemerintah record.
        $pemerintah = Pemerintah::find($user->id);
        $this->assertNotNull($pemerintah);
        $this->assertEquals('123456789', $pemerintah->phone);

        // Assert that the Registered event was dispatched.
        Event::assertDispatched(Registered::class);
    }

    /**
     * Test updatePemerintah returns validation error.
     */
    public function testUpdatePemerintahValidationError()
    {
        $controller = $this->getControllerMock();

        $request = Request::create('/update-pemerintah', 'POST', [
            'username' => str_repeat('a', 300),
        ]);
        $response = $controller->updatePemerintah($request, 1);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test updatePemerintah returns 404 when the User or Pemerintah is not found.
     */
    public function testUpdatePemerintahUserNotFound()
    {
        $controller = $this->getControllerMock();

        $request = Request::create('/update-pemerintah', 'POST', []);
        $response = $controller->updatePemerintah($request, 999); // non-existent ID
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User or Pemerintah not found', $data['message']);
    }

    /**
     * Test updatePemerintah successfully updates the user and pemerintah.
     */
    public function testUpdatePemerintahSuccess()
    {
        $institusi = Institusi::factory()->create(['name' => 'Test Institusi', 'id' => 1]);
        $password = 'Password1';
        $user = User::create([
            'name'     => 'Old Name',
            'username' => 'oldusername',
            'password' => Hash::make($password),
            'foto'     => 'old/path.jpg',
        ]);
        $pemerintah = Pemerintah::create([
            'id'           => $user->id,
            'status'       => false,
            'phone'        => '0000000',
            'institusi_id' => 1,
        ]);

        $controller = $this->getControllerMock();
        // Override uploadImage and deleteImage for file handling.
        $controller->method('uploadImage')->willReturn('new/path.jpg');
        $controller->method('deleteImage')->willReturn(true);

        $newPassword = 'NewPassword1';
        $newData = [
            'name'     => 'New Name',
            'username' => 'newusername',
            'phone'    => '9999999',
            'password' => $newPassword,
            'status'   => true,
        ];
        $file = UploadedFile::fake()->image('newfoto.jpg');
        $request = Request::create('/update-pemerintah', 'POST', $newData, [], ['foto' => $file]);

        $response = $controller->updatePemerintah($request, $user->id);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User and Pemerintah updated successfully', $data['message']);

        // Retrieve updated models.
        $updatedUser = User::find($user->id);
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('newusername', $updatedUser->username);
        $this->assertTrue(Hash::check($newPassword, $updatedUser->password));
        $this->assertEquals('new/path.jpg', $updatedUser->foto);

        $updatedPemerintah = Pemerintah::find($user->id);
        $this->assertEquals('9999999', $updatedPemerintah->phone);
        $this->assertEquals(true, $updatedPemerintah->status);
    }

    /**
     * Test ubahPassword returns validation error when required fields are missing.
     */
    public function testUbahPasswordValidationError()
    {
        $controller = $this->getControllerMock();

        $request = Request::create('/ubah-password', 'POST', [
            'username'     => '',
            'new_password' => ''
        ]);
        $response = $controller->ubahPassword($request);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test ubahPassword returns 404 when the user is not found.
     */
    public function testUbahPasswordUserNotFound()
    {
        $controller = $this->getControllerMock();

        $request = Request::create('/ubah-password', 'POST', [
            'username'     => 'nonexistent',
            'new_password' => 'Password1'
        ]);
        $response = $controller->ubahPassword($request);
        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User not found', $data['message']);
    }

    /**
     * Test ubahPassword successfully updates the user's password.
     */
    public function testUbahPasswordSuccess()
    {
        $password = 'Password1';
        $user = User::create([
            'name'     => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make($password),
        ]);

        $controller = $this->getControllerMock();

        $newPassword = 'NewPassword1';
        $request = Request::create('/ubah-password', 'POST', [
            'username'     => 'testuser',
            'new_password' => $newPassword,
        ]);

        $response = $controller->ubahPassword($request);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Password updated successfully', $data['message']);

        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check($newPassword, $updatedUser->password));
    }
}
