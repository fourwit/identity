<?php

namespace Modules\Identity\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Modules\Identity\Providers\RouteServiceProvider;

class DisabledRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'identity.views.layout' => 'identity::components.layouts.master',
            'identity.features.web_views' => false,
            'identity.features.api_routes' => false,
        ]);

        // Rebuild only the route table for this test run so disabled flags apply.
        $router = $this->app['router'];
        $router->setRoutes(new RouteCollection());
        (new RouteServiceProvider($this->app))->map();

    }

    protected function tearDown(): void
    {
        // Restore a fresh application so route mutations in this test class
        // do not leak into subsequent test classes.
        $this->refreshApplication();

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
