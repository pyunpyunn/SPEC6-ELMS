<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeePortalController extends Controller
{
    private const DAILY_RATE = 1000;

    public function dashboard()
    {
        $data = $this->portalData();

        return view('employee.dashboard', $data + [
            'pendingLeaves' => $data['leaveApplications']->where('status', 'pending')->count(),
            'approvedLeaveDays' => $data['leaveApplications']->where('status', 'approved')->sum('days'),
            'availableLeaveDays' => $data['leaveTypes']->sum('remaining_days'),
            'recentLeaves' => $data['leaveApplications']->take(5),
        ]);
    }

    public function createLeave()
    {
        return redirect()->route('employee.leaves.index');
    }

    public function storeLeave(Request $request)
    {
        $employee = Auth::user()->employee;

        abort_unless($employee, 403, 'Employee profile is required before filing leave.');
        $this->syncCurrentYearBalances($employee);

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'max:10240'],
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        if (! $leaveType->is_active) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'This leave type is currently inactive.',
            ]);
        }

        abort_unless($leaveType->isVisibleForGender($employee->gender), 422, 'This leave type is not available for the employee gender on record.');
        $totalDays = $this->inclusiveDays($validated['start_date'], $validated['end_date']);

        if ($leaveType->requires_proof && ! $request->hasFile('proof')) {
            throw ValidationException::withMessages([
                'proof' => 'A supporting document is required for '.$leaveType->name.'.',
            ]);
        }

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', Carbon::parse($validated['start_date'])->year)
            ->first();
        $pendingDays = LeaveApplication::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'pending')
            ->whereYear('start_date', Carbon::parse($validated['start_date'])->year)
            ->sum('total_days');

        if (! $balance || ($balance->remaining_days - (int) $pendingDays) < $totalDays) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'You do not have enough remaining balance for this leave request.',
            ]);
        }

        $proofPath = $request->file('proof')?->store('leave-proofs', 'public');
        $status = $leaveType->requires_approval ? 'pending' : 'approved';

        $leave = DB::transaction(function () use ($employee, $leaveType, $validated, $totalDays, $proofPath, $status) {
            $lockedBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', Carbon::parse($validated['start_date'])->year)
                ->lockForUpdate()
                ->first();
            $pendingDays = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'pending')
                ->whereYear('start_date', Carbon::parse($validated['start_date'])->year)
                ->sum('total_days');

            if (! $lockedBalance || ($lockedBalance->remaining_days - (int) $pendingDays) < $totalDays) {
                throw ValidationException::withMessages([
                    'leave_type_id' => 'You do not have enough remaining balance for this leave request.',
                ]);
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
                $lockedBalance->increment('used_days', $totalDays);
            }

            return $leave;
        })->load(['employee.user', 'employee.manager.user', 'employee.departmentRecord', 'leaveType']);

        if ($leave->status === 'pending') {
            $this->notifyLeaveSubmitted($leave);
        } else {
            $this->notifyLeaveAutoApproved($leave);
        }

        return redirect()
            ->route('employee.leaves.index')
            ->with('success', $leave->status === 'pending'
                ? 'Leave request submitted. It is now pending approval.'
                : 'Leave request submitted and approved based on the leave type configuration.');
    }

    public function leaveHistory()
    {
        return redirect()->route('employee.leaves.index');
    }

    public function myLeave()
    {
        $data = $this->portalData();
        $status = request('status');

        if (in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
            $data['leaveApplications'] = $data['leaveApplications']
                ->where('status', $status)
                ->values();
        }

        return view('employee.leaves.index', $data);
    }

    public function cancelLeave(LeaveApplication $leaveApplication)
    {
        $employee = Auth::user()->employee;

        abort_unless($employee && $leaveApplication->employee_id === $employee->id, 403);

        if ($leaveApplication->status !== 'pending') {
            return back()->with('error', 'Cannot cancel a reviewed leave.');
        }

        $leaveApplication->delete();

        return back()->with('success', 'Pending leave request cancelled.');
    }

    public function reports()
    {
        $employee = Auth::user()?->employee;

        return view('employee.reports', $this->portalData() + [
            'dailyRate' => (float) ($employee?->daily_rate ?? self::DAILY_RATE),
        ]);
    }

    public function notifications()
    {
        $data = $this->portalData();
        Auth::user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        $notifications = Auth::user()->notifications()->latest()->paginate(15);

        return view('employee.notifications', $data + compact('notifications'));
    }

    public function readNotification(SystemNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read_at' => $notification->read_at ?: now()]);

        return redirect($notification->action_url ?: route('employee.notifications'));
    }

    public function profile()
    {
        return view('employee.profile', $this->portalData());
    }

    protected function portalData(): array
    {
        $user = Auth::user();
        $employee = $user->employee;
        $leaveApplications = $employee
            ? LeaveApplication::with('leaveType')
                ->with('reviewer')
                ->where('employee_id', $employee->id)
                ->latest()
                ->get()
                ->map(function ($leave) {
                    $leave->days = (int) ($leave->total_days ?: $this->inclusiveDays($leave->start_date, $leave->end_date));
                    return $leave;
                })
            : collect();

        $order = collect($this->defaultLeaveTypes())->pluck('name')->flip();
        $currentYear = now()->year;

        if ($employee) {
            $this->syncCurrentYearBalances($employee);
        }

        $pendingDaysByType = $leaveApplications
            ->where('status', 'pending')
            ->filter(fn ($leave) => $leave->start_date->year === $currentYear)
            ->groupBy('leave_type_id')
            ->map(fn ($leaves) => (int) $leaves->sum('total_days'));

        $leaveTypes = $employee
            ? LeaveBalance::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', $currentYear)
                ->whereHas('leaveType', fn ($query) => $query->where('is_active', true))
                ->get()
            : collect();

        $leaveTypes = $leaveTypes
            ->filter(fn (LeaveBalance $balance) => $balance->leaveType?->isVisibleForGender($employee?->gender))
            ->sortBy(fn (LeaveBalance $balance) => $order[$balance->leaveType->name] ?? 999)
            ->values()
            ->map(function (LeaveBalance $balance) use ($pendingDaysByType) {
            $type = $balance->leaveType;
            $pending = (int) ($pendingDaysByType[$type->id] ?? 0);
            $used = (int) $balance->used_days + $pending;
            $total = (int) $balance->allocated_days;

            return (object) [
                'id' => $type->id,
                'name' => $type->name,
                'used_days' => $used,
                'approved_used_days' => (int) $balance->used_days,
                'pending_days' => $pending,
                'total_days' => $total,
                'remaining_days' => max(0, $total - $used),
                'actual_remaining_days' => $balance->remaining_days,
                'percent_used' => $total > 0 ? min(100, round(($used / $total) * 100)) : 0,
                'policy_note' => $this->policyNote($type),
                'requires_approval' => (bool) $type->requires_approval,
                'requires_proof' => (bool) $type->requires_proof,
                'is_compensable' => (bool) $type->is_compensable,
                'proof_rules' => $type->proof_rules,
            ];
        });

        return [
            'user' => $user,
            'employee' => $employee,
            'leaveTypes' => $leaveTypes,
            'employeeLeaveBalances' => $leaveTypes,
            'leaveApplications' => $leaveApplications,
        ];
    }

    private function inclusiveDays($start, $end): int
    {
        $start = $start instanceof Carbon ? $start : Carbon::parse($start);
        $end = $end instanceof Carbon ? $end : Carbon::parse($end);

        return $start && $end ? $start->diffInDays($end) + 1 : 0;
    }

    private function defaultLeaveTypes(): array
    {
        return [
            ['name' => 'Sick Leave', 'annual_allocation' => 15],
            ['name' => 'Vacation Leave', 'annual_allocation' => 15],
            ['name' => 'Bereavement Leave', 'annual_allocation' => 15],
            ['name' => 'Special Emergency Leave', 'annual_allocation' => 15],
            ['name' => 'Maternity Leave', 'annual_allocation' => 105],
            ['name' => 'Paternity Leave', 'annual_allocation' => 7],
        ];
    }

    private function policyNote(LeaveType $type): string
    {
        if ($type->proof_rules) {
            return $type->proof_rules;
        }

        return match ($type->name) {
            'Maternity Leave' => '105 days; 120 days if solo parent; 60 days if stillbirth or miscarriage. Female employees only.',
            'Paternity Leave' => '7 days per delivery/miscarriage, first 4 only. Male employees only.',
            'Bereavement Leave' => '15 days/year. Also called RIP leave in the prototype notes.',
            default => (int) $type->annual_allocation.' days/year.'.($type->requires_approval ? '' : ' Auto-approved.'),
        };
    }

    private function syncCurrentYearBalances($employee): void
    {
        $year = now()->year;

        LeaveType::where('is_active', true)->get()->each(function (LeaveType $type) use ($employee, $year): void {
            $usedDays = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('total_days');

            LeaveBalance::updateOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year],
                ['allocated_days' => $type->annual_allocation, 'used_days' => $usedDays]
            );
        });
    }

    private function notifyLeaveSubmitted(LeaveApplication $leave): void
    {
        $title = 'Leave request pending';
        $body = $leave->employee->full_name.' submitted a '.$leave->leaveType->name.' request for '.
            $leave->start_date->format('M d, Y').' to '.$leave->end_date->format('M d, Y').'.';

        $managerRecipients = collect();

        if ($leave->employee->manager?->user?->status === 'active') {
            $managerRecipients->push($leave->employee->manager->user);
        }

        if ($managerRecipients->isEmpty() && $leave->employee->department_id) {
            $managerRecipients = User::with('employee.departmentRecord')
                ->where('status', 'active')
                ->whereHas('employee', fn ($query) => $query->where('department_id', $leave->employee->department_id))
                ->get()
                ->filter(fn (User $user) => $user->hasAccessRole('manager'))
                ->values();
        }

        $managerRecipients
            ->unique('id')
            ->each(fn (User $manager) => SystemNotification::sendTo(
                $manager,
                $title,
                $body,
                route('manager.approvals.show', $leave),
                'leave_request'
            ));

        SystemNotification::sendToRole(
            'hr_admin',
            $title,
            $body,
            route('admin.requests.index'),
            'leave_request'
        );
    }

    private function notifyLeaveAutoApproved(LeaveApplication $leave): void
    {
        SystemNotification::sendTo(
            $leave->employee->user,
            'Leave request approved',
            'Your '.$leave->leaveType->name.' request was automatically approved by the leave configuration.',
            route('employee.leaves.show', $leave),
            'leave_status'
        );

        SystemNotification::sendToRole(
            'hr_admin',
            'Leave request auto-approved',
            $leave->employee->full_name."'s ".$leave->leaveType->name.' request was automatically approved.',
            route('admin.requests.index'),
            'leave_status'
        );
    }
}
