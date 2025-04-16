<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\KategoriBerita;
use App\Models\KategoriReport;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function testIndexBeritaReturnsAllKategoriBerita()
    {
        KategoriBerita::factory()->create(['name' => 'Berita A']);
        KategoriBerita::factory()->create(['name' => 'Berita B']);

        $response = $this->json('GET', '/api/kategori/berita');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonFragment(['name' => 'Berita A'])
                 ->assertJsonFragment(['name' => 'Berita B']);
    }

    /** @test */
    public function testIndexReportReturnsAllKategoriReport()
    {
        KategoriReport::factory()->create(['name' => 'Report X']);
        KategoriReport::factory()->create(['name' => 'Report Y']);

        $response = $this->json('GET', '/api/kategori/report');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonFragment(['name' => 'Report X'])
                 ->assertJsonFragment(['name' => 'Report Y']);
    }

    /** @test */
    public function testStoreReportValidationFailsWhenNameMissing()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->json('POST', '/api/kategori/report', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function testStoreReportSuccessfullyCreatesKategoriReport()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $postData = ['name' => 'New Report'];

        $response = $this->json('POST', '/api/kategori/report', $postData);

        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJsonFragment(['name' => 'New Report']);

        $this->assertDatabaseHas('kategori_report', $postData);
    }

    /** @test */
    public function testStoreBeritaValidationFailsWhenFieldsMissing()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->json('POST', '/api/kategori/berita', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonValidationErrors(['name', 'foto']);
    }

    /** @test */
    public function testStoreBeritaSuccessfullyCreatesKategoriBerita()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $this->withoutMiddleware();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('kategori.jpg');

        $postData = [
            'name' => 'Berita Keren',
            'foto' => $file,
        ];

        $response = $this->postJson('/api/kategori/berita', $postData);

        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJsonFragment(['name' => 'Berita Keren']);

        $this->assertDatabaseHas('kategori_berita', ['name' => 'Berita Keren']);
    }
}
