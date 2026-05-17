<div class="modal-overlay" id="applyLeaveModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Apply for Leave</h3>
            <button class="modal-close" type="button" onclick="closeModal('applyLeaveModal')">x</button>
        </div>

        <form method="POST" action="{{ route('employee.leaves.store') }}" enctype="multipart/form-data" onsubmit="return validateApplyLeaveConflicts();">
            @csrf

            @php
                // If the parent page provides $leaveApplications, use it to disable conflicting dates in the UI.
                // Backend already enforces these rules; this is only to improve UX.
                $activeLeaveRanges = collect();
                if (isset($leaveApplications) && $leaveApplications) {
                    $activeLeaveRanges = $leaveApplications
                        ->whereIn('status', ['approved'])
                        ->map(function ($leave) {
                            return [
                                'start' => $leave->start_date->toDateString(),
                                'end' => $leave->end_date->toDateString(),
                                'status' => $leave->status,
                            ];
                        })
                        ->values();
                }
            @endphp

            <div class="modal-body">
                <div class="flash flash-warning" style="margin-bottom:14px">
                    Your leave request will be reviewed by HR for approval.
                </div>

                <div class="form-grid" style="gap:14px">
                    <div class="form-group span2">
                        <label>Leave Type <span class="req">*</span></label>
                        <select id="applyLeaveType" name="leave_type_id" onchange="handleLeaveTypeChange()" required>
                            <option value="">Select leave type...</option>
                            @foreach($leaveTypes as $type)
                                <option
                                    value="{{ $type->id }}"
                                    data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}"
                                    data-requires-approval="{{ $type->requires_approval ? 1 : 0 }}"
                                    data-proof-rules="{{ $type->proof_rules }}"
                                    @selected(old('leave_type_id') == $type->id)
                                >
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Start Date <span class="req">*</span></label>
                        <input
                            type="date"
                            id="applyStart"
                            name="start_date"
                            value="{{ old('start_date') }}"
                            required
                            onchange="calcDays(); clampWeekendStart(this); toggleEndDateBasedOnStart(); validateApplyLeaveConflicts();"
                        >
                    </div>

                    <div class="form-group">
                        <label>End Date <span class="req">*</span></label>
                        <input
                            type="date"
                            id="applyEnd"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            required
                            onchange="calcDays(); validateApplyLeaveConflicts();"
                        >
                    </div>

                    <div class="flash flash-error" id="applyWeekendError" style="display:none;margin-top:10px;grid-column:1/-1">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
                            <div>You can't apply leave on a weekend.</div>
                            <button type="button" class="btn btn-outline btn-sm" onclick="dismissWeekendError()" style="white-space:nowrap">✕</button>
                        </div>
                    </div>

                    <div class="flash flash-error" id="applyConflictError" style="display:none;margin-top:10px;grid-column:1/-1">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
                            <div id="applyConflictErrorText">You already have an approved leave scheduled on one or more selected dates. Please adjust your range.</div>
                            <button type="button" class="btn btn-outline btn-sm" onclick="dismissConflictError()" style="white-space:nowrap">✕</button>
                        </div>
                    </div>

                    <div class="form-group span2">
                        <label>Total Working Days</label>
                        <input id="applyTotalDays" readonly placeholder="Auto calculated" value="{{ old('total_days') }}">
                    </div>

                    <div class="form-group span2 leave-reason">
                        <label>Reason <span class="req">*</span></label>
                        <textarea
                            id="applyReason"
                            name="reason"
                            placeholder="Describe your reason for leave..."
                            required
                        >{{ old('reason') }}</textarea>
                    </div>

                    <div class="form-group span2 leave-proof-section" id="proofSection" style="display:none;">
                        <label>
                            Supporting Document <span id="proofRequired" class="req"></span>
                        </label>

                        <div class="file-upload" onclick="document.getElementById('proofFile').click()">
                            <p>Drag & drop or click to upload</p>
                            <span>PDF, JPG, PNG up to 5MB</span>

                                <input
                                    type="file"
                                    name="proof"
                                    id="proofFile"
                                    class="upload-hidden"
                                    accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx"
                                    style="display:none"
                                >
                        </div>

                        <span class="form-hint" id="proofHint"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeModal('applyLeaveModal')">Cancel</button>
                <button class="btn btn-primary" type="submit">Submit Request</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function closeModal(id) {
    document.getElementById(id)?.classList.remove('open');
}

function applySickLeaveProofVisibilityRule_old() {
    const leaveTypeSelect = document.getElementById('applyLeaveType');
    const proofSection = document.getElementById('proofSection');
    const proofFile = document.getElementById('proofFile');

    if (!leaveTypeSelect || !proofSection || !proofFile) return;

    const selectedOptionText = leaveTypeSelect?.options?.[leaveTypeSelect.selectedIndex]?.textContent?.toLowerCase?.() || '';
    const isSick = selectedOptionText.includes('sick');

    const totalText = document.getElementById('applyTotalDays')?.value || '';
    const parsedDays = parseInt(String(totalText).split(' ')[0], 10);
    const workingDays = Number.isFinite(parsedDays) ? parsedDays : 0;

    const shouldShow = isSick && workingDays >= 3;

    proofSection.style.display = shouldShow ? 'block' : 'none';
    proofFile.required = shouldShow;
}

function applySickLeaveProofVisibilityRule() {
    const leaveTypeSelect = document.getElementById('applyLeaveType');
    const proofSection = document.getElementById('proofSection');
    const proofFile = document.getElementById('proofFile');

    if (!leaveTypeSelect || !proofSection || !proofFile) return;

    const selectedOptionText = leaveTypeSelect?.options?.[leaveTypeSelect.selectedIndex]?.textContent?.toLowerCase?.() || '';
    const isSick = selectedOptionText.includes('sick');

    const totalText = document.getElementById('applyTotalDays')?.value || '';
    const parsedDays = parseInt(String(totalText).split(' ')[0], 10);
    const workingDays = Number.isFinite(parsedDays) ? parsedDays : 0;

    const shouldShow = isSick && workingDays >= 3;

    proofSection.style.display = shouldShow ? 'block' : 'none';
    proofFile.required = shouldShow;
}

function handleLeaveTypeChange() {
    const select = document.getElementById('applyLeaveType');
    const option = select?.options?.[select.selectedIndex];
    const proofSec = document.getElementById('proofSection');
    const proofReq = document.getElementById('proofRequired');
    const proofHint = document.getElementById('proofHint');

    if (!option || !proofSec || !proofReq || !proofHint) return;

    const requiresProof = option.dataset.requiresProof === '1';
    const requiresApproval = option.dataset.requiresApproval === '0';
    const proofRules = option.dataset.proofRules || '';

    if (requiresProof) {
        proofSec.style.display = 'block';
        proofReq.textContent = '*';
        proofHint.textContent = proofRules || 'Proof document required for this leave type.';
    } else {
        proofSec.style.display = 'none';
        proofReq.textContent = '';
        proofHint.textContent = requiresApproval ? 'This leave type is auto-approved by configuration.' : '';
    }

    // Override for sick leave: only show proof section when 3+ working days
    applySickLeaveProofVisibilityRule();
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

function clampWeekend(inputEl) {
    if (!inputEl) return;

    // Only show the error when a weekend date is selected.
    if (inputEl.value && isWeekendISO(inputEl.value)) {
        showWeekendError(true);
        return;
    }

    const startVal = document.getElementById('applyStart')?.value || '';
    const endVal = document.getElementById('applyEnd')?.value || '';

    const startOk = !startVal || !isWeekendISO(startVal);
    const endOk = !endVal || !isWeekendISO(endVal);

    showWeekendError(!(startOk && endOk));
}

function calcDays() {
    const start = document.getElementById('applyStart')?.value || '';
    const end = document.getElementById('applyEnd')?.value || '';
    const totalEl = document.getElementById('applyTotalDays');

    if (!totalEl) return;

    if (!start || !end) {
        totalEl.value = '';
        return;
    }

    const s = new Date(start);
    const e = new Date(end);
    if (e < s) {
        totalEl.value = 'Invalid range';
        return;
    }

    let count = 0;
    for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
        const day = d.getDay();
        if (day !== 0 && day !== 6) count++;
    }

    totalEl.value = count + ' working day' + (count !== 1 ? 's' : '');

    // Sick leave proof depends on computed working days
    applySickLeaveProofVisibilityRule();
}

function dismissWeekendError() {
    showWeekendError(false);
}

function toggleEndDateBasedOnStart() {
    const startInput = document.getElementById('applyStart');
    const endInput = document.getElementById('applyEnd');
    if (!startInput || !endInput) return;

    const startVal = startInput.value || '';
    const startIsWeekend = !!startVal && isWeekendISO(startVal);

    endInput.disabled = startIsWeekend;
    // Keep UI consistent (optional)
    if (startIsWeekend) {
        endInput.classList.add('is-disabled');
    } else {
        endInput.classList.remove('is-disabled');
    }
}

function dismissConflictError() {
    const el = document.getElementById('applyConflictError');
    if (!el) return;
    el.style.display = 'none';
}

// Expose approved-only active ranges to JS for overlap detection (UI only).
// $activeLeaveRanges is built server-side from $leaveApplications.
window.ACTIVE_LEAVE_RANGES = @json(
    ($activeLeaveRanges ?? collect())->values()->map(fn ($r) => [
        'start' => $r['start'],
        'end' => $r['end'],
    ])->all()
);

function parseISODate(iso) {
    if (!iso) return null;
    const parts = String(iso).split('-');
    if (parts.length !== 3) return null;
    const year = Number(parts[0]);
    const monthIndex = Number(parts[1]) - 1;
    const day = Number(parts[2]);
    const d = new Date(year, monthIndex, day);
    return Number.isNaN(d.getTime()) ? null : d;
}

function dateRangeOverlaps(aStartIso, aEndIso, bStartIso, bEndIso) {
    const aStart = parseISODate(aStartIso);
    const aEnd = parseISODate(aEndIso);
    const bStart = parseISODate(bStartIso);
    const bEnd = parseISODate(bEndIso);
    if (!aStart || !aEnd || !bStart || !bEnd) return false;
    return aStart <= bEnd && aEnd >= bStart;
}

function validateApplyLeaveConflicts() {
    const weekendEl = document.getElementById('applyWeekendError');
    const conflictEl = document.getElementById('applyConflictError');

    const startVal = document.getElementById('applyStart')?.value || '';
    const endVal = document.getElementById('applyEnd')?.value || '';

    // If the user is still editing (one of the dates missing), clear the conflict message immediately.
    if (!startVal || !endVal) {
        if (conflictEl) conflictEl.style.display = 'none';
        return true;
    }

    // Weekend first (START_DATE weekend is not allowed for submission entry validation)
    if (isWeekendISO(startVal)) {
        showWeekendError(true);
        if (conflictEl) conflictEl.style.display = 'none';
        return false; // block submission until start_date is weekday
    }

    // Clear weekend error if START_DATE is not weekend
    showWeekendError(false);

    // Approved-only overlap check (approved leaves only)
    const conflictRanges = window.ACTIVE_LEAVE_RANGES || [];
    const hasConflict = conflictRanges.some(r =>
        dateRangeOverlaps(startVal, endVal, r.start, r.end)
    );

    if (hasConflict) {
        if (conflictEl) conflictEl.style.display = 'block';

        // Clear inputs immediately when conflict message appears
        const startInput = document.getElementById('applyStart');
        const endInput = document.getElementById('applyEnd');
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';

        return false;
    }

    if (conflictEl) conflictEl.style.display = 'none';
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    // Weekend guard + min date
    const startInput = document.getElementById('applyStart');
    const endInput = document.getElementById('applyEnd');

    const minISO = toISODate(new Date());
    if (startInput) startInput.min = minISO;
    if (endInput) endInput.min = minISO;

    if (startInput) clampWeekend(startInput);
    if (endInput) clampWeekend(endInput);

    // Conditional proof
    handleLeaveTypeChange();

    // Working days if old input exists
    calcDays();

    // If old leave_type_id exists but onchange didn't fire
    document.getElementById('applyLeaveType')?.addEventListener('change', function () {
        handleLeaveTypeChange();
    });
});
</script>
@endpush
