<?php

namespace Modules\Identity\Tests\Feature\Web;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\IdentityProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserWebConfigTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_uuid_is_generated_when_enabled_in_web()
    {
        config(['identity.features.uuid' => true]);
        $user = User::factory()->create();
        IdentityProfile::updateOrCreate(['user_id' => $user->id], ['uuid' => (string) \Illuminate\Support\Str::uuid()]);
        $this->assertNotNull(app(\Modules\Identity\Contracts\UserRepositoryInterface::class)->findByIdOrFail($user->id)->uuid);
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
        $user = User::factory()->create();
        IdentityProfile::updateOrCreate(['user_id' => $user->id], ['username' => 'johndoe']);
        $this->get("/admin/users/{$user->id}/edit")->assertStatus(200)->assertSee('johndoe');
    }

    public function test_that_username_does_not_appear_when_disabled_in_web()
    {
        config(['identity.features.username' => false]);
        $this->get('/admin/users/create')->assertStatus(200)->assertDontSee('username');
    }
}
