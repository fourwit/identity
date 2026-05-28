<?php

namespace Modules\Identity\Tests\Feature\Web;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserWebActivityLogTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_deleting_user_logs_activity()
    {
        $user = User::factory()->create(['name' => 'Delete Web Test']);
        $this->delete("/admin/users/{$user->id}");
        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Deleted user: Delete Web Test',
            'source' => 'web',
        ]);
    }

    public function test_that_can_view_activity_logs_page()
    {
        ActivityLog::factory()->count(3)->create();
        $this->get('/admin/activity-logs')->assertStatus(200)->assertSee('Activity Logs');
    }
}
