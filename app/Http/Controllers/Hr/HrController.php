<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\ActivateUserRequest;
use App\Http\Requests\Hr\DeactivateEmployeeRequest;
use App\Http\Requests\Hr\DepartmentRequest;
use App\Http\Requests\Hr\EmployeeRequest;
use App\Http\Requests\Hr\LeaveDecisionRequest;
use App\Http\Requests\Hr\LeaveTypeRequest;
use App\Http\Requests\Hr\ProfileRequest;
use App\Http\Requests\Hr\StoreHrLeaveRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HrController extends Controller
{
    public function dashboard(Request $request): View
    {
        $departmentId = $request->integer('department_id') ?: null;
        $employeeQuery = Employee::query()->where('employment_status', 'active');
        $requestQuery = LeaveApplication::query()->with(['employee.departmentRecord', 'employee.user', 'leaveType']);

        if ($departmentId) {
            $employeeQuery->where('department_id', $departmentId);
            $requestQuery->whereHas('employee', fn ($query) => $query->where('department_id', $departmentId));
        }

        $monthRequestQuery = (clone $requestQuery)->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()]);
        $dashboardRequests = (clone $requestQuery)->get();
        $mostUsedTypeId = $dashboardRequests->countBy('leave_type_id')->sortDesc()->keys()->first();
        $mostUsed = $mostUsedTypeId ? LeaveType::find($mostUsedTypeId) : null;

        $departmentSummaries = Department::withCount(['employees as active_employees_count' => fn ($query) => $query->where('employment_status', 'active')])
            ->with(['employees.leaveApplications' => fn ($query) => $query->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())])
            ->get()
            ->map(function (Department $department) {
                return [
                    'department' => $department,
                    'on_leave' => $department->employees->flatMap->leaveApplications->count(),
                ];
            });

        return view('admin.dashboard', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'selectedDepartmentId' => $departmentId,
            'stats' => [
                'employees' => $employeeQuery->count(),
                'pending_manager' => (clone $requestQuery)->where('status', 'pending')->count(),
                'pending_users' => User::where('status', 'pending')->count(),
                'leaves_this_month' => $monthRequestQuery->count(),
                'most_used' => $mostUsed?->name ?? 'None yet',
            ],
            'statusBreakdown' => $dashboardRequests->countBy('status'),
            'recentRequests' => (clone $requestQuery)->latest()->take(5)->get(),
            'departmentSummaries' => $departmentSummaries,
            'onLeaveToday' => LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
                ->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())->take(5)->get(),
        ]);
    }

    public function pendingUsers(Request $request): View
    {
        return $this->users($request);
    }

    public function users(Request $request): View
    {
        $allUsers = User::with('employee.departmentRecord', 'employee.positionRecord')
            ->where('status', '!=', 'pending')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search'));
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('employee', fn ($eq) => $eq->where('employee_id', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('department_id'), fn ($query) => $query->whereHas('employee', fn ($eq) => $eq->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10, ['*'], 'users_page')
            ->withQueryString();

        return view('admin.users.pending', [
            'pendingUsers' => User::where('status', 'pending')->latest()->paginate(10, ['*'], 'pending_page'),
            'allUsers' => $allUsers,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers' => $this->managerEmployees(),
            'positions' => Position::orderBy('name')->get(),
            'userFilters' => [
                'search' => $request->string('search')->toString(),
                'department_id' => $request->integer('department_id') ?: null,
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function activateUser(ActivateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $position = $this->positionForDepartment($request->integer('position_id'), $request->integer('department_id'));

        if (! $position) {
            return back()
                ->withErrors(['position_id' => 'The selected position does not belong to the selected department.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $user, $position) {
            $department = $position->department;
            $name = $this->splitName($user->name);

            // Generate employee ID
            $employeeId = Employee::generateEmployeeId($department->code, $position->name);
            $role = Employee::accessRoleFor($department->code, $position->name);

            $user->update([
                'role' => $role,
                'status' => 'active',
                'pending_employee_id' => null,
                'department_id' => $department->id,
                'position_id' => $position->id,
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'department_id' => $department->id,
                'position_id' => $position->id,
                'manager_id' => $validated['manager_id'] ?? null,
                'first_name' => $name[0],
                'last_name' => $name[1],
                'department' => $department?->name,
                'position' => $position->name,
                'gender' => $validated['gender'] ?? null,
                'date_hired' => $validated['date_hired'],
                'contact_info' => $user->email,
                'employment_status' => 'active',
                'daily_rate' => 1000,
            ]);

            $this->seedBalancesFor($employee);
            $this->notify($user, 'Account activated', 'Your ELMS account is active. You can now sign in and file leave requests.', route('home'), 'account_status');
        });

        return back()->with('success', 'Account activated, employee profile created, balances seeded, and notification sent.');
    }

    public function deactivateUser(User $user): RedirectResponse
    {
        $user->update(['status' => 'inactive']);
        $user->employee?->update(['employment_status' => 'terminated']);

        return back()->with('warning', 'Account deactivated.');
    }

    public function employees(Request $request): View
    {
        $departmentId = $request->filled('department_id') ? $request->integer('department_id') : null;

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
            ->when($request->filled('position'), fn ($query) => $query->where('position', $request->string('position')))
            ->when($request->filled('employment_status'), fn ($query) => $query->where('employment_status', $request->string('employment_status')))
            ->orderBy('employee_id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.employees.index', $this->employeeFormData() + [
            'employees' => $employees,
            'positions' => Position::orderBy('name')->get(),
            'nextEmployeeId' => $this->nextEmployeeId(),
            'selectedDepartment' => $departmentId ? Department::find($departmentId) : null,
            'selectedDepartmentId' => $departmentId,
        ]);
    }

    public function showEmployee(Employee $employee): View
    {
        $currentYear = now()->year;
        $year = request()->integer('year') ?: $currentYear;
        $typeId = request()->integer('leave_type_id') ?: null;

        $yearsWithRecords = LeaveBalance::where('employee_id', $employee->id)
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values()
            ->all();

        $years = collect(array_unique(array_merge([$currentYear], $yearsWithRecords)))
            ->sort()
            ->reverse()
            ->values();

        $employee->load([
            'user',
            'departmentRecord',
            'manager.user',
            'leaveBalances' => fn ($query) => $query
                ->where('year', $year)
                ->when($typeId, fn ($balanceQuery) => $balanceQuery->where('leave_type_id', $typeId))
                ->with('leaveType'),
            'leaveApplications.leaveType',
        ]);

        return view('admin.profile.show', [
            'employee' => $employee,
            'leaveTypes' => $this->visibleLeaveTypes($employee),
            'leaveBalances' => $this->visibleLeaveBalances($employee),
            'year' => $year,
            'years' => $years,
            'selectedTypeId' => $typeId,
            'viewingEmployee' => true,
        ]);
    }

    public function storeEmployee(EmployeeRequest $request): RedirectResponse
    {
        $position = $this->positionForDepartment($request->integer('position_id'), $request->integer('department_id'));

        if (! $position) {
            return back()
                ->withErrors(['position_id' => 'The selected position does not belong to the selected department.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $position) {
            $department = $position->department;
            $role = Employee::accessRoleFor($department->code, $position->name);
            $user = User::create([
                'name' => "{$request->first_name} {$request->last_name}",
                'email' => $request->email,
                'password' => Hash::make('password'),
                'role' => $role,
                'status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ]);

            $payload = $request->validated();
            $payload['department_id'] = $department->id;
            $payload['position_id'] = $position->id;
            $payload['position'] = $position->name;
            $payload['department'] = $department?->name;
            $payload['contact_info'] = $request->contact_info ?: $request->email;

            // Generate employee ID if not provided
            if (empty($payload['employee_id'])) {
                $payload['employee_id'] = Employee::generateEmployeeId($department->code, $position->name);
            }

            $employee = Employee::create($payload + [
                'user_id' => $user->id,
            ]);

            $this->seedBalancesFor($employee);
            $this->notify($user, 'Employee profile created', 'HR created your ELMS profile. Default password: password', route('home'), 'account_status');
        });

        return back()->with('success', 'Employee created with default password password.');
    }

    public function updateEmployee(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $position = $this->positionForDepartment($request->integer('position_id'), $request->integer('department_id'));

        if (! $position) {
            return back()
                ->withErrors(['position_id' => 'The selected position does not belong to the selected department.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $employee, $position) {
            $department = $position->department;
            $role = Employee::accessRoleFor($department->code, $position->name);
            $employee->user->update([
                'name' => "{$request->first_name} {$request->last_name}",
                'email' => $request->email,
                'role' => $role,
                'status' => $request->employment_status === 'active' ? 'active' : 'inactive',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ]);
            $payload = $request->validated();
            $payload['department'] = $department?->name;
            $payload['department_id'] = $department->id;
            $payload['position_id'] = $position->id;
            $payload['position'] = $position->name;
            $payload['contact_info'] = $request->contact_info ?: $request->email;
            $employee->update($payload);
        });

        return back()->with('success', 'Employee updated successfully.');
    }

    public function deactivateEmployee(DeactivateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validated();
        $employee->update(['employment_status' => $validated['employment_status']]);
        $employee->user->update(['status' => 'inactive']);

        return back()->with('warning', 'Employee marked as '.$validated['employment_status'].'.');
    }

    public function departments(): View
    {
        return view('admin.departments.index', [
            'departments' => Department::with(['manager', 'employees.user', 'employees.leaveApplications' => fn ($query) => $query->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())])->withCount('employees')->orderBy('name')->get(),
            'managers' => $this->managerUsers(),
        ]);
    }

    public function storeDepartment(DepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Department created.');
    }

    public function updateDepartment(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Department updated.');
    }

    public function leaveTypes(): View
    {
        return view('admin.leave-types.index', [
            'leaveTypes' => LeaveType::orderBy('name')->get(),
        ]);
    }

    public function storeLeaveType(LeaveTypeRequest $request): RedirectResponse
    {
        $leaveType = LeaveType::create($this->leaveTypePayload($request));
        $this->seedTypeBalances($leaveType);

        return back()->with('success', 'Leave type added and balances seeded.');
    }

    public function updateLeaveType(LeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($this->leaveTypePayload($request));
        $this->seedTypeBalances($leaveType, true);

        return back()->with('success', 'Leave type updated. Current-year balances were recalculated.');
    }

    public function requests(Request $request): View
    {
        $requests = LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'employee.leaveBalances', 'leaveType', 'reviewer'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search'));
                $tokens = collect(preg_split('/\s+/', $search) ?: [])->filter()->values();
                $query->where(function ($searchQuery) use ($search, $tokens): void {
                    $searchQuery->whereHas('employee', function ($employee) use ($search) {
                        $employee->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });

                    if ($tokens->count() > 1) {
                        $searchQuery->orWhereHas('employee', function ($employee) use ($tokens) {
                            $tokens->each(function (string $token) use ($employee): void {
                                $employee->where(function ($name) use ($token): void {
                                    $name->where('first_name', 'like', "%{$token}%")
                                        ->orWhere('last_name', 'like', "%{$token}%");
                                });
                            });
                        });
                    }
                });
            })
            ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
            ->when($request->leave_type_id, fn ($q, $id) => $q->where('leave_type_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('start_date', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('start_date', '<=', $date))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.requests.index', [
            'requests' => $requests,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'selectedDepartment' => $request->department_id ? Department::find($request->department_id) : null,
        ]);
    }

    public function reviewRequest(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        abort_unless($leaveApplication->status === 'pending', 422, 'Only pending requests can be reviewed.');

        $validated = $request->validated();

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

            $this->notify($leaveApplication->employee->user, 'Leave request '.$validated['status'], 'Your '.$leaveApplication->leaveType->name.' request was '.$validated['status'].' by HR.', route('home'), 'leave_status');

            if ($leaveApplication->employee->manager?->user?->status === 'active') {
                $this->notify(
                    $leaveApplication->employee->manager->user,
                    'Leave request '.$validated['status'].' by HR',
                    $leaveApplication->employee->full_name."'s ".$leaveApplication->leaveType->name.' request was '.$validated['status'].' by HR.',
                    route('manager.approvals.show', $leaveApplication),
                    'leave_status'
                );
            }
        });

        return back()->with('success', 'Leave request reviewed.');
    }

    public function reports(Request $request): View
    {
        $year = $request->integer('year') ?: now()->year;
        $departmentId = $request->integer('department_id') ?: null;
        $employeeId = $request->integer('employee_id') ?: null;

        $balanceQuery = LeaveBalance::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
            ->where('year', $year)
            ->when($departmentId, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $departmentId)));

        $employeeReport = $employeeId
            ? Employee::with(['user', 'departmentRecord', 'leaveBalances' => fn ($q) => $q->where('year', $year)->with('leaveType'), 'leaveApplications.leaveType'])->find($employeeId)
            : null;

        return view('admin.reports.index', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::with('user')->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))->orderBy('last_name')->get(),
            'positions' => Employee::when($departmentId, fn ($q) => $q->where('department_id', $departmentId))->select('position')->distinct()->orderBy('position')->pluck('position'),
            'summaries' => Department::with('employees.leaveApplications')->get(),
            'balances' => $balanceQuery->get()
                ->filter(fn (LeaveBalance $balance) => $balance->leaveType?->isVisibleForGender($balance->employee?->gender))
                ->values(),
            'employeeReport' => $employeeReport,
            'year' => $year,
            'selectedDepartmentId' => $departmentId,
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'leaves');
        $department = $request->department_id ? Department::find($request->department_id) : null;
        $key = $department ? Str::slug($department->code) : 'all-departments';
        $filename = 'elms-'.$type.'-'.$key.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request, $type) {
            $out = fopen('php://output', 'w');
            if ($type === 'balances') {
                fputcsv($out, ['Employee ID', 'Name', 'Department', 'Position', 'Leave Type', 'Compensable', 'Year', 'Allocated', 'Used', 'Remaining', 'Daily Rate', 'Compensation']);
                LeaveBalance::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
                    ->when($request->year, fn ($q, $year) => $q->where('year', $year))
                    ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
                    ->chunk(100, function ($balances) use ($out) {
                        foreach ($balances as $balance) {
                            if (! $balance->leaveType?->isVisibleForGender($balance->employee?->gender)) {
                                continue;
                            }

                            fputcsv($out, [
                                $balance->employee->employee_id,
                                $balance->employee->full_name,
                                $balance->employee->departmentRecord?->name,
                                $balance->employee->position,
                                $balance->leaveType->name,
                                $balance->leaveType->is_compensable ? 'Yes' : 'No',
                                $balance->year,
                                $balance->allocated_days,
                                $balance->used_days,
                                $balance->remaining_days,
                                $balance->employee->daily_rate,
                                $balance->leaveType->is_compensable ? $balance->remaining_days * (float) $balance->employee->daily_rate : 0,
                            ]);
                        }
                    });

                return;
            }

            fputcsv($out, ['Employee ID', 'Name', 'Department', 'Position', 'Leave Type', 'Start', 'End', 'Days', 'Status', 'Reviewed By', 'Remarks']);
            LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'leaveType', 'reviewer'])
                ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
                ->chunk(100, function ($leaves) use ($out) {
                    foreach ($leaves as $leave) {
                        fputcsv($out, [
                            $leave->employee->employee_id,
                            $leave->employee->full_name,
                            $leave->employee->departmentRecord?->name,
                            $leave->employee->position,
                            $leave->leaveType->name,
                            $leave->start_date?->toDateString(),
                            $leave->end_date?->toDateString(),
                            $leave->total_days,
                            $leave->status,
                            $leave->reviewer?->name,
                            $leave->remarks,
                        ]);
                    }
                });
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function calendar(Request $request): View
    {
        return view('admin.calendar', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'selectedDepartmentId' => $request->integer('department_id') ?: null,
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'approvedLeaves' => LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
                ->where('status', 'approved')
                ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
                ->when($request->filled('leave_type_ids'), fn ($q) => $q->whereIn('leave_type_id', (array) $request->leave_type_ids))
                ->orderBy('start_date')
                ->get(),
        ]);
    }

    public function myLeave(Request $request): View
    {
        $year = $request->integer('year') ?: now()->year;
        $employee = ($request->user() ?: auth()->user())?->employee?->load([
            'leaveBalances' => fn ($query) => $query->where('year', $year)->with('leaveType'),
            'leaveApplications.leaveType',
            'departmentRecord',
        ]);
        $leaveBalances = $this->visibleLeaveBalances($employee);

        return view('admin.my-leave', [
            'employee' => $employee,
            'year' => $year,
            'selectedTypeId' => $request->integer('leave_type_id') ?: null,
            'leaveTypes' => $this->visibleLeaveTypes($employee),
            'leaveBalances' => $leaveBalances,
            'compensationEstimate' => $leaveBalances
                ->filter(fn ($balance) => (bool) $balance->leaveType?->is_compensable)
                ->sum(fn ($balance) => $balance->remaining_days * (float) ($employee?->daily_rate ?? 0)),
        ]);
    }

    public function storeMyLeave(StoreHrLeaveRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $employee = $request->user()?->employee;
        abort_unless($employee, 403);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        if (! $leaveType->is_active) {
            throw ValidationException::withMessages(['leave_type_id' => 'This leave type is currently inactive.']);
        }

        abort_unless($leaveType->isVisibleForGender($employee->gender), 422, 'This leave type is not available for the employee gender on record.');

        $totalDays = $this->workingDaysBetween(Carbon::parse($validated['start_date']), Carbon::parse($validated['end_date']));

        if ($leaveType->requires_proof && ! $request->hasFile('proof')) {
            throw ValidationException::withMessages(['proof' => 'A supporting document is required for '.$leaveType->name.'.']);
        }

        $proofPath = $request->file('proof')?->store('leave-proofs', 'public');
        $status = $leaveType->requires_approval ? 'pending' : 'approved';

        $leave = DB::transaction(function () use ($employee, $leaveType, $validated, $totalDays, $proofPath, $status) {
            $balance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', Carbon::parse($validated['start_date'])->year)
                ->lockForUpdate()
                ->first();
            $pendingDays = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'pending')
                ->whereYear('start_date', Carbon::parse($validated['start_date'])->year)
                ->sum('total_days');

            if (! $balance || ($balance->remaining_days - (int) $pendingDays) < $totalDays) {
                throw ValidationException::withMessages(['leave_type_id' => 'You do not have enough remaining balance for this request.']);
            }

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
                $balance->increment('used_days', $totalDays);
            }

            return $leave;
        })->load(['employee', 'leaveType']);

        if ($leave->status === 'pending') {
            SystemNotification::sendToRole(
                'hr_admin',
                'Leave request pending',
                $leave->employee->full_name.' submitted a '.$leave->leaveType->name.' request.',
                route('admin.requests.index'),
                'leave_request'
            );
        }

        return back()->with('success', $leave->status === 'pending' ? 'Leave request submitted.' : 'Leave request submitted and auto-approved.');
    }

    public function notifications(): View
    {
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return view('admin.notifications', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function readNotification(SystemNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->action_url ?: route('admin.notifications'));
    }

    public function profile(): View
    {
        $currentYear = now()->year;
        $year = request()->integer('year') ?: $currentYear;
        $typeId = request()->integer('leave_type_id') ?: null;
        $employee = auth()->user()->employee;

        // Get all years with leave balances + current year
        $yearsWithRecords = LeaveBalance::where('employee_id', $employee?->id)
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values()
            ->all();

        // Ensure current year is always included
        $allYears = collect(array_unique(array_merge([$currentYear], $yearsWithRecords)))
            ->sort()
            ->reverse()
            ->values();

        return view('admin.profile.show', [
            'employee' => $employee?->load(['departmentRecord', 'leaveBalances' => fn ($q) => $q->where('year', $year)->when($typeId, fn ($b) => $b->where('leave_type_id', $typeId))->with('leaveType'), 'leaveApplications.leaveType']),
            'leaveTypes' => $this->visibleLeaveTypes($employee),
            'leaveBalances' => $this->visibleLeaveBalances($employee),
            'year' => $year,
            'years' => $allYears,
            'selectedTypeId' => $typeId,
        ]);
    }

    public function updateProfile(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'name' => "{$request->first_name} {$request->last_name}",
            'email' => $request->email,
        ]);
        $payload = $request->validated();
        $payload['contact_info'] = $request->contact_info ?: $request->email;
        $user->employee?->update($payload);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password changed successfully.');
    }

    private function employeeFormData(): array
    {
        return [
            'departments' => Department::with('positions')->where('is_active', true)->orderBy('name')->get(),
            'managers' => $this->managerEmployees(),
            'positions' => Position::orderBy('name')->get(),
            'positionConfig' => config('positions.position_ids') ?? [],
            'departmentPrefixes' => config('positions.department_prefixes') ?? [],
        ];
    }

    private function managerEmployees()
    {
        return Employee::with(['user', 'departmentRecord'])
            ->where('employment_status', 'active')
            ->orderBy('last_name')
            ->get()
            ->filter(fn (Employee $employee) => $employee->isManagerPosition())
            ->values();
    }

    private function managerUsers()
    {
        return $this->managerEmployees()
            ->pluck('user')
            ->filter()
            ->sortBy('name')
            ->values();
    }

    private function seedBalancesFor(Employee $employee): void
    {
        LeaveType::where('is_active', true)->get()->each(fn ($type) => LeaveBalance::updateOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => now()->year],
            ['allocated_days' => $type->annual_allocation]
        ));
    }

    private function visibleLeaveBalances(?Employee $employee)
    {
        $balances = $employee?->leaveBalances?->load('leaveType') ?? collect();

        return $balances
            ->filter(fn ($balance) => $balance->leaveType?->isVisibleForGender($employee?->gender))
            ->sortBy(function ($balance) {
                $name = strtolower($balance->leaveType?->name ?? '');

                return match (true) {
                    str_contains($name, 'sick') => 0,
                    str_contains($name, 'vacation') => 1,
                    str_contains($name, 'emergency') => 2,
                    str_contains($name, 'bereavement') => 3,
                    str_contains($name, 'maternity') => 4,
                    str_contains($name, 'paternity') => 5,
                    default => 99,
                };
            });
    }

    private function visibleLeaveTypes(?Employee $employee)
    {
        return LeaveType::where('is_active', true)
            ->get()
            ->filter(fn (LeaveType $leaveType) => $leaveType->isVisibleForGender($employee?->gender))
            ->sortBy(fn (LeaveType $leaveType) => match (true) {
                str_contains(strtolower($leaveType->name), 'sick') => 0,
                str_contains(strtolower($leaveType->name), 'vacation') => 1,
                str_contains(strtolower($leaveType->name), 'emergency') => 2,
                str_contains(strtolower($leaveType->name), 'bereavement') => 3,
                str_contains(strtolower($leaveType->name), 'maternity') => 4,
                str_contains(strtolower($leaveType->name), 'paternity') => 5,
                default => 99,
            });
    }

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

    private function leaveTypePayload(LeaveTypeRequest $request): array
    {
        return [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'annual_allocation' => $request->annual_allocation,
            'requires_approval' => $request->boolean('requires_approval'),
            'is_compensable' => $request->boolean('is_compensable'),
            'requires_proof' => $request->boolean('requires_proof'),
            'proof_rules' => $request->proof_rules,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function notify(User $user, string $title, string $body, ?string $url = null, string $type = 'info'): void
    {
        SystemNotification::sendTo($user, $title, $body, $url, $type);
    }

    private function positionForDepartment(int $positionId, int $departmentId): ?Position
    {
        return Position::with('department')
            ->whereKey($positionId)
            ->where('department_id', $departmentId)
            ->first();
    }

    private function nextEmployeeId(): string
    {
        return '(Auto-generated based on department & position)';
    }

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

    private function splitName(string $name): array
    {
        $parts = explode(' ', trim($name), 2);

        return [$parts[0] ?? $name, $parts[1] ?? ''];
    }
}
