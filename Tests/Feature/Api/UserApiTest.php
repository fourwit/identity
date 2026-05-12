<?php

namespace Modules\Identity\Tests\Feature\Api;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_that_can_list_users_via_api()
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'status'
                ]
            ],
            'pagination' => [
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);
    }

    /** @test */
    public function test_that_can_create_user_via_api()
    {
        $response = $this->postJson('/api/v1/users', [
            'name'   => 'API User',
            'email'  => 'api@example.com',
            'status' => 'active',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_that_returns_proper_json_on_validation_error()
    {
        $response = $this->postJson('/api/v1/users', ['name' => '']);

        $response->assertStatus(422);
        $response->assertJsonStructure(['success', 'message', 'errors']);
    }

    /** @test */
    public function test_that_can_get_single_user_via_api()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_that_returns_404_when_user_not_found()
    {
        $response = $this->getJson('/api/v1/users/999999');
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function test_that_can_update_user_via_api()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name'   => 'Updated',
            'email'  => $user->email,
            'status' => 'active',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_that_can_delete_user_via_api()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_that_creating_user_logs_activity_via_api()
    {
        $this->postJson('/api/v1/users', [
            'name'   => 'API Log Test',
            'email'  => 'apilog@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Created new user: API Log Test',
            'source'      => 'api',
        ]);
    }


    /** @test */
    public function test_that_deleting_user_logs_activity_via_api()
    {
        $user = User::factory()->create(['name' => 'Delete API Test']);

        $this->deleteJson("/api/v1/users/{$user->id}");

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Deleted user: Delete API Test',
            'source'      => 'api',
        ]);
    }

    /** @test */
    public function test_that_can_list_activity_logs_via_api()
    {
        ActivityLog::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_that_pagination_works_in_api()
    {
        User::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/users?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
    }


    /** @test */
    public function test_that_uuid_is_generated_when_enabled()
    {
        config(['identity.enable_uuid' => true]);

        $user = User::factory()->create();

        $this->assertNotNull($user->uuid);
    }

    /** @test */
    public function test_that_uuid_is_not_generated_when_disabled()
    {
        config(['identity.enable_uuid' => false]);

        $user = User::factory()->create();

        $this->assertNull($user->uuid);
    }

    /** @test */
    public function test_that_uuid_appears_in_api_response_when_enabled()
    {
        config(['identity.enable_uuid' => true]);

        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertJsonStructure([
            'data' => ['uuid']
        ]);
    }

    /** @test */
    public function test_that_username_appears_in_api_response_when_enabled()
    {
        config(['identity.enable_username' => true]);

        $user = User::factory()->create(['username' => 'johndoe']);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertJsonStructure([
            'data' => ['username']
        ]);
    }

    /** @test */
    public function test_that_username_does_not_appear_when_disabled()
    {
        config(['identity.enable_username' => false]);

        $user = User::factory()->create(['username' => 'johndoe']);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertJsonMissing(['username']);
    }

}