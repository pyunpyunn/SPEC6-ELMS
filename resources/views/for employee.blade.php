@extends('layouts.employee')

@section('title', 'Employee Leave')

@section('content')
<div class="page-header">
    <div>
        <h1>My Leave</h1>
        <p>Quick access to leave filing, history, balances, and profile features.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('employee.leave.create') }}" class="btn btn-primary">Apply Leave</a>
        <a href="{{ route('employee.leave.history') }}" class="btn btn-outline">Leave History</a>
    </div>
</div>

<div class="cards">
    <div class="card">
        <div class="card-header"><span class="card-title">Leave Balances</span></div>
        <div class="card-body">
            <div class="leave-balance-list">
                @forelse(($leaveTypes ?? collect()) as $type)
                    @php
                        $used = $type->used_days ?? 0;
                        $total = $type->total_days ?? $type->annual_allocation ?? 0;
                        $percent = $total > 0 ? min(100, round(($used / $total) * 100)) : 0;
                    @endphp
                    <div class="employee-balance-item" data-leave-type-id="{{ $type->id ?? '' }}">
                        <div class="balance-top">
                            <span class="balance-name">{{ $type->name }}</span>
                            <span class="balance-count">{{ $used }}/{{ $total }} days used</span>
                        </div>
                        <div class="progress"><div class="progress-bar" style="width: {{ $percent }}%"></div></div>
                    </div>
                @empty
                    <div class="empty-state">Open the employee dashboard to load your leave balances.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Employee Features</span></div>
        <div class="card-body">
            <div class="page-actions">
                <a href="{{ route('dashboard.employee') }}" class="btn btn-outline">Dashboard</a>
                <a href="{{ route('employee.reports') }}" class="btn btn-outline">My Reports</a>
                <a href="{{ route('employee.notifications') }}" class="btn btn-outline">Notifications</a>
                <a href="{{ route('employee.profile') }}" class="btn btn-outline">My Profile</a>
            </div>
        </div>
    </div>
</div>
@endsection
