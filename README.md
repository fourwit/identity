# Fourwit Identity (`fourwit/identity`)

Reusable Laravel identity module for user management, profile/account flows, and activity logs.

This README is intentionally practical: setup, configuration, usage modes, and development workflow.

## 1. What This Module Does

### Included
- Admin user management (web + API)
- Account self-service endpoints (web + API)
- Shared mode and owned mode support
- Activity logging for identity actions
- Configurable layouts for host app integration
- Configurable pagination/search behavior

### Not Included
- Login/session/token systems (Breeze/Fortify/Sanctum auth flows remain host/app responsibility)
- OTP/MFA
- Media upload implementation
- RBAC/permissions

---

## 2. Install & Link

### A) From git repository
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

### B) Local path development (recommended during module development)
```json
{
  "repositories": [
    {
      "type": "path",
      "url": "/var/www/html/fourwit-packages/fourwit-identity"
    }
  ]
}
```

Then in host app:
```bash
composer require fourwit/identity:@dev
```

---

## 3. Quick Start

1. Configure `.env` (see full env list below).
2. Run migrations:
```bash
php artisan migrate
```
3. Validate host compatibility (especially shared mode):
```bash
php artisan identity:doctor
```
4. Optional profile sync in shared mode:
```bash
php artisan identity:sync-profiles
```

---

## 3.1 Setup Checklist

Use this quick guide to avoid migration confusion.

### Current setup
1. Set mode and mapping in `.env` (`IDENTITY_MODE`, `IDENTITY_USER_MODEL`, table names).
2. Run:
```bash
php artisan migrate
php artisan identity:doctor
```
3. Optional (only if users already existed before enabling Identity):
```bash
php artisan identity:sync-profiles
```

Notes:
- In shared mode, host `users` is not required to include identity-only columns like `uuid`, `username`, `phone`, `status`, etc.
- This package is currently pre-v1; schema is considered the current baseline.
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
