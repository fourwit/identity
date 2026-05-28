<?php

namespace Modules\Identity\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Identity\Support\IdentityConfig;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Identity';
    protected string $moduleBasePath;

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        $this->moduleBasePath = dirname(__DIR__, 2);
        Route::model('user', IdentityConfig::userModelClass());
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        if (!config('identity.features.web_views', true)) {
            return; // Web routes disabled
        }

        Route::group([], $this->moduleBasePath.'/routes/web.php');
        
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        if (!config('identity.features.api_routes', true)) {
            return; // API routes disabled
        }
        
        Route::prefix(config('identity.routes.api_prefix', 'api/v1'))
           ->group($this->moduleBasePath.'/routes/api.php');
        
    }
}
