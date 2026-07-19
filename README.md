# Hermes ISP Billing

A production-ready **Laravel 12** skeleton for an ISP billing system targeting
**MikroTik Hotspot** and **FreeRADIUS**. This repository contains only the
project skeleton — billing features are intentionally **not** implemented yet.

## Stack

| Concern        | Technology                          |
|----------------|-------------------------------------|
| Language       | PHP 8.4                             |
| Framework      | Laravel 12                          |
| Database       | PostgreSQL 17                       |
| Cache / Queue  | Redis 7                             |
| UI             | Bootstrap 5                         |
| Auth scaffold  | Laravel Breeze (Blade)              |
| Web server     | Nginx (1.27)                        |
| Runtime        | Docker + Docker Compose             |
| Tests          | Pest 3 + PHPUnit                    |
| Code style     | Laravel Pint (PSR-12)               |

## Architecture

The project follows a **clean, layered architecture**:

```
app/
├── Http/
│   └── Controllers/      # Thin controllers: validate request, call a service, return response
├── Services/             # Service Layer — business logic lives here (BaseService)
├── Repositories/         # Repository Pattern — persistence (BaseRepository, RepositoryInterface)
├── Jobs/                 # Queueable jobs (implement ShouldQueue)
├── Helpers/              # Global helpers.php (autoloaded)
└── Models/               # Eloquent models
```

**Key conventions**

- **No business logic in controllers.** Controllers delegate to services.
- **Service Layer** orchestrates repositories and other services. It never
  touches Eloquent or the HTTP layer directly.
- **Repository Pattern** isolates data access behind `RepositoryInterface`.
  Bindings are registered in `AppServiceProvider`.
- Example implementations: `UserRepository`, `UserService`, `ExampleJob`.

## Project Structure

```text
hermes-isp-billing/
├── app/
│   ├── Console/Kernel.php        # Scheduler placeholder
│   ├── Helpers/helpers.php       # Global helpers (human_bytes, mac_normalize, ...)
│   ├── Http/Controllers/         # Thin controllers
│   ├── Http/Middleware/RoleMiddleware.php  # super_admin / admin / operator
│   ├── Models/
│   │   ├── User.php              # Application staff (public.users)
│   │   ├── Billing/            # billing.users, packages, subscriptions, payments, payment_logs
│   │   └── Radius/             # radius.routers, mikrotik_profiles, admins, settings, activity_logs
│   ├── Enums/UserRole.php      # Role enum
│   ├── Jobs/ExampleJob.php       # Queueable job placeholder
│   ├── Repositories/             # BaseRepository, RepositoryInterface, UserRepository
│   ├── Services/                 # BaseService, UserService
│   └── Providers/AppServiceProvider.php  # Repository bindings
├── database/migrations/          # Laravel + billing/radius schema migrations
├── docs/database/er-diagram.md # Mermaid ER diagram
├── docker/
│   ├── nginx/default.conf        # Nginx virtual host
│   └── php/opcache.ini           # OpCache tuning
├── tests/                        # Pest tests (Feature + Unit)
├── .env.example                  # PostgreSQL / Redis / Queue / Scheduler config
├── docker-compose.yml            # app + nginx + queue + scheduler + postgres + redis
├── Dockerfile                    # PHP 8.4-FPM
├── pint.json                     # Laravel Pint (PSR-12) rules
├── phpunit.xml                   # Test suite config
├── Pest.php                      # Pest bootstrap
└── README.md
```

## Database

The application uses a single PostgreSQL database (`hermes_isp`) accessed through the
`radius` login role (`radius` / `radius`). Inside it there are two dedicated
schemas plus the default `public` schema:

| Schema | Purpose | Tables |
|---------|----------|--------|
| `billing` | Business domain | `users`*, `packages`, `subscriptions`, `payments`, `payment_logs` |
| `radius` | MikroTik / FreeRADIUS integration | `routers`, `mikrotik_profiles`, `admins`, `settings`, `activity_logs` |
| `public` | Laravel framework + staff | `users` (application staff from the auth module) |

> `*` `billing.users` are **customers/subscribers**, distinct from `public.users`
> which are **application staff** (Super Admin / Admin / Operator).

Conventions:

- **UUID** primary and foreign keys (`gen_random_uuid()` default, no extension).
- **Foreign keys** are enforced at the database level (CASCADE on owners, SET NULL on optionals).
- **Indexes** on all foreign keys, status/active flags, unique business keys.
- **Soft deletes** (`deleted_at`) on every mutable entity. `payment_logs` and
  `activity_logs` are append-only audit tables and are NOT soft-deletable.
- The `radius` role has `search_path = billing, radius, public`, so models reference
  tables by bare name (e.g. `billing.users` is resolved automatically).

See [docs/database/er-diagram.md](docs/database/er-diagram.md) for the full ER diagram.

## Getting Started (Docker)

```bash
# 1. Copy environment configuration
cp .env.example .env

# 2. Generate the application key
docker compose run --rm app php artisan key:generate

# 3. Start all services (app, nginx, queue, scheduler, postgres, redis)
docker compose up -d

# 4. Run migrations
docker compose exec app php artisan migrate --force

# 5. Open http://localhost:8080
```

## Common Commands

```bash
# Run the test suite (Pest)
docker compose exec app composer test

# Check code style (Pint)
docker compose exec app composer format

# Run the queue worker manually (if not using the dedicated service)
docker compose exec app php artisan queue:work redis

# Run scheduled commands once
docker compose exec app php artisan schedule:run
```

## Code Style

The project enforces **PSR-12** via Laravel Pint. Configuration lives in
`pint.json`. Run `composer format` before committing.

## Testing

Tests use **Pest** (with the Laravel plugin) backed by `phpunit.xml`. A sample
unit test lives in `tests/Unit/ExampleTest.php`. Run with `composer test`.

## Status

This is a **skeleton only**. Billing features (customers, packages, vouchers,
sessions, RADIUS/FREE-RADIUS/MikroTik integrations) are not implemented yet and
will be added incrementally following the layering conventions above.

## License

MIT
