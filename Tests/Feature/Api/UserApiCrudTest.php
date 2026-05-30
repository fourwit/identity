<?php

namespace Modules\Identity\Tests\Feature\Api;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserApiCrudTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_can_list_users_via_api()
    {
        User::factory()->count(5)->create();
        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200)->assertJsonStructure([
            'success', 'message',
            'data' => [['id', 'name', 'email', 'status']],
            'pagination' => ['current_page', 'per_page', 'total', 'last_page']
        ]);
    }

    public function test_that_can_create_user_via_api()
    {
        $response = $this->postJson('/api/v1/users', [
            'name' => 'API User',
            'first_name' => 'API',
            'last_name' => 'User',
            'email' => 'api@example.com',
            'status' => 'active',
            'password' => 'secret1234',
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_that_returns_proper_json_on_validation_error()
    {
        $this->postJson('/api/v1/users', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_that_can_get_single_user_via_api()
    {
        $user = User::factory()->create();
        $this->getJson("/api/v1/users/{$user->id}")->assertStatus(200);
    }

    public function test_that_can_update_user_via_api()
    {
        $user = User::factory()->create();

        $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated',
            'first_name' => $user->first_name ?? 'Updated',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => 'active',
        ])->assertStatus(200);
    }

    public function test_that_can_delete_user_via_api()
    {
        $user = User::factory()->create(['id' => 2, 'status' => 'active']);

        $this->deleteJson("/api/v1/users/{$user->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_that_pagination_works_in_api()
    {
        User::factory()->count(25)->create();
        $this->getJson('/api/v1/users?per_page=10')->assertStatus(200)->assertJsonCount(10, 'data');
    }

    public function test_that_can_filter_users_by_status_via_api()
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);
        $this->getJson('/api/v1/users?status=active')->assertStatus(200)->assertJsonCount(1, 'data');
    }
}
