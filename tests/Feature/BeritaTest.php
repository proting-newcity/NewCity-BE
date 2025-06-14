<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use App\Models\User;
use App\Models\Berita;
use App\Models\KategoriBerita;

class BeritaTest extends TestCase
{
    private const PATH_BERITA = '/api/berita';

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function testIndexBerita()
    {
        $kategori = KategoriBerita::factory()->create();
        $user = Admin::factory()->create();
        Berita::factory()->count(5)->create([
            'id_kategori' => $kategori->id,
            'id_user' => $user->id,
        ]);

        $response = $this->getJson(self::PATH_BERITA);
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links']);
    }

    public function testGetByCategorySuccess()
    {
        $kategori = KategoriBerita::factory()->create();
        $user = Admin::factory()->create();
        Berita::factory()->create([
            'id_kategori' => $kategori->id,
            'id_user' => $user->id,
        ]);

        $response = $this->getJson("/api/berita/category/{$kategori->id}");
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function testGetByCategoryNotFound()
    {
        $response = $this->getJson('/api/berita/category/99999');
        $response->assertStatus(404);
    }

    public function testStoreBeritaValidationError()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $response = $this->postJson(self::PATH_BERITA, []);
        $response->assertStatus(422);
    }

    public function testStoreBeritaSuccess()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $kategori = KategoriBerita::factory()->create();
        $file = UploadedFile::fake()->image('berita.jpg');

        $response = $this->postJson(self::PATH_BERITA, [
            'title' => 'Berita Test',
            'content' => 'Berita Content',
            'status' => 'published',
            'id_kategori' => $kategori->id,
            'foto' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Berita Test']);
    }

    public function testUpdateBeritaNotFound()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $response = $this->postJson('/api/berita/999', [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(404);
    }

    public function testDestroyBeritaSuccess()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $kategori = KategoriBerita::factory()->create();
        $berita = Berita::factory()->create([
            'id_user' => $user->id,
            'id_kategori' => $kategori->id
        ]);

        $response = $this->deleteJson("/api/berita/{$berita->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Berita deleted successfully']);
    }



    public function testLikeBerita()
    {
        $user = User::factory()->create();
        Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $kategori = KategoriBerita::factory()->create();
        $berita = Berita::factory()->create([
            'id_user' => $user->id,
            'id_kategori' => $kategori->id
        ]);

        $response = $this->postJson('/api/berita/like', ['id' => $berita->id]);
        $response->assertStatus(200)
            ->assertJsonStructure(['success']);
    }
}
