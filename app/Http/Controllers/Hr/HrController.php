<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\DepartmentRequest;
use App\Http\Requests\Hr\EmployeeRequest;
use App\Http\Requests\Hr\LeaveTypeRequest;
use App\Http\Requests\Hr\ProfileRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
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
        $mostUsed = (clone $requestQuery)
            ->select('leave_type_id', DB::raw('count(*) as total'))
            ->groupBy('leave_type_id')
            ->orderByDesc('total')
            ->with('leaveType')
            ->first();

        $departmentSummaries = Department::withCount(['employees as active_employees_count' => fn ($query) => $query->where('employment_status', 'active')])
            ->with(['employees.leaveApplications' => fn ($query) => $query->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())])
            ->get()
            ->map(function (Department $department) {
                return [
                    'department' => $department,
                    'on_leave' => $department->employees->flatMap->leaveApplications->count(),
                ];
            });

        return view('hr.dashboard', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'selectedDepartmentId' => $departmentId,
            'stats' => [
                'employees' => $employeeQuery->count(),
                'pending_manager' => (clone $requestQuery)->where('status', 'pending')->count(),
                'pending_users' => User::where('status', 'pending')->count(),
                'leaves_this_month' => $monthRequestQuery->count(),
                'most_used' => $mostUsed?->leaveType?->name ?? 'None yet',
            ],
            'statusBreakdown' => (clone $requestQuery)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'recentRequests' => (clone $requestQuery)->latest()->take(5)->get(),
            'departmentSummaries' => $departmentSummaries,
            'onLeaveToday' => LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
                ->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())->take(5)->get(),
        ]);
    }

    public function pendingUsers(): View
    {
        return view('hr.users.pending', [
            'pendingUsers' => User::where('status', 'pending')->latest()->paginate(10),
            'allUsers' => User::with('employee.departmentRecord')->latest()->paginate(10, ['*'], 'users_page'),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers' => Employee::with('user', 'departmentRecord')->whereHas('user', fn ($q) => $q->where('role', 'manager'))->get(),
        ]);
    }

    public function activateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:30', 'unique:employees,employee_id'],
            'role' => ['required', 'in:employee,manager,hr_admin'],
            'department_id' => ['required', 'exists:departments,id'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'position' => ['required', 'string', 'max:120'],
            'date_hired' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $department = Department::find($validated['department_id']);
            $name = $this->splitName($user->name);

            $user->update(['role' => $validated['role'], 'status' => 'active', 'pending_employee_id' => null]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'],
                'department_id' => $validated['department_id'],
                'manager_id' => $validated['manager_id'] ?? null,
                'first_name' => $name[0],
                'last_name' => $name[1],
                'department' => $department?->name,
                'position' => $validated['position'],
                'date_hired' => $validated['date_hired'],
                'contact_info' => $user->email,
                'employment_status' => 'active',
                'daily_rate' => 1000,
            ]);

            $this->seedBalancesFor($employee);
            $this->notify($user, 'Account activated', 'Your ELMS account is active. You can now sign in and file leave requests.', route('home'));
        });

        return back()->with('success', 'Account activated, employee profile created, balances seeded, and notification sent.');
    }

    public function employees(Request $request): View
    {
        $employees = Employee::with(['user', 'departmentRecord', 'manager.user'])
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('email', 'like', "%{$search}%"));
            }))
            ->when($request->department_id, fn ($q, $id) => $q->where('department_id', $id))
            ->when($request->position, fn ($q, $position) => $q->where('position', $position))
            ->when($request->employment_status, fn ($q, $status) => $q->where('employment_status', $status))
            ->orderBy('employee_id')
            ->paginate(10)
            ->withQueryString();

        return view('hr.employees.index', $this->employeeFormData() + [
            'employees' => $employees,
            'positions' => Employee::select('position')->distinct()->orderBy('position')->pluck('position'),
            'nextEmployeeId' => $this->nextEmployeeId(),
        ]);
    }

    public function storeEmployee(EmployeeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $department = Department::find($request->department_id);
            $user = User::create([
                'name' => "{$request->first_name} {$request->last_name}",
                'email' => $request->email,
                'password' => Hash::make('temp_pass'),
                'role' => $request->role,
                'status' => 'active',
            ]);

            $employee = Employee::create($request->validated() + [
                'user_id' => $user->id,
                'department' => $department?->name,
                'contact_info' => $request->contact_info ?: $request->email,
            ]);

            $this->seedBalancesFor($employee);
            $this->notify($user, 'Employee profile created', 'HR created your ELMS profile. Temporary password: temp_pass', route('home'));
        });

        return back()->with('success', 'Employee created with temporary password temp_pass.');
    }

    public function updateEmployee(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $department = Department::find($request->department_id);
            $employee->user->update([
                'name' => "{$request->first_name} {$request->last_name}",
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->employment_status === 'active' ? 'active' : 'inactive',
            ]);
            $employee->update($request->validated() + ['department' => $department?->name]);
        });

        return back()->with('success', 'Employee updated successfully.');
    }

    public function deactivateEmployee(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate(['employment_status' => ['required', 'in:resigned,terminated']]);
        $employee->update(['employment_status' => $validated['employment_status']]);
        $employee->user->update(['status' => 'inactive']);

        return back()->with('warning', 'Employee marked as '.$validated['employment_status'].'.');
    }

    public function departments(): View
    {
        return view('hr.departments.index', [
            'departments' => Department::with(['manager', 'employees.user'])->withCount('employees')->orderBy('name')->get(),
            'managers' => User::where('role', 'manager')->where('status', 'active')->orderBy('name')->get(),
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
        return view('hr.leave-types.index', [
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
        $oldAllocation = $leaveType->annual_allocation;
        $leaveType->update($this->leaveTypePayload($request));

        if ((int) $oldAllocation !== (int) $request->annual_allocation) {
            $this->seedTypeBalances($leaveType, true);
        }

        return back()->with('success', 'Leave type updated. Current-year balances were recalculated.');
    }

    public function requests(Request $request): View
    {
        $requests = LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'leaveType', 'reviewer'])
            ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
            ->when($request->leave_type_id, fn ($q, $id) => $q->where('leave_type_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.requests.index', [
            'requests' => $requests,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'selectedDepartment' => $request->department_id ? Department::find($request->department_id) : null,
        ]);
    }

    public function reviewRequest(Request $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $leaveApplication) {
            $leaveApplication->update([
                'status' => $validated['status'],
                'remarks' => $validated['remarks'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            if ($validated['status'] === 'approved') {
                LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                    ->where('leave_type_id', $leaveApplication->leave_type_id)
                    ->where('year', $leaveApplication->start_date->year)
                    ->increment('used_days', (int) $leaveApplication->total_days);
            }

            $this->notify($leaveApplication->employee->user, 'Leave request '.$validated['status'], 'Your '.$leaveApplication->leaveType->name.' request was '.$validated['status'].'.', route('home'));
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

        return view('hr.reports.index', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::with('user')->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))->orderBy('last_name')->get(),
            'positions' => Employee::when($departmentId, fn ($q) => $q->where('department_id', $departmentId))->select('position')->distinct()->orderBy('position')->pluck('position'),
            'summaries' => Department::with('employees.leaveApplications')->get(),
            'balances' => $balanceQuery->get(),
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
                fputcsv($out, ['Employee ID', 'Name', 'Department', 'Position', 'Leave Type', 'Year', 'Allocated', 'Used', 'Remaining', 'Daily Rate', 'Compensation']);
                LeaveBalance::with(['employee.user', 'employee.departmentRecord', 'leaveType'])
                    ->when($request->year, fn ($q, $year) => $q->where('year', $year))
                    ->when($request->department_id, fn ($q, $id) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $id)))
                    ->chunk(100, function ($balances) use ($out) {
                        foreach ($balances as $balance) {
                            fputcsv($out, [
                                $balance->employee->employee_id,
                                $balance->employee->full_name,
                                $balance->employee->departmentRecord?->name,
                                $balance->employee->position,
                                $balance->leaveType->name,
                                $balance->year,
                                $balance->allocated_days,
                                $balance->used_days,
                                $balance->remaining_days,
                                $balance->employee->daily_rate,
                                $balance->remaining_days * (float) $balance->employee->daily_rate,
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
        return view('hr.calendar', [
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
        $employee = ($request->user() ?: auth()->user())?->employee?->load(['leaveBalances.leaveType', 'leaveApplications.leaveType']);

        return view('hr.my-leave', [
            'employee' => $employee,
            'year' => $request->integer('year') ?: now()->year,
            'selectedTypeId' => $request->integer('leave_type_id') ?: null,
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function notifications(): View
    {
        return view('hr.notifications', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function profile(): View
    {
        $year = request()->integer('year') ?: now()->year;
        $typeId = request()->integer('leave_type_id') ?: null;

        return view('hr.profile.show', [
            'employee' => auth()->user()->employee?->load(['departmentRecord', 'leaveBalances' => fn ($q) => $q->where('year', $year)->when($typeId, fn ($b) => $b->where('leave_type_id', $typeId))->with('leaveType')]),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'year' => $year,
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
        $user->employee?->update($request->validated());

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password changed successfully.');
    }

    private function employeeFormData(): array
    {
        return [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers' => Employee::with('user')->whereHas('user', fn ($q) => $q->where('role', 'manager'))->get(),
        ];
    }

    private function seedBalancesFor(Employee $employee): void
    {
        LeaveType::where('is_active', true)->get()->each(fn ($type) => LeaveBalance::updateOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => now()->year],
            ['allocated_days' => $type->annual_allocation]
        ));
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
            'requires_proof' => $request->boolean('requires_proof'),
            'proof_rules' => $request->proof_rules,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function notify(User $user, string $title, string $body, ?string $url = null): void
    {
        SystemNotification::create(['user_id' => $user->id, 'title' => $title, 'body' => $body, 'action_url' => $url]);
    }

    private function nextEmployeeId(): string
    {
        $latest = Employee::orderByDesc('id')->value('employee_id');
        $number = $latest ? ((int) preg_replace('/\D/', '', $latest)) + 1 : 1;

        return 'EMP-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    private function splitName(string $name): array
    {
        $parts = explode(' ', trim($name), 2);

        return [$parts[0] ?? $name, $parts[1] ?? ''];
    }
}
