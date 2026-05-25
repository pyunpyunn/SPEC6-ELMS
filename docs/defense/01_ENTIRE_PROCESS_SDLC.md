# Defense Documentation 1: Entire Process and SDLC

This document explains how the Employee Leave Management System was built from the first commit up to the final commit. Use this as the main story for the project defense.

## 1. Project Summary

The system is an Employee Leave Management System built with Laravel 13. It lets:

- Employees register, log in, file leave requests, view leave history, view balances, and receive notifications.
- Managers review leave requests from their department/team, approve or reject with remarks, view team leaves, and receive notifications.
- HR Admins manage users, employees, departments, leave types, requests, reports, dashboards, calendars, CSV exports, and approvals.

The project follows the instructor requirements:

- Laravel 13
- Fortify authentication
- MVC architecture
- Eloquent ORM
- Migrations and seeders
- Model relationships
- Form Request validation
- Blade layouts
- Route groups and named routes
- Role-based middleware
- Pagination
- Flash messages

## 2. Team Delegation

The group divided the work into two modules per member.

| Member | Defense Ownership | Modules |
| --- | --- | --- |
| Kath | HR/Admin-related leave operations | Module 3: Leave Application, Module 4: Leave Approval Workflow |
| Adriane | Manager/reporting/auth flow | Module 5: Reports and Dashboards, Module 6: User Authentication and Roles |
| Vinzon | Employee/admin configuration records | Module 1: Employee Management, Module 2: Leave Type Configuration |

Important note: some Laravel files are shared. For example, `HrController` contains HR/Admin features that support several modules.

## 3. SDLC Overview

### Phase 1: Planning

We started by reading the requirements:

- Build a Laravel final project.
- Use CRUD operations and data processing.
- Use MVC strictly.
- Use Laravel authentication.
- Use Eloquent, migrations, seeders, relationships, Form Requests, Blade templates, route groups, middleware, pagination, and flash messages.
- Implement the Employee Leave Management System with six modules.

The required modules became the project backlog:

1. Employee Management
2. Leave Type Configuration
3. Leave Application
4. Leave Approval Workflow
5. Reports and Dashboards
6. User Authentication and Roles

We also decided on three user roles:

- `employee`
- `manager`
- `hr_admin`

### Phase 2: Analysis

We identified the main data needed by the system:

- Users need accounts, roles, and approval status.
- Employees need personal profile data, department, position, manager, contact info, and date hired.
- Departments and positions organize employees.
- Leave types define rules such as allocation, approval requirement, proof requirement, and compensable flag.
- Leave applications store filed requests.
- Leave balances track annual allocation and used days.
- Notifications inform users of account validation, activation, leave submissions, and status changes.

This analysis produced the main business tables:

| Table | Purpose |
| --- | --- |
| `users` | Login account, role, status, pending employee ID |
| `employees` | Employee profile linked to a user |
| `departments` | Company departments |
| `positions` | Job titles per department |
| `leave_types` | Leave categories and leave rules |
| `leave_applications` | Filed leave requests |
| `leave_balances` | Annual balance per employee and leave type |
| `system_notifications` | In-app notifications |

### Phase 3: Design

The system was designed around MVC.

| Layer | What We Put There |
| --- | --- |
| Model | Database tables, relationships, computed properties |
| View | Blade pages for HR/Admin, Manager, Employee, Auth |
| Controller | Request handling, page loading, CRUD flow, transactions |
| Form Request | Validation rules |
| Middleware | Role checks, active account checks, complete profile checks |
| Migrations | Database structure |
| Seeders | Demo data and default accounts |

Main relationships:

- `User hasOne Employee`
- `Employee belongsTo User`
- `Employee belongsTo Department`
- `Employee belongsTo Position`
- `Employee hasMany LeaveApplication`
- `Employee hasMany LeaveBalance`
- `LeaveApplication belongsTo Employee`
- `LeaveApplication belongsTo LeaveType`
- `LeaveApplication belongsTo User as reviewer`
- `LeaveType hasMany LeaveApplication`
- `LeaveType hasMany LeaveBalance`
- `User hasMany SystemNotification`

### Phase 4: Implementation

Implementation happened in layers:

1. Laravel project setup
2. Fortify authentication setup
3. Database migrations
4. Models and relationships
5. Seeders
6. HR/Admin employee and leave type CRUD
7. Employee leave filing and balance validation
8. Manager approval workflow
9. Notifications
10. Reports, dashboard, calendar, CSV export
11. UI cleanup and Blade layouts
12. Testing and final cleanup

### Phase 5: Testing

Testing was done using Laravel feature tests and manual checks.

Important test files:

- `tests/Feature/RoleBasedLoginTest.php`
- `tests/Feature/RolePageSmokeTest.php`
- `tests/Feature/NotificationFlowTest.php`
- `tests/Feature/LeaveConfigurationRulesTest.php`
- `tests/Feature/GenderLeaveVisibilityTest.php`

Final verification command:

```bash
php artisan test
```

Final result:

```text
18 tests passed
77 assertions passed
```

### Phase 6: Deployment/Presentation Preparation

Before the final commit:

- Route closures were moved into controllers for stricter MVC.
- Raw-style report queries were replaced with Eloquent/collection processing.
- Listing pages were adjusted to paginate at least 10 records.
- Flash messages were added consistently.
- Employee department and position were normalized to foreign keys.
- Demo accounts were added for HR, Manager, and Employee.
- Documentation and README were updated.

Final commit:

```text
e63030c final commit
```

## 4. Actual Git Timeline From First Commit

This timeline is based on `git log --reverse --oneline`.

| Date | Commit | What Happened |
| --- | --- | --- |
| 2026-05-01 | `be3af4a` SPEC6 Final project set up | Initial Laravel project setup. |
| 2026-05-01 | `97c1045` gitignore update | Ignored unnecessary/generated files. |
| 2026-05-01 | `4084b52` login and home page started | Started authentication pages and home redirect concept. |
| 2026-05-11 | `0c2f923` hr-changes | HR/Admin features began. |
| 2026-05-12 | `78968cb` emp-dev | Employee portal work began. |
| 2026-05-12 | `e2d2b86` hr | More HR functionality. |
| 2026-05-12 | `aea6836` fixed merge conflicts | HR and employee branches were integrated. |
| 2026-05-13 | `4c6bbb9` manager | Manager module started. |
| 2026-05-13 | `f2d3e07` hr and emp merge | HR and employee work merged. |
| 2026-05-13 | `144209a` manager-dev merge | Manager work merged into integration. |
| 2026-05-14 | `bc19949` department-position alignment | Added structured department and position handling. |
| 2026-05-16 | `a4c894f` initial run update | Improved working project state. |
| 2026-05-16 | `30c1953` notifications/upload/weekend fixes | Added notification fixes, proof upload behavior, weekend restrictions. |
| 2026-05-17 | `98734f3`, `2f9cbf8`, `1a90982` bug fixes | Fixed employee and leave-related bugs. |
| 2026-05-18 | `d6454e6`, `d4cdb5f`, `22ce7e9` HR/UI cleanup | Updated HR dev, styling, and cleaned files. |
| 2026-05-18 | `44f4ae8` employee fixes PR | Employee fixes merged. |
| 2026-05-18 | `a444aa4` manager UI/function fixes | Manager module improved. |
| 2026-05-19 | `0dbdcf5` WIP before integration | Saved local work before group integration. |
| 2026-05-19 | `81099e8`, `365e61e` merges/conflicts | Merged groupmate work and resolved conflicts. |
| 2026-05-19 | `5e2a65f`, `ce33569` login/root route cleanup | Restored login and root redirect behavior. |
| 2026-05-19 | `df7a937`, `2171cce`, `06fd608` cleanup/main update | Cleaned tracked artifacts and merged into main. |
| 2026-05-20 | `1c8e97b` prototype/docs update | Added additional prototype or documentation work. |
| 2026-05-22 | `86b0395`, `2f6693d` portal/notification cleanup | Final portal cleanup and notification fixes. |
| 2026-05-23 | `e63030c` final commit | Final MVC cleanup, docs, seeders, normalization, tests. |

## 5. Local Setup From Zero

These are the steps to run the system locally.

### Step 1: Clone the repository

```bash
git clone https://github.com/pyunpyunn/SPEC6-ELMS.git
cd SPEC6-ELMS
```

### Step 2: Install PHP packages

```bash
composer install
```

This reads `composer.json` and installs Laravel, Fortify, PHPUnit, and other PHP packages into `vendor/`.

### Step 3: Install frontend packages

```bash
npm install
```

This reads `package.json` and installs Vite/Tailwind tooling into `node_modules/`.

### Step 4: Create environment file

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### Step 5: Set database credentials

Open `.env` and update:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elms_db
DB_USERNAME=root
DB_PASSWORD=
```

The database name can be different, but it must exist in MySQL before migration.

### Step 6: Generate Laravel app key

```bash
php artisan key:generate
```

The app key is needed for encryption, sessions, and secure Laravel behavior.

### Step 7: Run migrations and seeders

```bash
php artisan migrate:fresh --seed
```

This creates all tables and inserts default demo data.

### Step 8: Start backend server

```bash
php artisan serve
```

Default URL:

```text
http://127.0.0.1:8000
```

### Step 9: Start frontend dev server

In another terminal:

```bash
npm run dev
```

### Step 10: Log in with demo accounts

| Role | Email Login | Employee ID Login | Password |
| --- | --- | --- | --- |
| HR Admin | `hr@company.com` | `HR-2000-001` | `password` |
| Manager | `manager@company.com` | `OPS-5000-001` | `password` |
| Employee | `employee@company.com` | `OPS-5001-001` | `password` |

## 6. High-Level Request Flow

### Guest Login Flow

1. User visits `/`.
2. `HomeController@landing` shows the login page if the user is not logged in.
3. Fortify handles `/login`.
4. `FortifyServiceProvider` checks email or employee ID.
5. If login is valid, Fortify redirects to `/home`.
6. `HomeController@redirectByRole` sends user to the correct dashboard:
   - HR Admin: `/admin/dashboard`
   - Manager: `/manager/dashboard`
   - Employee: `/employee/dashboard`

### Employee Leave Filing Flow

1. Employee opens `/employee/leaves`.
2. Employee submits leave form.
3. Route calls `Employee\LeaveApplicationController@store`.
4. Controller delegates to `EmployeePortalController@storeLeave`.
5. `StoreLeaveApplicationRequest` validates fields.
6. Controller checks leave type, gender visibility, dates, duplicate leaves, proof requirement, and balance.
7. A database transaction creates the leave application.
8. If auto-approved, used days are incremented.
9. If pending, manager and HR receive notifications.
10. Employee is redirected with a flash success message.

### Manager Approval Flow

1. Manager opens `/manager/approvals`.
2. Manager views pending requests from their department/team.
3. Manager clicks approve or reject.
4. Route calls `LeaveApprovalController@approve` or `reject`.
5. Controller delegates to `ManagerController@reviewRequest`.
6. `Manager\LeaveDecisionRequest` validates status and remarks.
7. Transaction locks the balance row if approval.
8. If approved, used days are incremented.
9. Leave status, remarks, reviewer, and review time are saved.
10. Notifications are sent to employee and HR.

### HR Reporting Flow

1. HR opens `/admin/reports`.
2. `ReportController@index` loads filters and page data.
3. JavaScript fetches report JSON routes:
   - `/admin/reports/yearly-compensation`
   - `/admin/reports/individual-balance`
4. Controller calculates summaries using Eloquent data.
5. HR can export CSV through report export routes.

## 7. Main Folder Structure

```text
app/
  Actions/Fortify/          Fortify registration/password actions
  Http/Controllers/         MVC controllers
  Http/Middleware/          Role/account/profile middleware
  Http/Requests/            Form Request validation
  Models/                   Eloquent models and relationships
  Providers/                Fortify and app bootstrapping

database/
  migrations/               Table definitions and schema changes
  seeders/                  Demo/default data

resources/views/
  admin/                    HR/Admin Blade pages
  manager/                  Manager Blade pages
  employee/                 Employee Blade pages
  auth/                     Login/register pages
  layouts/                  Shared layouts

routes/
  web.php                   Web routes and route groups

tests/
  Feature/                  Feature tests for login, pages, notifications, rules
```

## 8. HOW WE DID THE MVC

> We used MVC by keeping database logic and relationships inside Eloquent models, request handling inside controllers, validation inside Form Request classes, and UI inside Blade views. Routes only map URLs to controllers and are grouped by role. We avoided placing business logic directly in Blade templates.

Examples:

- Model: `app/Models/LeaveApplication.php`
- Controller: `app/Http/Controllers/EmployeePortalController.php`
- View: `resources/views/employee/leaves/index.blade.php`
- Request validation: `app/Http/Requests/StoreLeaveApplicationRequest.php`
- Route: `routes/web.php`

## 9. HOW WE DID THE ELOQUENT

> We used Eloquent ORM for database operations. For example, leave applications are created with `LeaveApplication::create()`, balances are loaded with `LeaveBalance::where(...)`, and relationships like `$leaveApplication->employee`, `$employee->leaveBalances`, and `$leaveType->applications()` are defined in models.

## 10. HOW WE DID THE SECURITY

Security points:

- Fortify handles login, registration, password reset, and password hashing.
- Passwords are hashed using Laravel's hashing.
- Middleware protects role routes.
- Pending users cannot access modules until HR activates them.
- Form Requests validate all important forms.
- Route model binding plus ownership checks protect leave viewing and notification reading.

## 11. HOW WE MADE SURE THAT THERE WAS DATA INTEGRITY

Data integrity points:

- Foreign keys connect users, employees, departments, positions, leave types, applications, and balances.
- Leave approval uses database transactions.
- Balance rows are locked with `lockForUpdate()` during approval/filing to avoid incorrect used days.
- Leave request validation checks remaining balance before saving.
- Seeders create consistent demo records.

## 12. DEMONSTRATION FLOW

1. Log in as HR Admin.
2. Show HR dashboard.
3. Show employee management CRUD.
4. Show leave type configuration.
5. Show reports and CSV export.
6. Log out.
7. Log in as Employee.
8. Show employee dashboard and leave balances.
9. File a leave request.
10. Log out.
11. Log in as Manager.
12. Show approval inbox.
13. Approve/reject with remarks.
14. Show notification/status update.

