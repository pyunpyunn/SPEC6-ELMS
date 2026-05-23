# Defense Documentation 3: Adriane's Tasks - Modules 5 and 6

Adriane's assigned modules:

- Module 5: Reports and Dashboards
- Module 6: User Authentication and Roles

Defense angle: Adriane explains how HR sees summaries, reports, calendars, exports, and how login/registration/roles protect each module.

## Module 5: Reports and Dashboards

### Requirement From Instructor

HR dashboard must show:

- Leave summaries per department
- Individual leave balance report
- Monthly leave calendar view
- Export leave records as CSV

## Module 5 Main Code Files To Study

| Purpose | File |
| --- | --- |
| HR dashboard wrapper | `app/Http/Controllers/Admin/DashboardController.php` |
| HR dashboard logic | `app/Http/Controllers/Hr/HrController.php` |
| Main report controller | `app/Http/Controllers/Admin/ReportController.php` |
| HR dashboard view | `resources/views/admin/dashboard.blade.php` |
| HR report page view | `resources/views/admin/reports/index.blade.php` |
| HR report content and JavaScript | `resources/views/admin/reports/_content.blade.php` |
| HR calendar view | `resources/views/admin/calendar.blade.php` |
| Request log view | `resources/views/admin/requests/index.blade.php` |
| Leave balance model | `app/Models/LeaveBalance.php` |
| Leave application model | `app/Models/LeaveApplication.php` |
| Department model | `app/Models/Department.php` |
| Leave type model | `app/Models/LeaveType.php` |
| Report routes | `routes/web.php` |

## Module 5 Routes

Report routes under HR/Admin:

```php
Route::middleware(['account.approved', 'role:hr'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/calendar', [AdminReportController::class, 'calendar'])->name('reports.calendar');
        Route::get('/calendar', [AdminReportController::class, 'calendar'])->name('calendar');
    });
```

AJAX/JSON report routes:

```php
Route::middleware(['auth', 'profile.complete', 'account.approved', 'role:hr'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/reports/yearly-compensation', [AdminReportController::class, 'yearlyCompensation'])
            ->name('reports.yearly-compensation');
        Route::get('/reports/individual-balance', [AdminReportController::class, 'individualBalance'])
            ->name('reports.individual-balance');
        Route::get('/reports/yearly-compensation/export', [AdminReportController::class, 'exportYearlyCompensation'])
            ->name('reports.yearly-compensation.export');
        Route::get('/reports/individual-balance/export', [AdminReportController::class, 'exportIndividualBalance'])
            ->name('reports.individual-balance.export');
    });
```

## HR Dashboard Flow

Entry file:

```text
app/Http/Controllers/Admin/DashboardController.php
```

This simply calls:

```php
return app(HrController::class)->dashboard($request);
```

Main logic:

```text
app/Http/Controllers/Hr/HrController.php
```

Important method:

```php
public function dashboard(Request $request): View
```

It builds:

- total active employees
- pending manager requests
- pending users
- leaves this month
- most used leave type
- status breakdown
- recent requests
- department summaries
- employees on leave today

Important dashboard data:

```php
'stats' => [
    'employees' => $employeeQuery->count(),
    'pending_manager' => (clone $requestQuery)->where('status', 'pending')->count(),
    'pending_users' => User::where('status', 'pending')->count(),
    'leaves_this_month' => $monthRequestQuery->count(),
    'most_used' => $mostUsed?->name ?? 'None yet',
],
```

Defense explanation:

> The HR dashboard uses Eloquent queries to count employees, leave requests, pending users, and department summaries. It gives HR a quick overview of leave activity.

## Yearly Compensation Report

File:

```text
app/Http/Controllers/Admin/ReportController.php
```

Important method:

```php
public function yearlyCompensation(Request $request): JsonResponse
```

It returns JSON:

```php
return response()->json([
    'year' => $year,
    'department' => $departmentId,
    'rows' => $this->yearlyCompensationRows($year, $departmentId)->values(),
    'compensation_types' => $compensableTypes->values(),
    'compensation_rows' => $this->employeeCompensationRows($year, $departmentId, $compensableTypes)->values(),
]);
```

What it calculates:

- Department leave summary
- Employee count
- Total leave days taken
- Average leave per employee
- Sick/vacation/emergency/unpaid leave totals
- Compensation for compensable leave types

Helper method:

```php
private function yearlyCompensationRows(int $year, ?int $departmentId): Collection
```

Defense explanation:

> The yearly report loads approved leave applications and groups them by department and leave type. It calculates totals using Laravel collections after retrieving Eloquent models.

## Individual Balance Report

Important method:

```php
public function individualBalance(Request $request): JsonResponse
```

Step-by-step:

1. Get selected year.
2. Get selected employee ID.
3. Find employee with department and position.
4. Load employee balances.
5. Calculate entitlement, used days, and remaining days.
6. Calculate compensation summary.
7. Return JSON to the Blade page.

Important helper:

```php
private function individualBalancePayload(Employee $employee, int $year): array
```

Defense explanation:

> The individual balance report shows one employee's leave balance per leave type. It uses `LeaveBalance` rows and the related `LeaveType` records.

## CSV Export

Files:

```text
app/Http/Controllers/Admin/ReportController.php
app/Http/Controllers/Hr/HrController.php
```

Important methods:

- `exportYearlyCompensation(Request $request)`
- `exportIndividualBalance(Request $request)`
- `export(Request $request)`

CSV is generated with:

```php
return response()->streamDownload(function () use (...) {
    $out = fopen('php://output', 'w');
    fputcsv($out, [...]);
    fclose($out);
}, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
```

Defense explanation:

> We used `streamDownload` and `fputcsv` so the user can download report data as a CSV file without manually creating a temporary file on disk.

## Monthly Leave Calendar

Route:

```text
GET /admin/calendar
```

Controller:

```php
public function calendar(Request $request): View
{
    return app(HrController::class)->calendar($request);
}
```

Main data:

```php
'approvedLeaves' => LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
    ->where('status', 'approved')
    ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
    ->when($request->filled('leave_type_ids'), fn ($q) => $q->whereIn('leave_type_id', (array) $request->leave_type_ids))
    ->orderBy('start_date')
    ->get(),
```

View:

```text
resources/views/admin/calendar.blade.php
```

Defense explanation:

> The calendar only displays approved leaves. HR can filter by department and leave type.

## Module 5 Views

| View | Purpose |
| --- | --- |
| `resources/views/admin/dashboard.blade.php` | HR dashboard summaries |
| `resources/views/admin/reports/index.blade.php` | Report wrapper page |
| `resources/views/admin/reports/_content.blade.php` | Report tables, filters, JavaScript fetch calls |
| `resources/views/admin/calendar.blade.php` | Monthly leave calendar |
| `resources/views/admin/requests/index.blade.php` | Master request log |

## Module 5 Demo Steps

1. Log in as HR:
   - `hr@company.com`
   - password: `password`
2. Open `/admin/dashboard`.
3. Explain summary cards.
4. Open `/admin/reports`.
5. Show yearly compensation review.
6. Search/select employee for individual balance report.
7. Download CSV.
8. Open `/admin/calendar`.
9. Filter by department or leave type.

## Module 6: User Authentication and Roles

### Requirement From Instructor

The system must have:

- Laravel Breeze/Fortify authentication
- Three roles:
  - Employee
  - Manager
  - HR Admin
- Role-based middleware

This project uses Laravel Fortify.

## Module 6 Main Code Files To Study

| Purpose | File |
| --- | --- |
| Fortify configuration | `config/fortify.php` |
| Fortify boot setup | `app/Providers/FortifyServiceProvider.php` |
| Registration action | `app/Actions/Fortify/CreateNewUser.php` |
| Password reset action | `app/Actions/Fortify/ResetUserPassword.php` |
| Password update action | `app/Actions/Fortify/UpdateUserPassword.php` |
| Login/register views | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php` |
| User model role methods | `app/Models/User.php` |
| Role middleware | `app/Http/Middleware/RoleMiddleware.php` |
| Account approval middleware | `app/Http/Middleware/EnsureAccountApproved.php` |
| Profile middleware | `app/Http/Middleware/EnsureProfileComplete.php` |
| Middleware registration | `bootstrap/app.php` |
| Home role redirect | `app/Http/Controllers/HomeController.php` |
| User activation | `app/Http/Controllers/Hr/HrController.php` |
| Pending user admin page | `resources/views/admin/users/pending.blade.php` |
| User seeder | `database/seeders/UserSeeder.php` |
| Employee seeder | `database/seeders/EmployeeSeeder.php` |

## Fortify Setup

File:

```text
app/Providers/FortifyServiceProvider.php
```

Important setup:

```php
Fortify::createUsersUsing(CreateNewUser::class);
Fortify::loginView(fn () => view('auth.login'));
Fortify::registerView(fn () => view('auth.register'));
```

This tells Fortify:

- Use our custom registration action.
- Show our Blade login page.
- Show our Blade register page.

## Login With Email Or Employee ID

Still in `FortifyServiceProvider`:

```php
Fortify::authenticateUsing(function (Request $request) {
    $login = trim((string) $request->input(Fortify::username()));
    $email = Str::lower($login);
    $employeeId = Str::upper($login);

    $user = User::query()
        ->where('email', $email)
        ->orWhereHas('employee', fn ($query) => $query->where('employee_id', $employeeId))
        ->first();

    if (
        $user
        && $user->status === 'active'
        && Hash::check((string) $request->input('password'), $user->password)
    ) {
        return $user;
    }

    return null;
});
```

Defense explanation:

> The login field accepts either email or employee ID. Fortify calls this custom authentication function. It searches the user by email or by related employee ID. It also checks that the account is active before allowing login.

## Registration Flow

File:

```text
app/Actions/Fortify/CreateNewUser.php
```

Important behavior:

```php
$user = User::create([
    'name' => $input['name'],
    'email' => $input['email'],
    'password' => Hash::make($input['password']),
    'role' => 'employee',
    'status' => 'pending',
    'pending_employee_id' => $input['employee_id'] ?? null,
]);
```

After registration:

```php
SystemNotification::sendToRole(
    'hr_admin',
    'Account validation pending',
    $user->name.' registered and is waiting for HR account validation.',
    route('admin.users.pending'),
    'account_validation'
);
```

Defense explanation:

> New public registrations are not active immediately. They are saved as pending users, and HR receives a notification to validate the account.

## HR Account Activation Flow

File:

```text
app/Http/Controllers/Hr/HrController.php
```

Important method:

```php
public function activateUser(ActivateUserRequest $request, User $user): RedirectResponse
```

Steps:

1. HR selects department, position, gender, manager, and hire date.
2. Request is validated.
3. System generates employee ID.
4. System derives role based on department/position.
5. User status becomes active.
6. Employee profile is created.
7. Leave balances are seeded.
8. User receives notification.

Important code:

```php
$employeeId = Employee::generateEmployeeId($department->code, $position->name);
$role = Employee::accessRoleFor($department->code, $position->name);
```

Then:

```php
$user->update([
    'role' => $role,
    'status' => 'active',
    'pending_employee_id' => null,
    'department_id' => $department->id,
    'position_id' => $position->id,
]);
```

## Role Logic

File:

```text
app/Models/User.php
```

Important methods:

```php
public function derivedRole(): string
```

This decides the real role from:

- `users.role`
- related employee department/position

Role helper:

```php
public function hasAccessRole(string $role): bool
{
    return match ($role) {
        'hr', 'hr_admin' => $this->isHR(),
        'manager' => $this->isManager(),
        'employee' => $this->isEmployee(),
        default => $this->derivedRole() === $role,
    };
}
```

Access level:

```php
public function getAccessLevel(): string
{
    return match ($this->derivedRole()) {
        'hr_admin' => 'hr',
        'manager' => 'manager',
        default => 'employee',
    };
}
```

## Role-Based Middleware

File:

```text
app/Http/Middleware/RoleMiddleware.php
```

Important code:

```php
foreach ($roles as $role) {
    if ($user->hasAccessRole($role)) {
        return $next($request);
    }
}

abort(403, 'Unauthorized - Insufficient permissions for role: ' . implode(', ', $roles));
```

Defense explanation:

> The middleware checks if the logged-in user has the role required by the route. If not, it returns 403.

## Account Approval Middleware

File:

```text
app/Http/Middleware/EnsureAccountApproved.php
```

Important behavior:

```php
if ($user && $user->status !== 'active') {
    return redirect()
        ->route('employee.profile')
        ->with('warning', 'Your account is not approved yet. HR must activate your account before you can use ELMS modules.');
}
```

Defense explanation:

> Pending users cannot access modules until HR activates their account.

## Profile Complete Middleware

File:

```text
app/Http/Middleware/EnsureProfileComplete.php
```

Important behavior:

```php
if ($user && $user->status === 'active' && ! $user->employee) {
    abort(403, 'Please ask HR Admin to complete your employee profile.');
}
```

Defense explanation:

> Even if a user exists, they need an employee profile before accessing employee/manager/HR modules.

## Middleware Registration

File:

```text
bootstrap/app.php
```

Aliases:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\RoleMiddleware::class,
    'account.approved' => \App\Http\Middleware\EnsureAccountApproved::class,
    'profile.complete' => \App\Http\Middleware\EnsureProfileComplete::class,
]);
```

## Home Redirect By Role

File:

```text
app/Http/Controllers/HomeController.php
```

Important method:

```php
public function redirectByRole(): RedirectResponse
{
    $user = auth()->user();

    if ($user->getAccessLevel() === 'hr') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->getAccessLevel() === 'manager') {
        return redirect()->route('manager.dashboard');
    }

    return redirect()->route('employee.dashboard');
}
```

Defense explanation:

> After login, `/home` checks the user's access level and sends them to the correct dashboard.

## Protected Route Groups

HR:

```php
Route::middleware(['account.approved', 'role:hr'])
    ->prefix('admin')
    ->name('admin.')
    ->group(...);
```

Manager:

```php
Route::middleware(['account.approved', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(...);
```

Employee:

```php
Route::middleware(['account.approved', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(...);
```

## Module 6 Demo Steps

1. Show login page.
2. Login as HR Admin.
3. Explain redirect to HR dashboard.
4. Logout.
5. Login as Manager.
6. Explain redirect to manager dashboard.
7. Logout.
8. Login as Employee.
9. Explain redirect to employee dashboard.
10. Register a new account.
11. Show it becomes pending.
12. Login as HR.
13. Activate pending user.
14. Explain middleware restrictions.

## Common Defense Questions For Adriane

### Q: Why use Fortify?

Answer:

> Fortify provides Laravel authentication features like login, registration, password reset, password update, and secure password handling without requiring us to manually write every authentication route.

### Q: How are roles enforced?

Answer:

> Routes are grouped by role and protected by middleware such as `role:hr`, `role:manager`, and `role:employee`. The middleware calls `User::hasAccessRole()`.

### Q: How does the system know where to redirect after login?

Answer:

> Fortify redirects to `/home`, and `HomeController@redirectByRole` checks the user's access level and sends them to the correct dashboard.

### Q: How does HR export reports?

Answer:

> The report controller uses `response()->streamDownload()` and `fputcsv()` to output CSV rows directly to the browser.

### Q: What is shown in HR dashboard?

Answer:

> It shows active employee count, pending requests, pending users, leave activity this month, most used leave type, department summaries, recent requests, and who is on leave today.

