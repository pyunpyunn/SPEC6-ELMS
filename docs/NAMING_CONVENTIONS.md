# Naming Conventions

## Routes
- HR route names use the `hr.` prefix, for example `hr.dashboard`, `hr.employees.index`, and `hr.reports.export`.
- Shared routes are `profile`, `notifications`, and Laravel Fortify auth routes.

## Controllers and Requests
- HR controller: `App\Http\Controllers\Hr\HrController`.
- HR validation requests live in `App\Http\Requests\Hr`.
- Request classes: `EmployeeRequest`, `DepartmentRequest`, `LeaveTypeRequest`, `ProfileRequest`.

## Models
- `User` owns authentication, role, and status.
- `Employee` stores company profile data and public employee ID.
- `Department` stores department setup and assigned manager.
- `LeaveType` stores leave policy configuration.
- `LeaveApplication` stores leave filings and HR/manager review data.
- `LeaveBalance` stores yearly allocations and usage.
- `SystemNotification` stores in-app alerts.

## Blade Views
- Shared HR layout: `resources/views/hr/layout.blade.php`.
- Main HR pages are grouped by module under `resources/views/hr`.
- Reusable create/edit fields use `partials/form.blade.php` inside each module folder.
- Prototype styling is loaded from `public/hr-prototype.css`, extracted from `kuan1.html`.

## CSS Classes
- `.card`, `.card-h`, `.card-b`: framed content blocks.
- `.filters`: compact filter rows.
- `.form`: responsive two-column forms.
- `.badge`: status and role labels.
- `.grid`, `.stats`, `.two`, `.cards`: responsive grid layouts.
- `.sidebar`, `.nav`, `.sb-foot`: navigation shell.
- `.sb-item`, `.sb-section`, `.sb-leave-balance`: prototype sidebar navigation and leave balance UI.
- `.full-cal-grid`, `.full-cal-dow`, `.full-cal-body`, `.cal-cell`, `.cal-event`: company calendar grid.
