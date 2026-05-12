<?php

namespace Modules\Identity\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DisabledRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // 1. Set the environment variable to false BEFORE booting the app
        putenv('USER_ENABLE_WEB_VIEWS=false');
        putenv('USER_ENABLE_API_ROUTES=false');

        parent::setUp();

    }

    protected function tearDown(): void
    {
        // 4. Clean up the environment variables so they don't break other tests
        putenv('USER_ENABLE_WEB_VIEWS');
        putenv('USER_ENABLE_API_ROUTES');

        parent::tearDown();
    }

    /** @test */
    public function test_web_routes_are_disabled()
    {
        $response = $this->get('/admin/users');
        $response->assertStatus(404);
    }

    /** @test */
    public function test_api_routes_are_disabled()
    {
        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(404);
    }
}