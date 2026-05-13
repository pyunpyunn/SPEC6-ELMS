<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeePortalController extends Controller
{
    private const DAILY_RATE = 1000;

    public function dashboard()
    {
        $data = $this->portalData();

        return view('employee-dashboard', $data + [
            'pendingLeaves' => $data['leaveApplications']->where('status', 'pending')->count(),
            'approvedLeaveDays' => $data['leaveApplications']->where('status', 'approved')->sum('days'),
            'availableLeaveDays' => $data['leaveTypes']->sum('remaining_days'),
            'recentLeaves' => $data['leaveApplications']->take(5),
        ]);
    }

    public function createLeave()
    {
        return redirect()->route('employee.leave.index');
    }

    public function storeLeave(Request $request)
    {
        $employee = Auth::user()->employee;

        abort_unless($employee, 403, 'Employee profile is required before filing leave.');

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'max:10240'],
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        abort_unless($this->isVisibleForGender($leaveType->name, $employee->gender), 422, 'This leave type is not available for the employee gender on record.');

        LeaveApplication::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $this->inclusiveDays($validated['start_date'], $validated['end_date']),
            'reason' => $validated['reason'],
            'status' => 'pending',
            'proof_path' => $request->file('proof')?->store('leave-proofs', 'public'),
        ]);

        return redirect()
            ->route('employee.leave.history')
            ->with('success', 'Leave request submitted. It is now pending approval.');
    }

    public function leaveHistory()
    {
        return redirect()->route('employee.leave.index');
    }

    public function myLeave()
    {
        return view('employee.index', $this->portalData());
    }

    public function cancelLeave(LeaveApplication $leaveApplication)
    {
        $employee = Auth::user()->employee;

        abort_unless($employee && $leaveApplication->employee_id === $employee->id, 403);
        abort_unless($leaveApplication->status === 'pending', 403, 'Only pending requests can be cancelled.');

        $leaveApplication->delete();

        return back()->with('success', 'Pending leave request cancelled.');
    }

    public function reports()
    {
        return view('employee.reports', $this->portalData() + [
            'dailyRate' => self::DAILY_RATE,
        ]);
    }

    public function notifications()
    {
        $data = $this->portalData();

        $notifications = $data['leaveApplications']
            ->filter(fn ($leave) => in_array($leave->status, ['approved', 'rejected', 'pending'], true))
            ->map(fn ($leave) => (object) [
                'title' => match ($leave->status) {
                    'approved' => 'Leave Approved',
                    'rejected' => 'Leave Rejected',
                    default => 'Leave Pending',
                },
                'body' => ($leave->leaveType->name ?? 'Leave') . ' for ' .
                    $leave->start_date->format('M d, Y') . ' to ' . $leave->end_date->format('M d, Y') .
                    ($leave->remarks ? '. Remark: ' . $leave->remarks : '.'),
                'status' => $leave->status,
                'time' => $leave->updated_at?->format('M d, Y h:i A') ?? $leave->created_at?->format('M d, Y h:i A'),
                'unread' => $leave->status === 'pending',
            ])
            ->values();

        return view('employee.notifications', $data + compact('notifications'));
    }

    public function profile()
    {
        return view('employee.profile', $this->portalData());
    }

    private function portalData(): array
    {
        $user = Auth::user();
        $employee = $user->employee;
        $leaveApplications = $employee
            ? LeaveApplication::with('leaveType')
                ->where('employee_id', $employee->id)
                ->latest()
                ->get()
                ->map(function ($leave) {
                    $leave->days = $this->inclusiveDays($leave->start_date, $leave->end_date);
                    return $leave;
                })
            : collect();

        $this->ensureDefaultLeaveTypes();

        $order = collect($this->defaultLeaveTypes())->pluck('name')->flip();

        $leaveTypes = LeaveType::orderBy('name')
            ->get()
            ->filter(fn (LeaveType $type) => $this->isVisibleForGender($type->name, $employee?->gender))
            ->sortBy(fn ($type) => $order[$type->name] ?? 999)
            ->values()
            ->map(function ($type) use ($leaveApplications) {
            $used = $leaveApplications
                ->where('leave_type_id', $type->id)
                ->whereIn('status', ['approved', 'pending'])
                ->sum('days');
            $total = (int) ($type->annual_allocation ?? 0);

            return (object) [
                'id' => $type->id,
                'name' => $type->name,
                'used_days' => $used,
                'total_days' => $total,
                'remaining_days' => max(0, $total - $used),
                'percent_used' => $total > 0 ? min(100, round(($used / $total) * 100)) : 0,
                'policy_note' => $this->policyNote($type->name),
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

    private function ensureDefaultLeaveTypes(): void
    {
        foreach ($this->defaultLeaveTypes() as $leaveType) {
            LeaveType::query()->updateOrCreate(
                ['name' => $leaveType['name']],
                ['annual_allocation' => $leaveType['annual_allocation'], 'requires_approval' => true]
            );
        }
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

    private function policyNote(string $name): string
    {
        return match ($name) {
            'Maternity Leave' => '105 days; 120 days if solo parent; 60 days if stillbirth or miscarriage. Female employees only.',
            'Paternity Leave' => '7 days per delivery/miscarriage, first 4 only. Male employees only.',
            'Bereavement Leave' => '15 days/year. Also called RIP leave in the prototype notes.',
            default => (int) LeaveType::where('name', $name)->value('annual_allocation') . ' days/year.',
        };
    }

    private function isVisibleForGender(string $leaveTypeName, ?string $gender): bool
    {
        $gender = strtolower((string) $gender);
        $name = strtolower($leaveTypeName);

        if ($gender === 'female' && str_contains($name, 'paternity')) {
            return false;
        }

        if ($gender === 'male' && str_contains($name, 'maternity')) {
            return false;
        }

        return true;
    }
}
