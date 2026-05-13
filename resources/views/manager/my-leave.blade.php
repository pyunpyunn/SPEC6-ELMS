@extends('manager.layout')

@section('title', 'Apply for Leave')
@section('page_title', 'My Leave Application')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px">
    <div class="card">
        <div class="card-body">
        <h3>New Leave Request</h3>
        <div class="flash flash-warning" style="margin-top:16px">As a manager, your requests are reviewed by HR Admin.</div>
        <form method="POST" action="{{ route('manager.my-leave.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:14px">
                <label>Leave Type</label>
                <select class="form-control" name="leave_type_id" required>
                    @foreach($leaveTypes as $type)
                        @php($balance = $employee?->leaveBalances->firstWhere('leave_type_id', $type->id))
                        <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }} ({{ (int) ($balance?->remaining_days ?? 0) }} left)</option>
                    @endforeach
                </select>
            </div>
            <div class="form-grid">
                <div><label>Start Date</label><input class="form-control" type="date" name="start_date" value="{{ old('start_date') }}" required></div>
                <div><label>End Date</label><input class="form-control" type="date" name="end_date" value="{{ old('end_date') }}" required></div>
                <div class="form-full"><label>Reason</label><textarea class="form-control" name="reason" rows="4" required>{{ old('reason') }}</textarea></div>
                <div class="form-full"><label>Attach Document</label><input class="form-control" type="file" name="proof"></div>
            </div>
            <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:18px">Submit Request to HR</button>
        </form>
        </div>
    </div>
    <div class="card" style="padding:0">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border)"><h3>My Leave History</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($myLeaves as $leave)
                    <tr>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('M d') }} to {{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ (int) $leave->total_days }}</td>
                        <td><span class="status-pill status-{{ $leave->status }}">{{ $leave->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No personal leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($myLeaves, 'links'))<div class="pagination" style="padding:16px">{{ $myLeaves->links() }}</div>@endif
    </div>
</div>
@endsection
