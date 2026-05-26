<?php

namespace Modules\Identity\Tests\Unit\Actions;

use Tests\TestCase;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\DTOs\UserData;
use Modules\Identity\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Observers\UserObserver;
use Modules\Identity\Events\UserCreated;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    protected CreateUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        
         // Only fake domain events — lets Eloquent lifecycle events fire normally
        Event::fake([
            \Modules\Identity\Events\UserCreated::class,
            \Modules\Identity\Events\UserUpdated::class,
            \Modules\Identity\Events\UserDeleted::class,
            \Modules\Identity\Events\UserSuspended::class,
            \Modules\Identity\Events\UserActivated::class,
        ]);

        $this->action = new CreateUserAction(
            app(\Modules\Identity\Contracts\UserRepositoryInterface::class)
        );
    }

    /** @test */
    public function test_creates_user_successfully()
    {
        $data = new UserData(
            name: 'John Doe',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '1234567890',
            username: 'johndoe',
            status: 'active'
        );

        $user = $this->action->execute($data, 'web');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('active', $user->status->value);

        Event::assertDispatched(UserCreated::class);
    }

    /** @test */
    public function test_creates_user_with_default_status()
    {
        
        $data = new UserData(
            name: 'Jane Doe',
            firstName: 'Jane',
            lastName: null,
            email: 'jane@example.com',
            phone: null,
            username: null,
            status: null  // No status provided
        );


        $user = $this->action->execute($data, 'web');

        $this->assertEquals('active', $user->status->value);  // Should default to 'active'
    }

    /** @test */
    public function test_dispatches_user_created_event()
    {
        $data = new UserData(
            name: 'Test User',
            firstName: null,
            lastName: null,
            email: 'test@example.com',
            phone: null,
            username: null,
            status: 'active'
        );

        $this->action->execute($data, 'api');

        Event::assertDispatched(UserCreated::class, function ($event) {
            return $event->user->email === 'test@example.com';
        });
    }
}