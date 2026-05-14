# Employee Leave Management System

Laravel final project for managing employee leave requests, manager approvals, HR admin monitoring, leave balances, and reports.

## Team Members

- Add member name here
- Add member name here
- Add member name here

## Tech Stack

- Laravel 12
- Laravel Fortify authentication
- Blade templates
- Eloquent ORM
- MySQL for local development
- SQLite in-memory database for tests

## Main Roles

- Employee: file leave requests, view history, check balances, view own profile.
- Manager: review department/team leave requests, approve or reject with remarks, view team calendar.
- HR Admin: manage employees, departments, leave types, user activation, reports, calendar, and CSV export.

The database role value for HR Admin is `hr_admin`, while the route/controller folder is named `admin` for readability.

## Clean Project Structure

```text
app/
  Http/
    Controllers/
      Admin/
      Manager/
      Employee/
    Middleware/
    Requests/
  Models/
  Notifications/
database/
  migrations/
  seeders/
resources/
  views/
    admin/
    manager/
    employee/
    layouts/
routes/
  web.php
```

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

Update `.env` database values before running migrations.

## Default Login Credentials

You can log in using either email or employee ID.

| Role | Login | Password |
| --- | --- | --- |
| HR Admin | `HR-0001` or `hr@company.com` | `password` |
| Manager | `MGR-0004` or `manager@test.com` | `password` |
| Employee | `EMP-STAFF-01` or `staff@test.com` | `staffpassword123` |

## Feature Checklist

- [x] Fortify login, registration, and password reset routes
- [x] Role-based middleware for Employee, Manager, and HR Admin
- [x] Employee management CRUD
- [x] Department management
- [x] Leave type configuration CRUD
- [x] Employee leave application with balance validation
- [x] Manager approval workflow with remarks
- [x] In-app notifications through `system_notifications`
- [x] HR dashboard and department summaries
- [x] Leave balance report
- [x] Monthly leave calendar
- [x] CSV export for leave records and balances
- [x] Pagination on listing pages
- [x] Form Request validation classes
- [x] Eloquent model relationships

## Useful Commands

```bash
php artisan route:list --except-vendor
php artisan test
php artisan migrate:fresh --seed
```
