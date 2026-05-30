<?php

namespace Modules\Identity\Tests\Unit\Observers;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Observers\UserObserver;
use Modules\Identity\Models\IdentityProfile;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;


class UserObserverTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();

        User::observe(UserObserver::class);

        Event::fake();
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
    public function test_observer_generates_uuid_when_enabled()
    {
        config(['identity.features.uuid' => true]);
        $user = User::factory()->create();
        IdentityProfile::updateOrCreate(['user_id' => $user->id], ['uuid' => (string) \Illuminate\Support\Str::uuid()]);
        $this->assertNotNull($user->fresh()->identityProfile?->uuid);
    }
}
