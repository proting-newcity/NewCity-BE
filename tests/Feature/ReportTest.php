<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use App\Models\KategoriReport;
use App\Models\Report;
use App\Models\RatingReport;
use App\Models\Diskusi;

class ReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

public function index_returns_paginated_reports_with_pelapor()
{
    // Create 15 distinct users, each with a masyarakat and a report
    $users = User::factory()->count(15)->create();

    foreach ($users as $user) {
        $masyarakat = Masyarakat::factory()->create([
            'id' => $user->id,
        ]);
        Report::factory()->create([
            'id_masyarakat' => $masyarakat->id,
        ]);
    }

    $response = $this->getJson('/api/report');

    $response->assertStatus(200)
             ->assertJsonStructure(['data', 'links']);

    $first = $response->json('data')[0];
    $this->assertArrayHasKey('pelapor', $first);
    $this->assertArrayNotHasKey('masyarakat', $first);
}

    public function index_admin_returns_only_pending_inprocess_or_rejected_reports()
    {
        // Create reports with various statuses
        $matching = Report::factory()->create(['status' => [
            ['status' => 'Menunggu', 'deskripsi' => 'test', 'tanggal' => now()->toISOString()],
        ]]);
        Report::factory()->create(['status' => [
            ['status' => 'Selesai', 'deskripsi' => 'done', 'tanggal' => now()->toISOString()],
        ]]);

        $response = $this->getJson('/api/report/admin');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
        $this->assertEquals($matching->id, $response->json()[0]['id']);
    }

    public function index_admin_returns_404_when_no_matching_reports()
    {
        Report::factory()->create(['status' => [
            ['status' => 'Selesai', 'deskripsi' => 'done', 'tanggal' => now()->toISOString()],
        ]]);

        $response = $this->getJson('/api/report/admin');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'No reports found for this status']);
    }

    public function store_requires_auth_and_validation_and_creates_report()
    {
        Storage::fake('public');

        // Create KategoriReport and user/masyarakat
        $KategoriReport = KategoriReport::factory()->create();
        $user = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $user->id]);

        // Attempt without auth
        $file = UploadedFile::fake()->image('foto.jpg');
        $payload = [
            'judul' => 'Test Judul',
            'deskripsi' => 'Deskripsi',
            'lokasi' => 'Lokasi',
            'foto' => $file,
            'id_kategori' => $KategoriReport->id,
        ];
        $this->postJson('/api/report', $payload)
             ->assertStatus(401);

        // Attempt with missing fields
        Sanctum::actingAs($user, [], 'sanctum');
        $this->postJson('/api/report', [])->assertStatus(422);

        // Successful creation
        $response = $this->postJson('/api/report', $payload);
        $response->assertStatus(201)
                 ->assertJsonFragment(['judul' => 'Test Judul', 'deskripsi' => 'Deskripsi']);

        $this->assertDatabaseHas('report', ['judul' => 'Test Judul', 'id_kategori' => $KategoriReport->id]);
    }

    public function get_by_KategoriReport_returns_reports_or_message()
    {
        $catA = KategoriReport::factory()->create();
        $catB = KategoriReport::factory()->create();
        Report::factory()->count(2)->create(['id_kategori' => $catA->id]);
        Report::factory()->create(['id_kategori' => $catB->id]);

        $resA = $this->getJson("/api/report/category/{$catA->id}");
        $resA->assertStatus(200)
             ->assertJsonStructure(['data', 'links']);

        $resNone = $this->getJson('/api/report/category/9999');
        $resNone->assertStatus(200)
                ->assertJson(['message' => 'No reports found']);
    }

    public function get_reports_by_status_returns_data_or_404()
    {
        Report::factory()->create(['status' => [
            ['status' => 'Ditolak', 'deskripsi' => 'nope', 'tanggal' => now()->toISOString()],
        ]]);
        Report::factory()->create(['status' => [
            ['status' => 'Selesai', 'deskripsi' => 'done', 'tanggal' => now()->toISOString()],
        ]]);

        $res = $this->getJson('/api/report/status/Ditolak');
        $res->assertStatus(200)
            ->assertJsonStructure(['data']);

        $res404 = $this->getJson('/api/report/status/Unknown');
        $res404->assertStatus(404)
              ->assertJson(['message' => 'No reports found for this status']);
    }

    public function my_reports_requires_auth_and_returns_paginated()
    {
        $user = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $user->id]);
        Report::factory()->count(3)->create(['id_masyarakat' => $masyarakat->id]);

        $this->getJson('/api/report/my')->assertStatus(401);

        Sanctum::actingAs($user, [], 'sanctum');
        $this->getJson('/api/report/my')
             ->assertStatus(200)
             ->assertJsonStructure(['data', 'links']);
    }

    public function add_status_handles_not_found_and_appends_status()
    {
        // Not found
        $this->postJson('/api/report/status/9999', ['status' => 'Selesai'])
             ->assertStatus(404)
             ->assertJson(['message' => 'Report not found']);

        // Prepare data
        Pemerintah::factory()->count(2)->create();
        $report = Report::factory()->create(['status' => []]);

        $res = $this->postJson("/api/report/status/{$report->id}", ['status' => 'Selesai']);
        $res->assertStatus(200);
        $this->assertNotNull($res->json('id_pemerintah'));
        $this->assertEquals('Selesai', last($res->json('status'))['status']);
    }

    public function show_returns_report_or_404()
    {
        $not = $this->getJson('/api/report/9999');
        $not->assertStatus(404)->assertJson(['message' => 'Report not found']);

        // Setup
        $userM = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $userM->id]);
        $userP = User::factory()->create();
        $pemerintah = Pemerintah::factory()->create(['id' => $userP->id]);
        $KategoriReport = KategoriReport::factory()->create();
        $report = Report::factory()->create([
            'id_masyarakat' => $masyarakat->id,
            'id_pemerintah' => $pemerintah->id,
            'id_kategori' => $KategoriReport->id,
        ]);
        RatingReport::factory()->count(2)->create(['id_report' => $report->id]);
        Diskusi::factory()->count(3)->create(['id_report' => $report->id]);

        $res = $this->getJson("/api/report/{$report->id}");
        $res->assertStatus(200)
            ->assertJsonStructure(['report', 'masyarakat', 'pemerintah', 'kategori', 'like', 'comment', 'hasLiked', 'hasBookmark']);
    }

    public function search_reports_returns_results_or_message()
    {
        Report::factory()->create(['judul' => 'UniqueTitle']);
        $match = $this->getJson('/api/report/search?search=e');
        $match->assertStatus(200)
              ->assertJsonStructure(['data', 'links']);

        $none = $this->getJson('/api/report/search?search=NoMatch');
        $none->assertStatus(200)
             ->assertJson(['message' => 'No reports found']);
    }

    public function update_and_destroy_handle_authorization_and_crud()
    {
        $user = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $user->id]);
        $report = Report::factory()->create(['id_masyarakat' => $masyarakat->id]);

        // Unauthorized update/delete
        $this->postJson("/api/report/{$report->id}", [])->assertStatus(401);
        $this->deleteJson("/api/report/{$report->id}")->assertStatus(401);

        // Authorized
        Sanctum::actingAs($user, [], 'sanctum');
        $updateRes = $this->postJson("/api/report/{$report->id}", ['judul' => 'NewTitle']);
        $updateRes->assertStatus(200)
                  ->assertJsonFragment(['judul' => 'NewTitle']);

        $deleteRes = $this->deleteJson("/api/report/{$report->id}");
        $deleteRes->assertStatus(200)
                  ->assertJson(['message' => 'Report deleted successfully']);
    }

    public function like_and_bookmark_toggle_successfully()
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();

        Sanctum::actingAs($user, [], 'sanctum');
        $like = $this->postJson('/api/report/like', ['id' => $report->id]);
        $like->assertStatus(200)->assertJsonStructure(['success']);

        $bookmark = $this->postJson('/api/report/bookmark', ['id' => $report->id]);
        $bookmark->assertStatus(200)->assertJsonStructure(['success']);
    }

    public function diskusi_store_and_show_work_as_expected()
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();

        // Validation error
        Sanctum::actingAs($user, [], 'sanctum');
        $this->postJson("/api/report/diskusi/{$report->id}", [])->assertStatus(422);

        // Success
        $res = $this->postJson("/api/report/diskusi/{$report->id}", ['content' => 'Hello']);
        $res->assertStatus(200)->assertJsonStructure(['success']);
        $this->assertDatabaseHas('diskusi', ['id_report' => $report->id, 'content' => 'Hello']);

        // Show
        $show = $this->getJson("/api/report/diskusi/{$report->id}");
        $show->assertStatus(200)
             ->assertJsonCount(1);
    }

    public function liked_reports_returns_user_likes()
    {
        $user = User::factory()->create();
        $r1 = Report::factory()->create();
        $r2 = Report::factory()->create();
        RatingReport::factory()->create(['id_user' => $user->id, 'id_report' => $r1->id]);

        Sanctum::actingAs($user, [], 'sanctum');
        $res = $this->getJson('/api/report/liked');
        $res->assertStatus(200)
            ->assertJsonStructure(['data', 'links']);
        $this->assertCount(1, $res->json('data'));
    }
}
