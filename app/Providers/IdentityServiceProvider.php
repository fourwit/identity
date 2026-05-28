<?php

namespace Modules\Identity\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Identity\Policies\UserPolicy;
use Modules\Identity\Observers\UserObserver;
use Modules\Identity\Support\IdentityConfig;

class IdentityServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Identity';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'identity';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        \Modules\Identity\Console\Commands\IdentityDoctorCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

     /**
     * Register is called FIRST
     */
    public function register(): void
    {
        $moduleBasePath = dirname(__DIR__, 2);

        $this->mergeConfigFrom(
            $moduleBasePath.'/config/identity.php', 'identity'
        );

        // Bind Repository
        $this->app->bind(
            \Modules\Identity\Contracts\UserRepositoryInterface::class,
            \Modules\Identity\Repositories\UserRepository::class
        );

         // Register global exception handler for this module
        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
        ->renderable(function (\Throwable $e, $request) {
            if ($e instanceof \Modules\Identity\Exceptions\ModuleException) {
                return \Modules\Identity\Exceptions\ModuleExceptionHandler::handle($e, $request);
            }

            if ($e instanceof ModelNotFoundException && ($request->is('api/*') || $request->expectsJson())) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
        });

        // Bind Identity Manager (for Facade)
        $this->app->singleton('identity', function ($app) {
            return new \Modules\Identity\Services\IdentityManager(
                $app->make(\Modules\Identity\Contracts\UserRepositoryInterface::class)
            );
        });

        // 2. Call parent register to handle the $providers array
        parent::register();

        $this->commands($this->commands);
    }

    public function boot(): void
    {
        $moduleBasePath = dirname(__DIR__, 2);

        // Register View Namespace (Fix for "No hint path defined for [user]")
        $this->loadViewsFrom($moduleBasePath.'/resources/views', 'identity');

        $this->mergeConfigFrom(
            $moduleBasePath.'/config/identity.php', 'identity'
        );

        foreach (glob($moduleBasePath.'/database/migrations/*.php') as $migrationFile) {
            if (!IdentityConfig::isOwnedMode() && str_contains($migrationFile, 'create_users_table')) {
                continue;
            }

            $this->loadMigrationsFrom($migrationFile);
        }
        
        if (IdentityConfig::isOwnedMode()) {
            IdentityConfig::userModelClass()::observe(UserObserver::class);
        }
        
        // dd(User::getEventDispatcher());

        // Register User Policy
        Gate::policy(IdentityConfig::userModelClass(), UserPolicy::class);

       
    }

    
}
