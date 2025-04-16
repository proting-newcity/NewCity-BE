<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use App\Models\Berita;
use App\Models\KategoriBerita;

class BeritaTest extends TestCase
{
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

        $response = $this->getJson('/api/berita');
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
            ->assertJsonStructure(['data', 'links']);
    }

    public function testGetByCategoryNotFound()
    {
        $response = $this->getJson('/api/berita/category/999');
        $response->assertStatus(404);
    }

    public function testStoreBeritaValidationError()
    {
        $user = \App\Models\User::factory()->create();
        $admin = \App\Models\Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $response = $this->postJson('/api/berita', []);
        $response->assertStatus(422);
    }

    public function testStoreBeritaSuccess()
    {
        $user = \App\Models\User::factory()->create();
        $admin = \App\Models\Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $kategori = KategoriBerita::factory()->create();
        $file = UploadedFile::fake()->image('berita.jpg');

        $response = $this->postJson('/api/berita', [
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
        $user = \App\Models\User::factory()->create();
        $admin = \App\Models\Admin::factory()->create([
            'id' => $user->id
        ]);

        $this->actingAs($user, 'sanctum');


        $response = $this->putJson('/api/berita/999', [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(404);
    }

    public function testDestroyBeritaSuccess()
    {
        $user = \App\Models\User::factory()->create();
        $admin = \App\Models\Admin::factory()->create([
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

    public function testSearchBeritaNotFound()
    {
        $response = $this->getJson('/api/berita/search?search=unknownkeyword');
        $response->assertStatus(404);
    }

    public function testLikeBerita()
    {
        $user = \App\Models\User::factory()->create();
        $admin = \App\Models\Admin::factory()->create([
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
