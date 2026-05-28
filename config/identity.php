<?php

return [
    'mode' => env('IDENTITY_MODE', 'shared'),

    'tables' => [
        'users' => env('IDENTITY_USERS_TABLE', 'users'),
        'profiles' => env('IDENTITY_PROFILES_TABLE', 'identity_profiles'),
        'activity_logs' => env('IDENTITY_ACTIVITY_LOGS_TABLE', 'activity_logs'),
    ],

    'models' => [
        'user' => env('IDENTITY_USER_MODEL', Modules\Identity\Models\User::class),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    | Toggle features ON/OFF. Use environment variables for production.
    */
    'features' => [
        'uuid' => env('IDENTITY_ENABLE_UUID', false),
        'username' => env('IDENTITY_ENABLE_USERNAME', true),
        'web_views' => env('USER_ENABLE_WEB_VIEWS', true),
        'api_routes' => env('USER_ENABLE_API_ROUTES', true),
        'two_factor' => env('IDENTITY_ENABLE_TWO_FACTOR', false),
        'login_history' => env('IDENTITY_ENABLE_LOGIN_HISTORY', true),
        'device_management' => env('IDENTITY_ENABLE_DEVICE_MANAGEMENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Requirements & Defaults
    |--------------------------------------------------------------------------
    */
    'user' => [
        'require_email' => env('IDENTITY_REQUIRE_EMAIL', true),
        'require_phone' => env('IDENTITY_REQUIRE_PHONE', false),
        'require_username' => env('IDENTITY_REQUIRE_USERNAME', false),
        'allow_phone_only_accounts' => env('IDENTITY_ALLOW_PHONE_ONLY', false),
        'default_status' => env('IDENTITY_DEFAULT_STATUS', 'active'),
        'per_page' => env('IDENTITY_PER_PAGE', 5),
        'searchable_fields' => ['name', 'email', 'phone', 'username'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar Settings
    |--------------------------------------------------------------------------
    */
    'avatar' => [
        'disk' => env('IDENTITY_AVATAR_DISK', 'public'),
        'max_size_kb' => env('IDENTITY_AVATAR_MAX_SIZE_KB', 2048),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'timezone' => env('IDENTITY_DEFAULT_TIMEZONE', 'UTC'),
        'locale' => env('IDENTITY_DEFAULT_LOCALE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'rate_limit' => env('IDENTITY_API_RATE_LIMIT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Options
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        'active',
        'inactive',
        'suspended',
        'pending',
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    | Customize the module branding for your project.
    */
    'branding' => [
        'name' => env('IDENTITY_BRANDING_NAME', 'Fourwit'),
        'admin_email' => env('IDENTITY_BRANDING_EMAIL', 'admin@fourwit.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    | Customize route prefixes and middleware for your project.
    */
    'routes' => [
        'api_prefix' => env('IDENTITY_API_PREFIX', 'api/v1'),
        'admin_prefix' => env('IDENTITY_ADMIN_PREFIX', 'admin'),
        'middleware' => [
            'web' => env('APP_ENV') === 'testing' ? ['web'] : ['web', 'auth'],
            'api' => env('APP_ENV') === 'testing' ? ['api'] : ['api', 'auth:sanctum'],
            'admin' => env('APP_ENV') === 'testing' ? ['web'] : ['web', 'auth'],
        ],
    ],

    'auth' => [
        'guards' => [
            'web' => env('IDENTITY_AUTH_GUARD_WEB', 'web'),
            'api' => env('IDENTITY_AUTH_GUARD_API', 'sanctum'),
            'admin' => env('IDENTITY_AUTH_GUARD_ADMIN', 'web'),
        ],
        'providers' => [
            'users' => env('IDENTITY_AUTH_PROVIDER_USERS', 'users'),
        ],
    ],

];
