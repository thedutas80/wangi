<img width="1575" height="746" alt="image" src="https://github.com/user-attachments/assets/3f4e6ef1-32fd-4b15-bb36-fbf5f9e92f62" />



# Wangi

Aplikasi manajemen sesi aktivitas dan alokasi tamu untuk objek wisata, dibangun dengan Laravel 12 dan FilamentPHP admin panel.

---

## Setup Instruction

### Prerequisites

- **PHP 8.2+** dengan ekstensi: `bcmath`, `ctype`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenxml`, `xml`
- **Composer** v2+
- **Node.js** 18+ dan **npm**
- **MySQL** 8.0+ (atau SQLite untuk development cepat)

### Langkah-langkah

```bash
# 1. Clone repositori
git clone https://github.com/thedutas80/wangi.git
cd wangi

# 2. Install dependency PHP
composer install

# 3. Buat file environment
cp .env.example .env

# 4. Konfigurasi database di .env
#    Untuk MySQL (disarankan):
#      DB_CONNECTION=mysql
#      DB_HOST=127.0.0.1
#      DB_PORT=3306
#      DB_DATABASE=db_wangi
#      DB_USERNAME=root
#      DB_PASSWORD=
#
#    Untuk SQLite (cepat, tanpa DB eksternal):
#      DB_CONNECTION=sqlite

# 5. Generate app key
php artisan key:generate

# 6. Jalankan migrasi dan seeder
php artisan migrate --seed

# 7. Install dan build frontend
npm install
npm run build
```

Atau gunakan satu perintah:

```bash
composer setup
```

### Menjalankan Dev Server

```bash
composer dev
```

Menjalankan empat proses bersamaan: PHP server, queue worker, logs, dan Vite dev server.

### Testing

```bash
composer test
```

Menjalankan `php artisan test` dengan SQLite in-memory — tanpa database eksternal.

```bash
php artisan test tests/Feature/ExampleTest.php   # Test file tertentu
./vendor/bin/pint                                 # Cek style PHP
```

### Admin Panel

Akses di `/admin` setelah seeder:

| Email | Password | Role |
|---|---|---|
| admin@wangi.com | password | Admin |
| operator@wangi.com | password | Operator |

Re-seed:

```bash
php artisan migrate:fresh --seed
```

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| **Backend** | PHP 8.2+, Laravel 12 |
| **Admin Panel** | FilamentPHP 3.3 dengan Shield (role & permission) |
| **Database** | MySQL (dev), SQLite in-memory (test) |
| **Frontend** | Blade, Tailwind CSS v4, Vite |
| **Package** | `spatie/laravel-permission`, `spatie/laravel-activitylog` |
| **Queue & Cache** | Database driver |

---

## Assumptions

1. **Pengguna sudah familiar dengan Laravel** — dokumentasi ini tidak menjelaskan konsep dasar Laravel seperti Eloquent, migration, atau service container.
2. **MySQL sebagai database production** — semua pengembangan dan deployment menggunakan MySQL; SQLite hanya untuk testing.
3. **Satu sesi per hari per attraction** — logika bisnis tidak menangani sesi berulang (recurring); setiap sesi dibuat manual dengan tanggal spesifik.
4. **Alokasi tamu selalu dalam satu transaksi** — tidak ada mekanisme partial booking atau waiting list.
5. **Autentikasi menggunakan Filament** — tidak ada public-facing API atau frontend untuk pengunjung.

---

## Tradeoffs

1. **Status sesi diupdate via `retrieved` event** — alih-alih scheduler/cron job, status sesi diubah menjadi `inactive` saat model di-load dari DB jika sudah lewat waktu. Ini efisien untuk read-heavy, tapi tidak memperbarui sesi yang tidak pernah di-query. Tradeoff: kesederhanaan vs kelengkapan data.
2. **Permissions di-seed manual** — Shield auto-discovery dimatikan (`discover_all_resources = false`). Permission dibuat manual via `RolePermissionSeeder`. Tradeoff: kontrol penuh vs kenyamanan.
3. **Queue & cache pakai database driver** — tidak menggunakan Redis atau dedicated queue service. Tradeoff: setup sederhana vs performa tinggi.
4. **Tidak ada pagination API** — semua data dikelola via Filament admin panel; tidak ada REST/GraphQL endpoint publik. Tradeoff: cepat develop vs fleksibilitas integrasi.
5. **Locking optimistis via `lockForUpdate`** — alokasi menggunakan row-level locking untuk mencegah overcapacity. Tradeoff: konsistensi data vs throughput pada konkurensi tinggi.
6. **Tidak ada scheduled task** — tidak ada command artisan untuk maintenance otomatis (expired session, cleanup, dll). Tradeoff: sederhana vs pemeliharaan otomatis.

---

## AI Usage

Proyek ini dikerjakan dengan bantuan **AI coding assistant** untuk:

- **Generasi boilerplate** — migration, model, factory, resource Filament dibuat dengan prompt ke AI, mempercepat setup awal.
- **Dokumentasi** — README dan komentar kode ditulis/direvisi dengan bantuan AI.
- **Refactoring** — AI digunakan untuk restruktur kode (misalnya memindahkan logic dari controller ke service layer).
- **Testing** — test case dan data factory dibantu pembuatannya oleh AI.

Semua output AI **selalu ditinjau dan diuji secara manual** sebelum di-commit. AI digunakan sebagai alat bantu produktivitas, bukan sebagai pengganti keputusan teknis.

---

## Architecture

### Models

| Model | Key Relations | Notes |
|---|---|---|
| `Attraction` | hasMany `ActivitySession` | Soft deletes |
| `ActivitySession` | belongsTo `Attraction`, hasMany `GuestAllocation` | Table: `activity_sessions` |
| `GuestAllocation` | belongsTo `ActivitySession`, `User` | — |
| `User` | — | Column `role` (admin/operator), Spatie `HasRoles` |

### Enums

| Enum | Values |
|---|---|
| `SessionStatus` | `active`, `inactive`, `blocked` (string-backed) |
| `AllocationSource` | `Walk In`, `Travel Agent`, `Hotel Partner`, `Online Booking`, `Internal Reservation` |

### Key Behaviors

- **Auto-inactive**: sesi yang sudah lewat tanggal+jam otomatis menjadi `inactive` saat di-load dari database
- **scopeActive**: query scope hanya mengembalikan sesi dengan status `active` yang belum lewat
- **AllocationService**: semua operasi alokasi berjalan dalam `DB::transaction` dengan `lockForUpdate`
- **Validasi alokasi**: tidak bisa membuat/mengubah alokasi untuk sesi inactive, sudah lewat, atau penuh
