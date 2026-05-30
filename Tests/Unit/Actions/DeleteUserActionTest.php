<?php

namespace Modules\Identity\Tests\Unit\Actions;

use Tests\TestCase;
use Modules\Identity\Actions\DeleteUserAction;
use Modules\Identity\Models\User;
use Modules\Identity\Enums\UserStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Exceptions\CannotDeleteUserException;
use Modules\Identity\Support\BootstrapsIdentitySchema;
use Modules\Identity\Models\IdentityProfile;

class DeleteUserActionTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected DeleteUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
        config([
            'identity.protection.enabled' => true,
            'identity.deletion.strategy' => 'safe',
            'identity.protection.super_admin_email' => 'super-admin@example.com',
            'identity.protection.super_admin_name' => 'Super Admin',
            'identity.protection.super_admin_uuid' => null,
        ]);
        
        Event::fake([
            UserDeleted::class,
        ]);

        $this->action = new DeleteUserAction(
            app(\Modules\Identity\Contracts\UserRepositoryInterface::class)
        );
    }

    /** @test */
    public function test_deletes_user_successfully()
    {
        $user = User::factory()->create([
            'id' => 2,
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
    public function test_throws_exception_when_deleting_configured_super_admin_by_email()
    {
        $user = User::factory()->create([
            'name' => 'Any Name',
            'email' => 'super-admin@example.com',
            'status' => 'active',
        ]);

        $this->expectException(CannotDeleteUserException::class);
        $this->expectExceptionMessage('Cannot delete the main admin user');

        $this->action->execute($user, 'web');
    }

    /** @test */
    public function test_does_not_dispatch_suspended_event_when_active_user_deleted()
    {
        $user = User::factory()->create([
            'id' => 2,
            'name' => 'Active User',
            'status' => 'active',
        ]);

        $this->action->execute($user, 'web');

        Event::assertDispatched(UserDeleted::class);
    }

    /** @test */
    public function test_shared_mode_cannot_delete_super_admin_by_configured_name()
    {
        config([
            'identity.mode' => 'shared',
            'identity.models.user' => User::class,
            'identity.tables.users' => 'users',
            'identity.tables.profiles' => 'identity_profiles',
            'identity.protection.super_admin_email' => 'super-admin@example.com',
            'identity.protection.super_admin_name' => 'Super Admin',
        ]);

        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'someone-else@example.com',
        ]);

        IdentityProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'active']
        );

        $this->expectException(CannotDeleteUserException::class);
        $this->expectExceptionMessage('Cannot delete the main admin user');

        $this->action->execute($user, 'web');
    }
}
