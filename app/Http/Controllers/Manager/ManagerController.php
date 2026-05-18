<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\LeaveDecisionRequest;
use App\Http\Requests\Manager\ProfileRequest;
use App\Http\Requests\Manager\StoreManagerLeaveRequest;
use App\Http\Requests\UpdatePasswordRequest;
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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $manager = $this->managerEmployee($request);
        $requests = $this->teamLeaveQuery($manager)->latest()->take(5)->get();

        return view('manager.dashboard', $this->baseData($request) + [
            'pendingRequests' => $requests,
            'miniCalendar' => $this->calendarGridData($this->teamLeaveQuery($manager)->where('status', 'approved')->get(), now()->year, now()->month),
            'stats' => [
                'pending' => $this->teamLeaveQuery($manager)->where('status', 'pending')->count(),
                'on_leave_today' => $this->teamLeaveQuery($manager)->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())->count(),
                'approved_mtd' => $this->teamLeaveQuery($manager)->where('status', 'approved')->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            ],
            'onLeaveToday' => $this->teamLeaveQuery($manager)->where('status', 'approved')->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now())->get(),
        ]);
    }

    public function requests(Request $request): View
    {
        $manager = $this->managerEmployee($request);

        return view('manager.approvals.index', $this->baseData($request) + [
            'requests' => $this->teamLeaveQuery($manager)
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = trim($request->string('search'));
                    $query->whereHas('employee', function ($employee) use ($search) {
                        $employee->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
                })
                ->when($request->filled('leave_type_id'), fn ($query) => $query->where('leave_type_id', $request->integer('leave_type_id')))
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('start_date', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('start_date', '<=', $request->input('date_to')))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function showRequest(Request $request, LeaveApplication $leaveApplication): View
    {
        $manager = $this->managerEmployee($request);
        abort_unless($this->isTeamLeave($manager, $leaveApplication), 403);

        return view('manager.approvals.show', $this->baseData($request) + [
            'leave' => $leaveApplication->load(['employee.user', 'employee.departmentRecord', 'employee.leaveBalances.leaveType', 'leaveType']),
        ]);
    }

    public function reviewRequest(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        $manager = $this->managerEmployee($request);
        abort_unless($this->isTeamLeave($manager, $leaveApplication), 403);
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

            $this->notify(
                $leaveApplication->employee->user,
                'Leave request '.$validated['status'],
                'Your '.$leaveApplication->leaveType->name.' request was '.$validated['status'].' by your manager.',
                route('home'),
                'leave_status'
            );

            SystemNotification::sendToRole(
                'hr_admin',
                'Leave request '.$validated['status'],
                $leaveApplication->employee->full_name."'s ".$leaveApplication->leaveType->name.' request was '.$validated['status'].' by '.auth()->user()->name.'.',
                route('admin.requests.index'),
                'leave_status'
            );
        });

        return redirect()->route('manager.approvals.index')->with('success', 'Leave request reviewed successfully.');
    }

    public function calendar(Request $request): View
    {
        $manager = $this->managerEmployee($request);
        $month = Carbon::create(
            $request->integer('year') ?: now()->year,
            $request->integer('month') ?: now()->month,
            1
        );
        $approvedLeaves = $this->teamLeaveQuery($manager)
            ->where('status', 'approved')
            ->when($request->filled('leave_type_ids'), fn ($query) => $query->whereIn('leave_type_id', (array) $request->leave_type_ids))
            ->orderBy('start_date')
            ->get();

        return view('manager.calendar', $this->baseData($request) + [
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'approvedLeaves' => $approvedLeaves,
            'calendarMonth' => $month,
            'previousMonth' => $month->copy()->subMonth(),
            'nextMonth' => $month->copy()->addMonth(),
            'calendarGrid' => $this->calendarGridData($approvedLeaves, $month->year, $month->month),
        ]);
    }

    public function team(Request $request): View
    {
        $manager = $this->managerEmployee($request);

        return view('manager.team', $this->baseData($request) + [
            'teamMembers' => $this->teamEmployeesQuery($manager)
                ->with(['user', 'departmentRecord', 'leaveBalances.leaveType', 'leaveApplications.leaveType'])
                ->orderBy('last_name')
                ->paginate(10),
        ]);
    }

    public function myLeave(Request $request): View
    {
        $employee = $this->managerEmployee($request)?->load(['leaveBalances.leaveType', 'leaveApplications.leaveType']);

        return view('manager.my-leave', $this->baseData($request) + [
            'employee' => $employee,
            'leaveTypes' => $this->visibleLeaveTypes($employee),
            'myLeaves' => $employee?->leaveApplications()->with('leaveType')->latest()->paginate(10) ?? collect(),
        ]);
    }

    public function storeMyLeave(StoreManagerLeaveRequest $request): RedirectResponse
    {
        $employee = $this->managerEmployee($request);
        abort_unless($employee, 403, 'Manager profile is required before filing leave.');

        $validated = $request->validated();
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        if (! $leaveType->is_active) {
            throw ValidationException::withMessages(['leave_type_id' => 'This leave type is currently inactive.']);
        }

        abort_unless($leaveType->isVisibleForGender($employee->gender), 422, 'This leave type is not available for the employee gender on record.');

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Past dates are not allowed
        abort_if($startDate->lt(now()->startOfDay()), 422, 'You cannot file leave for past dates.');

        $totalDays = $this->workingDaysBetween($startDate, $endDate);

        // Duplicate prevention:
        // If employee already has a leave that overlaps the selected week/day and is not yet finished,
        // block re-application until they are done.
        $weekStart = $startDate->copy()->startOfWeek();
        $weekEnd = $startDate->copy()->endOfWeek();

        $overlappingActiveLeaveExists = LeaveApplication::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $weekEnd)
            ->where('end_date', '>=', $weekStart)
            ->whereDate('end_date', '>=', now()->startOfDay())
            ->exists();

        if ($overlappingActiveLeaveExists) {
            throw ValidationException::withMessages([
                'start_date' => 'You already have a leave scheduled for this day/week. You can apply again once you are done with your leave.',
            ]);
        }

        $proofRequired = false;

        if ($leaveType->requires_proof) {
            if ($leaveType->max_document_days === null || $leaveType->max_document_days <= 0) {
                $proofRequired = true;
            } else {
                $proofRequired = $totalDays >= $leaveType->max_document_days;
            }
        }

        if ($proofRequired && ! $request->hasFile('proof')) {
            throw ValidationException::withMessages(['proof' => 'A supporting document is required for '.$leaveType->name.' when the request is '.$leaveType->max_document_days.' or more working days.']);
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
                'Manager leave request pending',
                $leave->employee->full_name.' submitted a '.$leave->leaveType->name.' request.',
                route('admin.requests.index'),
                'leave_request'
            );
        }

        return back()->with('success', $leave->status === 'pending' ? 'Leave request submitted to HR.' : 'Leave request submitted and auto-approved.');
    }

    public function notifications(Request $request): View
    {
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return view('manager.notifications', $this->baseData($request) + [
            'notifications' => auth()->user()->notifications()->latest()->paginate(10),
        ]);
    }

    public function readNotification(SystemNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read_at' => $notification->read_at ?: now()]);

        return redirect($notification->action_url ?: route('manager.notifications'));
    }

    public function profile(Request $request): View
    {
        return view('manager.profile', $this->baseData($request) + [
            'employee' => $this->managerEmployee($request)?->load('departmentRecord'),
        ]);
    }

    public function updateProfile(ProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $employee = $request->user()->employee;
        $request->user()->fill(['name' => $validated['first_name'].' '.$validated['last_name'], 'email' => $validated['email']]);
        $employee?->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'contact_info' => $validated['email'],
        ]);

        if (! $request->user()->isDirty() && (! $employee || ! $employee->isDirty())) {
            return back()->with('warning', 'No profile changes were made.');
        }

        $request->user()->save();
        $employee?->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password changed.');
    }

    private function baseData(Request $request): array
    {
        $manager = $this->managerEmployee($request);
        $balances = $manager?->leaveBalances()
            ->with('leaveType')
            ->where('year', now()->year)
            ->get()
            ->filter(fn (LeaveBalance $balance) => $balance->leaveType?->isVisibleForGender($manager?->gender))
            ->values() ?? collect();

        return [
            'manager' => $manager,
            'department' => $manager?->departmentRecord,
            'sidebarBalances' => $balances,
            'unreadCount' => $request->user()->notifications()->whereNull('read_at')->count(),
            'latestNotifications' => $request->user()->notifications()->latest()->take(5)->get(),
            'pendingCount' => $this->teamLeaveQuery($manager)->where('status', 'pending')->count(),
        ];
    }

    private function managerEmployee(Request $request): ?Employee
    {
        return $request->user()?->employee;
    }

    private function teamLeaveQuery(?Employee $manager)
    {
        return LeaveApplication::with(['employee.user', 'employee.departmentRecord', 'employee.leaveBalances.leaveType', 'leaveType', 'reviewer'])
            ->whereHas('employee', fn ($query) => $this->scopeTeamEmployees($query, $manager));
    }

    private function teamEmployeesQuery(?Employee $manager)
    {
        return Employee::query()->where(fn ($query) => $this->scopeTeamEmployees($query, $manager));
    }

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

    private function isTeamLeave(?Employee $manager, LeaveApplication $leave): bool
    {
        return $this->teamLeaveQuery($manager)->whereKey($leave->id)->exists();
    }

    private function visibleLeaveTypes(?Employee $employee)
    {
        return LeaveType::where('is_active', true)->get()
            ->filter(fn (LeaveType $leaveType) => $leaveType->isVisibleForGender($employee?->gender))
            ->sortBy('name');
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

    private function notify(User $user, string $title, string $body, ?string $url = null, string $type = 'info'): void
    {
        SystemNotification::sendTo($user, $title, $body, $url, $type);
    }

    private function calendarGridData($leaves, int $year, int $month): array
    {
        $current = Carbon::create($year, $month, 1);
        $start = $current->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = $current->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $days = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayLeaves = $leaves->filter(fn (LeaveApplication $leave) => $leave->start_date->lte($date) && $leave->end_date->gte($date));
            $days[] = [
                'date' => $date->copy(),
                'in_month' => $date->month === $month,
                'is_today' => $date->isToday(),
                'has_leave' => $dayLeaves->isNotEmpty(),
                'events' => $dayLeaves->map(fn (LeaveApplication $leave) => [
                    'name' => $this->shortName($leave->employee),
                    'type' => $leave->leaveType?->name ?? 'Leave',
                    'class' => $this->leaveTypeClass($leave->leaveType?->name ?? ''),
                ])->values(),
            ];
        }

        return $days;
    }

    private function leaveTypeClass(string $name): string
    {
        $name = strtolower($name);

        return match (true) {
            str_contains($name, 'sick') => 'sick',
            str_contains($name, 'emergency') => 'emergency',
            str_contains($name, 'maternity') => 'maternity',
            str_contains($name, 'bereavement') => 'bereavement',
            default => 'vacation',
        };
    }

    private function shortName(?Employee $employee): string
    {
        if (! $employee) {
            return 'Employee';
        }

        $first = trim((string) $employee->first_name);
        $last = trim((string) $employee->last_name);

        return trim(substr($first, 0, 1).'. '.$last) ?: $employee->full_name;
    }
}
