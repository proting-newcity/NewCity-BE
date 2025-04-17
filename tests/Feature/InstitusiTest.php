<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Institusi;

class InstitusiTest extends TestCase
{
    private const PATH_INSTITUSI = '/api/institusi/';
    private const MSG_NOT_FOUND = 'Institusi not found';

    use RefreshDatabase;

    /**
     * Test that the index method returns all institusis.
     */
    public function testIndexReturnsAllInstitusis()
    {
        // Create sample institusis using factory.
        Institusi::factory()->create(['name' => 'Test Institusi 1']);
        Institusi::factory()->create(['name' => 'Test Institusi 2']);

        // When a GET request is made to the index endpoint.
        $response = $this->json('GET', self::PATH_INSTITUSI);

        // Then the response status should be 200 OK and contain both institusis.
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'Test Institusi 1'])
            ->assertJsonFragment(['name' => 'Test Institusi 2']);
    }

    /**
     * Test that show returns an institusi when found.
     */
    public function testShowReturnsInstitusiWhenFound()
    {
        $institusi = Institusi::factory()->create(['name' => 'Test Institusi']);

        $response = $this->json('GET', self::PATH_INSTITUSI.$institusi->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'Test Institusi']);
    }

    /**
     * Test that show returns a 404 when the institusi is not found.
     */
    public function testShowReturnsNotFoundWhenInstitusiNotFound()
    {
        $nonExistentId = 9999;
        $response = $this->json('GET', self::PATH_INSTITUSI.$nonExistentId);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(['message' => self::MSG_NOT_FOUND]);
    }

    /**
     * Test that a new institusi can be stored.
     */
    public function testStoreCreatesNewInstitusi()
    {
        $postData = [
            'name' => 'New Institusi'
        ];

        $response = $this->json('POST', self::PATH_INSTITUSI, $postData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment(['name' => 'New Institusi']);

        // Verify that the institusi exists in the database.
        $this->assertDatabaseHas('institusi', $postData);
    }

    /**
     * Test that store validation fails when the required field is missing.
     */
    public function testStoreValidationFailsWhenNameMissing()
    {
        // Missing the "name" field.
        $response = $this->json('POST', self::PATH_INSTITUSI, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test that an existing institusi can be updated.
     */
    public function testUpdateUpdatesExistingInstitusi()
    {
        $institusi = Institusi::factory()->create(['name' => 'Old Name']);
        $updateData = ['name' => 'UpdatedName'];

        $response = $this->json('PUT', self::PATH_INSTITUSI.$institusi->id, $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'UpdatedName']);

        $this->assertDatabaseHas('institusi', [
            'id'   => $institusi->id,
            'name' => 'UpdatedName'
        ]);
    }

    /**
     * Test that update returns a 404 when the institusi is not found.
     */
    public function testUpdateReturnsNotFoundWhenInstitusiNotFound()
    {
        $nonExistentId = 9999;
        $updateData = ['name' => 'Updated Name'];

        $response = $this->json('PUT', self::PATH_INSTITUSI.$nonExistentId, $updateData);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(['message' => self::MSG_NOT_FOUND]);
    }

    /**
     * Test that update validation fails when an invalid name is provided.
     */
    public function testUpdateValidationFailsWhenInvalidName()
    {
        $institusi = Institusi::factory()->create(['name' => 'Valid Name']);
        // Providing a name that is too long.
        $updateData = ['name' => str_repeat('a', 300)];

        $response = $this->json('PUT', self::PATH_INSTITUSI.$institusi->id, $updateData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test that an institusi can be deleted.
     */
    public function testDestroyDeletesInstitusi()
    {
        $institusi = Institusi::factory()->create();

        $response = $this->json('DELETE', self::PATH_INSTITUSI.$institusi->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Institusi deleted successfully']);

        $this->assertDatabaseMissing('institusi', ['id' => $institusi->id]);
    }

    /**
     * Test that destroy returns a 404 when the institusi is not found.
     */
    public function testDestroyReturnsNotFoundWhenInstitusiNotFound()
    {
        $nonExistentId = 9999;
        $response = $this->json('DELETE', self::PATH_INSTITUSI.$nonExistentId);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(['message' => self::MSG_NOT_FOUND]);
    }
}
