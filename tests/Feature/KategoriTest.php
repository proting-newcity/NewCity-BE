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
    private const PATH_KATEGORI_BERITA = '/api/kategori/berita';
    private const PATH_KATEGORI_REPORT = '/api/kategori/report';

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function testIndexBeritaReturnsAllKategoriBerita()
    {
        KategoriBerita::factory()->create(['name' => 'Berita A']);
        KategoriBerita::factory()->create(['name' => 'Berita B']);

        $response = $this->json('GET', self::PATH_KATEGORI_BERITA);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'Berita A'])
            ->assertJsonFragment(['name' => 'Berita B']);
    }

    public function testIndexReportReturnsAllKategoriReport()
    {
        KategoriReport::factory()->create(['name' => 'Report X']);
        KategoriReport::factory()->create(['name' => 'Report Y']);

        $response = $this->json('GET', self::PATH_KATEGORI_REPORT);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'Report X'])
            ->assertJsonFragment(['name' => 'Report Y']);
    }

    public function testStoreReportValidationFailsWhenNameMissing()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->json('POST', self::PATH_KATEGORI_REPORT, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name']);
    }

    public function testStoreReportSuccessfullyCreatesKategoriReport()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $postData = ['name' => 'New Report'];

        $response = $this->json('POST', self::PATH_KATEGORI_REPORT, $postData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment(['name' => 'New Report']);

        $this->assertDatabaseHas('kategori_report', $postData);
    }

    public function testStoreBeritaValidationFailsWhenFieldsMissing()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->json('POST', self::PATH_KATEGORI_BERITA, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name', 'foto']);
    }

    public function testStoreBeritaSuccessfullyCreatesKategoriBerita()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');

        $this->withoutMiddleware();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('kategori.jpg');

        $postData = [
            'name' => 'BeritaKeren',
            'foto' => $file,
        ];

        $response = $this->postJson(self::PATH_KATEGORI_BERITA, $postData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment(['name' => 'BeritaKeren']);

        $this->assertDatabaseHas('kategori_berita', ['name' => 'BeritaKeren']);
    }
}
