@extends('admin.layout')

@section('content')
<div class="page active" id="page-dashboard">
    <div class="page-header">
        <div><h1>Dashboard</h1><p>Leave overview — {{ now()->format('F j, Y') }}</p></div>
        <div class="page-actions">
            <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', ['type' => 'leaves', 'department_id' => $selectedDepartmentId]) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="dash-dept-filter">
        <label for="dashboardDepartmentFilter">Dashboard Department Filter</label>
        <form method="GET" style="margin-left:auto;">
            <select id="dashboardDepartmentFilter" name="department_id" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected($selectedDepartmentId === $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="stats-grid">
        <a class="stat-card" style="display:block;text-decoration:none;color:inherit" href="{{ route('admin.employees.index', array_filter(['department_id' => $selectedDepartmentId])) }}">
            <div class="stat-top">
                <div>
                    <div class="stat-value">{{ $stats['employees'] }}</div>
                    <div class="stat-label">Total Employees</div>
                </div>
            </div>
        </a>
        <a class="stat-card" style="display:block;text-decoration:none;color:inherit" href="{{ route('admin.requests.index', array_filter(['department_id' => $selectedDepartmentId, 'status' => 'pending'])) }}">
            <div class="stat-top">
                <div>
                    <div class="stat-value">{{ $stats['pending_manager'] }}</div>
                    <div class="stat-label">Pending Manager Leave Approval</div>
                </div>
            </div>
            
        </a>
        <a class="stat-card" style="display:block;text-decoration:none;color:inherit" href="{{ route('admin.users.pending', ['status' => 'pending']) }}">
            <div class="stat-top">
                <div>
                    <div class="stat-value">{{ $stats['pending_users'] }}</div>
                    <div class="stat-label">Pending Verification for Registered Accounts</div>
                </div>
            </div>
            
        </a>
        <a class="stat-card" style="display:block;text-decoration:none;color:inherit" href="{{ route('admin.requests.index', array_filter(['department_id' => $selectedDepartmentId, 'date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString()])) }}">
            <div class="stat-top">
                <div>
                    <div class="stat-value">{{ $stats['leaves_this_month'] }}</div>
                    <div class="stat-label">Total Leaves This Month</div>
                </div>
            </div>
        </a>
    </div>


    <div class="dash-layout">
        <div class="grid">
            <div class="card recent-requests-card">
                <div class="card-header recent-requests-header" style="background-color:#99baa9">
                    <span class="card-title">Recent Leave Requests</span>
                    <a class="btn btn-outline btn-sm recent-refresh-btn" href="{{ request()->fullUrl() }}" aria-label="Refresh recent leave requests">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 1-15.5 6.2"/><path d="M3 12A9 9 0 0 1 18.5 5.8"/><path d="M18 2v4h4"/><path d="M6 22v-4H2"/></svg>
                        Refresh
                    </a>
                </div>
                <div class="table-wrap" style="border:none;border-radius:0">
                    <table>
                        <thead><tr><th>Employee</th><th>Dept · Position</th><th>Leave Type</th><th>Duration</th><th>Filed</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($recentRequests as $leave)
                            <tr>
                                <td><div class="td-name">{{ $leave->employee->full_name }}</div></td>
                                <td><div class="td-sub">{{ $leave->employee->departmentRecord?->name }}</div><div class="td-pos">{{ $leave->employee->position }}</div></td>
                                <td>{{ $leave->leaveType->name }}</td>
                                <td>{{ $leave->total_days }} days</td>
                                <td>{{ $leave->created_at->diffForHumans() }}</td>
                                <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No requests yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="recent-requests-footer">
                    <a class="btn btn-outline btn-sm recent-view-all-btn" href="{{ route('admin.requests.index', ['department_id' => $selectedDepartmentId]) }}">View all</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-color:#99baa9"><span class="card-title">Leave Summary by Department</span><span style="font-size:10px;padding:10px;background-color:var(--bg);border-radius:8px;color:var(--text3)"><strong>Click a department to view request log</strong></span></div>
                <div class="card-body">
                    @foreach($departmentSummaries as $summary)
                        <a class="dept-leave-row" href="{{ route('admin.requests.index', ['department_id' => $summary['department']->id]) }}" style="padding:10px 0;margin:10px;border-bottom:1px solid var(--border)">
                            <div class="dept-leave-label"><span>{{ $summary['department']->name }}</span><span class="dept-leave-link">View logs →</span></div>
                            <div style="display:flex;align-items:center;gap:20px">
                                <div class="progress" style="flex:1"><div class="progress-bar" style="width:{{ min(100, $summary['on_leave'] * 18) }}%"></div></div>
                                <span class="dept-leave-stat">{{ $summary['on_leave'] }} currently on leave</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="info-card">
                <div class="info-card-header" style="background-color:#99baa9">This Month at a Glance</div>
                <div class="info-card-body">
                    @foreach(['approved','pending','rejected'] as $status)
                        <div class="quick-stat"><span class="quick-stat-label">{{ ucfirst($status) }}</span><span class="quick-stat-val">{{ $statusBreakdown[$status] ?? 0 }}</span></div>
                    @endforeach
                    <div class="quick-stat"><span class="quick-stat-label">Most common</span><span class="quick-stat-val">{{ $stats['most_used'] }}</span></div>
                </div>
            </div>
            <div class="info-card">
                <div class="info-card-header" style="display:flex;justify-content:space-between;align-items:center;background-color:#99baa9"><span>Calendar</span><a style="font-size:15px;color:var(--primary);font-weight:600" href="{{ route('admin.calendar') }}">Full view →</a></div>
                <a class="info-card-body" style="display:block;cursor:pointer" href="{{ route('admin.calendar') }}">
                    <div class="mini-cal" id="miniCal"></div>
                </a>
            </div>
            <div class="info-card" id="on-leave-today">
                <div class="info-card-header" style="background-color:#99baa9">On Leave Today</div>
                <div class="info-card-body on-leave-card-body">
                    <div class="on-leave-list">
                        @forelse($onLeaveToday as $leave)
                            <div class="on-leave-person">
                                <div class="avatar avatar-sm on-leave-avatar">{{ substr($leave->employee->full_name, 0, 1) }}</div>
                                <div class="on-leave-details">
                                    <div class="on-leave-name">{{ $leave->employee->full_name }}</div>
                                    <div class="on-leave-meta">{{ $leave->leaveType->name }} | returns {{ $leave->end_date->format('M d') }}</div>
                                    <div class="on-leave-meta">{{ $leave->employee->departmentRecord?->name }} | {{ $leave->employee->position }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="on-leave-empty">No one is on approved leave today.</div>
                        @endforelse
                    </div>

                    @if($onLeaveToday->hasPages())
                        <div class="on-leave-pagination" aria-label="On leave today pagination">
                            @if($onLeaveToday->onFirstPage())
                                <span class="on-leave-page-btn is-disabled" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
                            @else
                                <a class="on-leave-page-btn" href="{{ $onLeaveToday->previousPageUrl() }}#on-leave-today" aria-label="Previous page">&lsaquo;</a>
                            @endif

                            <span class="on-leave-page-count">Page {{ $onLeaveToday->currentPage() }} of {{ $onLeaveToday->lastPage() }}</span>

                            @if($onLeaveToday->hasMorePages())
                                <a class="on-leave-page-btn" href="{{ $onLeaveToday->nextPageUrl() }}#on-leave-today" aria-label="Next page">&rsaquo;</a>
                            @else
                                <span class="on-leave-page-btn is-disabled" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function renderMiniCalendar(){
    const target=document.getElementById('miniCal');
    if(!target)return;
    const now=new Date();
    const year=now.getFullYear();
    const month=now.getMonth();
    const first=new Date(year,month,1);
    const last=new Date(year,month+1,0);
    const labels=['S','M','T','W','T','F','S'];
    let html='<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:6px">'+labels.map(d=>`<span style="font-size:13px;color:var(--text3);text-align:center;font-weight:1000">${d}</span>`).join('')+'</div>';
    html+='<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px">';
    for(let i=0;i<first.getDay();i++){html+='<span class="cal-day other-month"></span>'}
    for(let day=1;day<=last.getDate();day++){
        const today=day===now.getDate()?' today':'';
        html+=`<span class="cal-day${today}">${day}</span>`;
    }
    html+='</div>';
    target.innerHTML=html;
}
renderMiniCalendar();
</script>
@endpush
