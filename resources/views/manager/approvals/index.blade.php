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
            <thead><tr><th>Employee</th><th>Type</th><th>Duration</th><th>Days</th><th>Status</th><th>Reviewed By</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($requests as $leave)
                <tr>
                    <td><strong>{{ $leave->employee->full_name }}</strong><div class="muted">{{ $leave->employee->departmentRecord?->name }}</div></td>
                    <td>{{ $leave->leaveType->name }}</td>
                    <td>{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }}</td>
                    <td>{{ (int) $leave->total_days }}</td>
                    <td><span class="status-pill status-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                    <td>{{ $leave->reviewer?->name ?? 'Not yet reviewed' }}</td>
                    <td>
                        @if($leave->status === 'pending')
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <form method="POST" action="{{ route('manager.approvals.approve', $leave) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="remarks" value="Approved from approval inbox.">
                                    <button class="btn btn-success btn-sm" type="submit">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('manager.approvals.reject', $leave) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="remarks" value="Rejected from approval inbox.">
                                    <button class="btn btn-danger btn-sm" type="submit">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No team leave requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">{{ $requests->links() }}</div>
@endsection
