@extends('hr.layout')

@section('content')
<div class="page-head"><div><h1>Reports & Analytics</h1><div class="muted">Department summaries, monthly calendar data, individual leave balance reports, and CSV exports.</div></div><div class="actions"><a class="btn" href="{{ route('hr.reports.export', request()->all() + ['type' => 'leaves']) }}">Export All Leaves (CSV)</a><a class="btn" href="{{ route('hr.reports.export', request()->all() + ['type' => 'balances']) }}">Export Balances (CSV)</a></div></div>
<form class="filters" method="GET"><select name="department_id" onchange="this.form.submit()"><option value="">All Departments</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected($selectedDepartmentId == $d->id)>{{ $d->name }}</option>@endforeach</select><select name="year"><option>{{ now()->year }}</option><option>{{ now()->year - 1 }}</option></select><button class="btn primary">Apply</button></form>
<div class="grid two">
<div class="card"><div class="card-h">Leave Summary by Department</div><div class="card-b">@foreach($summaries as $department)<a href="{{ route('hr.requests.index', ['department_id' => $department->id]) }}" style="display:block;padding:8px 0;border-bottom:1px solid var(--border)"><strong>{{ $department->name }}</strong><div class="muted">{{ $department->employees->flatMap->leaveApplications->where('status','approved')->count() }} approved requests</div></a>@endforeach</div></div>
<div class="card"><div class="card-h">Yearly Compensation Review</div><div class="table-wrap"><table><thead><tr><th>Employee</th><th>Leave</th><th>Remaining</th><th>Compensation</th></tr></thead><tbody>@foreach($balances->take(12) as $balance)<tr><td>{{ $balance->employee->full_name }}<div class="muted">{{ $balance->employee->departmentRecord?->name }} · {{ $balance->employee->position }}</div></td><td>{{ $balance->leaveType->name }}</td><td>{{ (int) $balance->remaining_days }}</td><td>PHP {{ number_format($balance->remaining_days * $balance->employee->daily_rate, 2) }}</td></tr>@endforeach</tbody></table></div></div>
</div>
<div class="card" style="margin-top:16px"><div class="card-h">Individual Balance Report</div><div class="card-b">
    <form class="filters" method="GET">
        <select name="department_id" onchange="this.form.submit()"><option value="">Choose department</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected($selectedDepartmentId == $d->id)>{{ $d->name }}</option>@endforeach</select>
        <select name="position"><option value="">All positions</option>@foreach($positions as $p)<option @selected(request('position') === $p)>{{ $p }}</option>@endforeach</select>
        <select name="employee_id" required><option value="">Choose employee</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(request('employee_id') == $e->id)>{{ $e->full_name }} - {{ $e->position }}</option>@endforeach</select>
        <select name="year"><option>{{ $year }}</option><option>{{ $year - 1 }}</option></select>
        <button class="btn primary">Generate Report</button>
    </form>
    @if($employeeReport)
    <div class="card" style="box-shadow:none;margin-top:12px"><div class="card-h">{{ $employeeReport->full_name }} <a class="btn small" href="{{ route('hr.reports.export', ['type' => 'balances', 'department_id' => $employeeReport->department_id, 'year' => $year]) }}">Download CSV</a></div><div class="card-b">
        <p><strong>Employee ID:</strong> {{ $employeeReport->employee_id }} | <strong>Department:</strong> {{ $employeeReport->departmentRecord?->name }} | <strong>Position:</strong> {{ $employeeReport->position }} | <strong>Daily Rate:</strong> PHP {{ number_format($employeeReport->daily_rate, 2) }}</p>
        <table><thead><tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th><th>Yearly Compensation</th></tr></thead><tbody>@foreach($employeeReport->leaveBalances as $balance)<tr><td>{{ $balance->leaveType->name }}</td><td>{{ (int) $balance->allocated_days }}</td><td>{{ (int) $balance->used_days }}</td><td>{{ (int) $balance->remaining_days }}</td><td>PHP {{ number_format($balance->remaining_days * $employeeReport->daily_rate, 2) }}</td></tr>@endforeach</tbody></table>
    </div></div>
    @endif
</div></div>
@endsection
