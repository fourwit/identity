<?php

namespace Modules\Identity\Tests\Feature\Api;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserApiActivityLogTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_creating_user_logs_activity_via_api()
    {
        $this->postJson('/api/v1/users', [
            'name' => 'API Log Test',
            'first_name' => 'API',
            'last_name' => 'Log',
            'email' => 'apilog@example.com',
            'status' => 'active',
            'password' => 'secret1234',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Created new user: API Log Test',
            'source' => 'api',
        ]);
    }

    public function test_that_deleting_user_logs_activity_via_api()
    {
        $user = User::factory()->create(['id' => 2, 'name' => 'Delete API Test']);
        $this->deleteJson("/api/v1/users/{$user->id}");

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Deleted user: Delete API Test',
            'source' => 'api',
        ]);
    }

    public function test_that_can_list_activity_logs_via_api()
    {
        ActivityLog::factory()->count(3)->create();
        $this->getJson('/api/v1/activity-logs')->assertStatus(200)->assertJson(['success' => true]);
    }
}
