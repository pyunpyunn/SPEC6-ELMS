# HR Module Naming Conventions

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

## Manager Module Naming Conventions

### Routes
- Manager route names use the `manager.` prefix.
- Manager URLs are grouped under the `/manager` prefix.
- Manager routes are protected by `role:manager` middleware.
- Resource-like route names follow Laravel-style naming:
  - `manager.dashboard`
  - `manager.requests.index`
  - `manager.requests.show`
  - `manager.requests.review`
  - `manager.calendar`
  - `manager.team`
  - `manager.my-leave`
  - `manager.my-leave.store`
  - `manager.notifications`
  - `manager.notifications.read`
  - `manager.profile`
  - `manager.profile.update`
  - `manager.profile.password`

### Controllers and Requests
- Manager controller namespace: `App\Http\Controllers\Manager`.
- Main manager controller: `App\Http\Controllers\Manager\ManagerController`.
- Manager validation requests live in `App\Http\Requests\Manager`.
- Manager request class names describe the action being validated:
  - `LeaveDecisionRequest` validates approve/reject decisions.
  - `StoreManagerLeaveRequest` validates manager personal leave filing.
- Controller methods use clear action names:
  - `dashboard`
  - `requests`
  - `showRequest`
  - `reviewRequest`
  - `calendar`
  - `team`
  - `myLeave`
  - `storeMyLeave`
  - `notifications`
  - `readNotification`
  - `profile`
  - `updateProfile`
  - `updatePassword`

### Blade Views
- Manager views are grouped under `resources/views/manager`.
- Main manager layout: `resources/views/manager/layout.blade.php`.
- Page view names use lowercase kebab-case where needed:
  - `dashboard.blade.php`
  - `requests.blade.php`
  - `request-show.blade.php`
  - `calendar.blade.php`
  - `team.blade.php`
  - `my-leave.blade.php`
  - `notifications.blade.php`
  - `profile.blade.php`

### CSS and UI
- Manager styling is loaded from `public/manager-portal.css`.
- Manager UI follows the `kuan1.html` LeaveFlow prototype style.
- Shared prototype-style classes used by Manager:
  - `.sidebar`, `.sb-nav`, `.sb-item`, `.sb-section`, `.sb-footer`
  - `.sb-user`, `.sb-avatar`, `.sb-leave-balance`, `.sb-balance-item`
  - `.header`, `.header-left`, `.header-right`
  - `.notif-wrap`, `.notif-btn`, `.notif-dropdown`
  - `.profile-area`, `.profile-avatar`, `.profile-dropdown`
  - `.card`, `.card-header`, `.card-body`, `.card-title`
  - `.stats-grid`, `.stat-card`, `.stat-value`, `.stat-label`
  - `.table-wrap`, `.badge`, `.status-pill`
  - `.full-cal-grid`, `.full-cal-dow`, `.full-cal-body`, `.cal-cell`, `.cal-event`
- Manager-specific UI classes:
  - `.dash-layout`: dashboard two-column layout.
  - `.right-panel`: dashboard side panel.
  - `.mini-cal`: dashboard mini calendar.
  - `.cal-legend`, `.cal-legend-item`: team calendar leave type legend filters.
  - `.profile-grid`: profile page two-column layout.

### Documentation
- Manager module documentation uses uppercase module guide naming:
  - `MANAGER_MODULE_GUIDE.md`
