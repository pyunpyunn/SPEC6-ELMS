@extends('hr.layout')

@section('content')
<div class="page-head">
    <div>
        <h1>Analytics & Reports</h1>
        <div class="muted">Department summaries, yearly compensation, and individual balance reports</div>
    </div>
    <div class="actions">
        <a class="btn btn-outline btn-sm" href="{{ route('hr.reports.export', request()->all() + ['type' => 'leaves']) }}">Export Leaves (CSV)</a>
        <a class="btn btn-outline btn-sm" href="{{ route('hr.reports.export', request()->all() + ['type' => 'balances']) }}">Export Balances (CSV)</a>
    </div>
</div>

<form class="analytics-filter-bar" method="GET">
    <select name="department_id" onchange="this.form.submit()">
        <option value="">All Departments</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
    <select name="year" onchange="this.form.submit()">
        <option value="{{ $year }}">{{ $year }}</option>
        <option value="{{ $year - 1 }}">{{ $year - 1 }}</option>
    </select>
    <button class="btn btn-primary btn-sm" type="submit">Apply</button>
</form>

<div class="grid two">
    <div class="card">
        <div class="card-h">Yearly Compensation Review</div>
        <div class="table-wrap">
            <table class="report-table">
                <thead><tr><th>Employee</th><th>Leave</th><th>Remaining</th><th>Compensation</th></tr></thead>
                <tbody>
                    @forelse($balances->take(12) as $balance)
                        <tr>
                            <td>{{ $balance->employee->full_name }}<div class="muted">{{ $balance->employee->departmentRecord?->name }} · {{ $balance->employee->position }}</div></td>
                            <td>{{ $balance->leaveType->name }}</td>
                            <td>{{ (int) $balance->remaining_days }}</td>
                            <td>PHP {{ number_format($balance->remaining_days * $balance->employee->daily_rate, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No balances found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-h">Leave Summary by Department</div>
        <div class="card-b">
            @foreach($summaries as $department)
                <a href="{{ route('hr.requests.index', ['department_id' => $department->id]) }}" style="display:block;padding:12px 0;border-bottom:1px solid var(--border)">
                    <strong>{{ $department->name }}</strong>
                    <div class="muted">{{ $department->employees->flatMap->leaveApplications->where('status','approved')->count() }} approved requests</div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card" style="margin-top:16px">
    <div class="card-h">Individual Balance Report</div>
    <div class="card-b">
        <form class="analytics-filter-bar" method="GET">
            <select name="department_id" onchange="this.form.submit()">
                <option value="">Choose department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            <select name="position">
                <option value="">All positions</option>
                @foreach($positions as $position)
                    <option @selected(request('position') === $position)>{{ $position }}</option>
                @endforeach
            </select>
            <select name="employee_id" required>
                <option value="">Choose employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->full_name }} - {{ $employee->position }}</option>
                @endforeach
            </select>
            <select name="year">
                <option value="{{ $year }}">{{ $year }}</option>
                <option value="{{ $year - 1 }}">{{ $year - 1 }}</option>
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Generate Report</button>
        </form>

        @if($employeeReport)
            <div class="card" style="box-shadow:none;margin-top:12px">
                <div class="card-h">
                    {{ $employeeReport->full_name }}
                    <a class="btn btn-outline btn-sm" href="{{ route('hr.reports.export', ['type' => 'balances', 'department_id' => $employeeReport->department_id, 'year' => $year]) }}">Download CSV</a>
                </div>
                <div class="card-b">
                    <p><strong>Employee ID:</strong> {{ $employeeReport->employee_id }} | <strong>Department:</strong> {{ $employeeReport->departmentRecord?->name }} | <strong>Position:</strong> {{ $employeeReport->position }} | <strong>Daily Rate:</strong> PHP {{ number_format($employeeReport->daily_rate, 2) }}</p>
                    <table class="report-table" style="margin-top:14px;width:100%">
                        <thead><tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th><th>Yearly Compensation</th></tr></thead>
                        <tbody>
                            @foreach($employeeReport->leaveBalances as $balance)
                                <tr>
                                    <td>{{ $balance->leaveType->name }}</td>
                                    <td>{{ (int) $balance->allocated_days }}</td>
                                    <td>{{ (int) $balance->used_days }}</td>
                                    <td>{{ (int) $balance->remaining_days }}</td>
                                    <td>PHP {{ number_format($balance->remaining_days * $employeeReport->daily_rate, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
