@extends('manager.layout')

@section('title', 'Approval Inbox')
@section('page_title', 'Approval Inbox')

@section('content')
<div class="card">
    <div class="card-body">
    <form class="filters" method="GET">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search employee name">
        <select class="form-control" name="leave_type_id">
            <option value="">All Leave Types</option>
            @foreach($leaveTypes as $type)<option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>@endforeach
        </select>
        <select class="form-control" name="status">
            <option value="">All Status</option>
            @foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
        </select>
        <button class="btn btn-primary">Filter</button>
    </form>
    </div>
</div>
<div class="card" style="padding:0">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee</th><th>Type</th><th>Duration</th><th>Days</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($requests as $leave)
                <tr>
                    <td><strong>{{ $leave->employee->full_name }}</strong><div class="muted">{{ $leave->employee->departmentRecord?->name }}</div></td>
                    <td>{{ $leave->leaveType->name }}</td>
                    <td>{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }}</td>
                    <td>{{ (int) $leave->total_days }}</td>
                    <td><span class="status-pill status-{{ $leave->status }}">{{ $leave->status }}</span></td>
                    <td><a class="btn btn-primary btn-sm" href="{{ route('manager.requests.show', $leave) }}">Review</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No team leave requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">{{ $requests->links() }}</div>
@endsection
