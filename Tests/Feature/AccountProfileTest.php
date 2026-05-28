<?php

namespace Modules\Identity\Tests\Feature;

use Tests\TestCase;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Support\BootstrapsIdentitySchema;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrapIdentitySchemaForTests();
        
        // Remove auth middleware for tests, but we will mock login.
        // Wait, for account profile tests, we WANT auth to work!
        // But our identity.php config dynamically removes auth when APP_ENV=testing.
        // Force config to include auth middleware for testing the Account routes.
        config(['identity.routes.middleware.web' => ['web', 'auth']]);
        config(['identity.routes.middleware.api' => ['api', 'auth']]);
        
        \Route::get('/login', function () { return 'login'; })->name('login');
    }

    /** @test */
    public function test_it_can_show_profile_for_authenticated_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('identity.account.profile.show'));

        $response->assertStatus(200);
        $response->assertViewIs('identity::account.profile');
    }



    /** @test */
    public function test_it_can_update_profile()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($user)->put(route('identity.account.profile.update'), [
            'name' => 'New Name',
            'first_name' => 'New',
            'last_name' => 'Name',
            'email' => $user->email, // keep same to avoid unique validation error
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    /** @test */
    public function test_it_can_update_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword'),
        ]);

        $response = $this->actingAs($user)->put(route('identity.account.password.update'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertTrue(\Hash::check('newpassword', $user->fresh()->password));
    }

    /** @test */
    public function test_it_can_remove_avatar()
    {
        $user = User::factory()->create([
            'avatar_id' => 123,
        ]);

        $response = $this->actingAs($user)->delete(route('identity.account.avatar.remove'));

        $response->assertRedirect();
        $this->assertNull($user->fresh()->avatar_id);
    }

    /** @test */
    public function test_it_can_view_verification_status()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('identity.account.verification.status'));

        $response->assertStatus(200);
        $response->assertViewIs('identity::account.verification');
    }

    /** @test */
    public function test_api_can_fetch_me_endpoint()
    {
        $user = User::factory()->create([
            'name' => 'API User',
        ]);

        // Mock json request
        $response = $this->actingAs($user)->json('GET', '/api/v1/account/me');

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'API User');
    }
}
