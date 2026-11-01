# Pool Service System

Pool service management system built with Laravel 11, using the same admin UI (corporate theme) as SalonPOS.

## What's included

- Login, registration, password reset and profile (Laravel Breeze)
- Roles and permissions (spatie/laravel-permission), with an `admin` role seeded
- Activity log: logins, and add / change / delete on any model that uses the `LogsActivity` trait
- Admin layout (`resources/views/layouts/admin.blade.php`) with sidebar, toasts, Select2 dropdowns and confirm dialogs

## Local setup

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Log in with `admin@example.com` / `password` (override with `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` before seeding).

## Docker

```bash
cp .env.example .env   # then set APP_KEY
docker compose up -d --build
```

The app runs on http://localhost:8000 and MySQL on host port 3307.
