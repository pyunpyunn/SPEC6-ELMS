@extends('hr.layout')

@section('content')
<div class="page-head" id="page-reports">
    <div>
        <h1>Analytics & Reports</h1>
    </div>
</div>

<form class="analytics-search-bar" method="GET" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <input type="hidden" name="section" value="{{ $section }}">
    <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
    <input type="hidden" name="position" value="{{ $selectedPosition }}">
    <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}">
    <input type="hidden" name="year" value="{{ $year }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users by name or employee ID" style="flex:1;min-width:240px;padding:10px 12px;border:1px solid var(--border);border-radius:999px;">
    <button class="btn btn-primary btn-sm" type="submit">Search</button>
</form>

<div class="card" style="{{ $section !== 'compensation' ? 'display:none;' : '' }}">
    <div class="card-h" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <span></span>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', array_merge(['type' => 'leaves', 'year' => $year, 'section' => 'compensation', 'search' => request('search')], $selectedDepartmentId ? ['department_id' => $selectedDepartmentId] : [])) }}">Export Leaves (CSV)</a>
            <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', array_merge(['type' => 'balances', 'year' => $year, 'section' => 'compensation', 'search' => request('search')], $selectedDepartmentId ? ['department_id' => $selectedDepartmentId] : [])) }}">Export Balances (CSV)</a>
        </div>
    </div>

    <form class="analytics-filter-bar" method="GET">
        <input type="hidden" name="section" value="compensation">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <div class="filter-bar-row">
            <select name="department_id" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()">
                @foreach($yearOptions as $yearOption)
                    <option value="{{ $yearOption }}" @selected($yearOption == $year)>{{ $yearOption }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Apply</button>
        </div>
    </form>

    <div class="grid two">
        <div class="card">
            <div class="card-h">Yearly Compensation Review</div>
            <div class="table-wrap">
                <table class="report-table">
                    <thead><tr><th>Employee</th><th>Leave</th><th>Remaining</th><th>Compensation</th></tr></thead>
                    <tbody>
                        @forelse($balances as $balance)
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
            <div class="pagination">{{ $balances->links('vendor.pagination.hr', ['anchor' => 'page-reports']) }}</div>
        </div>
        <div class="card">
            <div class="card-h">Leave Summary by Department</div>
            <div class="card-b">
                @foreach($summaries as $department)
                    <a href="{{ route('admin.requests.index', ['department_id' => $department->id]) }}" style="display:block;padding:12px 0;border-bottom:1px solid var(--border)">
                        <strong>{{ $department->name }}</strong>
                        <div class="muted">{{ $department->employees->flatMap->leaveApplications->where('status','approved')->count() }} approved requests</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card" style="{{ $section !== 'individual' ? 'display:none;' : '' }}">
    <div class="card-h" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <span>Individual Balance Report</span>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', array_merge(['type' => 'leaves', 'year' => $year, 'section' => 'individual', 'search' => request('search')], array_filter(['department_id' => $selectedDepartmentId, 'position' => $selectedPosition, 'employee_id' => $selectedEmployeeId]))) }}">Export Leaves (CSV)</a>
            <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', array_merge(['type' => 'balances', 'year' => $year, 'section' => 'individual', 'search' => request('search')], array_filter(['department_id' => $selectedDepartmentId, 'position' => $selectedPosition, 'employee_id' => $selectedEmployeeId]))) }}">Export Balances (CSV)</a>
        </div>
    </div>

    <div class="card-b">
        <form class="analytics-filter-bar" method="GET">
            <input type="hidden" name="section" value="individual">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <div class="filter-bar-row">
                <select name="department_id" onchange="this.form.submit()">
                    <option value="">Choose department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <select name="position" onchange="this.form.submit()">
                    <option value="">All positions</option>
                    @foreach($positions as $position)
                        <option value="{{ $position }}" @selected(request('position') === $position)>{{ $position }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-bar-row">
                <select name="employee_id" required>
                    <option value="">Choose employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->full_name }} - {{ $employee->position }}</option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()">
                    @foreach($yearOptions as $yearOption)
                        <option value="{{ $yearOption }}" @selected($yearOption == $year)>{{ $yearOption }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm" type="submit">Generate Report</button>
            </div>
        </form>

        @if(! $employeeReport)
            <div class="flash flash-muted" style="margin-top:12px">Select an employee and year to view the individual balance report.</div>
        @endif

        @if($employeeReport)
            <div class="card" style="box-shadow:none;margin-top:12px">
                <div class="card-h" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <span>{{ $employeeReport->full_name }}</span>
                    <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', array_merge(['type' => 'balances', 'year' => $year, 'section' => 'individual', 'employee_id' => $employeeReport->id], $selectedDepartmentId ? ['department_id' => $selectedDepartmentId] : [])) }}">Download CSV</a>
                </div>
                <div class="card-b">
                    <p><strong>Employee ID:</strong> {{ $employeeReport->employee_id }} | <strong>Department:</strong> {{ $employeeReport->departmentRecord?->name }} | <strong>Position:</strong> {{ $employeeReport->position }} | <strong>Daily Rate:</strong> PHP {{ number_format($employeeReport->daily_rate, 2) }}</p>
                    <table class="report-table" style="margin-top:14px;width:100%">
                        <thead><tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th><th>Yearly Compensation</th></tr></thead>
                        <tbody>
                            @foreach($employeeReport->leaveBalances->filter(fn ($balance) => $balance->leaveType?->isVisibleForGender($employeeReport->gender)) as $balance)
                                @php($isCompensable = (bool) $balance->leaveType?->is_compensable)
                                <tr>
                                    <td>{{ $balance->leaveType->name }}<div class="muted">{{ $isCompensable ? 'Compensable' : 'Not compensable' }}</div></td>
                                    <td>{{ (int) $balance->allocated_days }}</td>
                                    <td>{{ (int) $balance->used_days }}</td>
                                    <td>{{ (int) $balance->remaining_days }}</td>
                                    <td>PHP {{ number_format($isCompensable ? $balance->remaining_days * $employeeReport->daily_rate : 0, 2) }}</td>
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


