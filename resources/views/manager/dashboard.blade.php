@extends('manager.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="page-header">
    <div><h1>Dashboard</h1><p>Review pending team leave requests and today’s team availability.</p></div>
</div>
<div class="dash-layout">
    <div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-value" style="color:var(--warning)">{{ $stats['pending'] }}</div><div class="stat-label">Pending Requests</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--primary)">{{ $stats['on_leave_today'] }}</div><div class="stat-label">On Leave Today</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--success)">{{ $stats['approved_mtd'] }}</div><div class="stat-label">Approved MTD</div></div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Action Required</span><a class="btn btn-outline btn-sm" href="{{ route('manager.requests.index') }}">Open Inbox</a></div>
            <div class="card-body">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Employee</th><th>Type</th><th>Days</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse($pendingRequests as $leave)
                        <tr>
                            <td><strong>{{ $leave->employee->full_name }}</strong><div class="muted">{{ $leave->employee->position }}</div></td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>{{ (int) $leave->total_days }}</td>
                            <td><a class="btn btn-primary btn-sm" href="{{ route('manager.requests.show', $leave) }}">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No pending team requests.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
    <div class="right-panel">
        <div class="info-card">
            <div class="info-card-header" style="display:flex;justify-content:space-between;align-items:center">
                <span>Calendar</span>
                <a style="font-size:10.5px;color:var(--primary);font-weight:600" href="{{ route('manager.calendar') }}">Full view</a>
            </div>
            <div class="info-card-body">
                <div class="mini-cal">
                    <div class="mini-cal-header"><span class="mini-cal-month">{{ now()->format('F Y') }}</span></div>
                    <div class="mini-cal-grid">
                        @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $dow)<div class="cal-dow">{{ $dow }}</div>@endforeach
                        @foreach($miniCalendar as $day)
                            <div class="cal-day {{ $day['in_month'] ? '' : 'other-month' }} {{ $day['is_today'] ? 'today' : '' }} {{ $day['has_leave'] ? 'has-leave' : '' }}">{{ $day['date']->day }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
