# Wangi — Laravel 12

## Dev commands

| Action | Command |
|---|---|
| Full setup | `composer setup` (installs deps, creates `.env`, generates key, migrates, builds assets) |
| Dev server (all processes) | `composer dev` (serves PHP, queue worker, logs, Vite concurrently) |
| Run tests | `composer test` (clears config then runs `php artisan test`) |
| Single test file | `php artisan test tests/Feature/ExampleTest.php` |
| PHP code style | `./vendor/bin/pint` |
| Frontend build | `npm run build` |
| Vite dev | `npm run dev` |

## Stack

- **PHP 8.2+**, Laravel 12
- **MySQL** (`db_wangi`) in dev; **SQLite in-memory** for tests (`phpunit.xml`)
- **Vite** + **Tailwind CSS v4** (`@tailwindcss/vite` plugin) + **Blade** templates
- Frontend JS is minimal — `resources/js/bootstrap.js` sets up Axios

## FilamentPHP Admin

- Admin panel at `/admin` — login with seeded credentials
- Users: `admin@wangi.com` / `password` (admin role) and `operator@wangi.com` / `password` (operator role)
- Resources: Attractions, Activity Sessions, Guest Allocations — under "Master Data" and "Operations" nav groups
- Role management: `/admin/shield/roles` — permission naming uses Shield convention (e.g. `view_activity::session` not `view_activity_session`)
- Dashboard widgets: stats overview, occupancy bar chart, allocation source doughnut chart
- Re-seed: `php artisan migrate:fresh --seed`

## Architecture

- Standard Laravel structure under `app/`, `config/`, `routes/`, `resources/`, `database/`
- PSR-4: `App\` → `app/`, `Database\Factories\` → `database/factories/`, `Database\Seeders\` → `database/seeders/`
- Base TestCase at `tests/TestCase.php` (no custom traits by default; uncomment `RefreshDatabase` when needed)
- **Key models**: `Attraction` (hasMany `ActivitySession`, soft deletes), `ActivitySession` (belongsTo `Attraction`, hasMany `GuestAllocation`, table is `activity_sessions` not `sessions`), `GuestAllocation` (belongsTo `ActivitySession`, `User`)
- **Service layer**: `App\Services\AllocationService` — all allocation create/update/delete runs in `DB::transaction` with row-level locking (`lockForUpdate`)
- **Enum backends**: `SessionStatus` (active/inactive/blocked), `AllocationSource` (Walk In / Travel Agent / Hotel Partner / Online Booking / Internal Reservation) — stored as string in DB
- `User` model adds `role` column (admin/operator), uses `Spatie\Permission\Traits\HasRoles`
- Policies under `app/Policies/` — check against Spatie permissions seeded by `RolePermissionSeeder`
- Permissions are seeded manually (not using Shield's auto-discovery since `discovery.discover_all_resources` is `false`)

## Testing quirks

- Tests use in-memory SQLite — no external DB needed
- Feature tests use `Tests\TestCase` (Laravel); Unit tests use plain `PHPUnit\Framework\TestCase`

## Config conventions

- `.editorconfig`: spaces, 4-space indent, LF line endings
- Session, queue, and cache default to `database` driver in `.env.example`
