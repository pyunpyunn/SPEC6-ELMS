@extends('layouts.employee')

@section('title', 'Leave Request')

@section('content')
<div class="page-header">
    <div>
        <h1>Leave Request</h1>
        <p>{{ $leave->leaveType->name ?? 'Leave' }} request details.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="{{ route('employee.leaves.index') }}">Back to My Leave</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Request Summary</span></div>
    <div class="card-body">
        <div class="grid two gap16">
            <div><strong>Leave Type</strong><div>{{ $leave->leaveType->name ?? 'Leave' }}</div></div>
            <div><strong>Status</strong><div><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></div></div>
            <div><strong>Start Date</strong><div>{{ $leave->start_date->format('M d, Y') }}</div></div>
            <div><strong>End Date</strong><div>{{ $leave->end_date->format('M d, Y') }}</div></div>
            <div><strong>Total Days</strong><div>{{ (int) $leave->total_days }}</div></div>
            <div><strong>Reviewed By</strong><div>{{ $leave->reviewer?->name ?? 'Pending review' }}</div></div>
            <div class="full"><strong>Reason</strong><div>{{ $leave->reason }}</div></div>
            <div class="full"><strong>Remarks</strong><div>{{ $leave->remarks ?: 'No remarks yet.' }}</div></div>
            @if($leave->proof_path)
                <div class="full">
                    <strong>Attached Document</strong>
                    <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--surface2);border-radius:var(--radius-sm);border:1px solid var(--border);margin-top:6px">
                        <span style="font-size:12px;color:var(--text)">{{ basename($leave->proof_path) }}</span>
                        <a href="{{ asset('storage/' . $leave->proof_path) }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer" style="margin-left:auto">Download</a>
                    </div>
                    @if(str_ends_with(strtolower($leave->proof_path), ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                        <img src="{{ asset('storage/' . $leave->proof_path) }}" alt="Proof document" style="max-width:100%;margin-top:12px;border-radius:8px;border:1px solid var(--border)">
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
