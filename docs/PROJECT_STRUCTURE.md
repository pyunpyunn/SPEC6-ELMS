# ELMS Project Structure

This project uses one Laravel application with shared models and separate role folders for controllers and views.

## Role Folders

- `app/Http/Controllers/Admin` handles HR Admin pages.
- `app/Http/Controllers/Manager` handles manager dashboards and approvals.
- `app/Http/Controllers/Employee` handles employee dashboard and leave filing.
- `resources/views/admin` contains HR Admin Blade pages.
- `resources/views/manager` contains Manager Blade pages.
- `resources/views/employee` contains Employee Blade pages.

## Shared Folders

- `app/Models` contains the shared Eloquent models.
- `app/Http/Requests` contains reusable Form Request validation.
- `app/Http/Middleware` contains role/profile middleware.
- `database/seeders` seeds users, departments, employees, leave types, balances, and sample requests.

## Route Groups

- `admin.*` routes use `/admin` and require `role:hr` or HR Admin access.
- `manager.*` routes use `/manager` and require `role:manager`.
- `employee.*` routes use `/employee` and require `role:employee`.

The HR Admin database role remains `hr_admin` because the original migrations use that enum value.
