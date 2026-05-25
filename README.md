# Employee Leave Management System

Laravel final project for managing employee leave requests, manager approvals, HR monitoring, leave balances, reports, calendars, in-app notifications, and CSV exports.

## Team Members

- Barro, Niña Kathleen B.
- Arellano, Vinzon
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

Employee: 
    - file leave requests, 
    - view own leave history, 
    - check balances, 
    - view notifications, 
    - and update own profile.
Manager: 
    - review team/department leave requests, 
    - approve or reject with remarks, 
    - view team  calendar, 
    - view team members, 
    - and file own leave requests.
HR Admin: 
    - manage users, 
    - employees, 
    - departments, 
    - leave types, 
    - leave requests, 
    - reports, 
    - calendar, 
    - CSV exports, 
    - and own profile.

The database role value for HR Admin is `hr_admin`, while the URL/controller folder is named `admin` for readability.

## Default Login Credentials

You can log in using either the HR email or employee ID.

| Role      | Login                                      | Password   |
| --------- | ------------------------------------------ | ---------- |
| HR Admin  | `hr@company.com` or `HR-2000-001`          | `password` |
| Manager   | `manager@company.com` or `OPS-5000-001`    | `password` |
| Employee  | `employee@company.com` or `OPS-5001-001`   | `password` |

The seeders include one account per required role so the instructor can test HR, Manager, and Employee pages immediately.

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

For an existing local database, run `php artisan migrate` to add the latest `system_notifications.related_type` and `system_notifications.related_id` columns used by the notification settlement logic.

On native Windows, do not use `php artisan pail` because it requires the `pcntl` extension. To watch logs, use:

```powershell
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

## Feature Checklist

- [x] Fortify login, registration, and password reset routes
- [x] Role-based middleware for Employee, Manager, and HR Admin
- [x] Controller-based routes for strict MVC flow
- [x] Employee management CRUD with linked user accounts
- [x] Department management
- [x] Leave type configuration CRUD
- [x] Employee leave application with balance validation
- [x] Conditional proof upload based on leave type proof rules and `max_document_days`
- [x] Manager approval workflow with required remarks
- [x] In-app notifications through `system_notifications`
- [x] Notification counts ignore settled leave-request alerts after another approver acts on the same request
- [x] HR dashboard and department summaries
- [x] Individual leave balance report
- [x] Monthly leave calendar view
- [x] CSV export for leave records and leave balances
- [x] Pagination on listing pages
- [x] Custom compact pagination for HR/Admin notification listings
- [x] Form Request validation classes
- [x] Eloquent model relationships
- [x] Seeders for HR, departments, positions, leave types, and balances
- [x] Normalized employee department and position foreign keys

## Instructor Requirements Checklist

| Requirement                      | Status                                                                       |
| -------------------------------- | ---------------------------------------------------------------------------- |
| Laravel 12 or 13                 | Laravel 13 in `composer.json`                                                |
| Fortify or manual authentication | Laravel Fortify                                                              |
| Strict MVC                       | Routes point to controllers; models handlerelationships; Blade handles views |
| Eloquent ORM                     | App queries use Eloquent models and relationships                            |
| Migrations and seeders           | Present in `database/migrations` and `database/seders`                       |
| Model relationships              | Present in `app/Models`                                                      |
| Form Request validation          | Present in `app/Http/Requests`                                               |
| Blade layouts                    | Admin, Manager, and Employee layouts use `@yield` and `@section`             |
| Route grouping/naming            | Role route groups use prefixes and route names                               |
| Role middleware                  | `role`, `account.approved`, and `profile.complete` middleware                |
| Pagination                       | Listing pages paginate at 10 or more records                                 |
| Flash messages                   | Success, warning, error, and validation messages are shown in layouts        |

## Current Structure Notes

- HR/Admin Blade pages are consolidated under `resources/views/admin`. The duplicate `resources/views/hr` tree was removed.
- Shared HR/Admin report content lives in `resources/views/admin/reports/_content.blade.php`.
- Employee layout behavior, including sidebar collapse state, lives in `resources/views/layouts/employee.blade.php` and `public/employee-prototype.css`.
- Reusable HR-style pagination lives in `resources/views/vendor/pagination/hr.blade.php`.

## Key Documentation

- `DESIGN.md` - UI design system and visual specifications.
- `docs/LARAVEL_STRUCTURE_BEGINNER_GUIDE.md` - beginner-friendly Laravel folder, MVC, Eloquent, and ELMS implementation guide.
- `docs/FINAL_PROJECT_DOCUMENTATION.md` - system overview, ER diagram, route/controller map, and screenshot checklist.
- `docs/MODULE_PAGE_MAP.md` - technical defense guide for locating modules, pages, layouts, styling, routes, controllers, models, and requests.

## Useful Commands

```bash
php artisan route:list --except-vendor
php artisan test
php artisan migrate:fresh --seed
php artisan optimize:clear
```
