@extends('admin.layout')

@section('content')
@php
    $visibleBalances = collect($leaveBalances ?? []);
    if ($selectedTypeId) {
        $visibleBalances = $visibleBalances->filter(fn ($balance) => $balance->leave_type_id === $selectedTypeId);
    }
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
        <div class="comp-sub">{{ $visibleBalances->sum(fn ($b) => (int) $b->remaining_days) }} unused days × ₱{{ number_format((float)($employee?->daily_rate ?? 0), 2) }}/day · paid at year-end</div>
    </div>

    <div class="flash flash-warning">
        As HR Admin, your leave requests are reviewed by another designated HR Admin or the CEO/Director.
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-header">
            <span class="card-title">My Leave Requests</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Reason</th><th>Status</th><th>Reviewed By</th><th>Action</th></tr></thead>
                <tbody>
                @forelse(($employee?->leaveApplications ?? collect())->sortByDesc('created_at') as $leave)
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
                                <button class="btn btn-danger btn-sm" type="button">Cancel</button>
                            @else
                                <button class="btn btn-outline btn-sm" type="button">View</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
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
                    <label>Leave Type <span class="req">*</span></label>
                    <select id="applyLeaveType" name="leave_type_id" onchange="handleLeaveTypeChange()" required>
                        <option value="">Select leave type...</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Start Date <span class="req">*</span></label><input type="date" id="applyStart" name="start_date" onchange="calcDays()" required></div>
                <div class="form-group"><label>End Date <span class="req">*</span></label><input type="date" id="applyEnd" name="end_date" onchange="calcDays()" required></div>
                <div class="form-group span2"><label>Total Working Days</label><input id="applyTotalDays" readonly placeholder="Auto calculated"></div>
                <div class="form-group span2"><label>Reason <span class="req">*</span></label><textarea id="applyReason" name="reason" placeholder="Describe your reason for leave..." required></textarea></div>
                <div class="form-group span2 leave-proof-section" id="proofSection">
                    <label>Supporting Document <span id="proofRequired" class="req"></span></label>
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

function handleLeaveTypeChange() {
    const select = document.getElementById('applyLeaveType');
    const type = select.options[select.selectedIndex]?.text?.toLowerCase() || '';
    const proofSec = document.getElementById('proofSection');
    const proofReq = document.getElementById('proofRequired');
    const proofHint = document.getElementById('proofHint');
    if (type.includes('sick')) {
        proofSec.classList.add('visible');
        proofReq.textContent = '(required if 3+ days)';
        proofHint.textContent = 'For 1–2 day sick leave, no certificate is needed. For 3+ days, medical certificate is required.';
    } else if (type.includes('maternity') || type.includes('bereavement')) {
        proofSec.classList.add('visible');
        proofReq.textContent = '*';
        proofHint.textContent = 'Proof document required for this leave type.';
    } else {
        proofSec.classList.remove('visible');
    }
}

function calcDays() {
    const start = document.getElementById('applyStart').value;
    const end = document.getElementById('applyEnd').value;
    if (!start || !end) return;
    const s = new Date(start);
    const e = new Date(end);
    if (e < s) {
        document.getElementById('applyTotalDays').value = 'Invalid range';
        return;
    }
    let count = 0;
    for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
        const day = d.getDay();
        if (day !== 0 && day !== 6) count++;
    }
    document.getElementById('applyTotalDays').value = count + ' working day' + (count !== 1 ? 's' : '');
}

</script>
@endpush
