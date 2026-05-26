# Identity Module

**Production-ready User Identity Management Module** for Laravel 13+.

Part of **Fourwit** — Professional Laravel Module Library.

---

## Features

- Single `users` table supporting Admin, Customer, Employee, Student, etc.
- Highly configurable via `config/config.php`
- Dual architecture: **Full-stack (Blade)** + **API-first**
- UUID support (optional, config-driven)
- Activity Logging (Web + API) with source tracking
- Professional API responses using `BaseApiController`
- Config-aware `UserResource`
- Proper validation, policies, and exception handling
- Soft deletes, Pagination, Search & Filters
- Production-grade code following SOLID principles

---

## Requirements

- Laravel 13+
- PHP 8.3+
- nwidart/laravel-modules ^13.0

---

## Installation

```bash
composer require fourwit/identity
```
and then 

```bash
php artisan migrate
php artisan db:seed --class="Modules\\Identity\\Database\\Seeders\\UserSeeder"
```

## Configuration

All settings are located in `config/identity.php` (after publishing).

| Setting | Default | Description |
|---|---|---|
| `enable_uuid` | `false` | Generate UUID for users |
| `enable_username` | `true` | Allow username field |
| `require_email` | `true` | Email is required |
| `require_phone` | `false` | Phone is optional |
| `default_status` | `active` | Default user status |
| `per_page` | `15` | Pagination limit |
|`api_rate_limit` | `60` | API requests per minute |
| `enable_two_factor_by_default` | `false` | Enable 2FA for new users |

After changing config, run:

```bash
php artisan config:clear
```
## Usage
**Web (Blade) Routes**

| Method | URL | Description |
|---|---|---|
| GET | `/admin/users` | List all users |
| GET | `/admin/users/create` | Create user form |
| POST | `/admin/users` | Store new user |
| GET | `/admin/users/{id}/edit` | Edit user form |
| PUT | `/admin/users/{id}` | Update user |
| DELETE | `/admin/users/{id}` | Delete user |
| GET | `/admin/activity-logs` | View activity logs |

## API Routes (v1)

**Base URL:** `/api/v1`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/users` | List all users |
| POST | `/users` | Create new user |
| GET | `/users/{id}` | Get single user |
| PUT | `/users/{id}` | Update user |
| DELETE | `/users/{id}` | Delete user |
| GET | `/api/v1/activity-logs` | List activity logs |


### Disabling Web or API Routes
By default, both Web and API features are enabled.

You can disable one or both using environment variables:

```bash
# Disable Web (Blade views + Admin routes)
USER_ENABLE_WEB_VIEWS=false

# Disable API routes
USER_ENABLE_API_ROUTES=false
```

After changing, run:

```bash
php artisan config:clear
```

**When to Use This?**

- Disable Web if you're only building a headless API (React, Vue, Mobile apps)
- Disable API if you're only building a simple admin panel (no mobile/frontend needed)
- Disable both if you're using this module only for its models/services in a different context

This makes the Identity Module flexible for different types of projects.


## API Response Format
All API responses follow this consistent structure


```bash
{
  "success": true,
  "message": "Success",
  "data": { ... },
  "pagination": { ... }   // Only for paginated endpoints
}
```

## Example: Create User via API

```bash
POST /api/v1/users
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "status": "active"
}
```

**Response**

```bash
{
    "success": true,
    "message": "User created successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "status": "active"
    }
}
```

## Activity Logging

All user actions (create, update, delete) are automatically logged from both Web and API.

### Logged Information:

- Action description
- Source (web / api)
- IP Address & User Agent
- Old/New values (for updates)
- Performed by (User ID)

---

## Testing

```bash
# Run all User Module tests
php artisan test Modules/Identity/tests/
```

**Test Coverage Includes:**

- Web CRUD operations
- API CRUD operations
- Search & Filters
- Pagination
- Activity Logging (Web + API)
- Validation & Error Handling
- 404 responses

## Extensibility

### Adding Custom Fields

Use the `metadata` JSON column or create a new migration in your project.

### Extending the User Model

```php
// In your project's User model

use Modules\Identity\Models\User as BaseUser;

class User extends BaseUser
{
    // Add your custom logic here
}
```

---

## Security

- Form Request validation on all endpoints
- Policy-based authorization (`UserPolicy`)
- Soft deletes (users are never permanently deleted by default)
- Rate limiting recommended on API routes
- Activity logging for audit trail

---

## Testing

```bash
# Run module tests (when available)

php artisan test --filter=User
```



## How to Use This Module in Another Project

### Web (Blade) Usage

By default, the module comes with its own simple design. However, you can fully customize it to match your project's UI.

**Steps:**
```bash
# Publish the views
php artisan vendor:publish --provider="Modules\\Identity\\Providers\\IdentityServiceProvider" --tag="identity-views"

# (Optional) Publish the config
php artisan vendor:publish --provider="Modules\Identity\Providers\IdentityServiceProvider" --tag="identity-config"
```
After publishing:

Views will be copied to resources/views/vendor/identity/

You can now edit the Blade files freely

You can even extend your own master layout

**Example:**
```bash
{{-- resources/views/vendor/user/admin/index.blade.php --}}
@extends('layouts.app')  {{-- Your project's layout --}}

@section('content')
    <h1>Custom User Management</h1>
    {{-- Your design here --}}
@endsection
```
### API Usage

The API is ready to use out of the box. It returns clean, consistent JSON responses.

Base URL: /api/v1

Example:
```bash
GET /api/v1/users
Authorization: Bearer {token}

Similarly Other routes

GET /api/v1/users
GET /api/v1/users?status=active
GET /api/v1/users?status=suspended&per_page=20
GET /api/v1/users?status=active&search=john

```

Response:

```bash
JSON{
    "success": true,
    "message": "Users retrieved successfully",
    "data": [...],
    "pagination": {...}
}
```

You can consume this API from any frontend (React, Vue, Angular, Mobile apps, etc.).

### Disable Web or API (Optional)

If you only need one (Web or API), you can disable the other:
```bash 
# Only use API (disable Web)
USER_ENABLE_WEB_VIEWS=false

# Only use Web (disable API)
USER_ENABLE_API_ROUTES=false
```
This makes the User Module flexible for different types of projects.



## Author

Fourwit — Professional Laravel Module Library

## License
MIT License
