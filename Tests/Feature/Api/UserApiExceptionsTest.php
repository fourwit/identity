<?php

namespace Modules\Identity\Tests\Feature\Api;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\IdentityProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserApiExceptionsTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_returns_404_when_user_not_found()
    {
        $this->getJson('/api/v1/users/99999')->assertStatus(404)->assertJsonStructure(['message']);
    }

    public function test_that_cannot_delete_main_admin_user()
    {
        $admin = User::factory()->create(['id' => 1, 'name' => 'Super Admin']);
        IdentityProfile::updateOrCreate(['user_id' => $admin->id], ['status' => 'active']);
        $this->deleteJson('/api/v1/users/1')
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Cannot delete the main admin user']);
    }

    public function test_that_cannot_create_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'status' => 'active',
            'password' => 'secret1234',
        ]);

        $this->assertTrue(in_array($response->status(), [422, 409]), 'Expected status 422 or 409, got: '.$response->status());
    }
}
