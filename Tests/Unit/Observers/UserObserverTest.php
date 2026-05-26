<?php

namespace Modules\Identity\Tests\Unit\Observers;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Observers\UserObserver;
use Modules\Identity\Events\UserSuspended;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;


class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::observe(UserObserver::class);

        Event::fake([
            UserSuspended::class
        ]);
    }

    /** @test */
    public function test_observer_sets_default_status()
    {
        $user = User::factory()->create([
            'status' => null  // No status provided
        ]);

        $this->assertEquals('active', $user->fresh()->status->value);
    }

    /** @test */
    public function test_observer_generates_uuid_when_enabled()
    {
        config(['identity.features.uuid' => true]);

        $user = User::factory()->create();

        $this->assertNotNull($user->fresh()->uuid);
        $this->assertTrue(strlen($user->fresh()->uuid) > 10);
    }

    /** @test */
    public function test_observer_lowercases_email()
    {
        $user = User::factory()->create([
            'email' => 'JOHN@EXAMPLE.COM'
        ]);

        $this->assertEquals('john@example.com', $user->fresh()->email);
    }

    /** @test */
    public function test_observer_dispatches_user_suspended_event()
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->status = 'suspended';
        $user->save();

        \Illuminate\Support\Facades\Event::assertDispatched(
            \Modules\Identity\Events\UserSuspended::class
        );
    }
}