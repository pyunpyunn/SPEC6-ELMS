@extends('manager.layout')

@section('title', 'Review Request')
@section('page_title', 'Review Request')

@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
    <div class="card-body">
    <a class="btn btn-outline btn-sm" href="{{ route('manager.requests.index') }}">Back</a>
    <div class="page-head" style="margin-top:18px">
        <div><h2>{{ $leave->employee->full_name }}</h2><p>{{ $leave->employee->position }} · {{ $leave->employee->departmentRecord?->name }}</p></div>
        <span class="status-pill status-{{ $leave->status }}">{{ $leave->status }}</span>
    </div>
    <div class="form-grid">
        <div><label>Leave Type</label><div>{{ $leave->leaveType->name }}</div></div>
        <div><label>Duration</label><div>{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }} ({{ (int) $leave->total_days }} days)</div></div>
        <div class="form-full"><label>Reason</label><div class="cal-item">{{ $leave->reason }}</div></div>
        <div class="form-full">
            <label>Current Balance</label>
            @foreach($leave->employee->leaveBalances as $balance)
                @if($balance->leave_type_id === $leave->leave_type_id)
                    <div>{{ (int) $balance->remaining_days }} remaining of {{ (int) $balance->allocated_days }} {{ $balance->leaveType->name }} days</div>
                @endif
            @endforeach
        </div>
    </div>
    @if($leave->status === 'pending')
        <form method="POST" action="{{ route('manager.requests.review', $leave) }}" style="margin-top:24px">
            @csrf @method('PATCH')
            <label>Manager Remarks</label>
            <textarea class="form-control" name="remarks" rows="4" required>{{ old('remarks') }}</textarea>
            <div style="display:flex;gap:12px;margin-top:16px">
                <button class="btn btn-success" name="status" value="approved">Approve</button>
                <button class="btn btn-danger" name="status" value="rejected">Reject</button>
            </div>
        </form>
    @else
        <div class="cal-item" style="margin-top:24px"><strong>Remarks:</strong> {{ $leave->remarks ?: 'No remarks.' }}</div>
    @endif
    </div>
</div>
@endsection
