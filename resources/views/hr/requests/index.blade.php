@extends('hr.layout')

@section('content')
<div class="page-head"><div><h1>Master Request Log{{ $selectedDepartment ? ' - '.$selectedDepartment->name : '' }}</h1><div class="muted">Company-wide request review. Department filter is driven by the selected department link.</div></div></div>
<form class="filters" method="GET">
    @if($selectedDepartment)<input type="hidden" name="department_id" value="{{ $selectedDepartment->id }}">@endif
    <select name="leave_type_id"><option value="">All leave types</option>@foreach($leaveTypes as $type)<option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>@endforeach</select>
    <select name="status"><option value="">All status</option>@foreach(['pending','approved','rejected','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
    <button class="btn primary">Filter</button><a class="btn" href="{{ route('hr.requests.index') }}">All Departments Log</a>
</form>
@unless($selectedDepartment)<div class="grid cards" style="margin-bottom:16px">@foreach($departments as $d)<a class="card" href="{{ route('hr.requests.index', ['department_id' => $d->id]) }}"><div class="card-b"><strong>{{ $d->name }}</strong><div class="muted">Open master request log for this department</div></div></a>@endforeach</div>@endunless
<div class="card"><div class="table-wrap"><table><thead><tr><th>Employee</th><th>Department · Position</th><th>Leave Type</th><th>Dates</th><th>Status</th><th>Reviewed By</th><th>Action</th></tr></thead><tbody>
@forelse($requests as $leave)
<tr><td>{{ $leave->employee->full_name }}<div class="muted">{{ $leave->employee->employee_id }}</div></td><td>{{ $leave->employee->departmentRecord?->name }}<div class="muted">{{ $leave->employee->position }}</div></td><td>{{ $leave->leaveType->name }}</td><td>{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}<div class="muted">{{ $leave->total_days }} days</div></td><td><span class="badge {{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td><td>{{ $leave->reviewer?->name ?? 'Pending' }}<div class="muted">{{ $leave->reviewer ? ucfirst(str_replace('_',' ', $leave->reviewer->role)) : '' }}</div></td><td>
    @if($leave->status === 'pending')<form method="POST" action="{{ route('hr.requests.review', $leave) }}" class="form" style="grid-template-columns:1fr;min-width:240px">@csrf @method('PATCH')<textarea name="remarks" placeholder="Decision remarks" required></textarea><div class="actions"><button name="status" value="approved" class="btn success small">Approve</button><button name="status" value="rejected" class="btn danger small">Reject</button></div></form>@else <span class="muted">{{ $leave->remarks }}</span>@endif
</td></tr>
@empty<tr><td colspan="7">No leave requests found.</td></tr>@endforelse
</tbody></table></div><div class="card-b">{{ $requests->links() }}</div></div>
@endsection
