@extends('manager.layout')

@section('title', 'Review Request')
@section('page_title', 'Review Request')

@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
    <div class="card-body">
    <a class="btn btn-outline btn-sm" href="{{ route('manager.approvals.index') }}">Back</a>
    <div class="page-head" style="margin-top:18px">
        <div><h2>{{ $leave->employee->full_name }}</h2><p>{{ $leave->employee->position }} · {{ $leave->employee->departmentRecord?->name }}</p></div>
        <span class="status-pill status-{{ $leave->status }}">{{ $leave->status }}</span>
    </div>
    <div class="form-grid">
        <div><span class="form-label">Leave Type</span><div>{{ $leave->leaveType->name }}</div></div>
        <div><span class="form-label">Duration</span><div>{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }} ({{ (int) $leave->total_days }} days)</div></div>
        <div class="form-full"><span class="form-label">Reason</span><div class="cal-item">{{ $leave->reason }}</div></div>
        <div class="form-full">
            <span class="form-label">Current Balance</span>
            @foreach($leave->employee->leaveBalances as $balance)
                @if($balance->leave_type_id === $leave->leave_type_id)
                    <div>{{ (int) $balance->remaining_days }} remaining of {{ (int) $balance->allocated_days }} {{ $balance->leaveType->name }} days</div>
                @endif
            @endforeach
        </div>
        @if($leave->proof_path)
            <div class="form-full">
                <span class="form-label">Attached Document</span>
                <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--surface2);border-radius:var(--radius-sm);border:1px solid var(--border)">
                    <span style="font-size:15px;color:var(--text)">{{ basename($leave->proof_path) }}</span>
                    <a href="{{ asset('storage/' . $leave->proof_path) }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer" style="margin-left:auto">View Proof</a>
                </div>
                @if(in_array(strtolower(pathinfo($leave->proof_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img src="{{ asset('storage/' . $leave->proof_path) }}" alt="Proof document" style="max-width:100%;margin-top:12px;border-radius:8px;border:1px solid var(--border)">
                @endif
            </div>
        @endif
    </div>
    @if($leave->status === 'pending')
        <form method="POST" action="{{ route('manager.approvals.review', $leave) }}" style="margin-top:24px">
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
