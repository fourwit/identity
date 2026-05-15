<?php

namespace Modules\Identity\Tests\Feature;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Modules\Identity\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserWebTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function test_that_can_list_users()
    {
        User::factory()->count(5)->create();

        $response = $this->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Users');
    }

    /** @test */
    public function test_that_can_create_a_new_user()
    {
        $response = $this->post('/admin/users', [
            'name'       => 'John Doe',
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'active',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['name' => 'John Doe']);
    }

    /** @test */
    public function test_that_validates_required_fields()
    {
        $response = $this->post('/admin/users', ['name' => '']);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_that_can_update_a_user()
    {
        $user = User::factory()->create();

        $response = $this->put("/admin/users/{$user->id}", [
            'name'       => 'Updated Name',
            'first_name' => $user->first_name ?? 'Updated',
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'status'     => 'active',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    /** @test */
    public function test_that_can_delete_a_user()
    {
        $user = User::factory()->create();

        $response = $this->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function test_that_can_search_users()
    {
        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);

        $response = $this->get('/admin/users?search=John');

        $response->assertStatus(200);
        $response->assertSee('John Smith');
        $response->assertDontSee('Jane Doe');
    }

    /** @test */
    public function test_that_can_filter_by_status()
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);

        $response = $this->get('/admin/users?status=active');

        $response->assertStatus(200);
        $response->assertSee('active');
    }

    /** @test */
    public function test_that_creating_user_logs_activity()
    {
        $this->post('/admin/users', [
            'name'       => 'Web Log Test',
            'first_name' => 'Web',
            'last_name'  => 'Log',
            'email'      => 'weblog@example.com',
            'status'     => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Created new user: Web Log Test',
            'source'      => 'web',
        ]);
    }

    /** @test */
    public function test_that_deleting_user_logs_activity()
    {
        $user = User::factory()->create(['name' => 'Delete Web Test']);

        $this->delete("/admin/users/{$user->id}");

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Deleted user: Delete Web Test',
            'source'      => 'web',
        ]);
    }

    /** @test */
    public function test_that_can_view_activity_logs_page()
    {
        ActivityLog::factory()->count(3)->create();

        $response = $this->get('/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertSee('Activity Logs');
    }

    // ==================== CONFIG-DRIVEN TESTS ====================

    /** @test */
    public function test_that_uuid_is_generated_when_enabled_in_web()
    {
        config(['identity.enable_uuid' => true]);

        $user = User::factory()->create();

        $this->assertNotNull($user->uuid);
    }

    /** @test */
    public function test_that_uuid_is_not_generated_when_disabled_in_web()
    {
        config(['identity.enable_uuid' => false]);

        $user = User::factory()->create();

        $this->assertNull($user->uuid);
    }

    /** @test */
    public function test_that_username_appears_in_create_form_when_enabled()
    {
        config(['identity.enable_username' => true]);

        $response = $this->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertSee('username'); // Field should be visible
    }

    /** @test */
    public function test_that_username_appears_in_edit_form_when_enabled()
    {
        config(['identity.enable_username' => true]);

        $user = User::factory()->create(['username' => 'johndoe']);

        $response = $this->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('johndoe');
    }

    /** @test */
    public function test_that_username_does_not_appear_when_disabled_in_web()
    {
        config(['identity.enable_username' => false]);

        $response = $this->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertDontSee('username'); // Field should NOT be visible
    }

}