<?php

namespace Modules\Identity\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Identity\Events\UserCreated::class => [
            \Modules\Identity\Listeners\LogUserActivity::class,
        ],
        \Modules\Identity\Events\UserUpdated::class => [
            \Modules\Identity\Listeners\LogUserActivity::class,
        ],
        \Modules\Identity\Events\UserDeleted::class => [
            \Modules\Identity\Listeners\LogUserActivity::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
