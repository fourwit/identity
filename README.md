# Fourwit Identity Module

A production-ready, reusable user identity management module for Laravel 13+. Provides comprehensive user management, account self-service, profile management, activity logging, and flexible deployment modes.

## Overview

The Identity module is a complete user management system that can be integrated into any Laravel application. It handles:
- User CRUD operations (admin interface + REST API)
- User account self-service (profile updates, password changes, account deletion)
- User status management (Active, Inactive, Suspended, Pending)
- Activity logging for audit trails
- Two deployment modes: **Shared** (use existing users table) and **Owned** (dedicated users table)
- Web UI + REST API for all operations
- Configurable layouts for seamless host app integration

---

## Features

### User Management
- Create, read, update, delete users
- Multiple user status states with validation
- Email verification tracking
- Optional username and UUID support
- Phone number support (optional)
- User search and filtering
- Pagination with configurable limits
- Avatar support (ready for Media module integration)

### Account Self-Service
- Users can view/update their own profile
- Password change with current password verification
- Account deletion with confirmation
- Email change with automatic verification reset
- Avatar removal

### Admin Interface
- Full user management dashboard
- Activity log viewer
- User search and status filtering
- Bulk user operations
- Responsive web UI built with Blade templates

### API & Integration
- RESTful API endpoints for user management
- Account API for self-service operations
- Rate limiting (configurable)
- Activity log tracking for all identity operations
- Event system for extensibility

### Flexibility
- **Shared Mode**: Users table managed by host app, Identity adds optional columns
- **Owned Mode**: Dedicated Identity users table for standalone identity management
- Configurable table names
- Swappable authentication guards
- Custom layout support for host app branding
- Extendable model relationships

---

## Installation

### From GitHub
```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:fourwit/identity.git"
    }
  ],
  "require": {
    "fourwit/identity": "^1.0"
  }
}
```

Then run:
```bash
composer install
php artisan migrate
```

### Local Development
```json
{
  "repositories": [
    {
      "type": "path",
      "url": "/path/to/fourwit-identity",
      "options": {"symlink": true}
    }
  ],
  "require": {
    "fourwit/identity": "@dev"
  }
}
```

Then:
```bash
composer install
```

---

## Quick Start

### 1. Set Environment Variables

Choose your deployment mode and add to `.env`:

```bash
# Mode: 'shared' (use host app's users table) or 'owned' (dedicated users table)
IDENTITY_MODE=shared

# User model (only needed if custom)
IDENTITY_USER_MODEL=App\Models\User

# Table names
IDENTITY_USERS_TABLE=users
IDENTITY_PROFILES_TABLE=identity_profiles
IDENTITY_ACTIVITY_LOGS_TABLE=activity_logs

# Features
IDENTITY_ENABLE_UUID=false
IDENTITY_ENABLE_USERNAME=true
IDENTITY_ENABLE_LOGIN_HISTORY=true
IDENTITY_ENABLE_DEVICE_MANAGEMENT=true

# User defaults
IDENTITY_REQUIRE_EMAIL=true
IDENTITY_REQUIRE_PHONE=false
IDENTITY_DEFAULT_STATUS=active
IDENTITY_PER_PAGE=15

# Layout (choose: 'identity::components.layouts.master', 'breeze-app', or 'layouts.app')
IDENTITY_LAYOUT=identity::components.layouts.master

# API
IDENTITY_API_RATE_LIMIT=60
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Verify Installation

```bash
php artisan identity:doctor
```

This validates your configuration and environment setup.

### 4. Optional: Sync Existing Users

If you already have users and are enabling Identity:

```bash
php artisan identity:sync-profiles
```

---

## Usage in Host Application

### Using the Identity Facade

The easiest way to interact with the Identity module is through the `Identity` facade:

```php
use Modules\Identity\Facades\Identity;

// Find users
$user = Identity::findUserById(1);
$user = Identity::findUserByEmail('user@example.com');
$user = Identity::findUserByUuid('550e8400-e29b-41d4-a716-446655440000');

// Query users
$allUsers = Identity::allUsers();
$users = Identity::allUsers(perPage: 25);

// Search users
$results = Identity::searchUsers('john', 'active', 15);
$activeUsers = Identity::activeUsers(perPage: 50);

// Create user
$user = Identity::createUser([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'securepassword',
    'status' => 'active',
]);

// Update user
Identity::updateUser($user, [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
]);

// Update user password
Identity::updateUserPassword($user, 'currentPassword', 'newPassword');

// Update account profile (triggers ProfileUpdated event)
Identity::updateAccountProfile($user, [
    'name' => 'Jane Smith',
    'email' => 'jane.smith@example.com',
    'first_name' => 'Jane',
    'last_name' => 'Smith',
]);

// Delete user
Identity::deleteUser($user);

// Delete own account (requires current password)
Identity::deleteOwnAccount($user, 'currentPassword');

// Get user model class
$modelClass = Identity::userModel();

// Get activity logs count
$count = Identity::activityLogsCount();

// Query builder access
$query = Identity::userQuery();
$suspended = $query->where('status', 'suspended')->get();
```

### User Model

Access the User model directly:

```php
use Modules\Identity\Models\User;
use Modules\Identity\Enums\UserStatus;

// Create
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
    'status' => UserStatus::ACTIVE,
]);

// Find
$user = User::find(1);
$user = User::where('email', 'john@example.com')->first();

// Update
$user->update([
    'name' => 'Jane Doe',
    'status' => UserStatus::SUSPENDED,
]);

// Access profile relationship
$profile = $user->identityProfile;

// Get full name
$fullName = $user->full_name; // Uses first_name + last_name if available, falls back to name
```

### User Status Enum

```php
use Modules\Identity\Enums\UserStatus;

// Get status
$status = UserStatus::ACTIVE;

// Check if active
if ($status->isActive()) {
    // ...
}

// Get label for display
echo $status->label(); // "Active"

// Get badge color
$color = $status->badgeColor(); // "success", "danger", "warning", etc.

// Get all values
$allStatuses = UserStatus::values(); // ['active', 'inactive', 'suspended', 'pending']

// Validation rule
$rule = UserStatus::forValidation(); // "in:active,inactive,suspended,pending"
```

---

## Web Routes

All routes are protected by authentication middleware (configurable).

### Admin Routes
Prefix: `/admin` (configurable via `IDENTITY_ADMIN_PREFIX`)

| Method | Route | Controller | Description |
|--------|-------|-----------|-------------|
| GET | `/admin/users` | UserController@index | List all users |
| GET | `/admin/users/create` | UserController@create | Show user creation form |
| POST | `/admin/users` | UserController@store | Create user |
| GET | `/admin/users/{id}` | UserController@show | View user details |
| GET | `/admin/users/{id}/edit` | UserController@edit | Show user edit form |
| PUT | `/admin/users/{id}` | UserController@update | Update user |
| DELETE | `/admin/users/{id}` | UserController@destroy | Delete user |
| GET | `/admin/activity-logs` | ActivityLogController@index | View activity logs |

### Account Self-Service Routes
Prefix: `/account`

| Method | Route | Controller | Description |
|--------|-------|-----------|-------------|
| GET | `/account/profile` | ProfileController@show | View own profile |
| PUT | `/account/profile` | ProfileController@update | Update own profile |
| PUT | `/account/password` | PasswordController@update | Change password |
| DELETE | `/account/delete-account` | ProfileController@destroy | Delete own account |
| DELETE | `/account/avatar` | ProfileController@removeAvatar | Remove avatar |
| GET | `/account/verification-status` | VerificationController@status | Check email verification |

---

## REST API Endpoints

All endpoints are JSON, require authentication, and respect rate limiting.

### User Management API
Prefix: `/api/v1` (configurable via `IDENTITY_API_PREFIX`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/users` | List users (paginated) |
| POST | `/api/v1/users` | Create user |
| GET | `/api/v1/users/{id}` | Get user |
| PUT | `/api/v1/users/{id}` | Update user |
| DELETE | `/api/v1/users/{id}` | Delete user |
| GET | `/api/v1/activity-logs` | List activity logs |

### Account Self-Service API
Prefix: `/api/v1/account`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/account/me` | Get authenticated user profile |
| PUT | `/api/v1/account/profile` | Update own profile |
| PUT | `/api/v1/account/password` | Change own password |
| DELETE | `/api/v1/account/avatar` | Remove own avatar |
| GET | `/api/v1/account/verification-status` | Check email verification status |

### API Examples

**List users:**
```bash
curl -X GET "http://localhost:8000/api/v1/users?page=1&per_page=15" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Create user:**
```bash
curl -X POST "http://localhost:8000/api/v1/users" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword",
    "status": "active"
  }'
```

**Get current user:**
```bash
curl -X GET "http://localhost:8000/api/v1/account/me" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Update own profile:**
```bash
curl -X PUT "http://localhost:8000/api/v1/account/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "first_name": "Jane",
    "last_name": "Doe"
  }'
```

---

## Events

The module fires events for key operations. Listen to these in your application:

```php
// app/Providers/EventServiceProvider.php
use Modules\Identity\Events\UserCreated;
use Modules\Identity\Events\UserUpdated;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\ProfileUpdated;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Events\AccountDeleted;
use Modules\Identity\Events\UserActivated;
use Modules\Identity\Events\UserSuspended;

protected $listen = [
    UserCreated::class => [
        'App\Listeners\SendWelcomeEmail',
    ],
    ProfileUpdated::class => [
        'App\Listeners\LogProfileChange',
    ],
    UserPasswordUpdated::class => [
        'App\Listeners\LogPasswordChange',
    ],
    AccountDeleted::class => [
        'App\Listeners\AnonymizeUserData',
    ],
];
```

---

## Activity Logging

All user management actions are automatically logged. Access logs via:

```php
use Modules\Identity\Models\ActivityLog;

// Get all logs
$logs = ActivityLog::latest()->paginate();

// Filter by user
$userLogs = ActivityLog::where('user_id', 1)->latest()->get();

// Filter by action
$createdLogs = ActivityLog::where('action', 'created')->get();

// Get count
$count = ActivityLog::count();
```

---

## Configuration

All configuration options can be set via environment variables. Create/update entries in `.env`:

### Core Settings
```bash
IDENTITY_MODE=shared                          # 'shared' or 'owned'
IDENTITY_USER_MODEL=App\Models\User           # Custom user model (optional)
```

### Table Names
```bash
IDENTITY_USERS_TABLE=users
IDENTITY_PROFILES_TABLE=identity_profiles
IDENTITY_ACTIVITY_LOGS_TABLE=activity_logs
```

### Features
```bash
IDENTITY_ENABLE_UUID=false                    # UUID support
IDENTITY_ENABLE_USERNAME=true                 # Username field
IDENTITY_ENABLE_LOGIN_HISTORY=true            # Track login history
IDENTITY_ENABLE_DEVICE_MANAGEMENT=true        # Device tracking
IDENTITY_ACCOUNT_WEB_ROUTES=true              # Enable account web routes
USER_ENABLE_WEB_VIEWS=true                    # Enable web UI
USER_ENABLE_API_ROUTES=true                   # Enable API routes
```

### User Requirements
```bash
IDENTITY_REQUIRE_EMAIL=true                   # Email is required
IDENTITY_REQUIRE_PHONE=false                  # Phone is required
IDENTITY_REQUIRE_USERNAME=false               # Username is required
IDENTITY_ALLOW_PHONE_ONLY=false               # Allow accounts without email
IDENTITY_DEFAULT_STATUS=active                # Default user status
IDENTITY_PER_PAGE=15                          # Default pagination
IDENTITY_PER_PAGE_OPTIONS=5,15,25,50,100     # Available page limits
```

### Avatar Settings
```bash
IDENTITY_AVATAR_DISK=public                   # Storage disk
IDENTITY_AVATAR_MAX_SIZE_KB=2048              # Max size in KB
```

### API Settings
```bash
IDENTITY_API_RATE_LIMIT=60                    # Requests per minute
IDENTITY_API_PREFIX=api/v1                    # API route prefix
```

### Routing
```bash
IDENTITY_ADMIN_PREFIX=admin                   # Admin route prefix
IDENTITY_AUTH_GUARD_WEB=web                   # Web guard
IDENTITY_AUTH_GUARD_API=sanctum               # API guard
IDENTITY_AUTH_GUARD_ADMIN=web                 # Admin guard
```

### UI/Layout
```bash
IDENTITY_LAYOUT=identity::components.layouts.master  # Layout view
IDENTITY_SHOW_PAGE_TITLE_ROW=true             # Show page titles
```

### Branding
```bash
IDENTITY_BRANDING_NAME=Fourwit
IDENTITY_BRANDING_EMAIL=admin@fourwit.com
```

### Timezone & Locale
```bash
IDENTITY_DEFAULT_TIMEZONE=UTC
IDENTITY_DEFAULT_LOCALE=en
```

---

## Integration with Host App

### Step 1: Install the Module
Follow the Installation section above.

### Step 2: Configure Environment
Set up `.env` with your preferences (mode, table names, features).

### Step 3: Run Migrations
```bash
php artisan migrate
```

### Step 4: Custom Layout (Optional)
To match your app's branding, create a custom layout:

```bash
# Create a layout in your app
touch resources/views/layouts/identity-app.blade.php
```

Then set in `.env`:
```bash
IDENTITY_LAYOUT=layouts.identity-app
```

### Step 5: Add Authentication
Ensure your host app has authentication (Breeze, Fortify, etc.). Identity uses your existing guards.

### Step 6: Create Admin Middleware (Optional)
If needed, create an admin-only middleware:

```php
// app/Http/Middleware/IsAdmin.php
namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403);
        }
        return $next($request);
    }
}
```

### Step 7: Publish Configuration (Optional)
```bash
php artisan vendor:publish --provider="Modules\Identity\Providers\IdentityServiceProvider" --tag="config"
```

---

## Examples

### Example 1: Create a User Programmatically
```php
use Modules\Identity\Facades\Identity;

$user = Identity::createUser([
    'name' => 'Alice Smith',
    'email' => 'alice@example.com',
    'password' => 'tempPassword123',
    'status' => 'active',
]);

echo $user->id; // 1
```

### Example 2: Suspend a User
```php
use Modules\Identity\Facades\Identity;
use Modules\Identity\Enums\UserStatus;

$user = Identity::findUserById(5);
Identity::updateUser($user, [
    'status' => UserStatus::SUSPENDED->value,
]);
```

### Example 3: Search and List Users
```php
use Modules\Identity\Facades\Identity;

// Get first 25 active users
$activeUsers = Identity::activeUsers(perPage: 25);

// Search for users
$results = Identity::searchUsers('john', 'active', 20);

foreach ($results as $user) {
    echo $user->name . ' - ' . $user->email;
}
```

### Example 4: Handle User Deletion Event
```php
// app/Listeners/LogUserDeletion.php
namespace App\Listeners;

use Modules\Identity\Events\UserDeleted;

class LogUserDeletion
{
    public function handle(UserDeleted $event): void
    {
        \Log::warning("User deleted: {$event->id} ({$event->email})");
    }
}
```

### Example 5: Custom User Model
Create a custom User model extending Identity's User:

```php
// app/Models/User.php
namespace App\Models;

use Modules\Identity\Models\User as IdentityUser;

class User extends IdentityUser
{
    protected $fillable = [
        ...parent::$fillable,
        'is_admin',
        'department',
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}

// Update .env
IDENTITY_USER_MODEL=App\Models\User
```

### Example 6: API Usage from Another Service
```php
// In a separate service/app
$response = \Illuminate\Support\Facades\Http::withToken('YOUR_API_TOKEN')
    ->post('http://main-app.local/api/v1/users', [
        'name' => 'Bob Wilson',
        'email' => 'bob@example.com',
        'password' => 'securePass123',
        'status' => 'active',
    ]);

$user = $response->json();
echo $user['id']; // New user ID
```

---

## Database Schema

### users table (created/extended by module)
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'active',
    first_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    username VARCHAR(255) UNIQUE NULL,
    uuid CHAR(36) UNIQUE NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### identity_profiles table
```sql
CREATE TABLE identity_profiles (
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNIQUE FOREIGN KEY,
    avatar_path VARCHAR(255) NULL,
    locale VARCHAR(5) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### activity_logs table
```sql
CREATE TABLE activity_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY,
    action VARCHAR(255),
    description TEXT NULL,
    changes JSON NULL,
    source VARCHAR(50) DEFAULT 'web',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## What's NOT Included

The following are **outside the scope** of this module:
- **Authentication/Login**: Use Breeze, Fortify, or Sanctum
- **Authorization/Roles**: Use Spatie's laravel-permission
- **MFA/OTP**: Third-party packages
- **Media Upload**: Future Media module will handle this
- **Email Verification Flow**: Your app controls this
- **Password Reset**: Standard Laravel functionality

---

## Deployment Modes

### Shared Mode (Default)
Uses the host app's existing `users` table. Identity module adds optional columns as needed.

**Use when:** Your host app already has a users table and user authentication system.

```bash
IDENTITY_MODE=shared
```

### Owned Mode
Creates a dedicated `users` table for Identity module. Suitable for standalone identity services.

**Use when:** You need a dedicated identity service separate from the host app.

```bash
IDENTITY_MODE=owned
```

---

## Troubleshooting

### Routes not registering?
- Check `.env` has `IDENTITY_LAYOUT` set correctly
- Ensure `IDENTITY_ACCOUNT_WEB_ROUTES=true`
- Run `php artisan route:list` to verify

### Authentication issues?
- Verify your auth guard is set correctly (`IDENTITY_AUTH_GUARD_WEB`, etc.)
- Ensure middleware matches your app's auth setup

### Activity logs not recording?
- Check listeners are registered in EventServiceProvider
- Verify activity_logs table exists: `php artisan migrate`

### Permission denied on admin routes?
- Add authentication middleware to routes
- Create an admin role/policy in your app

---

## Support & Contributing

For issues, feature requests, or contributions, visit:
- **GitHub**: https://github.com/fourwit/identity
- **Issues**: https://github.com/fourwit/identity/issues
- If `IDENTITY_DELETION_STRATEGY=safe`, deleting a user will soft-delete both the host `users` record and the matching `identity_profiles` record.

---

## 4. Owned vs Shared Mode

## `IDENTITY_MODE=owned`
Use when module owns user table/schema.

- Module creates a Laravel-default `users` table only:
  - `id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`
- Identity-specific fields are stored in `identity_profiles`.
- Suitable for greenfield apps or module-first projects.

## `IDENTITY_MODE=shared`
Use when host app already has `users` table/model.

- Core fields stay on host `users` table (for example: `name`, `email`, `password`).
- Module-specific fields live in `identity_profiles`.
- Best for integrating into existing projects.

### Shared mode reminder
If existing users were created outside module actions (for example via Breeze/Tinker), run:
```bash
php artisan identity:sync-profiles
```

---

## 5. Web vs API Usage

## Web
- Admin web pages: `/admin/users`, `/admin/activity-logs`
- Account web pages: `/account/profile`, `/account/password`, `/account/verification-status`

If host app already has profile/account pages (Breeze/Jetstream/custom), disable module account web routes:
```env
IDENTITY_ACCOUNT_WEB_ROUTES=false
```

## API
- Admin and account API endpoints remain available via module config.
- Useful for SPA/mobile/API-first projects.

---

## 5.1 Public PHP API

Use the `Identity` facade from your host app. It keeps repositories hidden and routes all work through the module’s internal manager/actions layer.

### Import
```php
use Modules\Identity\Facades\Identity;
```

### User management
```php
Identity::createUser(array $data);
Identity::updateUser($user, array $data);
Identity::deleteUser($user);
```

### User lookup and listing
```php
Identity::findUserById($id);
Identity::findUserByEmail($email);
Identity::findUserByUuid($uuid);
Identity::userModel();
Identity::userQuery();
Identity::allUsers();
Identity::searchUsers(?string $term = null, ?string $status = null, ?int $perPage = null);
Identity::activeUsers();
```

### Account/profile actions
```php
Identity::updateAccountProfile($user, array $data);
Identity::updateUserPassword($user, string $currentPassword, string $newPassword);
Identity::deleteOwnAccount($user, string $currentPassword);
```

### Dashboard/support helpers
```php
Identity::activityLogsCount();
```

### Example usage in a host controller
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Identity\Facades\Identity;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $users = Identity::searchUsers(
            term: $request->get('search'),
            status: $request->get('status'),
            perPage: $request->get('per_page')
        );

        $activeUsersCount = Identity::activeUsers()?->total() ?? 0;
        $activityLogsCount = Identity::activityLogsCount();

        return view('dashboard', compact('users', 'activeUsersCount', 'activityLogsCount'));
    }
}
```

Notes:
- `updateAccountProfile()` is the preferred entrypoint for Breeze/Jetstream/profile-page integration.
- `updateUser()` remains the generic update method for admin or internal use.
- The module keeps repository access internal; host apps should consume only the facade.
- `userModel()` returns the configured user model class string.
- `userQuery()` returns a configured query builder for the user model.

---

## 6. Layout Integration (Web)

Identity views are layout-configurable:

```env
IDENTITY_LAYOUT=identity::components.layouts.master
```

Common options:
- `identity::components.layouts.master` (module standalone default)
- `identity::components.layouts.breeze-app` (Breeze adapter)
- `identity::components.layouts.section` (generic section adapter)
- `identity::components.layouts.blank` (embedded/content-only)
- `layouts.app` (host layout, if available)
- `layouts.identity` (Jetstream host bridge layout, recommended for Jetstream apps)
- `layouts.admin` (host layout, if available)

### Published view overrides
Publish module views:
```bash
php artisan vendor:publish --provider="Modules\\Identity\\Providers\\IdentityServiceProvider" --tag=views
```

Published path:
- `resources/views/vendor/identity/...`

Resolution order is host overrides first, then module fallback.

### Tailwind note for host apps
If you use package views directly (without publishing), include the package Blade paths in Tailwind `content`.

Example `tailwind.config.js`:
```js
content: [
  './app/**/*.php',
  './resources/**/*.blade.php',
  './resources/**/*.js',
  './resources/**/*.vue',
  './vendor/fourwit/identity/resources/views/**/*.blade.php',
  './vendor/identity/resources/views/**/*.blade.php',
]
```

If you keep using the package source during local development, also include the package path itself:
```js
content: [
  './app/**/*.php',
  './resources/**/*.blade.php',
  '/var/www/html/fourwit-packages/fourwit-identity/resources/views/**/*.blade.php',
]
```

Important for host apps:
- If the module is mounted from a local path during development, the host app’s Tailwind build must scan the package source path.
- If you only scan `resources/views` inside the host app, Tailwind will not generate utilities used by the module’s admin/account pages.

If you want full visual customization, publish views and edit the published copies.

### Activity log search columns
You can control which activity-log columns are searchable from the host app:
```env
IDENTITY_ACTIVITY_LOG_SEARCHABLE_FIELDS=description,source,ip_address,performed_by
```
Use `performed_by` to search by the causer name, including soft-deleted users.

### Publish options (recommended)
You can publish specific assets instead of relying only on `.env`:

```bash
# Publish views
php artisan vendor:publish --provider="Modules\\Identity\\Providers\\IdentityServiceProvider" --tag=views

# Publish config (standard Laravel tag)
php artisan vendor:publish --provider="Modules\\Identity\\Providers\\IdentityServiceProvider" --tag=config

# Publish config (module-specific tag)
php artisan vendor:publish --provider="Modules\\Identity\\Providers\\IdentityServiceProvider" --tag=identity-config

# Publish views (module-specific tag)
php artisan vendor:publish --provider="Modules\\Identity\\Providers\\IdentityServiceProvider" --tag=identity-views
```

---

## 7. Environment Variables (Full List)

Use this as reference in host `.env`.

```env
# -----------------------------
# Identity: Mode
# -----------------------------
# owned  = module owns users schema
# shared = host app users table/model is reused
IDENTITY_MODE=shared

# -----------------------------
# Identity: User model/table mapping
# -----------------------------
# In shared mode usually App\\Models\\User
# In owned mode usually Modules\\Identity\\Models\\User
IDENTITY_USER_MODEL=App\\Models\\User

# Host users table (shared) or owned users table (owned)
IDENTITY_USERS_TABLE=users

# Table for module-specific fields in shared mode
IDENTITY_PROFILES_TABLE=identity_profiles

# Activity log table
IDENTITY_ACTIVITY_LOGS_TABLE=activity_logs

# -----------------------------
# Identity: Layout
# -----------------------------
# Examples:
# identity::components.layouts.master
# identity::components.layouts.breeze-app
# layouts.app
IDENTITY_LAYOUT=identity::components.layouts.master

# -----------------------------
# Identity: Route/feature toggles
# -----------------------------
IDENTITY_WEB_VIEWS=true
IDENTITY_API_ROUTES=true

# Disable if host already has profile/account web pages
IDENTITY_ACCOUNT_WEB_ROUTES=true

# -----------------------------
# Identity: Route prefixes
# -----------------------------
IDENTITY_ADMIN_PREFIX=admin
IDENTITY_API_PREFIX=api/v1

# -----------------------------
# Identity: Auth guards/providers compatibility
# -----------------------------
IDENTITY_AUTH_GUARD_WEB=web
IDENTITY_AUTH_GUARD_API=sanctum
IDENTITY_AUTH_GUARD_ADMIN=web
IDENTITY_AUTH_PROVIDER_USERS=users

# -----------------------------
# Identity: Feature toggles
# -----------------------------
IDENTITY_UUID_ENABLED=true
IDENTITY_USERNAME_ENABLED=true

# -----------------------------
# Identity: Pagination / API
# -----------------------------
IDENTITY_PER_PAGE=15
# Comma-separated values
IDENTITY_PER_PAGE_OPTIONS=5,15,25,50,100,500,1000
IDENTITY_API_RATE_LIMIT=60

# -----------------------------
# Identity: Deletion strategy
# -----------------------------
# safe = requires soft deletes on active user model
# hard = allows hard delete behavior
IDENTITY_DELETION_STRATEGY=safe

# -----------------------------
# Identity: Protected super-admin guardrails
# -----------------------------
IDENTITY_PROTECTION_ENABLED=true
# You can protect by UUID, email, and/or exact name
IDENTITY_SUPER_ADMIN_UUID=
IDENTITY_SUPER_ADMIN_EMAIL=super-admin@example.com
IDENTITY_SUPER_ADMIN_NAME=Super Admin
```

---

## 8. Helpful Commands

```bash
# Validate host schema compatibility
php artisan identity:doctor

# Backfill/create profiles in shared mode
php artisan identity:sync-profiles

# Seed fake users quickly (if command enabled in your version)
php artisan identity:seed-users --count=100 --status=random
```

---

## 9. Testing

When linked into a host Laravel app, run module tests from host root:

```bash
php artisan test /absolute/path/to/fourwit-packages/fourwit-identity/Tests
```

Example:
```bash
cd /var/www/html/lm-test
php artisan test /var/www/html/fourwit-packages/fourwit-identity/Tests
```

---

## 10. Troubleshooting

## `Target class [modules] does not exist`
- Ensure package/module provider registration is correct.
- Clear caches:
```bash
php artisan optimize:clear
```

## Account/profile pages duplicated with Breeze/Jetstream
- Set:
```env
IDENTITY_ACCOUNT_WEB_ROUTES=false
```

## Shared mode user status/profile fields missing
- Run:
```bash
php artisan identity:sync-profiles
```

## Soft delete SQL errors (`deleted_at` missing)
- If using `IDENTITY_DELETION_STRATEGY=safe`, ensure user model/table supports soft deletes.

---

## 11. Development Workflow (End)

Recommended branch flow:
- `main`: stable, releasable
- `develop`: integration branch
- `feature/*`: all changes via PR into `develop`

Recommended local workflow:
1. Work on module at `/var/www/html/fourwit-packages/fourwit-identity`
2. Link in host app via path repository
3. Run host + module tests
4. Update README/config notes with every feature affecting integration

Before release/tag:
- Run module test suite in host context
- Confirm shared and owned mode basics
- Confirm `identity:doctor` and `identity:sync-profiles` behavior
- Confirm published views and layout overrides
