<?php

$perPageOptions = array_values(array_filter(array_map(
    static fn ($value) => (int) trim((string) $value),
    explode(',', (string) env('IDENTITY_PER_PAGE_OPTIONS', '5,15,25,50,100,500,1000'))
), static fn (int $value) => $value > 0));

if (empty($perPageOptions)) {
    $perPageOptions = [5, 15, 25, 50, 100, 500, 1000];
}

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
        'account_web_routes' => env('IDENTITY_ACCOUNT_WEB_ROUTES', true),
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
        'per_page_options' => $perPageOptions,
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

    'views' => [
        /*
        |--------------------------------------------------------------------------
        | Layout Strategy
        |--------------------------------------------------------------------------
        | Default (standalone): identity::components.layouts.master
        | Breeze host apps:     identity::components.layouts.breeze-app
        | Generic host layouts: layouts.app / layouts.admin / custom layout
        | Embedded mode:        identity::components.layouts.blank
        */
        'layout' => env('IDENTITY_LAYOUT', 'identity::components.layouts.master'),
        // When false, title row + title-row action buttons are hidden on admin listing pages.
        'show_page_title_row' => env('IDENTITY_SHOW_PAGE_TITLE_ROW', true),
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

    'protection' => [
        'enabled' => env('IDENTITY_PROTECT_SUPER_ADMIN', true),
        'super_admin_uuid' => env('IDENTITY_SUPER_ADMIN_UUID', null),
        'super_admin_email' => env('IDENTITY_SUPER_ADMIN_EMAIL', 'admin@example.com'),
        'super_admin_name' => env('IDENTITY_SUPER_ADMIN_NAME', 'Super Admin'),
    ],

    'deletion' => [
        // safe: require SoftDeletes support; hard: allow hard delete fallback
        'strategy' => env('IDENTITY_DELETION_STRATEGY', 'safe'),
    ],

];
