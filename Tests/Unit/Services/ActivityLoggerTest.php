<?php

namespace Modules\Identity\Tests\Unit\Services;

use Tests\TestCase;
use Modules\Identity\Services\ActivityLogger;
use Modules\Identity\Models\User;
use Modules\Identity\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class ActivityLoggerTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
    }

    /** @test */
    public function test_logs_activity_successfully()
    {
        $user = User::factory()->create([
            'name' => 'Causer User',
        ]);

        $subject = User::factory()->create([
            'name' => 'Subject User',
        ]);

        Auth::login($user);

        ActivityLogger::log(
            'Test activity log description',
            $subject,
            ['key' => 'value'],
            'tested',
            'api'
        );

        $this->assertDatabaseHas('activity_logs', [
            'log_name' => 'user',
            'description' => 'Test activity log description',
            'subject_type' => User::class,
            'subject_id' => $subject->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'event' => 'tested',
            'source' => 'api',
        ]);

        $log = ActivityLog::first();
        $this->assertEquals(['key' => 'value'], $log->properties);
    }
}
