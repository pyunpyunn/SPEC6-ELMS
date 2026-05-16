@extends('layouts.employee')

@section('title', 'My Leave')

@section('content')
<div class="page-header">
    <div>
        <h1>My Leave</h1>
        <p>Balances, leave filing, and request history.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" type="button" onclick="openModal('applyLeaveModal')">Apply for Leave</button>
    </div>
</div>

@if($errors->any())
    <div class="flash flash-error">{{ $errors->first() }}</div>
@endif
@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">{{ session('error') }}</div>
@endif

<div class="card mb16">
    <div class="card-header"><span class="card-title">Leave Balance</span></div>
    <div class="card-body">
        <div class="grid three gap16">
            @foreach($leaveTypes as $type)
                <div class="leave-card">
                    <div class="leave-card-top">
                        <div class="leave-card-name">{{ $type->name }}</div>
                        <span class="badge badge-info">{{ $type->total_days }} days</span>
                    </div>
                    <div class="leave-bar">
                        <div class="leave-bar-fill {{ $type->percent_used > 70 ? 'danger' : ($type->percent_used > 45 ? 'warn' : '') }}" style="width: {{ $type->percent_used }}%"></div>
                    </div>
                    <div class="leave-card-foot">
                        <span>{{ $type->used_days }} used</span>
                        <span>{{ $type->remaining_days }} remaining</span>
                    </div>
                    <div class="td-sub mt8">{{ $type->policy_note }}</div>
                </div>
            @endforeach
        </div>
        @if(method_exists($leaveApplications, 'links') && $leaveApplications->hasPages())
            <div class="card-body" style="border-top:1px solid var(--border)">
                {{ $leaveApplications->links() }}
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">My Leave Request History</span>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap" style="border:0;border-radius:0">
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveApplications as $leave)
                        <tr>
                            <td>{{ $leave->leaveType->name ?? 'Leave' }}</td>
                            <td class="font-mono">{{ $leave->start_date->format('M d, Y') }}</td>
                            <td class="font-mono">{{ $leave->end_date->format('M d, Y') }}</td>
                            <td>{{ $leave->days }}</td>
                            <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                            <td>{{ $leave->reviewer?->name ?? 'Not yet reviewed' }}</td>
                            <td>
                                @if($leave->status === 'pending')
                                    <form method="POST" action="{{ route('employee.leaves.destroy', $leave) }}" onsubmit="return confirm('Cancel this pending leave request?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
                                    </form>
                                @else
                                    <span class="td-sub">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('employee.partials.apply-leave-modal')
@if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => openModal('applyLeaveModal'));</script>
@endif
@endsection
