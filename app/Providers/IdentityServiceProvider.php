<?php

namespace Modules\Identity\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Identity\Models\User;
use Modules\Identity\Policies\UserPolicy;
use Modules\Identity\Observers\UserObserver;

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
    // protected array $commands = [];

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
        $this->mergeConfigFrom(
            module_path('Identity', 'config/identity.php'), 'identity'
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
        });

        // Bind Identity Manager (for Facade)
        $this->app->singleton('identity', function ($app) {
            return new \Modules\Identity\Services\IdentityManager(
                $app->make(\Modules\Identity\Contracts\UserRepositoryInterface::class)
            );
        });

        // 2. Call parent register to handle the $providers array
        parent::register();
    }

    public function boot(): void
    {
        // Register View Namespace (Fix for "No hint path defined for [user]")
        $this->loadViewsFrom(module_path('Identity', 'resources/views'), 'identity');

        $this->mergeConfigFrom(
            module_path('Identity', 'config/identity.php'), 'identity'
        );
        
        // Register UserObserver
        User::observe(UserObserver::class);
        
        // dd(User::getEventDispatcher());

        // Register User Policy
        Gate::policy(User::class, UserPolicy::class);

       
    }

    
}
