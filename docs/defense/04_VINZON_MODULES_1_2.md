# Defense Documentation 4: Vinzon's Tasks - Modules 1 and 2

Vinzon's assigned modules:

- Module 1: Employee Management
- Module 2: Leave Type Configuration

Defense angle: Vinzon explains how HR manages employee records, links employees to user accounts, deactivates employees, and configures leave types with allocation/proof/compensable rules.

## Module 1: Employee Management

### Requirement From Instructor

CRUD for employee records:

- Name
- Department
- Position
- Date hired
- Contact info

Each employee must be linked to a user account.

## Module 1 Main Code Files To Study

| Purpose | File |
| --- | --- |
| Employee management routes | `routes/web.php` |
| Admin employee resource controller | `app/Http/Controllers/Admin/EmployeeController.php` |
| Main HR employee logic | `app/Http/Controllers/Hr/HrController.php` |
| Employee validation | `app/Http/Requests/Hr/EmployeeRequest.php` |
| Store employee alias | `app/Http/Requests/StoreEmployeeRequest.php` |
| Deactivation validation | `app/Http/Requests/Hr/DeactivateEmployeeRequest.php` |
| Employee model | `app/Models/Employee.php` |
| User model | `app/Models/User.php` |
| Department model | `app/Models/Department.php` |
| Position model | `app/Models/Position.php` |
| Employee directory view | `resources/views/admin/employees/index.blade.php` |
| Employee form partial | `resources/views/admin/employees/partials/form.blade.php` |
| Employee profile/show view | `resources/views/admin/profile/show.blade.php` |
| Pending users/activation view | `resources/views/admin/users/pending.blade.php` |
| Users migration | `database/migrations/0001_01_01_000000_create_users_table.php` |
| Employees migration | `database/migrations/2026_05_01_112323_create_employees_table.php` |
| HR schema migration | `database/migrations/2026_05_09_000001_build_hr_leave_management_schema.php` |
| Positions migration | `database/migrations/2026_05_14_000001_create_positions_and_align_department_positions.php` |
| Employee cleanup migration | `database/migrations/2026_05_23_190000_remove_legacy_employee_department_position_columns.php` |
| Employee seeder | `database/seeders/EmployeeSeeder.php` |
| Department seeder | `database/seeders/DepartmentSeeder.php` |
| Position seeder | `database/seeders/PositionSeeder.php` |

## Module 1 Routes

In `routes/web.php`:

```php
Route::resource('employees', AdminEmployeeController::class);
```

This creates:

| Method | URL | Controller Method | Route Name |
| --- | --- | --- | --- |
| GET | `/admin/employees` | `index` | `admin.employees.index` |
| GET | `/admin/employees/create` | `create` | `admin.employees.create` |
| POST | `/admin/employees` | `store` | `admin.employees.store` |
| GET | `/admin/employees/{employee}` | `show` | `admin.employees.show` |
| GET | `/admin/employees/{employee}/edit` | `edit` | `admin.employees.edit` |
| PUT/PATCH | `/admin/employees/{employee}` | `update` | `admin.employees.update` |
| DELETE | `/admin/employees/{employee}` | `destroy` | `admin.employees.destroy` |

## Module 1 Controller Structure

### Resource Controller

File:

```text
app/Http/Controllers/Admin/EmployeeController.php
```

This is the resource controller. It maps Laravel resource methods to the HR logic:

```php
public function index(Request $request): View
{
    return app(HrController::class)->employees($request);
}

public function store(StoreEmployeeRequest $request): RedirectResponse
{
    return app(HrController::class)->storeEmployee($request);
}

public function update(StoreEmployeeRequest $request, Employee $employee): RedirectResponse
{
    return app(HrController::class)->updateEmployee($request, $employee);
}
```

Defense explanation:

> `EmployeeController` is the resource controller required by Laravel conventions. The bigger HR business logic is delegated to `HrController` so HR/Admin actions stay consistent.

## Employee Listing

Main method:

```text
HrController@employees
```

File:

```text
app/Http/Controllers/Hr/HrController.php
```

Important code:

```php
$employees = Employee::with(['user', 'departmentRecord', 'positionRecord', 'manager.user'])
    ->when($request->filled('search'), function ($query) use ($request) {
        $search = trim($request->string('search')->toString());
        $query->where(function ($sub) use ($search) {
            $sub->where('employee_id', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
        });
    })
    ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
    ->when($request->filled('position_id'), fn ($query) => $query->where('position_id', $request->integer('position_id')))
    ->when($request->filled('employment_status'), fn ($query) => $query->where('employment_status', $request->string('employment_status')))
    ->orderBy('employee_id')
    ->paginate(10)
    ->withQueryString();
```

What it does:

- Loads employees with user, department, position, and manager.
- Supports search.
- Supports department filter.
- Supports position filter.
- Supports employment status filter.
- Uses pagination.

## Employee Create Flow

Main method:

```text
HrController@storeEmployee
```

Steps:

1. Validate form with `EmployeeRequest`.
2. Check that selected position belongs to selected department.
3. Create user account.
4. Generate employee ID if blank.
5. Create employee profile.
6. Seed leave balances.
7. Send notification.
8. Redirect with flash message.

Important code:

```php
$position = $this->positionForDepartment($request->integer('position_id'), $request->integer('department_id'));
```

This prevents invalid department-position combinations.

Create user:

```php
$user = User::create([
    'name' => "{$request->first_name} {$request->last_name}",
    'email' => $request->email,
    'password' => Hash::make('password'),
    'role' => $role,
    'status' => 'active',
    'department_id' => $department->id,
    'position_id' => $position->id,
]);
```

Generate employee ID:

```php
if (empty($payload['employee_id'])) {
    $payload['employee_id'] = Employee::generateEmployeeId($department->code, $position->name);
}
```

Create employee:

```php
$employee = Employee::create($payload + [
    'user_id' => $user->id,
]);
```

Seed balances:

```php
$this->seedBalancesFor($employee);
```

Defense explanation:

> Employee creation also creates the linked user account. This satisfies the requirement that every employee is linked to a user account.

## Employee Update Flow

Main method:

```text
HrController@updateEmployee
```

Important behavior:

- Updates `users` table for name, email, role, status, department, position.
- Updates `employees` table for profile fields.
- Uses validated data from `EmployeeRequest`.
- Keeps account status in sync with employment status.

Important code:

```php
$employee->user->update([
    'name' => "{$request->first_name} {$request->last_name}",
    'email' => $request->email,
    'role' => $role,
    'status' => $request->employment_status === 'active' ? 'active' : 'inactive',
    'department_id' => $department->id,
    'position_id' => $position->id,
]);
```

Then:

```php
$employee->update($payload);
```

## Employee Deactivation

Resource controller:

```php
public function destroy(Employee $employee): RedirectResponse
{
    $employee->update(['employment_status' => 'terminated']);
    $employee->user?->update(['status' => 'inactive']);

    return redirect()->route('admin.employees.index')->with('warning', 'Employee deactivated.');
}
```

Defense explanation:

> We do not hard-delete employee data. We mark the employee as terminated and deactivate the user account. This protects historical leave records.

## Employee Validation

File:

```text
app/Http/Requests/Hr/EmployeeRequest.php
```

Important rules:

```php
'first_name' => ['required', 'string', 'max:100'],
'last_name' => ['required', 'string', 'max:100'],
'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
'employee_id' => ['nullable', 'string', 'max:30', Rule::unique('employees', 'employee_id')->ignore($employeeId)],
'department_id' => ['required', 'exists:departments,id'],
'position_id' => ['required', 'exists:positions,id'],
'manager_id' => ['nullable', 'exists:employees,id'],
'date_hired' => ['required', 'date'],
'daily_rate' => ['required', 'numeric', 'min:0'],
'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated'])],
```

Additional validation:

```php
$positionBelongsToDepartment = Position::whereKey($this->integer('position_id'))
    ->where('department_id', $this->integer('department_id'))
    ->exists();
```

Defense explanation:

> The Form Request validates required employee fields and also checks that the selected position belongs to the selected department.

## Employee Model

File:

```text
app/Models/Employee.php
```

Important relationships:

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function departmentRecord(): BelongsTo
{
    return $this->belongsTo(Department::class, 'department_id');
}

public function positionRecord(): BelongsTo
{
    return $this->belongsTo(Position::class, 'position_id');
}

public function manager(): BelongsTo
{
    return $this->belongsTo(Employee::class, 'manager_id');
}
```

Employee ID generator:

```php
public static function generateEmployeeId(string|int $department, string $positionTitle): string
```

Format:

```text
DEPT-POSITIONID-COUNT
```

Example:

```text
OPS-5000-001
```

## Module 1 Demo Steps

1. Log in as HR.
2. Open `/admin/employees`.
3. Show employee list.
4. Search/filter employees.
5. Create a new employee.
6. Explain user account is created too.
7. Edit employee.
8. Deactivate employee.
9. Explain historical records remain.

## Module 2: Leave Type Configuration

### Requirement From Instructor

CRUD for leave types:

- Vacation
- Sick
- Emergency
- Others

Set:

- Annual allocation
- Whether approval is required

This system also supports:

- Proof requirement
- Proof rule text
- Maximum document days
- Gender-specific leave
- Compensable flag
- Active/inactive flag

## Module 2 Main Code Files To Study

| Purpose | File |
| --- | --- |
| Leave type routes | `routes/web.php` |
| Admin leave type resource controller | `app/Http/Controllers/Admin/LeaveTypeController.php` |
| Main HR leave type logic | `app/Http/Controllers/Hr/HrController.php` |
| Leave type validation | `app/Http/Requests/Hr/LeaveTypeRequest.php` |
| Store leave type alias | `app/Http/Requests/StoreLeaveTypeRequest.php` |
| Leave type model | `app/Models/LeaveType.php` |
| Leave balance model | `app/Models/LeaveBalance.php` |
| Leave type view | `resources/views/admin/leave-types/index.blade.php` |
| Leave types migration | `database/migrations/2026_05_01_112323_create_leave_types_table.php` |
| HR schema migration | `database/migrations/2026_05_09_000001_build_hr_leave_management_schema.php` |
| Compensable migration | `database/migrations/2026_05_16_000001_add_is_compensable_to_leave_types.php` |
| Max document days migration | `database/migrations/2026_05_17_150727_add_max_document_days_to_leave_types.php` |
| Gender migration | `database/migrations/2026_05_17_160000_add_gender_to_leave_types.php` |
| Leave type seeder | `database/seeders/LeaveTypeSeeder.php` |

## Module 2 Routes

In `routes/web.php`:

```php
Route::resource('leave-types', AdminLeaveTypeController::class)
    ->parameters(['leave-types' => 'leaveType'])
    ->except(['show']);
```

Generated routes:

| Method | URL | Purpose |
| --- | --- | --- |
| GET | `/admin/leave-types` | List leave types |
| GET | `/admin/leave-types/create` | Create form |
| POST | `/admin/leave-types` | Store leave type |
| GET | `/admin/leave-types/{leaveType}/edit` | Edit form |
| PUT/PATCH | `/admin/leave-types/{leaveType}` | Update leave type |
| DELETE | `/admin/leave-types/{leaveType}` | Delete leave type |

## Leave Type Controller

File:

```text
app/Http/Controllers/Admin/LeaveTypeController.php
```

Important methods:

```php
public function index(): View
{
    return app(HrController::class)->leaveTypes();
}

public function store(StoreLeaveTypeRequest $request): RedirectResponse
{
    return app(HrController::class)->storeLeaveType($request);
}

public function update(StoreLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
{
    return app(HrController::class)->updateLeaveType($request, $leaveType);
}

public function destroy(LeaveType $leaveType): RedirectResponse
{
    $leaveType->delete();

    return back()->with('success', 'Leave type deleted.');
}
```

Defense explanation:

> The leave type controller follows Laravel resource controller style. Store and update are delegated to HR logic, while delete removes the selected leave type.

## Leave Type List

Main method:

```php
public function leaveTypes(): View
{
    return view('admin.leave-types.index', [
        'leaveTypes' => LeaveType::orderBy('name')->get(),
    ]);
}
```

View:

```text
resources/views/admin/leave-types/index.blade.php
```

## Create Leave Type

Main method:

```php
public function storeLeaveType(LeaveTypeRequest $request): RedirectResponse
{
    $leaveType = LeaveType::create($this->leaveTypePayload($request));
    $this->seedTypeBalances($leaveType);

    return back()->with('success', 'Leave type added and balances seeded.');
}
```

Defense explanation:

> When HR creates a leave type, the system also creates balance rows for active employees so the new leave type becomes available.

## Update Leave Type

Main method:

```php
public function updateLeaveType(LeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
{
    $leaveType->update($this->leaveTypePayload($request));
    $this->seedTypeBalances($leaveType, true);

    return back()->with('success', 'Leave type updated. Current-year balances were recalculated.');
}
```

Defense explanation:

> When HR updates annual allocation or rules, the current-year balances are recalculated for active employees.

## Leave Type Payload

Important helper:

```php
private function leaveTypePayload(LeaveTypeRequest $request): array
{
    return [
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'annual_allocation' => $request->annual_allocation,
        'gender' => $request->gender,
        'requires_approval' => $request->boolean('requires_approval'),
        'is_compensable' => $request->boolean('is_compensable'),
        'requires_proof' => $request->boolean('requires_proof'),
        'proof_rules' => $request->proof_rules,
        'max_document_days' => $request->input('max_document_days'),
        'is_active' => $request->boolean('is_active', true),
    ];
}
```

Fields explained:

| Field | Meaning |
| --- | --- |
| `name` | Leave type name |
| `slug` | URL/code-friendly version of name |
| `annual_allocation` | Yearly number of days |
| `gender` | Optional gender restriction |
| `requires_approval` | If true, request becomes pending |
| `is_compensable` | If true, unused leave may be included in compensation reports |
| `requires_proof` | If true, file upload may be required |
| `proof_rules` | Explanation shown to users |
| `max_document_days` | Number of days before proof becomes required |
| `is_active` | If false, employees cannot file this leave type |

## Leave Type Validation

File:

```text
app/Http/Requests/Hr/LeaveTypeRequest.php
```

Important rules:

```php
'name' => ['required', 'string', 'max:120', Rule::unique('leave_types', 'name')->ignore($leaveTypeId)],
'annual_allocation' => ['required', 'integer', 'min:1', 'max:365'],
'gender' => ['nullable', Rule::in('male', 'female', 'other')],
'requires_approval' => ['boolean'],
'is_compensable' => ['boolean'],
'requires_proof' => ['boolean'],
'proof_rules' => ['nullable', 'string', 'max:500'],
'max_document_days' => ['nullable', 'integer', 'min:0', 'max:365'],
'is_active' => ['boolean'],
```

Boolean cleanup:

```php
protected function prepareForValidation(): void
{
    $this->merge([
        'requires_approval' => $this->boolean('requires_approval'),
        'is_compensable' => $this->boolean('is_compensable'),
        'requires_proof' => $this->boolean('requires_proof'),
        'is_active' => $this->boolean('is_active', true),
    ]);
}
```

Defense explanation:

> Checkboxes can be tricky in HTML because unchecked boxes may not submit a value. `prepareForValidation()` converts the checkbox inputs into true/false values before validation.

## Seed Balances For New Leave Types

Important helper:

```php
private function seedTypeBalances(LeaveType $leaveType, bool $recalculate = false): void
{
    Employee::where('employment_status', 'active')->get()->each(function (Employee $employee) use ($leaveType, $recalculate) {
        $payload = ['allocated_days' => $leaveType->annual_allocation];

        if ($recalculate) {
            $payload['used_days'] = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('total_days');
        }

        LeaveBalance::updateOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => now()->year],
            $payload
        );
    });
}
```

Defense explanation:

> This keeps leave balances synchronized. If HR adds or edits a leave type, active employees get a balance record for that leave type.

## LeaveType Model

File:

```text
app/Models/LeaveType.php
```

Important relationships:

```php
public function applications(): HasMany
{
    return $this->hasMany(LeaveApplication::class);
}

public function balances(): HasMany
{
    return $this->hasMany(LeaveBalance::class);
}
```

Gender visibility:

```php
public function isVisibleForGender(?string $gender): bool
```

Defense explanation:

> Leave types can be restricted by gender. This prevents maternity or paternity leave from showing to ineligible employees.

## Leave Type Seeder

File:

```text
database/seeders/LeaveTypeSeeder.php
```

Seeded leave types:

- Sick Leave
- Maternity Leave
- Paternity Leave
- Bereavement Leave
- Vacation Leave
- Special Emergency Leave

Each seeded type includes:

- annual allocation
- approval requirement
- compensable flag
- proof requirement
- proof rule text
- max document days
- active flag

## Module 2 Demo Steps

1. Log in as HR.
2. Open `/admin/leave-types`.
3. Show existing leave types.
4. Create a leave type.
5. Explain annual allocation.
6. Toggle approval requirement.
7. Toggle proof requirement.
8. Toggle compensable flag.
9. Edit the leave type.
10. Explain balance recalculation.
11. Delete or deactivate a leave type if needed.

## Common Defense Questions For Vinzon

### Q: How is an employee linked to a user?

Answer:

> The `employees` table has `user_id`, which is a foreign key to the `users` table. In the `Employee` model, `user()` is a `belongsTo` relationship. In the `User` model, `employee()` is a `hasOne` relationship.

### Q: Why deactivate instead of delete employees?

Answer:

> Employees can have historical leave records. If we hard-delete employees, old leave applications could lose their employee reference. Deactivation keeps the data but prevents account access.

### Q: How are leave balances created?

Answer:

> When an employee is created or activated, `seedBalancesFor()` creates leave balance rows for active leave types. When a leave type is created or updated, `seedTypeBalances()` creates or updates balances for active employees.

### Q: How does the system prevent invalid position selection?

Answer:

> `EmployeeRequest` checks that the selected `position_id` belongs to the selected `department_id`.

### Q: Where is annual allocation configured?

Answer:

> Annual allocation is stored in the `leave_types` table in the `annual_allocation` column. HR edits it from `/admin/leave-types`.

