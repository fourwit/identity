<?php

namespace Modules\Identity\Tests\Feature\Web;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class UserWebExceptionsTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
    }

    public function test_that_returns_404_when_user_not_found_web()
    {
        $this->get('/admin/users/99999/edit')->assertStatus(404);
    }

    public function test_that_cannot_delete_main_admin_user_web()
    {
        User::factory()->create(['id' => 1, 'name' => 'Super Admin', 'status' => 'active']);
        $response = $this->delete('/admin/users/1');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete the main admin user');
    }

    public function test_that_cannot_create_duplicate_email_web()
    {
        User::factory()->create(['email' => 'test@example.com']);
        $response = $this->post('/admin/users', [
            'name' => 'Test User',
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'status' => 'active',
            'password' => 'secret1234',
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }
}
