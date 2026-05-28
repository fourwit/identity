<?php

namespace Modules\Identity\Tests\Unit\Actions;

use Tests\TestCase;
use Modules\Identity\Actions\DeleteUserAction;
use Modules\Identity\Models\User;
use Modules\Identity\Enums\UserStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\UserSuspended;
use Modules\Identity\Exceptions\CannotDeleteUserException;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class DeleteUserActionTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected DeleteUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
        
        Event::fake([
            UserDeleted::class,
            UserSuspended::class,
        ]);

        $this->action = new DeleteUserAction(
            app(\Modules\Identity\Contracts\UserRepositoryInterface::class)
        );
    }

    /** @test */
    public function test_deletes_user_successfully()
    {
        $user = User::factory()->create([
            'name' => 'Regular User',
            'status' => 'inactive',
        ]);

        $userId = $user->id;
        $userName = $user->name;

        $this->action->execute($user, 'web');

        $this->assertSoftDeleted($user);
        Event::assertDispatched(UserDeleted::class, function ($event) use ($userId, $userName) {
            return $event->userId === $userId && $event->userName === $userName;
        });
    }

    /** @test */
    public function test_throws_exception_when_deleting_active_super_admin()
    {
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'status' => 'active',
        ]);

        $this->expectException(CannotDeleteUserException::class);
        $this->expectExceptionMessage('Cannot delete the main admin user');

        $this->action->execute($user, 'web');
    }

    /** @test */
    public function test_dispatches_suspended_event_when_active_user_deleted()
    {
        $user = User::factory()->create([
            'name' => 'Active User',
            'status' => 'active',
        ]);

        $this->action->execute($user, 'web');

        Event::assertDispatched(UserSuspended::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }
}
