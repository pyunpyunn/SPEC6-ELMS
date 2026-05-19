@extends('manager.layout')

@section('title', 'Team Overview')
@section('page_title', 'Team Overview')

@section('content')
<div class="page active" id="page-team">
    <div class="page-header">
        <div><h1>Team Overview</h1><p>Search team members and review current availability.</p></div>
    </div>

    <form class="filter-bar" method="GET">
        <div class="filter-bar-row">
            <div class="search-wrap">
                <label for="teamSearch" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;border:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">Search employees</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="teamSearch" type="search" name="search" value="{{ request('search') }}" placeholder="Search employees by employee ID..." aria-label="Search employees by employee ID">
            </div>
        </div>
        <div class="filter-bar-row">
            <select name="leave_type_id">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee</th><th>Employee ID</th><th>Position</th><th>Current Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($teamMembers as $member)
                @php($todayLeave = $member->leaveApplications->first(fn($leave) => $leave->status === 'approved' && $leave->start_date->lte(now()) && $leave->end_date->gte(now())))
                @php($profileData = [
                    'name' => $member->full_name,
                    'employee_id' => $member->employee_id,
                    'email' => $member->user?->email,
                    'department' => $member->departmentRecord?->name,
                    'position' => $member->position,
                    'status' => $todayLeave ? 'On Leave Today' : 'Active',
                    'leave_type' => $todayLeave?->leaveType?->name,
                    'balances' => $member->leaveBalances->map(fn($balance) => [
                        'type' => $balance->leaveType?->name,
                        'allocated' => (int) $balance->allocated_days,
                        'used' => (int) $balance->used_days,
                        'remaining' => (int) $balance->remaining_days,
                    ])->values(),
                ])
                <tr>
                    <td><div class="td-name">{{ $member->full_name }}</div><div class="td-sub">{{ $member->user?->email }}</div></td>
                    <td><span style="font-family:var(--mono);font-size:12px">{{ $member->employee_id }}</span></td>
                    <td>{{ $member->position }}</td>
                    <td><span class="badge badge-{{ $todayLeave ? 'pending' : 'active' }}">{{ $todayLeave ? 'On Leave Today' : 'Active' }}</span></td>
                    <td><button class="btn btn-outline btn-sm" type="button" data-profile='@json($profileData)' onclick="openEmployeeProfileFromElement(this)">View</button></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No active team members assigned.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $teamMembers->links('vendor.pagination.hr', ['anchor' => 'page-team']) }}</div>
</div>

<div class="modal-overlay" id="teamProfileModal" onclick="closeEmployeeProfile(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title" id="teamProfileName">Employee Profile</h3>
            <button class="modal-close" type="button" onclick="closeEmployeeProfile(event)">x</button>
        </div>
        <div class="modal-body">
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
                <div>
                    <div class="detail-row"><span class="dl">Employee ID</span><span class="dv" id="teamProfileId"></span></div>
                    <div class="detail-row"><span class="dl">Email</span><span class="dv" id="teamProfileEmail"></span></div>
                    <div class="detail-row"><span class="dl">Department</span><span class="dv" id="teamProfileDepartment"></span></div>
                </div>
                <div>
                    <div class="detail-row"><span class="dl">Position</span><span class="dv" id="teamProfilePosition"></span></div>
                    <div class="detail-row"><span class="dl">Current Status</span><span class="dv" id="teamProfileStatus"></span></div>
                    <div class="detail-row"><span class="dl">Leave Today</span><span class="dv" id="teamProfileLeaveToday"></span></div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th></tr></thead>
                    <tbody id="teamProfileBalances"></tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline" type="button" onclick="closeEmployeeProfile(event)">Back</button></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEmployeeProfile(data) {
    document.getElementById('teamProfileName').textContent = data.name || 'Employee Profile';
    document.getElementById('teamProfileId').textContent = data.employee_id || '-';
    document.getElementById('teamProfileEmail').textContent = data.email || '-';
    document.getElementById('teamProfileDepartment').textContent = data.department || '-';
    document.getElementById('teamProfilePosition').textContent = data.position || '-';
    document.getElementById('teamProfileStatus').innerHTML = '<span class="badge badge-' + (data.status === 'Active' ? 'active' : 'pending') + '">' + (data.status || 'Active') + '</span>';
    document.getElementById('teamProfileLeaveToday').textContent = data.leave_type || '-';

    const rows = Array.isArray(data.balances) && data.balances.length
        ? data.balances.map(function (balance) {
            return '<tr><td>' + escapeHtml(balance.type || '-') + '</td><td>' + Number(balance.allocated || 0) + '</td><td>' + Number(balance.used || 0) + '</td><td>' + Number(balance.remaining || 0) + '</td></tr>';
        }).join('')
        : '<tr><td colspan="4" class="muted">No leave balances available.</td></tr>';
    document.getElementById('teamProfileBalances').innerHTML = rows;
    document.getElementById('teamProfileModal').classList.add('open');
}

function openEmployeeProfileFromElement(button) {
    const payload = button?.dataset?.profile || '{}';
    openEmployeeProfile(JSON.parse(payload));
}

function closeEmployeeProfile(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('teamProfileModal').classList.remove('open');
}
</script>
@endpush
