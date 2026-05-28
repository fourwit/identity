<?php

namespace Modules\Identity\Tests\Feature\Web;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserWebCrudTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
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
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_that_can_search_users()
    {
        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);
        $this->get('/admin/users?search=John')->assertStatus(200)->assertSee('John Smith')->assertDontSee('Jane Doe');
    }

    public function test_that_can_filter_users_by_status()
    {
        User::factory()->create(['status' => 'active', 'name' => 'Active User']);
        User::factory()->create(['status' => 'inactive', 'name' => 'Inactive User']);
        $this->get('/admin/users?status=active')->assertStatus(200)->assertSee('Active User')->assertDontSee('Inactive User');
    }
}
