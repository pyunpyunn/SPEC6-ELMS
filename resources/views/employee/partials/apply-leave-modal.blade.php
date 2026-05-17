<div class="modal-overlay" id="applyLeaveModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Apply for Leave</h3>
            <button class="modal-close" type="button" onclick="closeModal('applyLeaveModal')">x</button>
        </div>
        <form method="POST" action="{{ route('employee.leaves.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form">
                    <div class="form-group span2">
                        <label>Leave Type</label>
                        <select name="leave_type_id" id="employeeApplyLeaveType" onchange="updateEmployeeProofHint()" required>
                            <option value="">Select leave type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}" data-requires-approval="{{ $type->requires_approval ? 1 : 0 }}" data-proof-rules="{{ $type->proof_rules }}" @selected(old('leave_type_id') == $type->id)>
                                    {{ $type->name }} · {{ $type->remaining_days }} remaining
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="employeeApplyStartDate" name="start_date" value="{{ old('start_date') }}" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" id="employeeApplyEndDate" name="end_date" value="{{ old('end_date') }}" required>
                    </div>

                    <div class="flash flash-error" id="employeeWeekendError" style="display:none;margin-top:10px">
                        You can't select a weekend date.
                    </div>
                    <div class="form-group span2">
                        <label>Reason</label>
                        <textarea name="reason" placeholder="Briefly describe the reason for your leave" required>{{ old('reason') }}</textarea>
                    </div>
                    <div class="form-group span2">
                        <label>File Upload</label>
                        <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="td-sub" id="employeeProofHint">Upload proof when required by the selected leave type.</div>
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
<script>
function updateEmployeeProofHint() {
    const select = document.getElementById('employeeApplyLeaveType');
    const hint = document.getElementById('employeeProofHint');
    const option = select?.selectedOptions?.[0];
    if (!hint || !option) return;

    const proofRequired = option.dataset.requiresProof === '1';
    const approvalRequired = option.dataset.requiresApproval !== '0';
    const rules = option.dataset.proofRules || '';
    hint.textContent = [
        proofRequired ? 'Proof required.' : 'Proof optional.',
        approvalRequired ? 'Requires approval.' : 'Auto-approved.',
        rules,
    ].filter(Boolean).join(' ');
}
document.addEventListener('DOMContentLoaded', updateEmployeeProofHint);

// Disable weekends + past dates on date inputs.
(function () {
    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function toISODate(d) {
        // d is local Date
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    function isWeekendISO(iso) {
        if (!iso) return false;
        // Parse yyyy-mm-dd as local date to avoid timezone shifts.
        const parts = iso.split('-');
        if (parts.length !== 3) return false;
        const year = Number(parts[0]);
        const monthIndex = Number(parts[1]) - 1; // 0-based
        const day = Number(parts[2]);
        const d = new Date(year, monthIndex, day);
        const dow = d.getDay(); // 0=Sun, 6=Sat
        return dow === 0 || dow === 6;
    }

    function showWeekendError(show) {
        const el = document.getElementById('employeeWeekendError');
        if (!el) return;
        el.style.display = show ? 'block' : 'none';
    }

    function clampAndValidate(input) {
        const min = input.dataset.minDate || '';
        if (min && input.value && input.value < min) {
            input.value = min;
        }

        // If chosen value is weekend, clear it and show error.
        if (input.value && isWeekendISO(input.value)) {
            input.value = '';
            showWeekendError(true);
        } else {
            // Hide error if both fields are not weekends (or are empty).
            showWeekendError(false);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const startInput = document.getElementById('employeeApplyStartDate');
        const endInput = document.getElementById('employeeApplyEndDate');
        if (!startInput || !endInput) return;

        const today = new Date();
        const minISO = toISODate(today);

        startInput.min = minISO;
        endInput.min = minISO;

        startInput.dataset.minDate = minISO;
        endInput.dataset.minDate = minISO;

        // Validate on change
        startInput.addEventListener('change', function () {
            clampAndValidate(startInput);
        });
        endInput.addEventListener('change', function () {
            clampAndValidate(endInput);
        });

        // Ensure weekend/past are cleared immediately if old values exist
        clampAndValidate(startInput);
        clampAndValidate(endInput);
    });
})();
</script>
