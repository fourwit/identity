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
4. Optional profile backfill in shared mode:
```bash
php artisan identity:sync-profiles
```

---

## 4. Owned vs Shared Mode

## `IDENTITY_MODE=owned`
Use when module owns user table/schema.

- Module manages its own full users fields.
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
If you use package views directly (without publishing), include package Blade paths in Tailwind `content`.

If you want full visual customization, publish views and edit the published copies.

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

