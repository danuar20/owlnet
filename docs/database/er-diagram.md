# Hermes ISP Billing — Database ER Diagram

Two PostgreSQL schemas live inside the `hermes_isp` database, owned by the `radius` role:

- **`billing`** — business domain (customers, packages, subscriptions, payments).
- **`radius`** — MikroTik / FreeRADIUS integration (routers, profiles, admins, settings, audit).

All primary and foreign keys are **UUID** (`gen_random_uuid()` default). Soft deletes
(`deleted_at`) are applied on every mutable entity; `payment_logs` and `activity_logs`
are append-only audit tables and are NOT soft-deletable.

> Note: `public.users` (application staff from the auth module) is intentionally
> separate from `billing.users` (customers / subscribers).

```mermaid
erDiagram
    BILLING_USERS {
        uuid id PK
        string name
        string email UK
        string phone
        text address
        string status
        uuid created_by FK
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    BILLING_PACKAGES {
        uuid id PK
        string name
        string code UK
        decimal price
        int duration_days
        string speed_download
        string speed_upload
        bool is_active
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    BILLING_SUBSCRIPTIONS {
        uuid id PK
        uuid user_id FK
        uuid package_id FK
        uuid router_id FK
        string username UK
        string status
        timestamp started_at
        timestamp expired_at
        decimal price
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    BILLING_PAYMENTS {
        uuid id PK
        uuid user_id FK
        uuid subscription_id FK
        string invoice_no UK
        decimal amount
        string method
        string gateway
        string status
        timestamp paid_at
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    BILLING_PAYMENT_LOGS {
        uuid id PK
        uuid payment_id FK
        uuid user_id FK
        string level
        string event
        text message
        jsonb payload
        timestamp created_at
        timestamp updated_at
    }

    RADIUS_ROUTERS {
        uuid id PK
        string name UK
        string ip_address UK
        int api_port
        string username
        string password
        string api_type
        bool is_active
        timestamp last_seen_at
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    RADIUS_MIKROTIK_PROFILES {
        uuid id PK
        uuid router_id FK
        string name
        string profile_type
        string rate_limit
        int session_timeout
        int idle_timeout
        bool is_active
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    RADIUS_ADMINS {
        uuid id PK
        string username UK
        string password
        string email
        bool is_active
        timestamp last_login_at
        uuid created_by FK
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    RADIUS_SETTINGS {
        uuid id PK
        string key UK
        text value
        string group
        bool is_encrypted
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    RADIUS_ACTIVITY_LOGS {
        uuid id PK
        uuid admin_id FK
        string subject_type
        uuid subject_id
        string event
        text description
        jsonb payload
        string ip_address
        timestamp created_at
    }

    BILLING_USERS ||--o{ BILLING_SUBSCRIPTIONS : "has"
    BILLING_USERS ||--o{ BILLING_PAYMENTS : "makes"
    BILLING_USERS ||--o{ BILLING_PAYMENT_LOGS : "logged for"
    BILLING_PACKAGES ||--o{ BILLING_SUBSCRIPTIONS : "defines"
    RADIUS_ROUTERS ||--o{ BILLING_SUBSCRIPTIONS : "serves"
    RADIUS_ROUTERS ||--o{ RADIUS_MIKROTIK_PROFILES : "owns"
    BILLING_SUBSCRIPTIONS ||--o{ BILLING_PAYMENTS : "billed by"
    BILLING_PAYMENTS ||--o{ BILLING_PAYMENT_LOGS : "logs"
    RADIUS_ADMINS ||--o{ RADIUS_ACTIVITY_LOGS : "performs"
```

## Key relationships

| Child | References | On delete |
|--------|------------|-----------|
| `billing.subscriptions.user_id` | `billing.users.id` | CASCADE |
| `billing.subscriptions.package_id` | `billing.packages.id` | CASCADE |
| `billing.subscriptions.router_id` | `radius.routers.id` | SET NULL |
| `billing.payments.user_id` | `billing.users.id` | CASCADE |
| `billing.payments.subscription_id` | `billing.subscriptions.id` | SET NULL |
| `billing.payment_logs.payment_id` | `billing.payments.id` | CASCADE |
| `billing.payment_logs.user_id` | `billing.users.id` | SET NULL |
| `radius.mikrotik_profiles.router_id` | `radius.routers.id` | SET NULL |
| `radius.activity_logs.admin_id` | `radius.admins.id` | SET NULL |

## Indexes

- `billing.users`: unique `email`, index `phone`, index `status`
- `billing.packages`: unique `code`, index `is_active`
- `billing.subscriptions`: `(user_id, status)`, `package_id`, `status`, `expired_at`
- `billing.payments`: `user_id`, `(subscription_id)`, `method`, `status`, `created_at`
- `billing.payment_logs`: `payment_id`, `user_id`, `level`, `created_at`
- `radius.routers`: unique `ip_address`, unique `name`, index `is_active`
- `radius.mikrotik_profiles`: unique `(router_id, name)`, `profile_type`, `is_active`
- `radius.admins`: unique `username`, index `is_active`
- `radius.settings`: unique `key`
- `radius.activity_logs`: `(subject_type, subject_id)`, `admin_id`, `created_at`
