@extends('hr.layout')

@section('content')
<div class="page active" id="page-myleave">
    <div class="page-header">
        <div><h1>My Leave</h1><p>{{ $employee?->full_name }} · {{ $employee?->position }} · {{ $employee?->departmentRecord?->name }}</p></div>
        <div class="page-actions">
            <form method="GET" class="page-actions">
                <select name="leave_type_id" onchange="this.form.submit()"><option value="">All Leave Types</option>@foreach($leaveTypes as $type)<option value="{{ $type->id }}" @selected($selectedTypeId === $type->id)>{{ $type->name }}</option>@endforeach</select>
                <select name="year" onchange="this.form.submit()"><option>{{ now()->year }}</option><option>{{ now()->year - 1 }}</option></select>
            </form>
        </div>
    </div>
    <div class="my-leave-grid">
        @foreach(($employee?->leaveBalances ?? collect())->where('year', $year)->when($selectedTypeId, fn($items)=>$items->where('leave_type_id', $selectedTypeId)) as $balance)
            <div class="my-bal-card">
                <div class="my-bal-type">{{ $balance->leaveType->name }}</div>
                <div class="my-bal-days">{{ (int) $balance->remaining_days }}</div>
                <div class="my-bal-total">of {{ (int) $balance->allocated_days }} days remaining</div>
                <div class="progress my-bal-bar"><div class="progress-bar" style="width:{{ $balance->allocated_days ? (int)(($balance->remaining_days / $balance->allocated_days) * 100) : 0 }}%"></div></div>
            </div>
        @endforeach
    </div>
    <div class="comp-card">
        <div class="comp-title">Yearly Compensation Estimate</div>
        @php($comp = ($employee?->leaveBalances ?? collect())->where('year', $year)->sum(fn($b) => $b->remaining_days * (float) $employee->daily_rate))
        <div class="comp-amount">PHP {{ number_format($comp, 2) }}</div>
        <div class="comp-sub">Based on unused leave days and daily rate.</div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">My Leave Requests</span></div>
        <div class="table-wrap"><table><thead><tr><th>Leave Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Reason</th></tr></thead><tbody>
            @foreach(($employee?->leaveApplications ?? collect())->sortByDesc('created_at') as $leave)
                <tr><td>{{ $leave->leaveType->name }}</td><td>{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}</td><td>{{ (int) $leave->total_days }}</td><td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td><td>{{ $leave->reason }}</td></tr>
            @endforeach
        </tbody></table></div>
    </div>
</div>
@endsection
