# Defense Documentation 2: Kath's Tasks - Modules 3 and 4

Kath's assigned modules:

- Module 3: Leave Application
- Module 4: Leave Approval Workflow

Defense angle: Kath explains how leave requests are filed, validated against balances, shown in history, approved/rejected, updated through transactions, and connected to notifications.

## Module 3: Leave Application

### Requirement From Instructor

Employees file leave requests specifying:

- Leave type
- Start date
- End date
- Reason

The system must:

- Validate against remaining leave balance.
- Let employees view leave history.
- Let employees view current balances.

### Main Code Files To Study

These are the complete source files for Module 3:

| Purpose                            | File                                                                           |
| ---------------------------------- | ------------------------------------------------------------------------------ |
| Employee leave routes              | `routes/web.php`                                                               |
| Employee leave resource controller | `app/Http/Controllers/Employee/LeaveApplicationController.php`                 |
| Shared employee leave logic        | `app/Http/Controllers/EmployeePortalController.php`                            |
| HR self-leave logic                | `app/Http/Controllers/Hr/HrController.php`                                     |
| Manager self-leave logic           | `app/Http/Controllers/Manager/ManagerController.php`                           |
| Leave form validation              | `app/Http/Requests/StoreLeaveApplicationRequest.php`                           |
| Manager leave form validation      | `app/Http/Requests/Manager/StoreManagerLeaveRequest.php`                       |
| HR leave form validation           | `app/Http/Requests/Hr/StoreHrLeaveRequest.php`                                 |
| Leave application model            | `app/Models/LeaveApplication.php`                                              |
| Leave balance model                | `app/Models/LeaveBalance.php`                                                  |
| Leave type model                   | `app/Models/LeaveType.php`                                                     |
| Employee dashboard view            | `resources/views/employee/dashboard.blade.php`                                 |
| Employee leave list/history view   | `resources/views/employee/leaves/index.blade.php`                              |
| Employee create leave view         | `resources/views/employee/leaves/create.blade.php`                             |
| Apply leave modal                  | `resources/views/employee/partials/apply-leave-modal.blade.php`                |
| Employee reports/balances view     | `resources/views/employee/reports.blade.php`                                   |
| Employee leave detail view         | `resources/views/employee/leaves/show.blade.php`                               |
| Leave applications migration       | `database/migrations/2026_05_01_112325_create_leave_applications_table.php`    |
| Leave balance migration            | `database/migrations/2026_05_09_000001_build_hr_leave_management_schema.php`   |
| Leave balance seeder               | `database/seeders/LeaveBalanceSeeder.php`                                      |

## Module 3 Leave Application Routes

In `routes/web.php`, employee leave routes are inside:

```php
Route::middleware(['account.approved', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::resource('leaves', LeaveApplicationController::class)
            ->parameters(['leaves' => 'leaveApplication'])
            ->only(['index', 'create', 'store', 'show']);
        Route::patch('/leaves/{leaveApplication}/cancel', [LeaveApplicationController::class, 'cancel'])->name('leaves.cancel');
        Route::delete('/leaves/{leaveApplication}', [LeaveApplicationController::class, 'destroy'])->name('leaves.destroy');
        Route::get('/reports', [LeaveApplicationController::class, 'reports'])->name('reports');
        Route::get('/leave-balances', [LeaveApplicationController::class, 'reports'])->name('leave-balances');
    });
```

What this means:

- `GET /employee/leaves` shows leave history.
- `GET /employee/leaves/create` shows the create form.
- `POST /employee/leaves` saves a leave request.
- `GET /employee/leaves/{leaveApplication}` shows one leave.
- `PATCH /employee/leaves/{leaveApplication}/cancel` cancels a pending leave.
- `GET /employee/reports` shows balances/report.

## Module 3 Leave Application Controller Flow

### Entry Controller

File:

```text
app/Http/Controllers/Employee/LeaveApplicationController.php
```

This controller is beginner-friendly because it maps resource methods to the shared leave logic:

```php
public function index(): View
{
    return $this->myLeave();
}

public function create(): View
{
    return view('employee.leaves.create', $this->portalData());
}

public function store(StoreLeaveApplicationRequest $request): RedirectResponse
{
    return $this->storeLeave($request);
}
```

Important explanation:

- `index()` displays the employee's leave history.
- `create()` displays the leave application form.
- `store()` validates and saves the request.
- The actual reusable logic is in `EmployeePortalController`.

### Main Leave Filing Logic

File:

```text
app/Http/Controllers/EmployeePortalController.php
```

Important method:

```php
public function storeLeave(StoreLeaveApplicationRequest $request)
```

Step-by-step:

1. Get logged-in employee:

```php
$employee = Auth::user()->employee;
abort_unless($employee, 403, 'Employee profile is required before filing leave.');
```

2. Sync current year balances:

```php
$this->syncCurrentYearBalances($employee);
```

This makes sure balances match approved leaves for the current year.

3. Validate form data:

```php
$validated = $request->validated();
```

4. Load selected leave type:

```php
$leaveType = LeaveType::findOrFail($validated['leave_type_id']);
```

5. Block inactive leave types:

```php
if (! $leaveType->is_active) {
    throw ValidationException::withMessages([
        'leave_type_id' => 'This leave type is currently inactive.',
    ]);
}
```

6. Check if leave type is allowed for the employee gender:

```php
abort_unless($leaveType->isVisibleForGender($employee->gender), 422, 'This leave type is not available for the employee gender on record.');
```

7. Parse dates:

```php
$startDate = Carbon::parse($validated['start_date']);
$endDate = Carbon::parse($validated['end_date']);
```

8. Block past dates:

```php
abort_if($startDate->lt(now()->startOfDay()), 422, 'You cannot file leave for past dates.');
```

9. Count working days only:

```php
$totalDays = $this->workingDaysBetween($startDate, $endDate);
```

The method skips Saturday and Sunday:

```php
private function workingDaysBetween(Carbon $start, Carbon $end): int
{
    $days = 0;

    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        if (! $date->isWeekend()) {
            $days++;
        }
    }

    return max(1, $days);
}
```

10. Prevent overlapping active leaves:

```php
$overlappingActiveLeaveExists = LeaveApplication::query()
    ->where('employee_id', $employee->id)
    ->whereIn('status', ['pending', 'approved'])
    ->where('start_date', '<=', $weekEnd)
    ->where('end_date', '>=', $weekStart)
    ->whereDate('end_date', '>=', now()->startOfDay())
    ->whereDate('start_date', '<=', $startDate)
    ->whereDate('end_date', '>=', $startDate)
    ->exists();
```

11. Check proof/document requirement:

```php
if ($leaveType->requires_proof) {
    if ($leaveType->max_document_days === null || $leaveType->max_document_days <= 0) {
        $proofRequired = true;
    } else {
        $proofRequired = $totalDays >= $leaveType->max_document_days;
    }
}
```

12. Check leave balance:

```php
$balance = LeaveBalance::where('employee_id', $employee->id)
    ->where('leave_type_id', $leaveType->id)
    ->where('year', Carbon::parse($validated['start_date'])->year)
    ->first();
```

It also subtracts pending days:

```php
$pendingDays = LeaveApplication::where('employee_id', $employee->id)
    ->where('leave_type_id', $leaveType->id)
    ->where('status', 'pending')
    ->whereYear('start_date', Carbon::parse($validated['start_date'])->year)
    ->sum('total_days');
```

If there is not enough balance:

```php
if (! $balance || ($balance->remaining_days - (int) $pendingDays) < $totalDays) {
    throw ValidationException::withMessages([
        'leave_type_id' => 'You do not have enough remaining balance for this leave request.',
    ]);
}
```

13. Save proof upload if any:

```php
$proofPath = $request->file('proof')?->store('leave-proofs', 'public');
```

14. Determine status:

```php
$status = $leaveType->requires_approval ? 'pending' : 'approved';
```

15. Create request inside transaction:

```php
$leave = DB::transaction(function () use ($employee, $leaveType, $validated, $totalDays, $proofPath, $status) {
    $lockedBalance = LeaveBalance::where('employee_id', $employee->id)
        ->where('leave_type_id', $leaveType->id)
        ->where('year', Carbon::parse($validated['start_date'])->year)
        ->lockForUpdate()
        ->first();

    $leave = LeaveApplication::create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'],
        'total_days' => $totalDays,
        'reason' => $validated['reason'],
        'status' => $status,
        'reviewed_at' => $status === 'approved' ? now() : null,
        'proof_path' => $proofPath,
    ]);

    if ($status === 'approved') {
        $lockedBalance->increment('used_days', $totalDays);
    }

    return $leave;
});
```

Why transaction matters:

- It prevents the balance from being changed incorrectly if two actions happen at almost the same time.
- `lockForUpdate()` locks the balance row while checking and updating it.

16. Send notifications:

```php
if ($leave->status === 'pending') {
    $this->notifyLeaveSubmitted($leave);
} else {
    $this->notifyLeaveAutoApproved($leave);
}
```

17. Redirect with flash message:

```php
return redirect()
    ->route('employee.leaves.index')
    ->with('success', 'Leave request submitted. It is now pending approval.');
```

## Module 3 Leave Application Validation

File:

```text
app/Http/Requests/StoreLeaveApplicationRequest.php
```

Rules:

```php
return [
    'leave_type_id' => ['required', 'exists:leave_types,id'],
    'start_date' => ['required', 'date'],
    'end_date' => ['required', 'date', 'after_or_equal:start_date'],
    'reason' => ['required', 'string', 'max:1000'],
    'proof' => [
        'nullable',
        'file',
        'max:10240',
        'mimes:pdf,doc,docx,xls,xlsx,png,jpeg,jpg',
    ],
];
```

Defense explanation:

- The Form Request handles basic validation.
- The controller handles business validation such as balance, gender restriction, proof requirement, and duplicate leave.

## Module 3 Leave Application Models

### LeaveApplication

File:

```text
app/Models/LeaveApplication.php
```

Important fields:

- `employee_id`
- `leave_type_id`
- `start_date`
- `end_date`
- `total_days`
- `reason`
- `status`
- `remarks`
- `reviewed_by`
- `reviewed_at`
- `proof_path`

Relationships:

```php
public function employee(): BelongsTo
{
    return $this->belongsTo(Employee::class);
}

public function leaveType(): BelongsTo
{
    return $this->belongsTo(LeaveType::class);
}

public function reviewer(): BelongsTo
{
    return $this->belongsTo(User::class, 'reviewed_by');
}
```

### LeaveBalance

File:

```text
app/Models/LeaveBalance.php
```

Important computed attribute:

```php
public function getRemainingDaysAttribute(): int
{
    return max(0, (int) $this->allocated_days - (int) $this->used_days);
}
```

This lets the system use:

```php
$balance->remaining_days
```

### LeaveType

File:

```text
app/Models/LeaveType.php
```

Important method:

```php
public function isVisibleForGender(?string $gender): bool
```

This hides maternity/paternity or gender-specific leaves if the employee is not eligible.

## Module 3 Leave Application Views

| View | Purpose |
| --- | --- |
| `resources/views/employee/dashboard.blade.php` | Employee summary cards |
| `resources/views/employee/leaves/index.blade.php` | Leave history and apply leave modal |
| `resources/views/employee/leaves/create.blade.php` | Leave creation page |
| `resources/views/employee/partials/apply-leave-modal.blade.php` | Leave form UI |
| `resources/views/employee/reports.blade.php` | Balance and report view |
| `resources/views/employee/leaves/show.blade.php` | Single leave request details |

## Module 3 Demo Steps

1. Log in as Employee:
   - `employee@company.com`
   - password: `password`
2. Go to `/employee/leaves`.
3. Click/apply for leave.
4. Select leave type.
5. Select start and end dates.
6. Enter reason.
7. Upload proof if required.
8. Submit.
9. Show success flash.
10. Show request in leave history.
11. Show balance decrease or pending days in balance view.

## Module 4: Leave Approval Workflow

### Requirement From Instructor

Managers must:

- View pending leave requests for their department.
- Approve or reject with remarks.
- Send email or in-app notification on status change.

This system uses in-app notifications through `system_notifications`.

## Module 4 Main Code Files To Study

| Purpose                                    | File                                                       |
| ------------------------------------------ | ---------------------------------------------------------- |
| Manager approval routes                    | `routes/web.php`                                           |
| Manager resource-style approval controller | `app/Http/Controllers/Manager/LeaveApprovalController.php` |
| Manager approval logic                     | `app/Http/Controllers/Manager/ManagerController.php`       |
| HR request review logic                    | `app/Http/Controllers/Hr/HrController.php`                 |
| Admin request wrapper                      | `app/Http/Controllers/Admin/LeaveRequestController.php`    |
| Manager decision validation                | `app/Http/Requests/Manager/LeaveDecisionRequest.php`       |
| HR decision validation                     | `app/Http/Requests/Hr/LeaveDecisionRequest.php`            |
| Notification model                         | `app/Models/SystemNotification.php`                        |
| Manager approval inbox view                | `resources/views/manager/approvals/index.blade.php`        |
| Manager approval detail view               | `resources/views/manager/approvals/show.blade.php`         |
| HR request log view                        | `resources/views/admin/requests/index.blade.php`           |

## Module 4 Routes

Manager approval routes:

```php
Route::middleware(['account.approved', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/approvals', [LeaveApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{leaveApplication}', [LeaveApprovalController::class, 'show'])->name('approvals.show');
        Route::patch('/approvals/{leaveApplication}/approve', [LeaveApprovalController::class, 'approve'])->name('approvals.approve');
        Route::patch('/approvals/{leaveApplication}/reject', [LeaveApprovalController::class, 'reject'])->name('approvals.reject');
        Route::patch('/approvals/{leaveApplication}/review', [LeaveApprovalController::class, 'review'])->name('approvals.review');
    });
```

HR/Admin review route:

```php
Route::get('/requests', [AdminLeaveRequestController::class, 'index'])->name('requests.index');
Route::patch('/requests/{leaveApplication}/review', [AdminLeaveRequestController::class, 'review'])->name('requests.review');
```

## Module 4 Manager Approval Flow

### Step 1: Manager opens approval inbox

File:

```text
app/Http/Controllers/Manager/LeaveApprovalController.php
```

```php
public function index(Request $request): View
{
    return $this->requests($request);
}
```

This delegates to `ManagerController@requests`.

### Step 2: Manager sees only team/department requests

File:

```text
app/Http/Controllers/Manager/ManagerController.php
```

Important method:

```php
private function teamLeaveQuery(?Employee $manager)
{
    return LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'employee.leaveBalances.leaveType', 'leaveType', 'reviewer'])
        ->whereHas('employee', fn ($query) => $this->scopeTeamEmployees($query, $manager));
}
```

Team scope:

```php
private function scopeTeamEmployees($query, ?Employee $manager): void
{
    if (! $manager) {
        $query->whereKey(0);
        return;
    }

    $query->where('employment_status', 'active')
        ->where('id', '!=', $manager->id)
        ->where(function ($team) use ($manager) {
            $team->where('manager_id', $manager->id);

            if ($manager->department_id) {
                $team->orWhere('department_id', $manager->department_id);
            }
        });
}
```

Defense explanation:

- The manager cannot see all company requests.
- The query limits records to employees under that manager or in the same department.

### Step 3: Manager approve/reject request

Approval controller:

```php
public function approve(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
{
    return $this->reviewRequest($request, $leaveApplication);
}
```

Reject controller:

```php
public function reject(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
{
    return $this->reviewRequest($request, $leaveApplication);
}
```

### Step 4: Decision validation

File:

```text
app/Http/Requests/Manager/LeaveDecisionRequest.php
```

```php
protected function prepareForValidation(): void
{
    if ($this->routeIs('manager.approvals.approve')) {
        $this->merge(['status' => 'approved']);
    }

    if ($this->routeIs('manager.approvals.reject')) {
        $this->merge(['status' => 'rejected']);
    }
}
```

Rules:

```php
return [
    'status' => ['required', 'in:approved,rejected'],
    'remarks' => ['required', 'string', 'max:500'],
];
```

Defense explanation:

- Remarks are required so every decision has a reason.
- Approve/reject routes automatically set the decision status.

### Step 5: Transaction and balance update

Important method:

```php
public function reviewRequest(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
```

Inside it:

```php
DB::transaction(function () use ($validated, $leaveApplication) {
    if ($validated['status'] === 'approved') {
        $balance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
            ->where('leave_type_id', $leaveApplication->leave_type_id)
            ->where('year', $leaveApplication->start_date->year)
            ->lockForUpdate()
            ->first();

        abort_if(! $balance || $balance->remaining_days < (int) $leaveApplication->total_days, 422, 'The employee does not have enough remaining balance for this leave.');

        $balance->increment('used_days', (int) $leaveApplication->total_days);
    }

    $leaveApplication->update([
        'status' => $validated['status'],
        'remarks' => $validated['remarks'],
        'reviewed_by' => auth()->id(),
        'reviewed_at' => now(),
    ]);
});
```

Defense explanation:

- If rejected, only the leave request status changes.
- If approved, the system also increments `used_days`.
- `lockForUpdate()` protects balance accuracy.
- `reviewed_by` stores who reviewed it.
- `reviewed_at` stores when it was reviewed.

### Step 6: Notifications

After updating request status:

```php
SystemNotification::markLeaveRequestSettled($leaveApplication);
```

This marks old pending notification badges as settled.

Then employee is notified:

```php
$this->notify(
    $leaveApplication->employee->user,
    'Leave request '.$validated['status'],
    'Your '.$leaveApplication->leaveType->name.' request was '.$validated['status'].' by your manager.',
    route('home'),
    'leave_status'
);
```

HR is also notified:

```php
SystemNotification::sendToRole(
    'hr_admin',
    'Leave request '.$validated['status'],
    $leaveApplication->employee->full_name."'s ".$leaveApplication->leaveType->name.' request was '.$validated['status'].' by '.auth()->user()->name.'.',
    route('admin.requests.index'),
    'leave_status'
);
```

## Module 4 HR Review

HR also has a master request log:

```text
resources/views/admin/requests/index.blade.php
```

Code is in:

```text
app/Http/Controllers/Hr/HrController.php
```

Important methods:

- `requests(Request $request)`
- `reviewRequest(LeaveDecisionRequest $request, LeaveApplication $leaveApplication)`

HR can filter by:

- department
- leave type
- status
- date range
- search keyword

## Module 4 Demo Steps

1. Log in as Employee.
2. File a leave request.
3. Log out.
4. Log in as Manager.
5. Open `/manager/approvals`.
6. Click the leave request.
7. Add remarks.
8. Approve or reject.
9. Show flash message.
10. Log in as Employee.
11. Show updated leave status and notification.
12. Log in as HR.
13. Show master request log.

## Common Defense Questions For Kath

### Q: How do you validate leave balance?

Answer:

> The system gets the employee's `LeaveBalance` for the selected leave type and year. It also subtracts pending leave days so the employee cannot overbook their balance. If remaining days are not enough, it throws a validation error.

### Q: Why use database transactions?

Answer:

> We use transactions because leave filing and approval affect multiple records. For example, approving a request updates both the leave application and the leave balance. The transaction makes sure both changes succeed together or fail together.

### Q: What happens when a manager approves a leave?

Answer:

> The manager submits remarks. The system validates the request, checks that the leave is pending and belongs to the manager's team, locks the leave balance row, increments used days, updates the leave status, saves reviewer information, and sends notifications.

### Q: What happens when the request is rejected?

Answer:

> The leave application status becomes `rejected`, remarks are saved, but used leave balance is not increased.

### Q: Where is the history shown?

Answer:

> Employee history is loaded in `EmployeePortalController@myLeave` and displayed in `resources/views/employee/leaves/index.blade.php`.

