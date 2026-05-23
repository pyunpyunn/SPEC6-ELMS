# ELMS — Complete Table Relationship Reference
**For Technical Defense | Every table, every relationship, fully explained**

---

# HOW TO READ THIS DOCUMENT

For each table you will find:
- **What the table is** — its purpose in plain English
- **Its own columns** — what each column stores
- **Relationships it owns (Foreign Keys)** — connections going OUT from this table
- **Relationships pointing TO it** — connections coming IN from other tables
- **Eloquent model methods** — the actual method names in your Laravel models
- **Defense answer** — what to say if your instructor asks about it

---

---

# TABLE 1 — `users`

## What this table is
The `users` table is the authentication table. Every person who can log in to the system — whether they are an employee, manager, or HR admin — has one row here. It stores login credentials, the assigned role, and the account status. It is the central identity table of the entire system.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. Every user has a unique ID. |
| `name` | The user's display name used during registration. |
| `email` | The email address used for login and password reset. Must be unique. |
| `email_verified_at` | Timestamp of email verification. Left null if email verification is not enforced. |
| `password` | The bcrypt-hashed password. Never stored as plain text — Laravel hashes it automatically. |
| `two_factor_secret` | Stores the two-factor authentication secret if 2FA is enabled via Fortify. Not actively used in your system. |
| `two_factor_recovery_codes` | Backup codes for 2FA. Also not actively used. |
| `two_factor_confirmed_at` | Timestamp for when 2FA was confirmed. Not actively used. |
| `role` | The user's system role. Enum values: `employee`, `manager`, `hr_admin`. Defaults to `employee`. This is what RoleMiddleware reads to decide access. |
| `status` | The account activation status. Enum values: `pending`, `active`, `inactive`. Newly registered users start as `pending` until HR activates them. |
| `pending_employee_id` | The company employee ID the user types during registration (e.g., `IT-3002-001`). HR uses this to match the registration to the correct employee record before activating. |
| `department_id` | Foreign key to `departments`. Stores which department this user belongs to. Used for quick lookups without joining through `employees`. |
| `position_id` | Foreign key to `positions`. Stores which position this user holds. Also a quick-access column. |
| `remember_token` | Laravel's standard "remember me" token for persistent login sessions. |
| `created_at` / `updated_at` | Laravel timestamps — automatically managed. |

## Foreign keys owned by `users` (going OUT)

### FK 1: `users.department_id` → `departments.id`
**What it means:** Each user is assigned to one department. This is a direct shortcut so the system can find a user's department without joining through the `employees` table. A user can only belong to one department at a time.
**Relationship type:** Many-to-One (many users can belong to the same department).
**Eloquent method in `User` model:** `belongsTo(Department::class)`
**Where it is used:** When loading a manager's portal, the system uses `auth()->user()->department_id` to scope which employees appear in their team list.

### FK 2: `users.position_id` → `positions.id`
**What it means:** Each user holds one position. Like `department_id`, this is a convenience column that avoids joining through `employees` every time you need to know what position a user holds.
**Relationship type:** Many-to-One.
**Eloquent method in `User` model:** `belongsTo(Position::class)`
**Where it is used:** Displayed on profile pages and used in the HR employee verification flow.

## Relationships pointing TO `users` (coming IN)

### From `employees.user_id`
One employee profile is linked back to one user account. The `employees` table points to `users` to say "this employee profile belongs to this login account."

### From `departments.manager_user_id`
A department can have a manager, and that manager is a user. The `departments` table points to `users` to record who the department manager is.

### From `leave_applications.reviewed_by`
When a manager or HR approves or rejects a leave request, their user ID is stored in `reviewed_by`. The `leave_applications` table points to `users` here.

### From `system_notifications.user_id`
Every in-app notification is addressed to a specific user. The `system_notifications` table points to `users` to know who receives each notification.

### From `sessions.user_id`
Laravel's session driver stores the logged-in user's ID in the `sessions` table to track active sessions.

---

---

# TABLE 2 — `employees`

## What this table is
The `employees` table stores the professional profile of each person in the organization. While `users` handles login and access, `employees` handles HR data — department assignment, position, manager, date hired, contact info, and employment status. Every activated user account has exactly one linked employee record. This is the most connected table in the entire system.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `employee_id` | The company-assigned employee ID in `DEPT-POSITIONID-COUNT` format, e.g. `IT-3002-001`. Displayed publicly on profile pages and used during registration matching. |
| `user_id` | Foreign key to `users`. Links this employee profile to the login account. NOT NULL — every employee must have a user account. |
| `department_id` | Foreign key to `departments`. Which department this employee belongs to. |
| `position_id` | Foreign key to `positions`. What position this employee holds. |
| `manager_id` | Foreign key to `employees` (self-referencing). Points to another employee who is this person's direct manager. NULL if the employee has no assigned manager. |
| `first_name` | Employee's first name. |
| `last_name` | Employee's last name. |
| `gender` | Employee's gender. Used by leave types like Maternity and Paternity that are gender-restricted. |
| `date_hired` | The date the employee was hired. Required. |
| `contact_info` | General contact information field. |
| `phone` | Employee's phone number. |
| `address` | Employee's address. |
| `daily_rate` | The employee's daily salary rate. Used to calculate yearly compensation estimates for compensable unused leave in the Reports module. Defaults to 1000.00. |
| `employment_status` | Whether the employee is `active` or `inactive`. Inactive employees cannot log in and are excluded from most queries. |

## Foreign keys owned by `employees` (going OUT)

### FK 1: `employees.user_id` → `users.id`
**What it means:** This employee profile belongs to this user account. It is a one-to-one link. When HR activates a pending user, the system creates the employee record and sets this foreign key.
**Relationship type:** One-to-One (one employee = one user).
**Eloquent method in `Employee` model:** `belongsTo(User::class)`
**Where it is used:** Everywhere the system needs to go from an employee profile to their login details — profile pages, notifications, approval lookups.

### FK 2: `employees.department_id` → `departments.id`
**What it means:** This employee is assigned to this department. When HR creates or edits an employee, they pick a department from the dropdown and this column is set.
**Relationship type:** Many-to-One (many employees can be in the same department).
**Eloquent method in `Employee` model:** `belongsTo(Department::class, 'department_id')` — aliased as `departmentRecord`. The model also has a `$employee->department` accessor for easy display in Blade.
**Where it is used:** Department filtering on the master request log, team scoping for managers, department summary cards on the HR dashboard.

### FK 3: `employees.position_id` → `positions.id`
**What it means:** This employee holds this position. Positions are department-specific, so an IT Engineer position only exists under the IT department.
**Relationship type:** Many-to-One.
**Eloquent method in `Employee` model:** `belongsTo(Position::class, 'position_id')` — aliased as `positionRecord`.
**Where it is used:** Profile display, employee directory, employee ID generation.

### FK 4: `employees.manager_id` → `employees.id` *(Self-referencing)*
**What it means:** This is a self-join. An employee can have a manager, and that manager is also an employee in the same table. The `manager_id` column stores the `id` of the manager's row in the same `employees` table. This is how the system knows who reports to whom.
**Relationship type:** Many-to-One, self-referencing (many employees can report to the same manager).
**Eloquent method in `Employee` model:** `belongsTo(Employee::class, 'manager_id')` — aliased as `manager`.
**Inverse method:** `hasMany(Employee::class, 'manager_id')` — aliased as `subordinates` or `team`.
**Where it is used:** The Manager module uses this to scope the approval inbox — it finds all employees where `manager_id = auth()->user()->employee->id`.

## Relationships pointing TO `employees` (coming IN)

### From `leave_applications.employee_id`
Every leave request is filed by an employee. The `leave_applications` table points to `employees` to record who filed the request.

### From `leave_balances.employee_id`
Every leave balance row belongs to one employee for one year. The `leave_balances` table points to `employees` to know whose balance it is.

### From `employees.manager_id` (self-reference, described above)
Other employee rows point back to this table to record the manager relationship.

---

---

# TABLE 3 — `departments`

## What this table is
The `departments` table stores the organizational units of the company — HR, IT, Finance, Operations, etc. Each department has a short code, a name, an optional description, a manager, and an active flag. HR creates and manages these from the `/admin/departments` page.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `code` | Short department code, e.g. `HR`, `IT`, `FIN`, `OPS`. Displayed as a badge in employee listings. |
| `name` | Full department name, e.g. "Information Technology". |
| `description` | Optional description of the department's function. |
| `manager_user_id` | Foreign key to `users`. Points to the user account of the person designated as this department's manager. Nullable — a department may not have a manager assigned yet. |
| `is_active` | Whether the department is active. Inactive departments are hidden from dropdowns. |

## Foreign keys owned by `departments` (going OUT)

### FK 1: `departments.manager_user_id` → `users.id`
**What it means:** A department has one designated manager, and that manager is identified by their user account ID. When HR sets or changes a department manager from the Departments page, this column is updated.
**Relationship type:** Many-to-One (many departments could theoretically have the same user as manager, though in practice each department has one).
**Eloquent method in `Department` model:** `belongsTo(User::class, 'manager_user_id')` — aliased as `managerUser`.
**Where it is used:** On the departments listing, to display who manages each department. Also used when scoping team members for a manager.

## Relationships pointing TO `departments` (coming IN)

### From `employees.department_id`
All employees assigned to this department point here.

### From `positions.department_id`
All positions that belong to this department point here.

### From `users.department_id`
User accounts also store their department directly for quick access without joining through `employees`.

---

---

# TABLE 4 — `positions`

## What this table is
The `positions` table stores the job positions available within each department. Positions are department-specific — "IT Manager" only exists under IT, "Finance Analyst" only under Finance. HR does not manage positions from a dedicated page; they are seeded via `PositionSeeder` and defined in `config/positions.php`.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `department_id` | Foreign key to `departments`. Which department this position belongs to. NOT NULL — every position must belong to a department. |
| `name` | The position title, e.g. "IT Manager", "Finance Analyst", "HR Officer". |

## Foreign keys owned by `positions` (going OUT)

### FK 1: `positions.department_id` → `departments.id`
**What it means:** Every position is scoped to one department. You cannot have a position that floats between departments. When a user selects a department on the employee form, the position dropdown dynamically filters to show only positions in that department.
**Relationship type:** Many-to-One (many positions belong to one department).
**Eloquent method in `Position` model:** `belongsTo(Department::class)`
**Where it is used:** The Add Employee form uses an AJAX or JavaScript filter to populate the Position dropdown based on the selected Department. Also used for employee ID generation — the position's ID is part of the `DEPT-POSITIONID-COUNT` format.

## Relationships pointing TO `positions` (coming IN)

### From `employees.position_id`
Employees are assigned a position, which must belong to their department.

### From `users.position_id`
Users also store their position directly for quick access.

---

---

# TABLE 5 — `leave_types`

## What this table is
The `leave_types` table stores the configuration for every type of leave available in the organization — Sick Leave, Vacation Leave, Maternity Leave, etc. HR manages this from the `/admin/leave-types` page. This table is the master configuration that drives both the leave filing form and the leave balance calculations.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `name` | The display name, e.g. "Sick Leave", "Vacation Leave". |
| `slug` | A URL-friendly version of the name, e.g. `sick-leave`. Used for filtering and identification. |
| `annual_allocation` | How many days per year employees get of this leave type. When HR changes this, all current-year `leave_balances` for this type are recalculated. |
| `gender` | Optional gender restriction. If set to `female`, only female employees see this leave type (e.g. Maternity Leave). If null, available to everyone. |
| `requires_approval` | Boolean. If true, the leave request goes through the manager/HR approval workflow. If false, it would be auto-approved (though in practice all your leave types require approval). |
| `is_compensable` | Boolean. If true, unused days of this leave type are converted to monetary compensation at year-end. Used in the Reports module to calculate yearly compensation estimates. |
| `requires_proof` | Boolean. If true, the employee must upload proof (e.g., a medical certificate) when filing this leave type. |
| `proof_rules` | Text field describing the proof rule, e.g. "Required if 3 or more consecutive days". |
| `max_document_days` | The threshold number of days after which proof becomes required. For Sick Leave this is 3. |
| `is_active` | Whether this leave type is currently available for filing. Inactive types are hidden from the employee filing form. |

## Foreign keys owned by `leave_types`
None. `leave_types` does not point to any other table. It is a configuration table that other tables reference.

## Relationships pointing TO `leave_types` (coming IN)

### From `leave_applications.leave_type_id`
Every leave application specifies which type of leave is being requested. The `leave_applications` table points here.

### From `leave_balances.leave_type_id`
Every balance row tracks one employee's allocation for one specific leave type. The `leave_balances` table points here.

---

---

# TABLE 6 — `leave_applications`

## What this table is
The `leave_applications` table is the core transaction table of the system. Every time an employee files a leave request, one row is created here. It records all details of the request — who filed it, what type, what dates, the reason, the current status, the reviewer's decision and remarks, and any uploaded proof file.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `employee_id` | Foreign key to `employees`. Who filed this leave request. NOT NULL. |
| `leave_type_id` | Foreign key to `leave_types`. What type of leave was requested. NOT NULL. |
| `start_date` | The first day of the requested leave. |
| `end_date` | The last day of the requested leave. |
| `total_days` | The computed number of leave days (end_date minus start_date + 1). Stored directly so queries don't need to recompute it. Defaults to 1. |
| `reason` | The employee's written explanation for the leave request. NOT NULL. |
| `status` | The current state of the request. Values: `pending`, `approved`, `rejected`. Defaults to `pending`. |
| `remarks` | The manager or HR's written response when approving or rejecting. Required by `LeaveDecisionRequest` before a decision can be submitted. Null until a decision is made. |
| `reviewed_by` | Foreign key to `users`. Stores the user ID of the manager or HR who made the decision. Null until reviewed. |
| `reviewed_at` | Timestamp of when the decision was made. |
| `proof_path` | File path to the uploaded proof document (e.g., a medical certificate). Stored in Laravel's filesystem. Null if no proof was uploaded or required. |

## Foreign keys owned by `leave_applications` (going OUT)

### FK 1: `leave_applications.employee_id` → `employees.id`
**What it means:** This leave request was filed by this employee. The system uses this to scope the employee's own leave history on `/employee/leaves`, and to scope the manager's approval inbox to only show requests from their team.
**Relationship type:** Many-to-One (one employee can file many leave requests).
**Eloquent method in `LeaveApplication` model:** `belongsTo(Employee::class)`
**Where it is used:** Employee leave history page, manager approval inbox, HR master request log, reports, calendar.

### FK 2: `leave_applications.leave_type_id` → `leave_types.id`
**What it means:** This request is for this specific type of leave. The system uses this to know the leave type's configuration (e.g., does it need proof? how many days are allocated?).
**Relationship type:** Many-to-One (many applications can be for the same leave type).
**Eloquent method in `LeaveApplication` model:** `belongsTo(LeaveType::class)`
**Where it is used:** The filing form dropdown, balance validation, calendar color-coding, report breakdowns.

### FK 3: `leave_applications.reviewed_by` → `users.id`
**What it means:** This column records who approved or rejected the request — identified by their user ID. It is nullable because a pending request has not been reviewed yet.
**Relationship type:** Many-to-One (one user can review many applications).
**Eloquent method in `LeaveApplication` model:** `belongsTo(User::class, 'reviewed_by')` — aliased as `reviewer`.
**Where it is used:** Displayed on the leave detail page to show the employee who acted on their request and when.

## Relationships pointing TO `leave_applications`

There is no physical foreign key pointing to `leave_applications` from `system_notifications`, but leave-request notifications logically reference it through `system_notifications.related_type = leave_application` and `system_notifications.related_id = leave_applications.id`.

---

---

# TABLE 7 — `leave_balances`

## What this table is
The `leave_balances` table tracks how many leave days each employee has available, used, and remaining for each leave type per year. It acts like a running ledger — when a leave application is approved, the `used_days` column here is incremented. When HR changes a leave type's annual allocation, this table is recalculated for all active employees.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `employee_id` | Foreign key to `employees`. Whose balance this row tracks. NOT NULL. |
| `leave_type_id` | Foreign key to `leave_types`. Which leave type this row is for. NOT NULL. |
| `year` | The calendar year this balance applies to (e.g., 2026). Allows the system to track balances year by year. |
| `allocated_days` | The total days allocated for this year — copied from `leave_types.annual_allocation` when the row is created or recalculated. |
| `used_days` | How many days have been approved so far this year. Starts at 0 and is incremented each time a leave application is approved. |
| *(computed)* `remaining` | Not a stored column — computed as `allocated_days - used_days` in application logic. |

## Foreign keys owned by `leave_balances` (going OUT)

### FK 1: `leave_balances.employee_id` → `employees.id`
**What it means:** This balance row belongs to this employee. When an employee is activated by HR, the system seeds one row per leave type per year for that employee.
**Relationship type:** Many-to-One (one employee has many balance rows — one per leave type).
**Eloquent method in `LeaveBalance` model:** `belongsTo(Employee::class)`
**Where it is used:** Employee reports page to display balances, filing validation to check if the employee has enough remaining days, manager approval to re-verify balance before approving.

### FK 2: `leave_balances.leave_type_id` → `leave_types.id`
**What it means:** This balance row is for this specific leave type. Together, `employee_id` + `leave_type_id` + `year` form a unique combination — no employee should have two balance rows for the same leave type in the same year.
**Relationship type:** Many-to-One.
**Eloquent method in `LeaveBalance` model:** `belongsTo(LeaveType::class)`
**Where it is used:** Displayed on the employee reports page as individual leave type balance cards.

## Relationships pointing TO `leave_balances`
None. `leave_balances` is also a leaf table.

---

---

# TABLE 8 — `system_notifications`

## What this table is
The `system_notifications` table stores every in-app notification generated by the system. Instead of using email, the system creates rows here to notify users about important events — account activation, leave submission confirmation, leave approval or rejection decisions, and leave requests that need review. Users see these through the notification bell in the header.

## Its columns and what they mean

| Column | What it stores |
|---|---|
| `id` | Auto-increment primary key. |
| `user_id` | Foreign key to `users`. Who this notification is addressed to. Nullable — could be a broadcast notification with no specific recipient. |
| `title` | The short heading of the notification, e.g. "Leave Request Approved". |
| `body` | The full message text of the notification. |
| `type` | The notification category. Values like `info`, `success`, `warning`. Defaults to `info`. Used to apply color styling in the notification dropdown. |
| `related_type` | Optional metadata describing what business record the notification belongs to. Leave-request review alerts use `leave_application`. |
| `related_id` | Optional metadata storing the related business record ID. For leave-request alerts, this is `leave_applications.id`. |
| `action_url` | An optional URL the user can click to go directly to the relevant page (e.g., the leave detail page). |
| `read_at` | Timestamp of when the user read the notification. Null = unread. Header badges count unread actionable rows, so settled leave-request notifications are excluded or marked read. |

## Foreign keys owned by `system_notifications` (going OUT)

### FK 1: `system_notifications.user_id` → `users.id`
**What it means:** This notification is for this user. The notification feed route at `/notifications/feed` filters by `user_id = auth()->id()` and returns only the current user's notifications.
**Relationship type:** Many-to-One (one user can have many notifications).
**Eloquent method in `SystemNotification` model:** `belongsTo(User::class)`
**Where it is used:** The notification bell dropdown in every layout header. The unread count badge. The notifications list page at `/admin/notifications`, `/manager/notifications`, `/employee/notifications`.

## Logical relationship metadata

`related_type` and `related_id` are not declared as a database foreign key, but the application uses them as a logical relationship. For leave-request notifications, `related_type = leave_application` and `related_id` points to the matching `leave_applications.id`. `SystemNotification::scopeUnreadActionable()` checks whether that leave request is still pending. `SystemNotification::markLeaveRequestSettled()` marks matching unread leave-request notifications as read when a manager or HR admin reviews the request, or when the employee cancels it.

---

---

# LARAVEL FRAMEWORK TABLES — What to say about each

These tables were NOT created by you manually. They come from running `php artisan migrate` on standard Laravel migration files that the framework includes automatically.

## `migrations`
**What it is:** Laravel's internal bookkeeping table. Every time you run `php artisan migrate`, Laravel records the migration filename and batch number here so it knows which migrations have already run and won't run them again.
**Do you use it in your code?** No. Laravel manages it entirely internally.
**What to say:** "This is Laravel's built-in migration tracking table. Every Laravel project has it. It is not part of our business logic."

## `sessions`
**What it is:** Stores active user session data in the database instead of in files. Present because your `.env` has `SESSION_DRIVER=database`.
**Do you use it in your code?** Indirectly — every time a user logs in, Laravel writes their session here automatically.
**What to say:** "This stores user sessions in the database because we set `SESSION_DRIVER=database` in our `.env` file. Laravel handles this automatically."

## `password_reset_tokens`
**What it is:** Stores temporary tokens for the "Forgot Password" flow. When a user requests a password reset, Fortify generates a token, emails it, and stores it here temporarily. Once used or expired, the token is deleted.
**Do you use it in your code?** Yes, indirectly through Fortify's built-in password reset feature.
**What to say:** "This is created by Laravel Fortify to support the password reset flow. When a user clicks 'Forgot Password', a token is stored here and sent to their email."

## `cache` and `cache_locks`
**What they are:** Store cached data in the database. Present because your `.env` has `CACHE_STORE=database`.
**Do you use them in your code?** Not actively — your system does not explicitly cache queries.
**What to say:** "These are standard Laravel cache tables generated because our `.env` uses database-backed caching. Our system does not actively write to these, but the framework creates the tables as part of the standard setup."

## `jobs`, `job_batches`, `failed_jobs`
**What they are:** Support Laravel's queue system for background jobs. Present because your `.env` has `QUEUE_CONNECTION=database`.
**Do you use them in your code?** No. Your system does not dispatch any queued jobs — notifications are created synchronously.
**What to say:** "These are Laravel queue tables auto-generated by our configuration. We do not use background jobs in our system — all operations including notifications run synchronously. These tables exist because `QUEUE_CONNECTION=database` is set in our `.env`."

---

---

# COMPLETE RELATIONSHIP MAP — Quick Reference

```
users ──────────────────────────────────────────────────────────┐
  │  has one:    employees (via employees.user_id)               │
  │  has many:   system_notifications (via user_id)              │
  │  belongsTo:  departments (via users.department_id)           │
  │  belongsTo:  positions (via users.position_id)               │
  └─ is referenced by: departments.manager_user_id               │
                        leave_applications.reviewed_by           │
                        sessions.user_id                         │
                                                                 │
employees ───────────────────────────────────────────────────────┤
  │  belongsTo:  users (via user_id)                             │
  │  belongsTo:  departments (via department_id)                 │
  │  belongsTo:  positions (via position_id)                     │
  │  belongsTo:  employees self (via manager_id)                 │
  │  has many:   leave_applications (via employee_id)            │
  │  has many:   leave_balances (via employee_id)                │
  │  has many:   employees (subordinates, via manager_id)        │
  └─────────────────────────────────────────────────────────────┤
                                                                 │
departments ─────────────────────────────────────────────────────┤
  │  belongsTo:  users (manager, via manager_user_id)            │
  │  has many:   employees (via department_id)                   │
  │  has many:   positions (via department_id)                   │
  │  has many:   users (via department_id)                       │
  └─────────────────────────────────────────────────────────────┤
                                                                 │
positions ───────────────────────────────────────────────────────┤
  │  belongsTo:  departments (via department_id)                 │
  │  has many:   employees (via position_id)                     │
  │  has many:   users (via position_id)                         │
  └─────────────────────────────────────────────────────────────┤
                                                                 │
leave_types ─────────────────────────────────────────────────────┤
  │  (no outgoing FKs)                                           │
  │  has many:   leave_applications (via leave_type_id)          │
  │  has many:   leave_balances (via leave_type_id)              │
  └─────────────────────────────────────────────────────────────┤
                                                                 │
leave_applications ──────────────────────────────────────────────┤
  │  belongsTo:  employees (via employee_id)                     │
  │  belongsTo:  leave_types (via leave_type_id)                 │
  │  belongsTo:  users (reviewer, via reviewed_by)               │
  └─────────────────────────────────────────────────────────────┤
                                                                 │
leave_balances ──────────────────────────────────────────────────┤
  │  belongsTo:  employees (via employee_id)                     │
  │  belongsTo:  leave_types (via leave_type_id)                 │
  └─────────────────────────────────────────────────────────────┤
                                                                 │
system_notifications ────────────────────────────────────────────┤
  │  belongsTo:  users (via user_id)                             │
  │  logically links to leave_applications for leave_request      │
  │    notifications (via related_type + related_id)              │
  └─────────────────────────────────────────────────────────────┘
```

---

# LIKELY DEFENSE QUESTIONS — answered

**Q: Why does `users` have both `department_id` and `position_id` when `employees` also has them?**
> "The `users` table stores `department_id` and `position_id` as convenience columns so the system can look up a user's department and position without joining through `employees`. This is useful in middleware and quick session checks. The authoritative profile data lives in `employees`, but `users` keeps these references for performance."

**Q: What is the self-referencing relationship in `employees`?**
> "The `manager_id` column in `employees` points back to the same `employees` table. This means a manager is also an employee in the system. When an employee is assigned a manager, we store that manager's employee ID in `manager_id`. In Eloquent, we define this as `belongsTo(Employee::class, 'manager_id')` aliased as `manager`, and the inverse `hasMany(Employee::class, 'manager_id')` aliased as `subordinates`."

**Q: How does `leave_balances` get populated?**
> "When HR activates a new user account, the system runs a loop through all active `leave_types` and creates one `leave_balance` row per leave type for the current year. The `allocated_days` is copied from `leave_types.annual_allocation`. The `used_days` starts at zero. If HR later updates a leave type's allocation, the system recalculates all matching `leave_balance` rows."

**Q: Why is `reviewed_by` in `leave_applications` pointing to `users` instead of `employees`?**
> "Because managers and HR admins act on leave requests in their capacity as system users, not as employees. The reviewer could be an HR admin who approves a manager's own leave, so it makes more sense to store the `users.id` directly. This also means we can always trace back who made a decision even if their employee profile is later deactivated."

**Q: What is the difference between `leave_applications` and `leave_balances`?**
> "`leave_applications` is a transaction log — it records every individual leave request ever filed, with full details, status, and remarks. `leave_balances` is a running summary — it tracks the totals per employee per leave type per year. When an application is approved, `leave_balances.used_days` is incremented. The two tables work together: applications are the individual events, balances are the aggregate state."

**Q: Why do notification badges disappear after another approver handles a leave request?**
> "Leave-request notifications are tagged with `related_type = leave_application` and the leave request ID. The badge count uses an `unreadActionable()` scope, which only counts leave-request notifications while the related leave is still pending. When a manager or HR admin reviews it, or the employee cancels it, the system marks matching unread notifications as read so the same settled request does not keep showing as an action item for other users."
