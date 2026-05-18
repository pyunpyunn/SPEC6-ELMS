@extends('manager.layout')

@section('title', 'Apply for Leave')
@section('page_title', 'My Leave Application')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px">
    <div class="card">
        <div class="card-body">
        <h3>New Leave Request</h3>
        <div class="flash flash-warning" style="margin-top:16px">As a manager, your requests are reviewed by HR Admin.</div>
        <form method="POST" action="{{ route('manager.my-leave.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:14px">
                <label>Leave Type</label>
                <select class="form-control" name="leave_type_id" id="managerLeaveType" onchange="updateManagerProofHint()" required>
                    @foreach($leaveTypes as $type)
                        @php($balance = $employee?->leaveBalances->firstWhere('leave_type_id', $type->id))
                        <option value="{{ $type->id }}" data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}" data-requires-approval="{{ $type->requires_approval ? 1 : 0 }}" data-proof-rules="{{ $type->proof_rules }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }} ({{ (int) ($balance?->remaining_days ?? 0) }} left)</option>
                    @endforeach
                </select>
            </div>
            <div class="form-grid">
                <div><label>Start Date</label><input class="form-control" type="date" id="managerApplyStartDate" name="start_date" value="{{ old('start_date') }}" required></div>
                <div><label>End Date</label><input class="form-control" type="date" id="managerApplyEndDate" name="end_date" value="{{ old('end_date') }}" required></div>
                <div class="flash flash-error" id="managerWeekendError" style="display:none;grid-column:1/-1">You can't select a weekend date.</div>
                <div class="form-full"><label>Reason</label><textarea class="form-control" name="reason" rows="4" required>{{ old('reason') }}</textarea></div>
                <div class="form-full"><label>Attach Document</label><input class="form-control" type="file" name="proof"><div class="muted" id="managerProofHint">Upload proof when required by the selected leave type.</div></div>
            </div>
            <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:18px">Submit Request to HR</button>
        </form>
        </div>
    </div>
    <div class="card" style="padding:0">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border)"><h3>My Leave History</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($myLeaves as $leave)
                    <tr>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('M d') }} to {{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ (int) $leave->total_days }}</td>
                        <td><span class="status-pill status-{{ $leave->status }}">{{ $leave->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No personal leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($myLeaves, 'links'))<div class="pagination" style="padding:16px">{{ $myLeaves->links() }}</div>@endif
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

(function () {
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
        const monthIndex = Number(parts[1]) - 1; // 0-based
        const day = Number(parts[2]);

        const d = new Date(year, monthIndex, day);
        const dow = d.getDay(); // 0=Sun, 6=Sat
        return dow === 0 || dow === 6;
    }

    function showWeekendError(show) {
        const el = document.getElementById('managerWeekendError');
        if (!el) return;
        el.style.display = show ? 'block' : 'none';
    }

    function clampAndValidate(input) {
        const min = input.dataset.minDate || '';
        if (min && input.value && input.value < min) {
            input.value = min;
        }

        if (input.value && isWeekendISO(input.value)) {
            input.value = '';
            showWeekendError(true);
            return;
        }

        // Hide error only when both dates are valid/non-weekend.
        const startVal = document.getElementById('managerApplyStartDate')?.value;
        const endVal = document.getElementById('managerApplyEndDate')?.value;

        const startOk = !startVal || !isWeekendISO(startVal);
        const endOk = !endVal || !isWeekendISO(endVal);

        showWeekendError(!(startOk && endOk));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const startInput = document.getElementById('managerApplyStartDate');
        const endInput = document.getElementById('managerApplyEndDate');
        if (!startInput || !endInput) return;

        const today = new Date();
        const minISO = toISODate(today);

        startInput.min = minISO;
        endInput.min = minISO;

        startInput.dataset.minDate = minISO;
        endInput.dataset.minDate = minISO;

        startInput.addEventListener('change', function () {
            clampAndValidate(startInput);
        });

        endInput.addEventListener('change', function () {
            clampAndValidate(endInput);
        });

        // Validate old values (if any)
        clampAndValidate(startInput);
        clampAndValidate(endInput);
    });
})();
</script>
@endpush
