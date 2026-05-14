@extends('layouts.employee')

@section('title', 'Employee Dashboard')

@section('content')
@php
    $employeeId = $employee ? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) : 'No profile';
    $totalRemaining = $leaveTypes->sum('remaining_days');
    $leavesTakenThisYear = $leaveApplications
        ->where('status', 'approved')
        ->filter(fn ($leave) => $leave->start_date->year === now()->year)
        ->sum('days');
    $yearlyCompensation = $totalRemaining * 1000;
@endphp

@if($errors->any())
    <div class="flash flash-error">{{ $errors->first() }}</div>
@endif
@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>{{ now()->format('l, F d, Y') }} · {{ $employeeId }} · {{ $employee->position ?? 'Employee' }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" type="button" onclick="openModal('applyLeaveModal')">Quick Apply Leave</button>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value" style="color:var(--primary)">{{ $totalRemaining }}</div>
        <div class="stat-label">My Leave Balance</div>
        <div class="stat-sub">Total remaining days</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--success)">{{ $leavesTakenThisYear }}</div>
        <div class="stat-label">Leaves Taken This Year</div>
        <div class="stat-sub">Approved days</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--warning)">{{ $pendingLeaves }}</div>
        <div class="stat-label">Pending Requests</div>
        <div class="stat-sub">Awaiting review</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">₱{{ number_format($yearlyCompensation) }}</div>
        <div class="stat-label">Yearly Compensation Estimate</div>
        <div class="stat-sub">₱1,000/day x unused days</div>
    </div>
</div>

<div class="employee-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Personal Leave Days</span>
            <a href="{{ route('employee.leaves.index') }}" class="btn btn-outline btn-sm">My Leave</a>
        </div>
        <div class="card-body">
            <div class="mini-cal-grid">
                @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $day)
                    <div class="mini-cal-dow">{{ $day }}</div>
                @endforeach
                @for($i = 1; $i <= now()->daysInMonth; $i++)
                    @php
                        $hasLeave = $leaveApplications->contains(fn ($leave) => $leave->start_date->month === now()->month && $leave->start_date->year === now()->year && $leave->start_date->day <= $i && $leave->end_date->day >= $i);
                    @endphp
                    <div class="mini-cal-day {{ $i === now()->day ? 'today' : '' }} {{ $hasLeave ? 'has-leave' : '' }}">{{ $i }}</div>
                @endfor
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Recent Requests</span></div>
        <div class="card-body" style="padding:0">
            <div class="table-wrap" style="border:0;border-radius:0">
                <table>
                    <thead><tr><th>Type</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentLeaves as $leave)
                        <tr>
                            <td>{{ $leave->leaveType->name ?? 'Leave' }}</td>
                            <td>{{ $leave->days }}</td>
                            <td><span class="badge badge-{{ $leave->status }}">{{ $leave->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">No requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('employee.partials.apply-leave-modal')
@if($errors->any())
    <script>document.addEventListener('DOMContentLoaded', () => openModal('applyLeaveModal'));</script>
@endif
@endsection
