@extends('admin.layout')

@section('content')
<div class="page active" id="page-requests">
    <div class="page-header">
        <div>
            <h1>Master Request Log{{ $selectedDepartment ? ' - '.$selectedDepartment->name : '' }}</h1>
            <p>Search, filter, review, and export leave requests</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-outline btn-sm" href="{{ route('admin.reports.export', request()->all() + ['type' => 'leaves']) }}">Export CSV</a>
        </div>
    </div>

    <div class="dept-log-tabs">
        <a class="dept-log-tab {{ ! $selectedDepartment ? 'active' : '' }}" href="{{ route('admin.requests.index') }}">All Departments</a>
        @foreach($departments as $department)
            <a class="dept-log-tab {{ $selectedDepartment?->id === $department->id ? 'active' : '' }}" href="{{ route('admin.requests.index', ['department_id' => $department->id]) }}">{{ $department->name }}</a>
        @endforeach
    </div>

    <form class="filter-bar" method="GET">
        @if($selectedDepartment)
            <input type="hidden" name="department_id" value="{{ $selectedDepartment->id }}">
        @endif
        <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search by employee name or ID">
        </div>
        <select name="leave_type_id">
            <option value="">All Leave Types</option>
            @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">All Status</option>
            @foreach(['pending','approved','rejected','cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Dept</th>
                    <th>Leave Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Filed</th>
                    <th>Status</th>
                    <th>Reviewed By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="reqTableBody">
                @forelse($requests as $leave)
                    <tr data-status="{{ $leave->status }}" data-dept="{{ $leave->employee->departmentRecord?->name }}" data-type="{{ str($leave->leaveType->name)->slug() }}">
                        <td>
                            <div class="td-name">{{ $leave->employee->full_name }}</div>
                            <div class="td-sub">{{ $leave->employee->employee_id }}</div>
                        </td>
                        <td class="td-pos">{{ $leave->employee->position }}</td>
                        <td>{{ $leave->employee->departmentRecord?->name }}</td>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('M d') }}</td>
                        <td>{{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ (int) $leave->total_days }}</td>
                        <td>{{ $leave->created_at->format('M d, g:i A') }}</td>
                        <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                        <td>{{ $leave->reviewer?->name ?? 'Not yet reviewed' }}</td>
                        <td>
                            @php
                                $reviewData = [
                                'id' => $leave->id,
                                'name' => $leave->employee->full_name,
                                'type' => $leave->leaveType->name,
                                'days' => $leave->total_days.' days',
                                'status' => ucfirst($leave->status),
                                'filed_by' => $leave->employee->full_name,
                                'filed_at' => $leave->created_at->format('M d, Y g:i A'),
                                'balance' => $leave->employee->leaveBalances->firstWhere('leave_type_id', $leave->leave_type_id)?->remaining_days,
                                'reason' => $leave->reason,
                                'proof' => $leave->proof_path,
                                'remarks' => $leave->remarks,
                                'review_action' => route('admin.requests.review', $leave),
                            ];
                            @endphp
                            @if($leave->status === 'pending')
                                <div class="actions">
                                    <button class="btn btn-success btn-sm" type="button" onclick="openReviewModal(@js($reviewData), 'approved')">Approve</button>
                                    <button class="btn btn-danger btn-sm" type="button" onclick="openReviewModal(@js($reviewData), 'rejected')">Reject</button>
                                </div>
                            @else
                                <span class="td-sub">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $requests->links() }}</div>
</div>

<div class="modal-overlay" id="reviewModal" onclick="closeReviewModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Leave Request - <span id="reviewEmpName"></span></h3>
            <button class="modal-close" type="button" onclick="closeReviewModal(event)">✕</button>
        </div>
        <form class="modal-body" method="POST" id="reviewForm">
            @csrf
            @method('PATCH')
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
                <div>
                    <div class="detail-row"><span class="dl">Leave Type</span><span class="dv" id="reviewType"></span></div>
                    <div class="detail-row"><span class="dl">Duration</span><span class="dv" id="reviewDays"></span></div>
                    <div class="detail-row"><span class="dl">Status</span><span class="dv" id="reviewStatusBadge"></span></div>
                </div>
                <div>
                    <div class="detail-row"><span class="dl">Filed by</span><span class="dv" id="reviewFiledBy"></span></div>
                    <div class="detail-row"><span class="dl">Date Filed</span><span class="dv" id="reviewFiledAt"></span></div>
                    <div class="detail-row"><span class="dl">Balance</span><span class="dv" id="reviewBalance"></span></div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:15px">
                <label>Reason</label>
                <textarea readonly id="reviewReason" style="background:var(--surface2)"></textarea>
            </div>
            <div id="reviewProofSection" style="display:none;margin-bottom:15px">
                <label style="display:block;margin-bottom:7px">Attached Document</label>
                <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--surface2);border-radius:var(--radius-sm);border:1px solid var(--border)">
                    <span id="reviewProofName" style="font-size:15px;color:var(--text)">medical_certificate.pdf</span>
                    <button class="btn btn-outline btn-sm" type="button" style="margin-left:auto">View Proof</button>
                </div>
            </div>
            <div class="form-group">
                <label>Remarks <span class="req">*</span></label>
                <textarea id="reviewRemarks" name="remarks" placeholder="Enter your decision remarks here..." required></textarea>
            </div>
            <input type="hidden" name="status" id="reviewDecisionStatus" value="approved">
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeReviewModal(event)">Cancel</button>
            <button class="btn btn-danger" type="button" onclick="submitDecision('rejected')">Reject</button>
            <button class="btn btn-success" type="button" onclick="submitDecision('approved')">Approve</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openReviewModal(data, decision = 'approved') {
    document.getElementById('reviewEmpName').textContent = data.name || '';
    document.getElementById('reviewType').textContent = data.type || '';
    document.getElementById('reviewDays').textContent = data.days || '';
    document.getElementById('reviewFiledBy').textContent = data.filed_by || '';
    document.getElementById('reviewFiledAt').textContent = data.filed_at || '';
    document.getElementById('reviewBalance').textContent = (data.balance ?? '—') + ' days remaining';
    document.getElementById('reviewReason').value = data.reason || '';
    document.getElementById('reviewForm').action = data.review_action || '#';
    document.getElementById('reviewDecisionStatus').value = decision;
    document.getElementById('reviewProofSection').style.display = data.proof ? 'block' : 'none';
    document.getElementById('reviewProofName').textContent = data.proof ? data.proof.split('/').pop() : 'medical_certificate.pdf';
    document.getElementById('reviewStatusBadge').innerHTML = '<span class="badge badge-' + ((data.status || 'Pending').toLowerCase()) + '">' + (data.status || 'Pending') + '</span>';
    document.getElementById('reviewModal').classList.add('open');
}

function closeReviewModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('reviewModal').classList.remove('open');
}

function submitDecision(status) {
    document.getElementById('reviewDecisionStatus').value = status;
    document.getElementById('reviewForm').submit();
}
</script>
@endpush
