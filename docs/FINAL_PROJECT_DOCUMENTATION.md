# Employee Leave Management System Documentation

## System Overview

The Employee Leave Management System is a Laravel 13 application for filing, approving, monitoring, and reporting employee leave requests.

Employees submit leave applications with leave type, date range, reason, and optional proof. The system validates requests against the employee's current-year leave balance. Managers review pending requests for employees in their team or department. HR Admins manage employee records, leave types, departments, user activation, reports, calendars, and CSV exports.

## Core Modules

| Module | Main Capability |
| --- | --- |
| Authentication and Roles | Fortify login, registration, password reset, active account checks, and role middleware |
| Employee Management | CRUD for employee records linked to user accounts |
| Department Management | HR management of departments and department managers |
| Leave Type Configuration | CRUD for leave types, annual allocation, approval, proof, active, and compensable settings |
| Leave Application | Employee, manager, and HR self-service leave filing with balance validation |
| Leave Approval Workflow | Manager and HR approve or reject requests with remarks |
| Reports and Dashboard | HR summaries, balances, department reports, calendar, and CSV export |
| Notifications | In-app notices for registration, activation, leave submission, and leave status changes |

## ER Diagram

```mermaid
erDiagram
    USERS ||--o| EMPLOYEES : "has profile"
    USERS }o--|| DEPARTMENTS : "assigned department"
    USERS }o--|| POSITIONS : "assigned position"
    USERS ||--o{ SYSTEM_NOTIFICATIONS : "receives"
    USERS ||--o{ LEAVE_APPLICATIONS : "reviews"
    DEPARTMENTS ||--o{ EMPLOYEES : "contains"
    DEPARTMENTS ||--o{ POSITIONS : "offers"
    EMPLOYEES ||--o{ EMPLOYEES : "manages"
    EMPLOYEES ||--o{ LEAVE_APPLICATIONS : "files"
    EMPLOYEES ||--o{ LEAVE_BALANCES : "owns"
    POSITIONS ||--o{ EMPLOYEES : "assigned"
    LEAVE_TYPES ||--o{ LEAVE_APPLICATIONS : "classifies"
    LEAVE_TYPES ||--o{ LEAVE_BALANCES : "allocates"
```

## Important Tables

| Table                  | Purpose |
| ---                    | --- |
| `users`                | Login credentials, role, status, pending employee ID, department, and position |
| `employees`            | Employee profile, company employee ID, department, position, manager, gender, contact info, hire date, and status |
| `departments`          | Department code, name, description, manager, and active flag |
| `positions`            | Department-specific positions |
| `leave_types`          | Leave type name, allocation, approval flag, proof rules, compensable flag, and active flag |
| `leave_balances`       | Employee yearly allocation, used days, and computed remaining days |
| `leave_applications`   | Filed leave requests, dates, total days, status, remarks, reviewer, and proof path |
| `system_notifications` | In-app notifications and read status |

## Routes and Controllers

| Area          | Route Prefix          | Main Controller Files |
| ---           | ---                   | --- |
| HR Admin      | `/admin`             | `app/Http/Controllers/Admin/*`, delegated business logic in `app/Http/Controllers/Hr/HrController.php` |
| Manager       | `/manager`           | `app/Http/Controllers/Manager/ManagerController.php`, `LeaveApprovalController.php` |
| Employee      | `/employee`          | `app/Http/Controllers/Employee/*`, shared logic in `EmployeePortalController.php` |
| Auth          | `/login`, `/register`, `/forgot-password` | Fortify setup in `app/Providers/FortifyServiceProvider.php` |
| Notifications | `/notifications/feed` | Closure route in `routes/web.php` using `SystemNotification` |

## Screenshots to Capture for Submission

Place final screenshots in `docs/screenshots/` before submission.

| Screenshot | Login As | Suggested URL |
| --- | --- | --- |
| Login page | Guest | `/login` |
| HR dashboard | HR Admin | `/admin/dashboard` |
| Employee directory | HR Admin | `/admin/employees` |
| Leave type configuration | HR Admin | `/admin/leave-types` |
| Master request log | HR Admin | `/admin/requests` |
| HR reports | HR Admin | `/admin/reports` |
| HR calendar | HR Admin | `/admin/calendar` |
| Manager dashboard | Manager | `/manager/dashboard` |
| Manager approval inbox | Manager | `/manager/approvals` |
| Employee dashboard | Employee | `/employee/dashboard` |
| Employee leave filing/history | Employee | `/employee/leaves` |
| Employee reports | Employee | `/employee/reports` |

## Default HR Account

| Field | Value |
| --- | --- |
| Name | Kathleen Barro |
| Email | `hr@company.com` |
| Employee ID | `HR-2000-001` |
| Password | `password` |
| Role | `hr_admin` |
| Gender | `female` |

## Technical Notes for Defense

- Routes are grouped by role in `routes/web.php`.
- Role checking is handled by `app/Http/Middleware/RoleMiddleware.php`.
- Active account enforcement is handled by `EnsureAccountApproved.php`.
- Form validation is handled by classes in `app/Http/Requests`.
- Relationships are defined inside `app/Models`.
- Leave approval updates used leave balance inside database transactions.
- In-app notifications are created through `App\Models\SystemNotification`.



ERD
-------------------------------------------------------------------------------------------------------
Your 8 Business Tables ✅
Know these cold — these are yours:

Table	                 Purpose
users	                 Accounts (HR Admin, Manager, Employee roles)
employees	             Employee data linked to users
departments	             Org structure
positions	             Job titles per department
leave_types	             Leave categories you configure
leave_applications	     Leave requests (core feature)
leave_balances	         Annual leave balance tracking per employee
system_notifications	 In-app notifications

All 14 foreign keys = valid connections. No dangling references.

The 8 "Framework Tables" — Be Ready to Explain ⚠️
If asked why these exist, don't get caught off-guard:

Table	Answer
migrations	"Tracks which database migrations have run — Laravel's internal audit log."
sessions, password_reset_tokens	"Auto-generated by SESSION_DRIVER=database and Fortify in our .env."
cache, cache_locks, jobs, job_batches, failed_jobs	"Standard Laravel queuing/caching infrastructure. Present because CACHE_STORE=database and QUEUE_CONNECTION=database in our config — not actively used by our leave features."
Key line: "These are Laravel framework tables auto-generated by our migrations based on .env configuration — not part of our business logic."

One Red Flag to Address Proactively 🚨
Your employees table has redundant columns:

✅ department_id (FK) — correct
❌ department (text) — redundant legacy column
✅ position_id (FK) — correct
❌ position (text) — redundant legacy column
If someone asks: "The text columns are legacy from an earlier schema version. The actual relationships are through the foreign keys (department_id, position_id). Should've been removed in a cleanup migration — would fix in production."




ROUTES
-------------------------------------------------------------------------------------------------------
LMS Routing System Explained
Your routing follows a role-based architecture:

Route Structure Overview

Public Routes (No Auth)
├── GET / → Login or Home
└── Auth Routes (login, register, password reset)

Protected Routes (Requires Auth + Profile Complete)
├── /home → Role-based redirect
├── /positions-by-department/{id} → JSON API
├── /notifications → Feed system
│
├── HR Admin Routes (/admin prefix)
│   ├── Dashboard, Users, Employees
│   ├── Departments, Leave Types
│   ├── Leave Requests Review
│   ├── Reports & Calendar
│   └── My Leave, Profile
│
├── Manager Routes (/manager prefix)
│   ├── Dashboard
│   ├── Leave Approvals
│   ├── Team View, Calendar
│   ├── My Leave, Profile
│   └── Notifications
│
└── Employee Routes (/employee prefix)
    ├── Dashboard
    ├── Leave Applications (CRUD)
    ├── Reports & Balances
    ├── Notifications
    └── Profile


How Routing Works - Step by Step
1. User visits /home:

        <?php
        Route::middleware('auth')->get('/home', function () {
            return match ($user->getAccessLevel()) {
                'hr' => redirect()->route('admin.dashboard'),      // → /admin/dashboard
                'manager' => redirect()->route('manager.dashboard'), // → /manager/dashboard
                default => redirect()->route('employee.dashboard'),  // → /employee/dashboard
            };
        })->name('home');

2. Request goes to appropriate controller (based on role):

        HR User → App\Http\Controllers\Admin\DashboardController
        Manager → App\Http\Controllers\Manager\DashboardController
        Employee → App\Http\Controllers\Employee\DashboardController

3. Controller renders appropriate view:

        HR Admin → resources/views/admin/dashboard.blade.php
        Manager → resources/views/manager/dashboard.blade.php
        Employee → resources/views/employee/dashboard.blade.php

Key Middleware Applied
        Middleware	        Purpose
        auth	            User must be logged in
        account.approved	User status must be 'active'
        profile.complete	User profile must be complete
        role:hr	            Only users with 'hr' role
        role:manager	    Only users with 'manager' role
        role:employee	    Only users with 'employee' role

Example: HR Leave Type Management Route
<?php
Route::resource('leave-types', AdminLeaveTypeController::class)
    ->parameters(['leave-types' => 'leaveType'])
    ->except(['show']);

Auto-generates these 5 routes:
HTTP Method	      URL	                           Controller Method	Name
GET	              /admin/leave-types	           index()	            admin.leave-types.index
GET	              /admin/leave-types/create	       create()	            admin.leave-types.create
POST	          /admin/leave-types	           store()	            admin.leave-types.store
GET	              /admin/leave-types/{id}/edit	   edit()	            admin.leave-types.edit
PUT            	  /admin/leave-types/{id}	       update()	            admin.leave-types.update
(No show() because .except(['show']))

Route Protection Pattern
Every role-specific route group requires:
1. Authentication (auth middleware)
2. Account Active (account.approved)
3. Correct Role (role:hr|manager|employee)

For example:
        <?php
        Route::middleware(['account.approved', 'role:employee'])
            ->prefix('employee')
            ->group(function () {
                // Only active employees can access these routes
            });
This ensures a regular employee can't manually navigate to /admin/dashboard even if they guess the URL.