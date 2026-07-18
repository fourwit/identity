<?php

namespace Modules\Identity\Tests\Unit\Actions;

use Tests\TestCase;
use Modules\Identity\Actions\UpdateUserAction;
use Modules\Identity\DTOs\UserData;
use Modules\Identity\Models\User;
use Modules\Identity\Models\IdentityProfile;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Events\UserUpdated;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UpdateUserActionTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected UpdateUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
        
        Event::fake([
            UserUpdated::class,
        ]);

        $this->action = new UpdateUserAction(
            app(\Modules\Identity\Contracts\UserRepositoryInterface::class)
        );
    }

    /** @test */
    public function test_updates_user_successfully()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        IdentityProfile::updateOrCreate(['user_id' => $user->id], ['status' => 'active']);

        $data = new UserData(
            name: 'Updated Name',
            firstName: 'Updated',
            lastName: 'Name',
            email: 'updated@example.com',
            phone: '0987654321',
            username: 'updatedusername',
            status: 'inactive'
        );

        $updatedUser = $this->action->execute($user, $data, 'web');

        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('updated@example.com', $updatedUser->email);
        $this->assertEquals('inactive', $updatedUser->status->value);

        Event::assertDispatched(UserUpdated::class, function ($event) use ($user) {
            return $event->id === $user->id;
        });
    }

    /** @test */
    public function test_filters_null_values_and_does_not_overwrite_existing_fields()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        IdentityProfile::updateOrCreate(['user_id' => $user->id], ['phone' => '123456789', 'status' => 'active']);

        // Create DTO with nulls for email and phone, meaning keep original
        $data = new UserData(
            name: 'Jane Doe',
            firstName: 'Jane',
            lastName: 'Doe',
            email: null,
            phone: null,
            username: null,
            status: null
        );

        $updatedUser = $this->action->execute($user, $data, 'web');

        $this->assertEquals('Jane Doe', $updatedUser->name);
        $this->assertEquals('john@example.com', $updatedUser->email); // Kept original
        $this->assertEquals('123456789', $updatedUser->phone);       // Kept original
        $this->assertEquals('active', $updatedUser->status->value);   // Kept original
    }
}
