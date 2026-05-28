<?php

namespace Modules\Identity\Tests\Feature\Web;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserWebConfigTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_uuid_is_generated_when_enabled_in_web()
    {
        config(['identity.features.uuid' => true]);
        $this->assertNotNull(User::factory()->create()->uuid);
    }

    public function test_that_uuid_is_not_generated_when_disabled_in_web()
    {
        config(['identity.features.uuid' => false]);
        $this->assertNull(User::factory()->create()->uuid);
    }

    public function test_that_username_appears_in_create_form_when_enabled()
    {
        config(['identity.features.username' => true]);
        $this->get('/admin/users/create')->assertStatus(200)->assertSee('username');
    }

    public function test_that_username_appears_in_edit_form_when_enabled()
    {
        config(['identity.features.username' => true]);
        $user = User::factory()->create(['username' => 'johndoe']);
        $this->get("/admin/users/{$user->id}/edit")->assertStatus(200)->assertSee('johndoe');
    }

    public function test_that_username_does_not_appear_when_disabled_in_web()
    {
        config(['identity.features.username' => false]);
        $this->get('/admin/users/create')->assertStatus(200)->assertDontSee('username');
    }
}
