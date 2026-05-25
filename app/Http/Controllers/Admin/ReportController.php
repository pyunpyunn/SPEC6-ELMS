<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $year = $this->reportYear($request);

        return view('admin.reports.index', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => Position::with('department')->orderBy('name')->get(['id', 'department_id', 'name']),
            'employees' => Employee::with(['departmentRecord:id,name', 'positionRecord:id,name'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'employee_id', 'department_id', 'position_id', 'first_name', 'last_name']),
            'year' => $year,
            'yearOptions' => $this->yearOptions(),
        ]);
    }

    public function yearlyCompensation(Request $request): JsonResponse
    {
        $year = $this->reportYear($request);
        $departmentId = $request->integer('department') ?: null;

        if ($departmentId && ! Department::whereKey($departmentId)->exists()) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $compensableTypes = $this->compensableLeaveTypes();

        return response()->json([
            'year' => $year,
            'department' => $departmentId,
            'rows' => $this->yearlyCompensationRows($year, $departmentId)->values(),
            'compensation_types' => $compensableTypes->values(),
            'compensation_rows' => $this->employeeCompensationRows($year, $departmentId, $compensableTypes)->values(),
        ]);
    }

    public function individualBalance(Request $request): JsonResponse
    {
        $year = $this->reportYear($request);
        $employeeId = $request->integer('employeeId') ?: $request->integer('employee_id');

        if (! $employeeId) {
            return response()->json(['message' => 'Employee ID is required.'], 422);
        }

        $employee = Employee::with(['departmentRecord:id,name', 'positionRecord:id,name'])->find($employeeId);

        if (! $employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        return response()->json($this->individualBalancePayload($employee, $year));
    }

    public function exportYearlyCompensation(Request $request)
    {
        $year = $this->reportYear($request);
        $departmentId = $request->integer('department') ?: null;
        $department = $departmentId ? Department::find($departmentId) : null;

        if ($departmentId && ! $department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $summaryRows = $this->yearlyCompensationRows($year, $departmentId);
        $compensableTypes = $this->compensableLeaveTypes();
        $compensationRows = $this->employeeCompensationRows($year, $departmentId, $compensableTypes);
        $departmentName = $department ? $this->fileToken($department->name) : 'ALL_DEPARTMENTS';
        $filename = "leave_summary_{$departmentName}_{$year}.csv";

        return response()->streamDownload(function () use ($summaryRows, $compensationRows, $compensableTypes): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $writeCsv = fn (array $row) => fputcsv($out, $row, ',', '"', '\\');

            $writeCsv(['DEPARTMENT LEAVE SUMMARY']);
            $writeCsv($this->yearlyColumns());

            foreach ($summaryRows as $row) {
                $writeCsv([
                    $row['department'],
                    $row['employee_count'],
                    $row['total_leave_days_taken'],
                    $row['avg_leave_per_employee'],
                    $row['sick_leave'],
                    $row['vacation_leave'],
                    $row['emergency_leave'],
                    $row['unpaid_leave'],
                ]);
            }

            $writeCsv([]);
            $writeCsv(['EMPLOYEE COMPENSATION BREAKDOWN']);
            $writeCsv($this->employeeCompensationColumns($compensableTypes));

            foreach ($compensationRows as $row) {
                $csvRow = [
                    $row['employee_id'],
                    $row['full_name'],
                    $row['department'],
                    $row['position'],
                ];

                foreach ($row['leave_compensations'] as $leaveCompensation) {
                    $csvRow[] = $leaveCompensation['days_used'];
                    $csvRow[] = $leaveCompensation['days_remaining'];
                    $csvRow[] = $leaveCompensation['total_compensation'];
                }

                $csvRow[] = $row['total_leave_compensation'];
                $writeCsv($csvRow);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportIndividualBalance(Request $request)
    {
        $year = $this->reportYear($request);
        $employeeId = $request->integer('employeeId') ?: $request->integer('employee_id');

        if (! $employeeId) {
            return response()->json(['message' => 'Employee ID is required.'], 422);
        }

        $employee = Employee::with(['departmentRecord:id,name', 'positionRecord:id,name'])->find($employeeId);

        if (! $employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $payload = $this->individualBalancePayload($employee, $year);
        $compensationRates = $this->compensableLeaveTypes($employee)
            ->mapWithKeys(fn (array $type) => [
                $type['name'] => $this->formatCurrency($this->compensationRateFor($type, $employee)),
            ]);
        $filename = 'leave_balance_'.$this->fileToken($employee->last_name ?: 'Employee').'_'.$this->fileToken($employee->first_name ?: $employee->id).'_'.$year.'.csv';

        return response()->streamDownload(function () use ($payload, $compensationRates): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $writeCsv = fn (array $row) => fputcsv($out, $row, ',', '"', '\\');

            $writeCsv(['Full Name', $payload['employee']['full_name']]);
            $writeCsv(['Employee ID', $payload['employee']['employee_id']]);
            $writeCsv(['Department', $payload['employee']['department']]);
            $writeCsv(['Position', $payload['employee']['position']]);
            $writeCsv(['Year', $payload['year']]);
            $writeCsv([]);
            $writeCsv(['LEAVE BALANCE']);
            $writeCsv(['Leave Type', 'Total Entitlement', 'Days Used', 'Days Remaining']);

            foreach ($payload['balances'] as $row) {
                $writeCsv([
                    $row['leave_type'],
                    $row['total_entitlement'],
                    $row['days_used'],
                    $row['days_remaining'],
                ]);
            }

            $writeCsv([]);
            $writeCsv(['LEAVE COMPENSATION SUMMARY']);
            $writeCsv(['Leave Type', 'Days Used', 'Compensation Per Day', 'Total Compensation']);

            foreach ($payload['leave_compensation_summary'] as $row) {
                $writeCsv([
                    $row['leave_type'],
                    $row['days_used'],
                    $compensationRates->get($row['leave_type'], $this->formatCurrency(0)),
                    $row['total_compensation'],
                ]);
            }

            $writeCsv(['Total Leave Compensation', '', '', $payload['total_leave_compensation']]);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function export(Request $request)
    {
        return $request->query('section') === 'individual'
            ? $this->exportIndividualBalance($request)
            : $this->exportYearlyCompensation($request);
    }

    public function calendar(Request $request): View
    {
        return app(HrController::class)->calendar($request);
    }

    private function yearlyCompensationRows(int $year, ?int $departmentId): Collection
    {
        $departments = Department::withCount('employees')
            ->where('is_active', true)
            ->when($departmentId, fn ($query) => $query->whereKey($departmentId))
            ->orderBy('name')
            ->get();

        $typeTotals = LeaveApplication::with(['employee:id,department_id', 'leaveType:id,name'])
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereHas('employee', function ($query) use ($departmentId): void {
                if ($departmentId) {
                    $query->where('department_id', $departmentId);
                }
            })
            ->get()
            ->groupBy(fn (LeaveApplication $leave) => $leave->employee?->department_id)
            ->map(function (Collection $departmentLeaves): Collection {
                return $departmentLeaves
                    ->groupBy(fn (LeaveApplication $leave) => $leave->leaveType?->name ?? 'Unknown')
                    ->map(function (Collection $leaveTypeLeaves, string $leaveType): array {
                        return [
                            'leave_type' => $leaveType,
                            'days' => (float) $leaveTypeLeaves->sum('total_days'),
                        ];
                    })
                    ->values();
            });

        return $departments->map(function (Department $department) use ($typeTotals): array {
            $leaveRows = $typeTotals->get($department->id, collect());
            $total = (float) $leaveRows->sum('days');
            $employeeCount = (int) $department->employees_count;

            return [
                'department' => $department->name,
                'employee_count' => $employeeCount,
                'total_leave_days_taken' => $this->formatDays($total),
                'avg_leave_per_employee' => $employeeCount > 0 ? $this->formatDays($total / $employeeCount) : '0',
                'sick_leave' => $this->formatDays($this->sumType($leaveRows, 'sick')),
                'vacation_leave' => $this->formatDays($this->sumType($leaveRows, 'vacation')),
                'emergency_leave' => $this->formatDays($this->sumType($leaveRows, 'emergency')),
                'unpaid_leave' => $this->formatDays($this->sumType($leaveRows, 'unpaid')),
            ];
        });
    }

    private function employeeCompensationRows(int $year, ?int $departmentId, Collection $compensableTypes): Collection
    {
        $balanceDays = $this->balanceDaysByEmployeeAndLeaveType($year, $compensableTypes, $departmentId);

        return Employee::with(['departmentRecord:id,name', 'positionRecord:id,name'])
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->when($departmentId, fn ($query) => $query->where('employees.department_id', $departmentId))
            ->orderBy('departments.name')
            ->orderBy('employees.last_name')
            ->orderBy('employees.first_name')
            ->select('employees.*')
            ->get()
            ->map(function (Employee $employee) use ($compensableTypes, $balanceDays): array {
                $employeeBalanceDays = $balanceDays->get($employee->id, collect());
                $total = 0.0;

                $leaveCompensations = $compensableTypes->map(function (array $type) use ($employee, $employeeBalanceDays, &$total): array {
                    if (! $this->leaveTypeAppliesToEmployee($type, $employee)) {
                        return [
                            'leave_type_id' => $type['id'],
                            'leave_type' => $type['name'],
                            'days_used' => '0',
                            'days_remaining' => '0',
                            'total_compensation' => $this->formatCurrency(0),
                        ];
                    }

                    $days = $employeeBalanceDays->get($type['id'], [
                        'allocated_days' => (float) $type['annual_allocation'],
                        'used_days' => 0,
                        'remaining_days' => (float) $type['annual_allocation'],
                    ]);
                    $daysUsed = (float) $days['used_days'];
                    $daysRemaining = max(0, (float) $days['remaining_days']);
                    $compensation = $daysRemaining * $this->compensationRateFor($type, $employee);
                    $total += $compensation;

                    return [
                        'leave_type_id' => $type['id'],
                        'leave_type' => $type['name'],
                        'days_used' => $this->formatWholeDays($daysUsed),
                        'days_remaining' => $this->formatWholeDays($daysRemaining),
                        'total_compensation' => $this->formatCurrency($compensation),
                    ];
                })->values();

                return [
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->full_name,
                    'department' => $employee->departmentRecord?->name ?? $employee->department ?? 'N/A',
                    'position' => $employee->positionRecord?->name ?? $employee->position ?? 'N/A',
                    'leave_compensations' => $leaveCompensations,
                    'total_leave_compensation' => $this->formatCurrency($total),
                ];
            });
    }

    private function individualBalancePayload(Employee $employee, int $year): array
    {
        $compensableTypes = $this->compensableLeaveTypes($employee);
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->get()
            ->keyBy('leave_type_id');

        $rows = $compensableTypes
            ->map(function (array $type) use ($employee, $year, $balances): array {
                $balance = $balances->get($type['id']);
                $entitlement = (float) ($balance?->allocated_days ?? $type['annual_allocation'] ?? 0);
                $used = $this->approvedLeaveDaysFor($employee, $type['id'], $year);
                $remaining = max(0, $entitlement - $used);

                return [
                    'leave_type' => $type['name'],
                    'total_entitlement' => $this->formatDays($entitlement),
                    'days_used' => $this->formatDays($used),
                    'days_remaining' => $this->formatDays($remaining),
                ];
            })
            ->values();

        $leaveCompensationRows = $this->leaveCompensationRowsForEmployee($employee, $year, $compensableTypes, $balances);
        $totalLeaveCompensation = $leaveCompensationRows->sum('total_compensation_value');

        return [
            'year' => $year,
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'department_id' => $employee->department_id,
                'department' => $employee->departmentRecord?->name ?? $employee->department,
                'position_id' => $employee->position_id,
                'position' => $employee->positionRecord?->name ?? $employee->position,
            ],
            'balances' => $rows,
            'leave_compensation_summary' => $leaveCompensationRows->map(fn (array $row) => [
                'leave_type' => $row['leave_type'],
                'days_used' => $row['days_used'],
                'days_remaining' => $row['days_remaining'],
                'total_compensation' => $row['total_compensation'],
            ])->values(),
            'total_leave_compensation' => $this->formatCurrency($totalLeaveCompensation),
        ];
    }

    private function leaveCompensationRowsForEmployee(Employee $employee, int $year, Collection $compensableTypes, Collection $balances): Collection
    {
        return $compensableTypes
            ->map(function (array $type) use ($employee, $year, $balances): array {
                $daysUsed = $this->approvedLeaveDaysFor($employee, $type['id'], $year);
                $entitlement = (float) ($balances->get($type['id'])?->allocated_days ?? $type['annual_allocation'] ?? 0);
                $daysRemaining = max(0, $entitlement - $daysUsed);
                $total = $daysRemaining * $this->compensationRateFor($type, $employee);

                return [
                    'leave_type_id' => $type['id'],
                    'leave_type' => $type['name'],
                    'days_used' => $this->formatWholeDays($daysUsed),
                    'days_remaining' => $this->formatWholeDays($daysRemaining),
                    'total_compensation' => $this->formatCurrency($total),
                    'total_compensation_value' => $total,
                ];
            })
            ->values();
    }

    private function balanceDaysByEmployeeAndLeaveType(int $year, Collection $compensableTypes, ?int $departmentId): Collection
    {
        if ($compensableTypes->isEmpty()) {
            return collect();
        }

        $typeIds = $compensableTypes->pluck('id');

        $balances = LeaveBalance::with('employee:id,department_id')
            ->where('year', $year)
            ->whereIn('leave_type_id', $typeIds)
            ->whereHas('employee', function ($query) use ($departmentId): void {
                if ($departmentId) {
                    $query->where('department_id', $departmentId);
                }
            })
            ->get()
            ->groupBy('employee_id')
            ->map(function (Collection $employeeBalances): Collection {
                return $employeeBalances
                    ->groupBy('leave_type_id')
                    ->mapWithKeys(function (Collection $leaveTypeBalances, int|string $leaveTypeId): array {
                        $allocatedDays = (float) $leaveTypeBalances->sum('allocated_days');
                        $usedDays = (float) $leaveTypeBalances->sum('used_days');

                        return [
                            (int) $leaveTypeId => [
                                'allocated_days' => $allocatedDays,
                                'balance_used_days' => $usedDays,
                            ],
                        ];
                    });
            });

        $approvedDays = LeaveApplication::with('employee:id,department_id')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereIn('leave_type_id', $typeIds)
            ->whereHas('employee', function ($query) use ($departmentId): void {
                if ($departmentId) {
                    $query->where('department_id', $departmentId);
                }
            })
            ->get()
            ->groupBy('employee_id')
            ->map(function (Collection $employeeLeaves): Collection {
                return $employeeLeaves
                    ->groupBy('leave_type_id')
                    ->mapWithKeys(fn (Collection $leaveTypeLeaves, int|string $leaveTypeId): array => [
                        (int) $leaveTypeId => (float) $leaveTypeLeaves->sum('total_days'),
                    ]);
            });

        return $balances
            ->keys()
            ->merge($approvedDays->keys())
            ->unique()
            ->mapWithKeys(function (int|string $employeeId) use ($balances, $approvedDays, $compensableTypes): array {
                $employeeBalances = $balances->get($employeeId, collect());
                $employeeApprovedDays = $approvedDays->get($employeeId, collect());

                return [
                    (int) $employeeId => $compensableTypes->mapWithKeys(function (array $type) use ($employeeBalances, $employeeApprovedDays): array {
                        $balance = $employeeBalances->get($type['id'], []);
                        $allocatedDays = (float) ($balance['allocated_days'] ?? $type['annual_allocation'] ?? 0);
                        $approvedUsedDays = (float) $employeeApprovedDays->get($type['id'], 0);

                        return [
                            $type['id'] => [
                                'allocated_days' => $allocatedDays,
                                'used_days' => $approvedUsedDays,
                                'remaining_days' => max(0, $allocatedDays - $approvedUsedDays),
                            ],
                        ];
                    }),
                ];
            });
    }

    private function compensableLeaveTypes(?Employee $employee = null): Collection
    {
        $amountColumn = $this->leaveTypeCompensationAmountColumn();

        return LeaveType::where('is_active', true)
            ->where('is_compensable', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (LeaveType $leaveType) => ! $employee || $leaveType->isVisibleForGender($employee->gender))
            ->map(function (LeaveType $leaveType) use ($amountColumn): array {
                $configuredAmount = $amountColumn && is_numeric($leaveType->{$amountColumn})
                    ? (float) $leaveType->{$amountColumn}
                    : null;

                return [
                    'id' => $leaveType->id,
                    'name' => $leaveType->name,
                    'gender' => $leaveType->gender,
                    'annual_allocation' => (float) $leaveType->annual_allocation,
                    'days_header' => $leaveType->name.' Days Used',
                    'remaining_header' => $leaveType->name.' Days Remaining',
                    'compensation_header' => $leaveType->name.' Compensation',
                    'compensation_per_day_value' => $configuredAmount,
                ];
            })
            ->values();
    }

    private function compensationRateFor(array $type, Employee $employee): float
    {
        if ($type['compensation_per_day_value'] !== null) {
            return (float) $type['compensation_per_day_value'];
        }

        return is_numeric($employee->daily_rate) ? (float) $employee->daily_rate : 0.0;
    }

    private function approvedLeaveDaysFor(Employee $employee, int $leaveTypeId, int $year): float
    {
        return (float) LeaveApplication::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_days');
    }

    private function leaveTypeAppliesToEmployee(array $type, Employee $employee): bool
    {
        $gender = Str::lower((string) $employee->gender);
        $typeGender = Str::lower((string) ($type['gender'] ?? ''));

        if ($typeGender !== '') {
            return $gender === $typeGender;
        }

        $name = Str::lower((string) $type['name']);

        if (str_contains($name, 'maternity')) {
            return $gender === 'female';
        }

        if (str_contains($name, 'paternity')) {
            return $gender === 'male';
        }

        return true;
    }

    private function leaveTypeCompensationAmountColumn(): ?string
    {
        return collect([
            'compensation_per_day',
            'compensation_amount_per_day',
            'compensation_amount',
            'daily_compensation_amount',
            'daily_compensation',
            'compensation_rate',
        ])->first(fn (string $column) => Schema::hasColumn('leave_types', $column));
    }

    private function yearOptions(): array
    {
        $currentYear = now()->year;
        $firstLeaveDate = LeaveApplication::min('start_date');
        $firstLeaveYear = $firstLeaveDate ? (int) date('Y', strtotime((string) $firstLeaveDate)) : $currentYear;
        $minimumYear = min(
            2022,
            $firstLeaveYear,
            (int) (LeaveBalance::min('year') ?: $currentYear)
        );

        return range($minimumYear, $currentYear);
    }

    private function reportYear(Request $request): int
    {
        $year = $request->integer('year') ?: now()->year;

        return max(2022, min($year, now()->year));
    }

    private function yearlyColumns(): array
    {
        return [
            'Department',
            'Employee Count',
            'Total Leave Days Taken',
            'Avg Leave Per Employee',
            'Sick Leave',
            'Vacation Leave',
            'Emergency Leave',
            'Unpaid Leave',
        ];
    }

    private function employeeCompensationColumns(Collection $compensableTypes): array
    {
        $columns = ['Employee ID', 'Full Name', 'Department', 'Position'];

        foreach ($compensableTypes as $type) {
            $columns[] = $type['days_header'];
            $columns[] = $type['remaining_header'];
            $columns[] = $type['compensation_header'];
        }

        $columns[] = 'Total Leave Compensation';

        return $columns;
    }

    private function sumType(Collection $leaveRows, string $needle): float
    {
        return (float) $leaveRows
            ->filter(fn (array $row) => str_contains(Str::lower((string) $row['leave_type']), $needle))
            ->sum('days');
    }

    private function formatDays(float $days): string
    {
        $formatted = number_format($days, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }

    private function formatWholeDays(float $days): string
    {
        return (string) (int) round($days);
    }

    private function formatCurrency(float $amount): string
    {
        return '₱ '.number_format($amount, 2, '.', ',');
    }

    private function fileToken(string $value): string
    {
        $token = preg_replace('/[^A-Za-z0-9]+/', '_', trim($value));

        return trim((string) $token, '_') ?: 'REPORT';
    }
}
