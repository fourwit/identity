<?php

namespace Modules\Identity\Tests\Feature\Web;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\IdentityProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserWebCrudTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_can_list_users()
    {
        User::factory()->count(5)->create();
        $this->get('/admin/users')->assertStatus(200)->assertSee('Users');
    }

    public function test_that_validates_required_fields()
    {
        $this->post('/admin/users', ['name' => ''])->assertSessionHasErrors(['name']);
    }

    public function test_that_can_update_a_user()
    {
        $user = User::factory()->create();
        $response = $this->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'first_name' => $user->first_name ?? 'Updated',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => 'active',
        ]);
        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    public function test_that_can_delete_a_user()
    {
        $user = User::factory()->create();
        $response = $this->delete("/admin/users/{$user->id}");
        $response->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_that_can_search_users()
    {
        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);
        $this->get('/admin/users?search=John')->assertStatus(200)->assertSee('John Smith')->assertDontSee('Jane Doe');
    }

    public function test_that_can_filter_users_by_status()
    {
        $active = User::factory()->create(['name' => 'Active User']);
        IdentityProfile::updateOrCreate(['user_id' => $active->id], ['status' => 'active']);
        $inactive = User::factory()->create(['name' => 'Inactive User']);
        IdentityProfile::updateOrCreate(['user_id' => $inactive->id], ['status' => 'inactive']);
        $this->get('/admin/users?status=active')->assertStatus(200)->assertSee('Active User')->assertDontSee('Inactive User');
    }
}
