# Fourwit Identity Module Blueprint

Welcome to the **Fourwit Identity Module**. This document serves as the complete enterprise-grade internal architecture blueprint and developer onboarding guide for the `fourwit/identity` Laravel package. 

This is not a marketing overview; it is a strict technical reference detailing how the module is built, its architectural boundaries, and how it should be consumed or extended in an enterprise ecosystem.

---

## 1. Module Overview

**Purpose**
The Identity module provides highly reusable, decoupled identity infrastructure for enterprise Laravel applications. It abstracts the core concept of a "User" and their immediate identity properties away from the host application, creating a highly portable, SOLID-compliant package that can be dropped into any project.

**Enterprise Architecture Goals**
- **Strict Isolation:** Provide user data management without becoming entangled in authentication workflows or media handling.
- **Portability:** Be consumable via Composer across multiple independent SaaS or internal tools.
- **FAANG-Grade Robustness:** Rely on strict typing, Data Transfer Objects (DTOs), Repository patterns, and dedicated Actions.

**What Identity OWNS:**
- Core `User` model and schema.
- User profile management (Name, Email, Phone, Locale, Timezone).
- Statuses (Active, Inactive, Suspended, Pending).
- Metadata (Schemaless JSON storage).
- UUID generation and tracking.
- Activity Logs (Audit trails for identity modifications).
- APIs (Admin user management & Self-service account retrieval).
- Self-service account infrastructure.

**What Identity DOES NOT OWN:**
- **Authentication:** Login, Forgot Password, Reset Password, Sessions, JWT, Sanctum, Passport, OTP, MFA (Belongs to the future **Auth Module**).
- **Media Processing:** Avatar uploads, cropping, S3 integration (Belongs to the future **Media Module**).
- **Permissions:** Roles, Permissions, Policies beyond basic user CRUD (Belongs to the future **RBAC Module**).

---

## 2. Current Features

The module is shipped with the following enterprise features fully implemented:

- **Admin User Management:** Full CRUD Web GUI for managing any user in the system.
- **Account Self-Service Management:** Dedicated controllers and views for users to manage their *own* profiles.
- **API Support:** Complete RESTful API coverage for both Admin endpoints and Self-service endpoints, fully paginated.
- **Blade Support:** Pre-built, styled Blade views using Tailwind CSS for out-of-the-box UI.
- **UUID Support:** Config-driven UUID v4 generation for decentralized ID tracking.
- **Enums:** Type-safe native PHP Enums for status tracking (`UserStatus`).
- **DTOs:** `UserData` objects to safely pass validated request data deep into the business logic.
- **Actions:** Single-responsibility action classes (`CreateUserAction`, `UpdateUserAction`, `DeleteUserAction`).
- **Repositories:** `UserRepository` extracting Eloquent queries away from the controllers.
- **Policies:** Basic authorization boundary for Admin vs Self-service contexts.
- **Events:** `UserCreated`, `UserUpdated`, `UserDeleted`, and `UserSuspended` events.
- **Activity Logs:** Automatic internal audit logging for all identity changes using Observers and Services.
- **Validation:** DRY validation rules using the `HasUserValidationRules` trait.
- **Factories & Seeders:** Robust testing and local dev data generation.
- **API Resources:** `UserResource` for strict, uniform JSON structures.
- **Config-Driven Behavior:** Toggles for usernames, UUIDs, middleware, and pagination via `identity.php`.
- **Account Profile Management:** Dedicated Web & API endpoints for profile edits.
- **Password Update Support:** Safely handling password hashing specifically for the authenticated user.
- **Verification Status Endpoints:** UI for checking email/phone verification.
- **Search & Query Scopes:** Extensible repository-level filtering by status and text.

---

## 3. Architecture Overview

This module is structured using a Domain-Driven Design (DDD) inspired folder hierarchy.

```text
Modules/Identity/
├── app/
│   ├── Actions/       # Single-responsibility business logic (e.g., CreateUserAction)
│   ├── DTOs/          # Data Transfer Objects enforcing strict typing before persistence
│   ├── Enums/         # PHP 8.1+ Enums ensuring state safety
│   ├── Events/        # Broadcastable state changes (e.g., UserCreated)
│   ├── Listeners/     # Decoupled responses to module events
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/   # Controllers that manage ANY user (Requires elevated access)
│   │   │   ├── Account/ # Controllers that manage ONLY the authenticated user
│   │   │   └── Api/     # Stateless REST endpoints
│   │   └── Requests/  # FormRequests enforcing validation & context authorization
│   ├── Models/        # Eloquent Models (User, ActivityLog)
│   ├── Observers/     # Model lifecycle hooks (UUID generation, Activity Logging)
│   ├── Policies/      # Authorization logic
│   ├── Repositories/  # Data access layer hiding Eloquent complexity
│   ├── Services/      # Complex cross-domain operations (e.g., ActivityLogger)
│   ├── Traits/        # Shared logic (HasUuid, HasStatus, HasUserValidationRules)
│   └── Transformers/  # API JSON Resource classes
├── config/            # identity.php (Module settings)
├── database/          # Migrations, Factories, Seeders
├── resources/         # Blade views (admin/, account/)
├── routes/            # web.php, api.php
└── Tests/             # Comprehensive Feature and Unit test suites
```

**Why this architecture?**
This architecture adheres to **SOLID principles**. Controllers are kept thin; their only job is to receive the HTTP Request, pass it to a FormRequest for validation, marshal it into a DTO, and hand it off to an Action. The Action interacts with the Repository to persist data, and fires an Event. This makes testing trivial, as Actions and Repositories can be unit-tested in isolation without HTTP overhead.

---

## 4. Module Boundaries

Strict module boundaries are critical to preventing "Big Ball of Mud" architectures.

**Identity OWNS:**
- The concept of a `User`.
- Securely storing passwords in the database.
- Defining if a user is `active`, `suspended`, or `pending` (Statuses).
- Defining if a user has verified their email or phone.
- A user's profile data (Names, Timezone, Locale).
- Flexible metadata attached to a user.
- Providing self-service endpoints to modify the above.

**Identity DOES NOT OWN:**
- **Login / Sessions / JWT / Sanctum / Passport:** Identity stores the password, but the mechanism of logging in, validating credentials, and issuing tokens belongs to the future **Auth Module**.
- **OTP / MFA:** Handled by Auth.
- **Media Processing:** Identity has an `avatar_id` foreign key placeholder. The actual upload, cropping, storage (S3), and serving of that image belongs to the future **Media Module**.
- **RBAC:** Roles and permissions belong to a future module.
- **Billing / Tenancy:** Subscriptions and multi-tenant scoping are explicitly excluded.

---

## 5. Database Structure

### `users` table
- `id` (BigInt, PK)
- `uuid` (UUID, Unique) - For decentralized client-side tracking.
- `username` (String, Unique, Nullable) - Configurable feature toggle.
- `password` (String) - Hashed password for the Auth module to consume.
- `avatar_id` (BigInt, Nullable) - Foreign key pointer to future Media module.
- `status` (String) - Enum representation (active, inactive, suspended).
- `timezone` / `locale` (String) - Localization preferences.
- `metadata` (JSON) - Schemaless storage for application-specific flexible data.
- `email_verified_at` / `phone_verified_at` (Timestamp)
- `deleted_at` (Timestamp) - Soft deletes implemented to preserve audit logs.

### `activity_logs` table
Tracks all mutations within the module.
- `user_id` (BigInt) - The user who was modified.
- `causer_id` (BigInt, Nullable) - The admin/user who performed the action.
- `action` (String) - e.g., `created`, `updated`, `deleted`.
- `description` (Text) - Human-readable summary.
- `properties` (JSON) - Snapshot of changed attributes.

---

## 6. Routes Documentation

The routing architecture relies on strict context separation.

### Admin Routes (Web)
Prefix: `config('identity.routes.admin_prefix', 'admin')`
Middleware: `['web', 'auth']`
- `GET /admin/users` - List users.
- `POST /admin/users` - Create any user.
- `GET /admin/activity-logs` - View system-wide identity logs.

### Account Routes (Web)
Prefix: `/account`
Middleware: `['web', 'auth']`
- `GET /account/profile` - View own profile.
- `PUT /account/profile` - Update own profile.
- `PUT /account/password` - Update own password.
- `GET /account/verification-status` - Check own verification.

**CRITICAL ARCHITECTURE RULE:** Account routes NEVER use `{id}` parameters (e.g., `/account/profile/{id}`). 
Why? To completely eliminate IDOR (Insecure Direct Object Reference) vulnerabilities at the routing layer. Account controllers implicitly act *only* on `auth()->user()`. 

### API Routes
Prefix: `config('identity.routes.api_prefix', 'api/v1')`
Middleware: `['api', 'auth:sanctum']`
- `GET /api/v1/users` - Admin API index.
- `GET /api/v1/account/me` - Self-service profile retrieval.
- `PUT /api/v1/account/profile` - Self-service update.

---

## 7. Admin Functionality

The Admin layer is designed for elevated users (Superadmins, Staff) to manipulate *other* users in the system.
- **Workflows:** Admin controllers instantiate `UpdateUserAction` to modify statuses or trigger password resets.
- **Safety:** The `UserController` actively prevents the deletion of the main admin user (ID 1).
- **Logging:** Admin actions populate the `causer_id` in the `activity_logs` table, ensuring an undeniable audit trail of which staff member modified which customer.

---

## 8. Account Functionality

The Account layer is built around self-service.
- **Isolation:** Managed via `Http/Controllers/Account/`.
- **Requests:** Uses context-specific FormRequests (e.g., `UpdateProfileRequest`) which strip out sensitive fields like `status` or `email_verified_at` that a user should never be able to modify themselves.
- **Password Updates:** Explicitly handles `current_password` validation before applying `new_password` hashes.

---

## 9. API Documentation

All API responses use `Modules\Identity\Transformers\UserResource` to ensure consistent JSON formatting.

**Example Request:** `GET /api/v1/account/me`
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@fourwit.com",
    "status": "active",
    "timezone": "UTC",
    "locale": "en",
    "avatar_url": null,
    "created_at": "2026-05-26T10:00:00.000000Z"
  }
}
```
**Pagination:** Index endpoints return Laravel standardized metadata (links, meta, total pages).
**Error Responses:** 422 Unprocessable Entity responses automatically map standard Laravel validation errors to JSON.

---

## 10. Events System

The module relies heavily on an Event-Driven Architecture to communicate with external modules without hard dependencies.

- `Modules\Identity\Events\UserCreated`
- `Modules\Identity\Events\UserUpdated`
- `Modules\Identity\Events\UserDeleted`
- `Modules\Identity\Events\UserSuspended`

**Extensibility Strategy:**
When the future Notifications module is built, it will simply register a Listener for `UserCreated` to send a welcome email. The Identity module does not need to know that the Notifications module exists.

---

## 11. Configuration

The module's behavior is heavily configurable via `config/identity.php`.

Key Toggles:
- `features.uuid` (bool): Enable/disable UUID generation.
- `features.username` (bool): Enable/disable username handling in UI and validation.
- `routes.middleware.web`: Dynamically arrays middleware. Defaults to `['web', 'auth']` in production, but cleverly bypasses `auth` during testing if configured.
- `api.rate_limit`: Defaults to 60 hits per minute on Identity endpoints.
- `branding.name`: Used dynamically across Blade views.

---

## 12. Testing

The module maintains FAANG-grade testing standards.

**Suite Composition:**
- `Tests/Unit/`: Verifies Actions, DTO mapping, Enum resolving, and Observers without touching the database/HTTP layer where possible.
- `Tests/Feature/`: HTTP integration tests verifying Web and API routing, Validation failures, and IDOR protection in the Account endpoints.

**Execution:**
Run all tests specific to the module:
```bash
php vendor/bin/phpunit Modules/Identity/Tests --testdox
```
*(As of writing, the suite contains 68 tests and 167 assertions, all passing).*

---

## 13. Seeder Information

The `IdentityDatabaseSeeder` is configured to bootstrap environments quickly.
- Generates a Superadmin (ID 1).
- Generates 50 randomized development users using `UserFactory`.
- Triggers Activity Logs natively during seeding to populate the dashboard with realistic data.

To run:
```bash
php artisan module:seed Identity
```

---

## 14. Installation & Usage

For standard monolithic inclusion (when the module lives inside `/Modules/Identity`):

1. **Enable the Module:**
   ```bash
   php artisan module:enable Identity
   ```
2. **Run Migrations:**
   ```bash
   php artisan module:migrate Identity
   ```
3. **Seed Database:**
   ```bash
   php artisan module:seed Identity
   ```
4. **Publish Configuration (Optional):**
   ```bash
   php artisan vendor:publish --provider="Modules\Identity\Providers\IdentityServiceProvider"
   ```

---

## 15. Consuming Via Git Repository

Because this module is designed as a reusable package, you can consume it via Composer in independent SaaS projects.

**In your host application's `composer.json`:**
```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:fourwit/identity.git"
    }
],
"require": {
    "fourwit/identity": "^1.0"
}
```

**Local Development Workflow (Path Repository):**
If you are developing the module alongside a host app on your local machine, use a path repository:
```json
"repositories": [
    {
        "type": "path",
        "url": "../fourwit-packages/identity"
    }
]
```
This forces Composer to symlink the module, meaning any changes you make in the module instantly reflect in the host app without needing a `composer update`.

---

## 16. Development Workflow

**Philosophy:**
- **Strict Typing:** Always use PHP 8.1+ types, return types, and Enums.
- **SOLID Principles:** No Fat Controllers. Keep business logic in Actions.
- **Agnosticism:** NEVER assume the host application has Sanctum, Passport, or specific packages installed unless explicitly required in the module's `composer.json`.

When contributing to this module, think like a SaaS Architect. If you build a feature, ask: *"If I drop this into a completely different project next year, will it break?"*

---

## 17. Future Planned Modules

The Identity module acts as the foundation for the upcoming Fourwit ecosystem. The following independent modules are planned to plug into this architecture:

1. **Authentication Module (`fourwit/auth`):** Handles login flows, Sanctum/Passport integration, and 2FA.
2. **Media Module (`fourwit/media`):** Integrates with Identity's `avatar_id` to process and serve profile pictures via S3.
3. **RBAC Module (`fourwit/permissions`):** Spatie-style Roles and Permissions that attach to Identity Users.
4. **Tenancy Module (`fourwit/tenancy`):** For B2B multi-tenant SaaS scoping.
5. **Notifications Module (`fourwit/notifications`):** Listens to Identity Events to dispatch emails, SMS, and Slack webhooks.
6. **Billing Module (`fourwit/billing`):** Stripe integration mapped to Identity users.
