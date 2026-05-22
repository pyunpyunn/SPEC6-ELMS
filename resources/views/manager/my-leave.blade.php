@extends('manager.layout')

@section('title', 'Apply for Leave')
@section('page_title', 'My Leave Application')

@section('content')
<div class="page-header">
    <div><h1>Apply for Leave</h1><p>Submit personal leave requests for HR review.</p></div>
</div>

<div style="display:grid;grid-template-columns:minmax(280px,420px) 1fr;gap:22px;align-items:start">
    <div class="card">
        <div class="card-header"><span class="card-title">New Leave Request</span></div>
        <div class="card-body">
            <div class="flash flash-warning">As a manager, your requests are reviewed by HR Admin.</div>
            <form method="POST" action="{{ route('manager.my-leave.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-grid single">
                    <div>
                        <label for="managerLeaveType">Leave Type</label>
                        <select name="leave_type_id" id="managerLeaveType" onchange="updateManagerProofHint()" required>
                            @foreach($leaveTypes as $type)
                                @php($balance = $employee?->leaveBalances->firstWhere('leave_type_id', $type->id))
                                <option value="{{ $type->id }}" data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}" data-requires-approval="{{ $type->requires_approval ? 1 : 0 }}" data-proof-rules="{{ $type->proof_rules }}" data-max-document-days="{{ $type->max_document_days ?? '' }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }} ({{ (int) ($balance?->remaining_days ?? 0) }} left)</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label for="managerApplyStartDate">Start Date</label><input type="date" id="managerApplyStartDate" name="start_date" value="{{ old('start_date') }}" onchange="calcManagerDays()" required></div>
                    <div><label for="managerApplyEndDate">End Date</label><input type="date" id="managerApplyEndDate" name="end_date" value="{{ old('end_date') }}" onchange="calcManagerDays()" required></div>
                    <div class="flash flash-warning" id="managerWeekendError" style="display:none">You can't select a weekend date.</div>
                    <div><label for="managerTotalDays">Total Working Days</label><input id="managerTotalDays" readonly placeholder="Auto calculated" value="{{ old('total_days') }}"></div>
                    <div><label for="managerLeaveReason">Reason</label><textarea id="managerLeaveReason" name="reason" rows="4" required>{{ old('reason') }}</textarea></div>
                    <div id="managerProofSection" style="display:none;">
                        <label for="managerProofFile">Attach Document <span id="managerProofRequired" class="req"></span></label>
                        <input id="managerProofFile" class="file-input-fit" type="file" name="proof">
                        <div class="muted" id="managerProofHint">Upload proof when required by the selected leave type.</div>
                    </div>
                </div>
                <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:18px">Submit Request to HR</button>
            </form>
        </div>
    </div>

    <div class="card" style="padding:0">
        <div class="card-header"><span class="card-title">My Leave History</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Status</th><th>Cancel</th><th>Details</th></tr></thead>
                <tbody>
                @forelse($myLeaves as $leave)
                    @php($reviewData = [
                        'name' => $employee?->full_name,
                        'dept' => $employee?->departmentRecord?->name,
                        'position' => $employee?->position,
                        'type' => $leave->leaveType->name,
                        'days' => (int) $leave->total_days.' days',
                        'status' => ucfirst($leave->status),
                        'filed_at' => $leave->created_at->format('M d, Y g:i A'),
                        'balance' => $employee?->leaveBalances->firstWhere('leave_type_id', $leave->leave_type_id)?->remaining_days,
                        'reason' => $leave->reason,
                        'proof' => $leave->proof_path,
                        'remarks' => $leave->remarks,
                    ])
                    <tr>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('M d') }}</td>
                        <td>{{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ (int) $leave->total_days }}</td>
                        <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                        <td>
                            @if($leave->status === 'pending')
                                <form method="POST" action="{{ route('manager.my-leave.cancel', $leave) }}" onsubmit="return confirm('Cancel this pending leave request?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
                                </form>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                        <td><button class="btn btn-outline btn-sm" type="button" data-review='@json($reviewData)' onclick="openLeaveDetailsFromElement(this)">View</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No personal leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($myLeaves, 'links'))<div class="pagination" style="padding:16px">{{ $myLeaves->links('vendor.pagination.hr') }}</div>@endif
    </div>
</div>

<div class="modal-overlay" id="leaveDetailsModal" onclick="closeLeaveDetails(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Leave Request - <span id="detailsEmpName"></span></h3>
            <button class="modal-close" type="button" onclick="closeLeaveDetails(event)">x</button>
        </div>
        <div class="modal-body">
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
                <div>
                    <div class="detail-row"><span class="dl">Leave Type</span><span class="dv" id="detailsType"></span></div>
                    <div class="detail-row"><span class="dl">Duration</span><span class="dv" id="detailsDays"></span></div>
                    <div class="detail-row"><span class="dl">Status</span><span class="dv" id="detailsStatus"></span></div>
                </div>
                <div>
                    <div class="detail-row"><span class="dl">Date Filed</span><span class="dv" id="detailsFiledAt"></span></div>
                    <div class="detail-row"><span class="dl">Balance</span><span class="dv" id="detailsBalance"></span></div>
                    <div class="detail-row"><span class="dl">Remarks</span><span class="dv" id="detailsRemarks"></span></div>
                </div>
                <div>
                    <div class="detail-row"><span class="dl">Department</span><span class="dv" id="detailsDept"></span></div>
                    <div class="detail-row"><span class="dl">Position</span><span class="dv" id="detailsPosition"></span></div>
                </div>
                <div><div class="detail-row"><span class="dl">Proof</span><span class="dv" id="detailsProofText">-</span></div></div>
            </div>
            <div class="form-group"><label class="form-label" for="detailsReason">Reason</label><textarea class="form-control field-readonly" readonly id="detailsReason"></textarea></div>
            <div id="detailsProofSection" style="display:none;margin-top:15px">
                <a id="detailsProofLink" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer" href="#">View Document</a>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline" type="button" onclick="closeLeaveDetails(event)">Back</button></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function getManagerSelectedLeaveTypeData() {
    const select = document.getElementById('managerLeaveType');
    const option = select?.options?.[select.selectedIndex];
    if (!option) return null;

    return {
        requiresProof: option.dataset.requiresProof === '1',
        maxDocumentDays: option.dataset.maxDocumentDays ? parseInt(option.dataset.maxDocumentDays, 10) : null,
        requiresApproval: option.dataset.requiresApproval === '1',
        proofRules: option.dataset.proofRules || '',
    };
}

function updateManagerProofVisibility() {
    const proofSection = document.getElementById('managerProofSection');
    const proofRequired = document.getElementById('managerProofRequired');
    const proofHint = document.getElementById('managerProofHint');
    const proofFile = document.getElementById('managerProofFile');
    const totalText = document.getElementById('managerTotalDays')?.value || '';
    const parsedDays = parseInt(String(totalText).split(' ')[0], 10);
    const workingDays = Number.isFinite(parsedDays) ? parsedDays : 0;
    const selected = getManagerSelectedLeaveTypeData();

    if (!proofSection || !proofRequired || !proofHint || !proofFile || !selected) return;

    const { requiresProof, maxDocumentDays, proofRules } = selected;
    let shouldShow = false;
    let message = '';

    if (requiresProof) {
        if (maxDocumentDays === null || maxDocumentDays <= 0) {
            shouldShow = workingDays > 0;
            message = proofRules || 'Proof document required for this leave type.';
        } else {
            shouldShow = workingDays >= maxDocumentDays;
            if (proofRules) {
                message = proofRules;
            } else {
                message = `Document Upload Requirement - Requests with ${maxDocumentDays} or more working day${maxDocumentDays > 1 ? 's' : ''} require supporting documents.`;
            }
        }
    }

    proofSection.style.display = shouldShow ? 'block' : 'none';
    proofFile.required = shouldShow;

    if (!shouldShow) {
        proofFile.value = '';
    }

    if (shouldShow) {
        proofRequired.textContent = '*';
        proofHint.textContent = message;
    } else {
        proofRequired.textContent = '';
        proofHint.textContent = requiresProof && maxDocumentDays > 0
            ? `Document upload will be required when your request reaches ${maxDocumentDays} working day${maxDocumentDays > 1 ? 's' : ''}.`
            : '';
    }
}

function updateManagerProofHint() {
    updateManagerProofVisibility();
}

function calcManagerDays() {
    const start = document.getElementById('managerApplyStartDate')?.value || '';
    const end = document.getElementById('managerApplyEndDate')?.value || '';
    const totalEl = document.getElementById('managerTotalDays');

    if (!totalEl) return;

    if (!start || !end) {
        totalEl.value = '';
        updateManagerProofVisibility();
        return;
    }

    const s = new Date(start);
    const e = new Date(end);
    if (e < s) {
        totalEl.value = 'Invalid range';
        updateManagerProofVisibility();
        return;
    }

    let count = 0;
    for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
        const day = d.getDay();
        if (day !== 0 && day !== 6) count++;
    }

    totalEl.value = count + ' working day' + (count !== 1 ? 's' : '');
    updateManagerProofVisibility();
}


function openLeaveDetails(data) {
    document.getElementById('detailsEmpName').textContent = data.name || '';
    document.getElementById('detailsType').textContent = data.type || '';
    document.getElementById('detailsDays').textContent = data.days || '';
    document.getElementById('detailsFiledAt').textContent = data.filed_at || '';
    document.getElementById('detailsBalance').textContent = (data.balance ?? '-') + ' days remaining';
    document.getElementById('detailsDept').textContent = data.dept || '-';
    document.getElementById('detailsPosition').textContent = data.position || '-';
    document.getElementById('detailsReason').value = data.reason || '';
    document.getElementById('detailsRemarks').textContent = data.remarks || '-';
    document.getElementById('detailsStatus').innerHTML = '<span class="badge badge-' + (data.status || 'pending').toLowerCase() + '">' + (data.status || 'Pending') + '</span>';

    const proofSection = document.getElementById('detailsProofSection');
    const proofText = document.getElementById('detailsProofText');
    const proofLink = document.getElementById('detailsProofLink');
    if (data.proof) {
        proofText.textContent = data.proof.split('/').pop();
        proofLink.href = '{{ asset('storage') }}/' + data.proof;
        proofSection.style.display = 'block';
    } else {
        proofText.textContent = '-';
        proofSection.style.display = 'none';
    }

    document.getElementById('leaveDetailsModal').classList.add('open');
}

function openLeaveDetailsFromElement(button) {
    const payload = button?.dataset?.review || '{}';
    openLeaveDetails(JSON.parse(payload));
}

function closeLeaveDetails(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('leaveDetailsModal').classList.remove('open');
}

(function () {
    function pad2(n) { return String(n).padStart(2, '0'); }
    function toISODate(d) { return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()); }
    function isWeekendISO(iso) {
        if (!iso) return false;
        const parts = iso.split('-');
        if (parts.length !== 3) return false;
        const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
        return d.getDay() === 0 || d.getDay() === 6;
    }
    function showWeekendError(show) {
        const el = document.getElementById('managerWeekendError');
        if (el) el.style.display = show ? 'block' : 'none';
    }
    function clampAndValidate(input) {
        const min = input.dataset.minDate || '';
        if (min && input.value && input.value < min) input.value = min;
        if (input.value && isWeekendISO(input.value)) {
            input.value = '';
            showWeekendError(true);
            return;
        }
        const startVal = document.getElementById('managerApplyStartDate')?.value;
        const endVal = document.getElementById('managerApplyEndDate')?.value;
        showWeekendError(isWeekendISO(startVal) || isWeekendISO(endVal));
    }
    document.addEventListener('DOMContentLoaded', function () {
        const startInput = document.getElementById('managerApplyStartDate');
        const endInput = document.getElementById('managerApplyEndDate');
        if (!startInput || !endInput) return;
        const minISO = toISODate(new Date());
        [startInput, endInput].forEach(function (input) {
            input.min = minISO;
            input.dataset.minDate = minISO;
            input.addEventListener('change', function () { clampAndValidate(input); });
            clampAndValidate(input);
        });        // Initialize proof visibility and working days
        calcManagerDays();
        updateManagerProofHint();    });
})();
</script>
@endpush
