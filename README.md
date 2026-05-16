# Employee Leave Management System

Laravel final project for managing employee leave requests, manager approvals, HR monitoring, leave balances, reports, calendars, and CSV exports.

## Team Members

- Arellano, Vinzon
- Barro, Nina Kathleen
- Montano, Adriane

## Tech Stack

- Laravel 13
- PHP 8.3
- Laravel Fortify authentication
- Blade templates
- Eloquent ORM
- MySQL for local development
- SQLite in-memory database for tests
- Vite and Tailwind build tooling

## Main Roles

- Employee: file leave requests, view own leave history, check balances, view notifications, and update own profile.
- Manager: review team/department leave requests, approve or reject with remarks, view team calendar, view team members, and file own leave requests.
- HR Admin: manage users, employees, departments, leave types, leave requests, reports, calendar, CSV exports, and own profile.

The database role value for HR Admin is `hr_admin`, while the URL/controller folder is named `admin` for readability.

## Default Login Credentials

You can log in using either the HR email or employee ID.

| Role | Login | Password |
| --- | --- | --- |
| HR Admin | `hr@company.com` or `HR-2000-001` | `password` |

The project intentionally seeds only one HR account so HR can create or activate all employee and manager accounts from a clean database.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
npm run dev
```

Update `.env` database values before running migrations.

On native Windows, do not use `php artisan pail` because it requires the `pcntl` extension. To watch logs, use:

```powershell
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

## Feature Checklist

- [x] Fortify login, registration, and password reset routes
- [x] Role-based middleware for Employee, Manager, and HR Admin
- [x] Employee management CRUD with linked user accounts
- [x] Department management
- [x] Leave type configuration CRUD
- [x] Employee leave application with balance validation
- [x] Manager approval workflow with required remarks
- [x] In-app notifications through `system_notifications`
- [x] HR dashboard and department summaries
- [x] Individual leave balance report
- [x] Monthly leave calendar view
- [x] CSV export for leave records and leave balances
- [x] Pagination on listing pages
- [x] Form Request validation classes
- [x] Eloquent model relationships
- [x] Seeders for HR, departments, positions, leave types, and balances

## Key Documentation

- `DESIGN.md` - UI design system and visual specifications.
- `docs/FINAL_PROJECT_DOCUMENTATION.md` - system overview, ER diagram, route/controller map, and screenshot checklist.
- `docs/MODULE_PAGE_MAP.md` - technical defense guide for locating modules, pages, layouts, styling, routes, controllers, models, and requests.

## Useful Commands

```bash
php artisan route:list --except-vendor
php artisan test
php artisan migrate:fresh --seed
php artisan optimize:clear
```
