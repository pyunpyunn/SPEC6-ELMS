@extends('hr.layout')

@section('content')
@php
    $visibleBalances = collect($leaveBalances ?? []);
    if ($selectedTypeId) {
        $visibleBalances = $visibleBalances->filter(fn ($balance) => $balance->leave_type_id === $selectedTypeId);
    }
    $compensableBalances = $visibleBalances->filter(fn ($balance) => (bool) $balance->leaveType?->is_compensable);
@endphp

<div class="page active" id="page-myleave">
    <div class="page-header">
        <div>
            <h1>My Leave</h1>
            <p>{{ $employee?->full_name }} · {{ $employee?->position }} · {{ $employee?->departmentRecord?->name }}</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" onclick="document.getElementById('applyLeaveModal').classList.add('open')">Apply for Leave</button>
        </div>
    </div>

    <div class="my-leave-grid">
        @forelse($visibleBalances as $balance)
            <div class="my-bal-card">
                <div class="my-bal-type">{{ $balance->leaveType->name }}</div>
                <div class="my-bal-days">{{ (int) $balance->remaining_days }}</div>
                <div class="my-bal-total">of {{ (int) $balance->allocated_days }} days remaining</div>
                <div class="my-bal-bar"><div class="progress"><div class="progress-bar" style="width:{{ $balance->allocated_days ? (int)(($balance->remaining_days / $balance->allocated_days) * 100) : 0 }}%"></div></div></div>
            </div>
        @empty
            <div class="card" style="grid-column:1/-1"><div class="card-body">No leave balances available.</div></div>
        @endforelse
    </div>

    <div class="comp-card" style="max-width:460px;margin-bottom:22px">
        <div class="comp-title">Estimated Yearly Compensation</div>
        <div class="comp-amount">₱{{ number_format($compensationEstimate ?? 0, 2) }}</div>
        <div class="comp-sub">{{ $compensableBalances->sum(fn ($b) => (int) $b->remaining_days) }} compensable unused days × ₱{{ number_format((float)($employee?->daily_rate ?? 0), 2) }}/day · paid at year-end</div>
    </div>

    <div class="flash flash-warning">
        As HR Admin, your leave requests are reviewed by another designated HR Admin or the CEO/Director.
    </div>

    <div class="card" style="margin-top:18px" id="page-myleave">
        <div class="card-header">
            <span class="card-title">My Leave Requests</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Reason</th><th>Status</th><th>Reviewed By</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($leaveApplications as $leave)
                    <tr>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('M d') }}</td>
                        <td>{{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ (int) $leave->total_days }}</td>
                        <td>{{ $leave->reason }}</td>
                        <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                        <td>{{ $leave->reviewer?->name ?? '—' }}</td>
                        <td>
                            @if($leave->status === 'pending')
                                <span class="muted">Pending</span>
                            @else
                                <button class="btn btn-outline btn-sm" type="button" data-leave='@json([
                                    'type' => $leave->leaveType->name,
                                    'start' => $leave->start_date->format('M d, Y'),
                                    'end' => $leave->end_date->format('M d, Y'),
                                    'days' => (int) $leave->total_days.' days',
                                    'status' => ucfirst($leave->status),
                                    'reviewer' => $leave->reviewer?->name ?? '—',
                                    'filed_at' => $leave->created_at->format('M d, Y g:i A'),
                                    'reason' => $leave->reason,
                                    'remarks' => $leave->remarks ?? '—',
                                    'proof' => $leave->proof_path,
                                ])' onclick="openLeaveDetailsFromElement(this)">View</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $leaveApplications->links('vendor.pagination.hr', ['anchor' => 'page-myleave']) }}</div>
    </div>
</div>

<div class="modal-overlay" id="applyLeaveModal" onclick="closeApplyModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Apply for Leave</h3>
            <button class="modal-close" type="button" onclick="closeApplyModal(event)">✕</button>
        </div>
        <form class="modal-body" id="applyLeaveForm" method="POST" action="{{ route('admin.my-leave.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="flash flash-warning">
                Your leave request will be routed to another designated HR Admin for approval.
            </div>
            <div class="form-grid" style="gap:14px">
                <div class="form-group span2">
                    <label for="applyLeaveType">Leave Type <span class="req">*</span></label>
                    <select id="applyLeaveType" name="leave_type_id" onchange="handleLeaveTypeChange()" required>
                        <option value="">Select leave type...</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}" data-requires-approval="{{ $type->requires_approval ? 1 : 0 }}" data-proof-rules="{{ $type->proof_rules }}" data-max-document-days="{{ $type->max_document_days ?? '' }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label for="applyStart">Start Date <span class="req">*</span></label><input type="date" id="applyStart" name="start_date" onchange="calcDays()" required></div>
                <div class="form-group"><label for="applyEnd">End Date <span class="req">*</span></label><input type="date" id="applyEnd" name="end_date" onchange="calcDays()" required></div>
                <div class="flash flash-error" id="applyWeekendError" style="display:none;margin-top:10px">You can't select a weekend date.</div>
                <div class="form-group span2"><label for="applyTotalDays">Total Working Days</label><input id="applyTotalDays" readonly placeholder="Auto calculated"></div>
                <div class="form-group span2"><label for="applyReason">Reason <span class="req">*</span></label><textarea id="applyReason" name="reason" placeholder="Describe your reason for leave..." required></textarea></div>
                <div class="form-group span2 leave-proof-section" id="proofSection" style="display:none;">
                    <label for="proofFile">Supporting Document <span id="proofRequired" class="req"></span></label>
                    <div class="file-upload" onclick="document.getElementById('proofFile').click()">
                        <p>Drag & drop or click to upload</p>
                        <span>PDF, JPG, PNG up to 5MB</span>
                        <input type="file" name="proof" id="proofFile" class="upload-hidden" accept=".pdf,.jpg,.jpeg,.png" style="display:none">
                    </div>
                    <span class="form-hint" id="proofHint"></span>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeApplyModal(event)">Cancel</button>
            <button class="btn btn-primary" type="submit" form="applyLeaveForm">Submit Request</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="leaveDetailsModal" onclick="closeLeaveDetails(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Leave Request Details</h3>
            <button class="modal-close" type="button" onclick="closeLeaveDetails(event)">✕</button>
        </div>
        <div class="modal-body">
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div class="detail-row"><span class="dl">Leave Type</span><span class="dv" id="detailsLeaveType"></span></div>
                <div class="detail-row"><span class="dl">Duration</span><span class="dv" id="detailsLeaveDates"></span></div>
                <div class="detail-row"><span class="dl">Total Days</span><span class="dv" id="detailsLeaveDays"></span></div>
                <div class="detail-row"><span class="dl">Status</span><span class="dv" id="detailsLeaveStatus"></span></div>
                <div class="detail-row"><span class="dl">Reviewed By</span><span class="dv" id="detailsLeaveReviewer"></span></div>
                <div class="detail-row"><span class="dl">Filed At</span><span class="dv" id="detailsLeaveFiledAt"></span></div>
            </div>
            <div class="form-group"><label class="form-label" for="detailsLeaveReason">Reason</label><textarea class="form-control field-readonly" readonly id="detailsLeaveReason"></textarea></div>
            <div class="form-group"><label class="form-label">Remarks</label><div class="form-control field-readonly" id="detailsLeaveRemarks" style="min-height:80px;padding:12px;white-space:pre-wrap"></div></div>
            <div class="form-group" id="detailsProofSection" style="display:none;margin-top:14px">
                <label class="form-label">Supporting Document</label>
                <a id="detailsProofLink" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer" href="#">Download</a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeLeaveDetails(event)">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closeApplyModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('applyLeaveModal').classList.remove('open');
}

function getSelectedLeaveTypeData() {
    const select = document.getElementById('applyLeaveType');
    const option = select?.options?.[select.selectedIndex];
    if (!option) return null;

    return {
        requiresProof: option.dataset.requiresProof === '1',
        maxDocumentDays: option.dataset.maxDocumentDays ? parseInt(option.dataset.maxDocumentDays, 10) : null,
        requiresApproval: option.dataset.requiresApproval === '1',
        proofRules: option.dataset.proofRules || '',
    };
}

function updateProofVisibility() {
    const proofSection = document.getElementById('proofSection');
    const proofReq = document.getElementById('proofRequired');
    const proofHint = document.getElementById('proofHint');
    const proofFile = document.getElementById('proofFile');
    const totalText = document.getElementById('applyTotalDays')?.value || '';
    const parsedDays = parseInt(String(totalText).split(' ')[0], 10);
    const workingDays = Number.isFinite(parsedDays) ? parsedDays : 0;
    const selected = getSelectedLeaveTypeData();

    if (!proofSection || !proofReq || !proofHint || !proofFile || !selected) return;

    const { requiresProof, maxDocumentDays, requiresApproval, proofRules } = selected;
    let shouldShow = false;
    let message = '';

    if (requiresProof) {
        if (maxDocumentDays === null || maxDocumentDays <= 0) {
            shouldShow = workingDays > 0;
            message = proofRules || 'Proof document required for this leave type.';
        } else {
            // max_document_days > 0 means require proof when working days REACHES or EXCEEDS the threshold
            // e.g., if max_document_days = 3, show upload when working days >= 3 (i.e., 3+ days)
            shouldShow = workingDays >= maxDocumentDays;
            message = proofRules || `Document Upload Requirement - Requests with ${maxDocumentDays} or more working day${maxDocumentDays > 1 ? 's' : ''} require supporting documents.`;
        }
    }

    proofSection.style.display = shouldShow ? 'block' : 'none';
    proofFile.required = shouldShow;

    if (!shouldShow) {
        proofFile.value = '';
    }

    if (shouldShow) {
        proofReq.textContent = '*';
        proofHint.textContent = message;
    } else {
        proofReq.textContent = '';
        proofHint.textContent = requiresProof && maxDocumentDays > 0
            ? `Document upload will be required when your request reaches ${maxDocumentDays} working day${maxDocumentDays > 1 ? 's' : ''}.`
            : requiresApproval
                ? 'This leave type is auto-approved by configuration.'
                : '';
    }
}

function handleLeaveTypeChange() {
    updateProofVisibility();
}

function pad2(n) {
    return String(n).padStart(2, '0');
}

function toISODate(d) {
    return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
}

function isWeekendISO(iso) {
    if (!iso) return false;
    const parts = iso.split('-');
    if (parts.length !== 3) return false;

    const year = Number(parts[0]);
    const monthIndex = Number(parts[1]) - 1;
    const day = Number(parts[2]);

    const d = new Date(year, monthIndex, day);
    const dow = d.getDay(); // 0=Sun, 6=Sat
    return dow === 0 || dow === 6;
}

function showWeekendError(show) {
    const el = document.getElementById('applyWeekendError');
    if (!el) return;
    el.style.display = show ? 'block' : 'none';
}

function clampWeekend(input) {
    if (input.value && isWeekendISO(input.value)) {
        input.value = '';
        showWeekendError(true);
        return;
    }

    // Hide error when both dates are non-weekends (or empty)
    const startVal = document.getElementById('applyStart')?.value || '';
    const endVal = document.getElementById('applyEnd')?.value || '';

    const startOk = !startVal || !isWeekendISO(startVal);
    const endOk = !endVal || !isWeekendISO(endVal);

    showWeekendError(!(startOk && endOk));
}

function setupWeekendAndPastGuards() {
    const startInput = document.getElementById('applyStart');
    const endInput = document.getElementById('applyEnd');
    if (!startInput || !endInput) return;

    const minISO = toISODate(new Date());
    startInput.min = minISO;
    endInput.min = minISO;

    // Validate on change
    startInput.addEventListener('change', function () {
        clampWeekend(startInput);
    });
    endInput.addEventListener('change', function () {
        clampWeekend(endInput);
    });

    // Validate old values (if any)
    clampWeekend(startInput);
    clampWeekend(endInput);
}

function calcDays() {
    const start = document.getElementById('applyStart').value;
    const end = document.getElementById('applyEnd').value;
    if (!start || !end) {
        document.getElementById('applyTotalDays').value = '';
        updateProofVisibility();
        return;
    }
    const s = new Date(start);
    const e = new Date(end);
    if (e < s) {
        document.getElementById('applyTotalDays').value = 'Invalid range';
        updateProofVisibility();
        return;
    }
    let count = 0;
    for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
        const day = d.getDay();
        if (day !== 0 && day !== 6) count++;
    }
    document.getElementById('applyTotalDays').value = count + ' working day' + (count !== 1 ? 's' : '');
    updateProofVisibility();
}

function openLeaveDetails(data) {
    document.getElementById('detailsLeaveType').textContent = data.type || '';
    document.getElementById('detailsLeaveDates').textContent = (data.start && data.end) ? `${data.start} – ${data.end}` : '';
    document.getElementById('detailsLeaveDays').textContent = data.days || '';
    document.getElementById('detailsLeaveStatus').innerHTML = '<span class="badge badge-' + (data.status || 'pending').toLowerCase() + '">' + (data.status || 'Pending') + '</span>';
    document.getElementById('detailsLeaveReviewer').textContent = data.reviewer || '—';
    document.getElementById('detailsLeaveFiledAt').textContent = data.filed_at || '';
    document.getElementById('detailsLeaveReason').value = data.reason || '';
    document.getElementById('detailsLeaveRemarks').textContent = data.remarks || '-';

    const proofSection = document.getElementById('detailsProofSection');
    const proofLink = document.getElementById('detailsProofLink');
    const storageBase = '{{ asset("storage") }}';
    if (data.proof) {
        proofSection.style.display = 'block';
        proofLink.href = storageBase + '/' + data.proof;
        proofLink.textContent = data.proof.split('/').pop();
    } else {
        proofSection.style.display = 'none';
        proofLink.href = '#';
        proofLink.textContent = 'No document';
    }

    document.getElementById('leaveDetailsModal').classList.add('open');
}

function openLeaveDetailsFromElement(button) {
    const payload = button?.dataset?.leave || '{}';
    openLeaveDetails(JSON.parse(payload));
}

function closeLeaveDetails(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('leaveDetailsModal').classList.remove('open');
}

// Initialize guards when scripts load
document.addEventListener('DOMContentLoaded', function () {
    setupWeekendAndPastGuards();
    updateProofVisibility();
});

</script>
@endpush


