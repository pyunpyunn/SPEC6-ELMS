@extends('hr.layout')

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

        <div class="filter-bar-row">
            <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input name="search" value="{{ request('search') }}" placeholder="Search leave requests by name, ID, or type...">
            </div>
        </div>

        <div class="filter-bar-row">
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
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department<br><span class="muted">Position</span></th>
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

                        <td>
                            <div class="td-name">{{ $leave->employee->departmentRecord?->name ?? '—' }}</div>
                            <div class="td-sub">{{ $leave->employee->position ?? '—' }}</div>
                        </td>

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
                                    'dept' => $leave->employee->departmentRecord?->name,
                                    'position' => $leave->employee->position,
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
                                    'is_pending' => $leave->status === 'pending',
                                ];
                            @endphp

                            <button class="btn btn-outline btn-sm" type="button" onclick="openReviewModal(@js($reviewData))">
                                Review
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $requests->links('vendor.pagination.hr', ['anchor' => 'page-requests']) }}</div>
</div>

<div class="modal-overlay" id="reviewModal" onclick="closeReviewModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Leave Request - <span id="reviewEmpName"></span></h3>
            <button class="modal-close" type="button" onclick="closeReviewModal(event)">✕</button>
        </div>

        <form class="modal-body" method="POST" id="reviewForm" data-action="">
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

                <div>
                    <div class="detail-row"><span class="dl">Department</span><span class="dv" id="reviewDept"></span></div>
                    <div class="detail-row"><span class="dl">Position</span><span class="dv" id="reviewPosition"></span></div>
                </div>

                <div>
                    <div class="detail-row"><span class="dl">Proof</span><span class="dv" id="reviewProofText">—</span></div>
                    <div class="detail-row"><span class="dl">Reviewed Remarks</span><span class="dv" id="reviewExistingRemarks">—</span></div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:15px">
                <label>Reason</label>
                <textarea readonly id="reviewReason" style="background:var(--surface2)"></textarea>
            </div>

            <div id="reviewProofSection" style="display:none;margin-bottom:15px">
                <label style="display:block;margin-bottom:7px">Attached Document</label>
                <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--surface2);border-radius:var(--radius-sm);border:1px solid var(--border)">
                    <span id="reviewProofName" style="font-size:15px;color:var(--text)">document.pdf</span>
                    <a id="reviewProofLink" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer" style="margin-left:auto" href="#">View Document</a>
                </div>
                <img id="reviewProofPreview" src="" alt="Proof document" style="max-width:100%;margin-top:12px;border-radius:8px;border:1px solid var(--border);display:none">
            </div>

            <div class="form-group" id="reviewRemarksWrap">
                <label>Remarks <span class="req">*</span></label>
                <textarea id="reviewRemarks" name="remarks" placeholder="Enter your decision remarks here..." required></textarea>
            </div>

            <input type="hidden" name="status" id="reviewDecisionStatus" value="approved">
            <input type="hidden" name="is_pending" id="reviewIsPending" value="0">
        </form>

        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeReviewModal(event)">Back</button>

            <div id="reviewPendingActions" style="display:none;gap:10px">
                <button class="btn btn-danger" type="button" onclick="requestDecision('rejected')">Reject</button>
                <button class="btn btn-success" type="button" onclick="requestDecision('approved')">Approve</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closeReviewModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('reviewModal').classList.remove('open');
}

function requestDecision(status) {
    const isPending = document.getElementById('reviewIsPending').value === '1';
    if (!isPending) return;

    const remarks = document.getElementById('reviewRemarks').value.trim();
    if (!remarks) {
        alert('Please enter remarks before proceeding.');
        return;
    }

    const actionLabel = status === 'approved' ? 'approve' : 'reject';
    if (!confirm(`Are you sure you want to ${actionLabel} this leave request?`)) return;

    document.getElementById('reviewDecisionStatus').value = status;
    document.getElementById('reviewForm').action = document.getElementById('reviewForm').dataset.action || '#';
    document.getElementById('reviewForm').submit();
}

function openReviewModal(data) {
    document.getElementById('reviewEmpName').textContent = data.name || '';
    document.getElementById('reviewType').textContent = data.type || '';
    document.getElementById('reviewDays').textContent = data.days || '';

    document.getElementById('reviewFiledBy').textContent = data.filed_by || '';
    document.getElementById('reviewFiledAt').textContent = data.filed_at || '';
    document.getElementById('reviewBalance').textContent = (data.balance ?? '—') + ' days remaining';

    document.getElementById('reviewDept').textContent = data.dept || '—';
    document.getElementById('reviewPosition').textContent = data.position || '—';

    document.getElementById('reviewReason').value = data.reason || '';

    document.getElementById('reviewForm').action = data.review_action || '#';
    document.getElementById('reviewForm').dataset.action = data.review_action || '#';

    const statusLower = (data.status || 'Pending').toLowerCase();
    document.getElementById('reviewStatusBadge').innerHTML =
        '<span class="badge badge-' + statusLower + '">' + (data.status || 'Pending') + '</span>';

    const pending = data.is_pending === true;
    document.getElementById('reviewIsPending').value = pending ? '1' : '0';

    const pendingActions = document.getElementById('reviewPendingActions');
    const remarksWrap = document.getElementById('reviewRemarksWrap');
    const remarksEl = document.getElementById('reviewRemarks');

    pendingActions.style.display = pending ? 'flex' : 'none';
    remarksWrap.style.opacity = pending ? '1' : '.9';
    remarksEl.disabled = !pending;
    remarksEl.placeholder = pending ? 'Enter your decision remarks here...' : '';
    remarksEl.value = data.remarks || '';

    // Proof
    const proofSection = document.getElementById('reviewProofSection');
    const proofText = document.getElementById('reviewProofText');
    const proofName = document.getElementById('reviewProofName');
    const proofLink = document.getElementById('reviewProofLink');
    const previewImg = document.getElementById('reviewProofPreview');

    if (data.proof) {
        const fileName = data.proof.split('/').pop();
        proofText.textContent = fileName;
        proofName.textContent = fileName;
        proofLink.href = '{{ asset('storage') }}/' + data.proof;
        proofSection.style.display = 'block';

        const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        const isImage = imageExtensions.some(ext => fileName.toLowerCase().endsWith(ext));

        if (isImage) {
            previewImg.src = '{{ asset('storage') }}/' + data.proof;
            previewImg.style.display = 'block';
        } else {
            previewImg.style.display = 'none';
        }
    } else {
        proofSection.style.display = 'none';
        proofText.textContent = '—';
        previewImg.style.display = 'none';
    }

    document.getElementById('reviewModal').classList.add('open');
}
</script>
@endpush


