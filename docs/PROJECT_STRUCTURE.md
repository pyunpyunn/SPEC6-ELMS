# ELMS Project Structure

This project uses one Laravel application with shared models and separate role folders for controllers and views.

## Role Folders

- `app/Http/Controllers/Admin` contains thin HR Admin route controllers.
- `app/Http/Controllers/Hr/HrController.php` contains the shared HR Admin business logic used by those route controllers.
- `app/Http/Controllers/Manager` handles manager dashboards, approvals, team views, notifications, and manager self-service leave filing.
- `app/Http/Controllers/Employee` handles employee route entry points, with shared employee portal logic in `app/Http/Controllers/EmployeePortalController.php`.
- `resources/views/admin` contains all HR Admin Blade pages. The old duplicate `resources/views/hr` tree has been removed.
- `resources/views/manager` contains Manager Blade pages.
- `resources/views/employee` contains Employee Blade pages.
- `resources/views/layouts` contains shared layouts, including the active employee shell at `resources/views/layouts/employee.blade.php`.
- `resources/views/vendor/pagination/hr.blade.php` contains the compact pagination component used by HR/Admin and several manager pages.

## Shared Folders

- `app/Models` contains the shared Eloquent models.
- `app/Http/Requests` contains reusable Form Request validation.
- `app/Http/Middleware` contains role/profile middleware.
- `database/seeders` seeds users, departments, employees, leave types, balances, and sample requests.
- `public/hr-prototype.css`, `public/manager-portal.css`, and `public/employee-prototype.css` hold the active role-specific styling.

## Route Groups

- `admin.*` routes use `/admin` and require `role:hr` or HR Admin access.
- `manager.*` routes use `/manager` and require `role:manager`.
- `employee.*` routes use `/employee` and require `role:employee`.

The HR Admin database role remains `hr_admin` because the original migrations use that enum value.

## Current Cleanup Notes

- HR/Admin views are intentionally consolidated in `resources/views/admin`; do not recreate duplicate HR views under `resources/views/hr`.
- The active system is light-mode only. Dark-mode toggles and active dark theme rules were removed from production layouts and stylesheets.
- Notification counts use `SystemNotification::scopeUnreadActionable()` so settled leave-request notifications no longer keep badges visible for other approvers.
