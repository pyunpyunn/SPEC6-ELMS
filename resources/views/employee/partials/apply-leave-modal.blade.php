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
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required>
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
</script>
