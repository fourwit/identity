<?php

namespace Modules\Identity\Tests\Feature\Api;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserApiConfigTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_uuid_is_generated_when_enabled()
    {
        config(['identity.features.uuid' => true]);
        $this->assertNotNull(User::factory()->create()->uuid);
    }

    public function test_that_uuid_is_not_generated_when_disabled()
    {
        config(['identity.features.uuid' => false]);
        $this->assertNull(User::factory()->create()->uuid);
    }

    public function test_that_uuid_appears_in_api_response_when_enabled()
    {
        config(['identity.features.uuid' => true]);
        $user = User::factory()->create();
        $this->getJson("/api/v1/users/{$user->id}")->assertJsonStructure(['data' => ['uuid']]);
    }

    public function test_that_username_appears_in_api_response_when_enabled()
    {
        config(['identity.features.username' => true]);
        $user = User::factory()->create(['username' => 'johndoe']);
        $this->getJson("/api/v1/users/{$user->id}")->assertJsonStructure(['data' => ['username']]);
    }

    public function test_that_username_does_not_appear_when_disabled()
    {
        config(['identity.features.username' => false]);
        $user = User::factory()->create(['username' => 'johndoe']);
        $this->getJson("/api/v1/users/{$user->id}")->assertJsonMissing(['username']);
    }
}
