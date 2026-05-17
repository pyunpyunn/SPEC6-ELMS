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
        <label>Dashboard Department Filter</label>
        <form method="GET" style="margin-left:auto;">
            <select name="department_id" onchange="this.form.submit()">
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
            <div class="stat-card-bottom">
                <span class="badge badge-pending">Needs review</span>
            </div>
        </a>
        <a class="stat-card" style="display:block;text-decoration:none;color:inherit" href="{{ route('admin.users.pending', ['status' => 'pending']) }}">
            <div class="stat-top">
                <div>
                    <div class="stat-value">{{ $stats['pending_users'] }}</div>
                    <div class="stat-label">Pending Verification for Registered Accounts</div>
                </div>
            </div>
            <div class="stat-card-bottom">
                <span class="badge badge-pending">Verify users</span>
            </div>
        </a>
        <a class="stat-card" style="display:block;text-decoration:none;color:inherit" href="{{ route('admin.requests.index', array_filter(['department_id' => $selectedDepartmentId, 'date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString()])) }}">
            <div class="stat-top">
                <div>
                    <div class="stat-value">{{ $stats['leaves_this_month'] }}</div>
                    <div class="stat-label">Total Leaves This Month</div>
                </div>
            </div>
            <div class="stat-card-bottom">
                <span class="badge badge-approved">{{ $stats['most_used'] }} most used</span>
            </div>
        </a>
    </div>

    <div class="dash-layout">
        <div class="grid">
            <div class="card">
                <div class="card-header"><span class="card-title">Recent Leave Requests</span><a class="btn btn-outline btn-sm" href="{{ route('admin.requests.index', ['department_id' => $selectedDepartmentId]) }}">View all</a></div>
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
            </div>

            <div class="card">
                <div class="card-header"><span class="card-title">Leave Summary by Department</span><span style="font-size:15px;color:var(--text3)">Click a department to view request log</span></div>
                <div class="card-body">
                    @foreach($departmentSummaries as $summary)
                        <a class="dept-leave-row" href="{{ route('admin.requests.index', ['department_id' => $summary['department']->id]) }}" style="padding:12px 0;border-bottom:1px solid var(--border)">
                            <div class="dept-leave-label"><span>{{ $summary['department']->name }}</span><span class="dept-leave-link">View logs →</span></div>
                            <div style="display:flex;align-items:center;gap:10px">
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
                <div class="info-card-header">This Month at a Glance</div>
                <div class="info-card-body">
                    @foreach(['approved','pending','rejected'] as $status)
                        <div class="quick-stat"><span class="quick-stat-label">{{ ucfirst($status) }}</span><span class="quick-stat-val">{{ $statusBreakdown[$status] ?? 0 }}</span></div>
                    @endforeach
                    <div class="quick-stat"><span class="quick-stat-label">Most common</span><span class="quick-stat-val">{{ $stats['most_used'] }}</span></div>
                </div>
            </div>
            <div class="info-card">
                <div class="info-card-header" style="display:flex;justify-content:space-between;align-items:center"><span>Calendar</span><a style="font-size:15px;color:var(--primary);font-weight:600" href="{{ route('admin.calendar') }}">Full view →</a></div>
                <a class="info-card-body" style="display:block;cursor:pointer" href="{{ route('admin.calendar') }}">
                    <div class="mini-cal" id="miniCal"></div>
                </a>
            </div>
            <div class="info-card" id="on-leave-today">
                <div class="info-card-header">On Leave Today</div>
                <div class="info-card-body" style="display:flex;flex-direction:column;gap:10px">
                    @forelse($onLeaveToday as $leave)
                        <div style="display:flex;align-items:center;gap:9px;padding-bottom:10px;border-bottom:1px solid var(--border)"><div class="avatar avatar-sm">{{ substr($leave->employee->full_name,0,1) }}</div><div><div style="font-size:15px;font-weight:600;color:var(--text)">{{ $leave->employee->full_name }}</div><div style="font-size:15px;color:var(--text3)">{{ $leave->leaveType->name }} · returns {{ $leave->end_date->format('M d') }} · {{ $leave->employee->departmentRecord?->name }} · {{ $leave->employee->position }}</div></div></div>
                    @empty
                        <div style="font-size:15px;color:var(--text3)">No one is on approved leave today.</div>
                    @endforelse

                    <div class="pagination">
                        {{ $onLeaveToday->withQueryString()->links('vendor.pagination.hr', ['anchor' => 'on-leave-today']) }}
                    </div>
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
    let html='<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:6px">'+labels.map(d=>`<span style="font-size:15px;color:var(--text3);text-align:center;font-weight:700">${d}</span>`).join('')+'</div>';
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
