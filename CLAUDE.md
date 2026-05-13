# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

RakTrek — web-based library management system. Users browse catalogue, borrow books, manage reading activity.

Stack: **Laravel 10** (backend) + **Inertia.js** + **Svelte 4** (frontend), **Laravel Sanctum** for API auth.

## Commands

```bash
# Backend
php artisan serve          # start dev server
php artisan migrate        # run migrations
php artisan migrate:fresh --seed  # reset + seed DB
php artisan tinker         # REPL
php artisan test           # run all tests
php artisan test --filter TestName  # run single test

# Frontend (run alongside artisan serve)
npm run dev    # Vite dev server with HMR
npm run build  # production build

# Code style
./vendor/bin/pint          # Laravel Pint (PHP code formatter)
```

## Architecture

### Request flow

```
Browser → Laravel Router (routes/web.php)
       → Controller (app/Http/Controllers/)
       → Inertia::render('PageName', $props)
       → resources/views/app.blade.php (shell)
       → Svelte page (resources/js/Pages/**/*.svelte)
```

`HandleInertiaRequests` middleware shares global props to every page: `appName`, `currentRouteName`, `user` (auth user or null).

### Backend structure

- **Controllers** in `app/Http/Controllers/` — thin, delegate to models. Return `Inertia::render()` or `redirect()`.
- **Form Requests** in `app/Http/Requests/` — all validation lives here, not in controllers.
- **Models** in `app/Models/` — core domain: `Book`, `Author`, `Genre`, `Borrowing`, `WaitingList`, `Review`, `BookImage`, `User`.

Key model relationships:
- `Book` → belongs to `Author`, many-to-many `Genre` (pivot `book_genre`), has many `BookImage`, `Review`, `Borrowing` (histories), `WaitingList` (reservations). Uses `SoftDeletes`.
- `Borrowing` → belongs to `User` and `Book`. Tracks `due_date` / `return_date`.
- `User` → has `role` field, supports `google_id` for OAuth. Uses `SoftDeletes`.

### Frontend structure

- Pages live in `resources/js/Pages/` — Inertia resolves by name, e.g. `Inertia::render('Auth/Login')` maps to `Pages/Auth/Login.svelte`.
- Shared components in `resources/js/Components/`.
- CSS in `resources/css/app.css` (single file, no preprocessor).
- Blade views in `resources/views/` are legacy layouts; Inertia uses only `app.blade.php` as the SPA shell.

### Auth

Session-based auth (not token). `LoginRequest::getCredential()` accepts email or username. `AuthController` uses `Auth::validate()` + `Auth::login()` — no Fortify/Breeze. API routes use `auth:sanctum`.

### Testing

PHPUnit with Feature and Unit suites. Tests use real DB (SQLite in-memory commented out — currently hits configured DB). `BCRYPT_ROUNDS=4` speeds up password hashing in tests.
