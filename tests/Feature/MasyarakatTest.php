<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Diskusi;
use App\Models\Report;
use App\Models\Masyarakat;
use App\Models\RatingReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class MasyarakatTest extends TestCase
{
    private const PATH_NOTIFICATION = '/api/notification';
    use RefreshDatabase;

    public function testNotificationUnauthorizedUser()
    {
        $user = User::factory()->create(); // Non-masyarakat user

        $response = $this->actingAs($user)->json('GET', self::PATH_NOTIFICATION);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['error' => 'You are not authorized!']);
    }

    public function testNotificationAuthorizedUser()
    {
        $masyarakatUser = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $masyarakatUser->id]);
        $masyarakatUser->masyarakat()->save($masyarakat);

        $this->actingAs($masyarakatUser, 'sanctum');

        $response = $this->json('GET', self::PATH_NOTIFICATION);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'foto_profile',
                        'name',
                        'type',
                        'content',
                        'foto',
                        'tanggal',
                        'id_report'
                    ]
                ]
            ]);
    }

    public function testNotificationNoNotifications()
    {
        $masyarakatUser = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $masyarakatUser->id]);
        $masyarakatUser->masyarakat()->save($masyarakat);

        $this->actingAs($masyarakatUser, 'sanctum');

        $response = $this->json('GET', self::PATH_NOTIFICATION);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function testNotificationWithDiskusiAndRatingReports()
    {
        $masyarakatUser = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $masyarakatUser->id]);
        $masyarakatUser->masyarakat()->save($masyarakat);

        // Create report owned by this user
        $report = Report::factory()->create([
            'id_masyarakat' => $masyarakatUser->id
        ]);

        // Create dummy diskusi and rating reports (bisa dari user lain atau user itu sendiri)
        $otherUser = User::factory()->create();

        Diskusi::factory()->create([
            'content' => 'This is a diskusi content',
            'id_report' => $report->id,
            'id_user' => $otherUser->id
        ]);

        RatingReport::factory()->create([
            'id_report' => $report->id,
            'id_user' => $otherUser->id
        ]);

        $this->actingAs($masyarakatUser);

        $response = $this->json('GET', self::PATH_NOTIFICATION);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'type' => 'diskusi',
                'content' => 'This is a diskusi content',
            ])
            ->assertJsonFragment([
                'type' => 'like', // karena di API response type-nya "like"
                'content' => 'Liked a report',
            ]);
    }

    public function testNotificationPagination()
    {
        $masyarakatUser = User::factory()->create();
        $masyarakat = Masyarakat::factory()->create(['id' => $masyarakatUser->id]);
        $masyarakatUser->masyarakat()->save($masyarakat);

        // Create report owned by this user
        $report = Report::factory()->create([
            'id_masyarakat' => $masyarakatUser->id
        ]);

        // Create dummy diskusi and rating reports (bisa dari user lain atau user itu sendiri)
        $otherUser = User::factory()->create();

        // Create 15 diskusi and RatingReports
        Diskusi::factory()->count(10)->create([
            'id_report' => $report->id,
            'id_user' => $otherUser->id
        ]);
        RatingReport::factory()->count(5)->create([
            'id_report' => $report->id,
            'id_user' => $otherUser->id
        ]);

        $this->actingAs($masyarakatUser);

        $response = $this->json('GET', '/api/notification?page=1');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(10, 'data') // First page should return 10 items
            ->assertJsonFragment(['type' => 'diskusi']);
    }
}
