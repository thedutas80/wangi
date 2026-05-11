# Wangi

A Laravel 12 application for managing tourist attraction activity sessions and guest allocations, powered by FilamentPHP admin panel.

## Tech Stack

- **PHP 8.2+**, Laravel 12
- **FilamentPHP** admin panel with Shield role/permission management
- **MySQL** (dev), **SQLite in-memory** (tests)
- **Vite** + **Tailwind CSS v4** + **Blade** templates
- **Spatie** packages: `laravel-permission`, `laravel-activitylog`

## Environment Setup

### Prerequisites

- **PHP 8.2+** with extensions: `bcmath`, `ctype`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenxml`, `xml`
- **Composer** (v2+)
- **Node.js** 18+ and **npm**
- **MySQL** 8.0+ (or SQLite for quick local dev)

### Step-by-step

```bash
# 1. Clone repository
git clone https://github.com/your-org/wangi.git
cd wangi

# 2. Install PHP dependencies
composer install

# 3. Create environment file
cp .env.example .env

# 4. Configure database in .env
#    For MySQL (recommended for full features):
#      DB_CONNECTION=mysql
#      DB_HOST=127.0.0.1
#      DB_PORT=3306
#      DB_DATABASE=db_wangi
#      DB_USERNAME=root
#      DB_PASSWORD=
#
#    For SQLite (quick start, no external DB needed):
#      DB_CONNECTION=sqlite
#      (database/database.sqlite will be created automatically)

# 5. Generate app key
php artisan key:generate

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. Install and build frontend assets
npm install
npm run build

# --- Quick alternative ---
# All steps above in one command:
composer setup
```

### Admin Credentials

After seeding, login at `/admin`:

| Email | Password | Role |
|---|---|---|
| admin@wangi.com | password | Admin |
| operator@wangi.com | password | Operator |

To re-seed:

```bash
php artisan migrate:fresh --seed
```

## Development

```bash
composer dev
```

Runs four concurrent processes: PHP server, queue worker, logs, and Vite dev server.

## Testing

```bash
composer test
```

Clears config then runs `php artisan test`. Tests use in-memory SQLite — no external database needed.

Standalone commands:

```bash
php artisan test tests/Feature/ExampleTest.php
./vendor/bin/pint                         # PHP code style
npm run build                              # Frontend build
npm run dev                                # Vite dev server
```

### Resources

- **Master Data** — Attractions, Activity Sessions
- **Operations** — Guest Allocations
- **Admin** — Users, Roles (Shield at `/admin/shield/roles`)

## Architecture

### Models

| Model | Key Relations | Notes |
|---|---|---|
| `Attraction` | hasMany `ActivitySession` | Soft deletes |
| `ActivitySession` | belongsTo `Attraction`, hasMany `GuestAllocation` | Table: `activity_sessions` |
| `GuestAllocation` | belongsTo `ActivitySession`, `User` | — |
| `User` | — | Has `role` column (admin/operator), uses Spatie `HasRoles` |

### Service Layer

`App\Services\AllocationService` handles all allocation create/update/delete operations inside `DB::transaction` with row-level locking (`lockForUpdate`).

### Enums

| Enum | Values |
|---|---|
| `SessionStatus` | `active`, `inactive`, `blocked` (string-backed) |
| `AllocationSource` | `Walk In`, `Travel Agent`, `Hotel Partner`, `Online Booking`, `Internal Reservation` |

### Key Behaviors

- Past sessions (date passed or date is today and end_time passed) are automatically marked as `inactive` upon retrieval
- `scopeActive` automatically excludes past sessions from queries
- Allocations cannot be created/updated for inactive, past, or full sessions

## Configuration

- `.editorconfig`: spaces, 4-space indent, LF line endings
- Session, queue, and cache default to `database` driver
- Permissions are seeded manually via `RolePermissionSeeder` (Shield auto-discovery is disabled)
