# Pool Service System

Pool service management system built with Laravel 11, using the same admin UI (corporate theme) as SalonPOS.

## What's included

- **Company signup**: `/register` creates a company and its owner, who becomes the company's first admin
- **Two roles** (spatie/laravel-permission, created by a migration):
  - **Admin** (owner / office): manages the team, company settings and sees the activity log
  - **Technician**: field staff; their screens come with the job features
- **Team** page: admins add, edit, turn off or delete admins and technicians of their own company. Turned-off people cannot log in and are signed out on their next click
- Every company sees only its own people and activity
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

`--seed` adds a demo company. Log in as admin with `admin@example.com` / `password` or as technician with `tech@example.com` / `password` (override the admin email and both passwords with `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` before seeding). Or register your own company at `/register`.

## Docker

```bash
cp .env.example .env   # then set APP_KEY
docker compose up -d --build
```

The app runs on http://localhost:8000 and MySQL on host port 3307.
