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
                        <label>Leave Type</label>
                        <select name="leave_type_id" id="managerLeaveType" onchange="updateManagerProofHint()" required>
                            @foreach($leaveTypes as $type)
                                @php($balance = $employee?->leaveBalances->firstWhere('leave_type_id', $type->id))
                                <option value="{{ $type->id }}" data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}" data-requires-approval="{{ $type->requires_approval ? 1 : 0 }}" data-proof-rules="{{ $type->proof_rules }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }} ({{ (int) ($balance?->remaining_days ?? 0) }} left)</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label>Start Date</label><input type="date" id="managerApplyStartDate" name="start_date" value="{{ old('start_date') }}" required></div>
                    <div><label>End Date</label><input type="date" id="managerApplyEndDate" name="end_date" value="{{ old('end_date') }}" required></div>
                    <div class="flash flash-warning" id="managerWeekendError" style="display:none">You can't select a weekend date.</div>
                    <div><label>Reason</label><textarea name="reason" rows="4" required>{{ old('reason') }}</textarea></div>
                    <div>
                        <label>Attach Document</label>
                        <input class="file-input-fit" type="file" name="proof">
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
                        <td><button class="btn btn-outline btn-sm" type="button" onclick="openLeaveDetails(@js($reviewData))">View</button></td>
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
            <h3>Leave Request - <span id="detailsEmpName"></span></h3>
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
            <div class="form-group"><label>Reason</label><textarea readonly id="detailsReason" style="background:var(--surface2)"></textarea></div>
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
function updateManagerProofHint(){
    const option = document.getElementById('managerLeaveType')?.selectedOptions?.[0];
    const hint = document.getElementById('managerProofHint');
    if (!option || !hint) return;

    hint.textContent = [
        option.dataset.requiresProof === '1' ? 'Proof required.' : 'Proof optional.',
        option.dataset.requiresApproval === '0' ? 'Auto-approved.' : 'Requires HR approval.',
        option.dataset.proofRules || '',
    ].filter(Boolean).join(' ');
}
document.addEventListener('DOMContentLoaded', updateManagerProofHint);

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
        });
    });
})();
</script>
@endpush
