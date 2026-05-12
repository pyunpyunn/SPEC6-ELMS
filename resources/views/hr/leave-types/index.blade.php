@extends('hr.layout')

@section('content')
<div class="page active" id="page-leavetypes">
    <div class="page-header">
        <div>
            <h1>Leave Configuration</h1>
            <p>Manage leave type allocations and rules</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" onclick="openLeaveTypeModal()">
                Add Leave Type
            </button>
        </div>
    </div>

    <div class="card" style="margin-bottom:18px">
        <div class="card-header">
            <span class="card-title">Yearly Leave Compensation Policy</span>
            <span class="badge badge-info">Prototype aligned</span>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px">
            <div class="info-card" style="margin:0">
                <div class="info-card-header">Compensable</div>
                <div class="info-card-body" style="font-size:15px;line-height:1.6">
                    Vacation Leave, Sick Leave, Emergency Leave, and Bereavement Leave may be converted at year-end.
                </div>
            </div>
            <div class="info-card" style="margin:0">
                <div class="info-card-header">Non-convertible</div>
                <div class="info-card-body" style="font-size:15px;line-height:1.6">
                    Maternity Leave and Paternity Leave remain non-convertible and follow separate rules.
                </div>
            </div>
            <div class="info-card" style="margin:0">
                <div class="info-card-header">Formula</div>
                <div class="info-card-body" style="font-size:15px;line-height:1.6">
                    Unused Days × Employee's Daily Rate = Yearly Compensation Amount
                </div>
            </div>
        </div>
    </div>

    <div class="lt-config-card">
        <div class="lt-config-filter">
            @foreach($leaveTypes as $index => $type)
                <button class="lt-tab {{ $index === 0 ? 'active' : '' }}" type="button" onclick="showLeaveType({{ $type->id }}, this)">{{ $type->name }}</button>
            @endforeach
        </div>

        @foreach($leaveTypes as $index => $type)
            <div class="lt-detail-pane {{ $index === 0 ? 'active' : '' }}" id="lt-{{ $type->id }}">
                <div class="lt-detail-top">
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Annual Allocation</div>
                        <div class="lt-stat-val">{{ (int) $type->annual_allocation }}</div>
                        <div class="lt-stat-sub">days per year</div>
                    </div>
                    @php($compensable = ! str($type->name)->lower()->contains(['maternity', 'paternity']))
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Compensation</div>
                        <div class="lt-stat-val">{{ $compensable ? 'Yes' : 'No' }}</div>
                        <div class="lt-stat-sub">{{ $compensable ? 'Unused days paid yearly' : 'Not convertible' }}</div>
                    </div>
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Requires Approval</div>
                        <div class="lt-stat-val">{{ $type->requires_approval ? 'Yes' : 'No' }}</div>
                        <div class="lt-stat-sub">{{ $type->requires_approval ? 'Manager / HR approval' : 'Auto-approved' }}</div>
                    </div>
                </div>

                <div class="lt-flags">
                    <span class="lt-flag approval">{{ $type->requires_approval ? 'Approval Required' : 'No Approval' }}</span>
                    <span class="lt-flag proof">{{ $type->requires_proof ? 'Proof Required' : 'Proof Conditional' }}</span>
                    <span class="lt-flag {{ $type->is_active ? 'noapproval' : 'proof' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                </div>

                <div class="lt-rules">
                    <div style="font-weight:700;color:var(--text);margin-bottom:8px">Rules & Notes</div>
                    <div style="line-height:1.8">{{ $type->proof_rules ?: 'No special proof rules configured.' }}</div>
                </div>

                <div style="margin-top:16px;display:flex;justify-content:flex-end">
                    <button class="btn btn-outline btn-sm" type="button" onclick='openLeaveTypeModal(@js([
                        "id" => $type->id,
                        "name" => $type->name,
                        "annual_allocation" => $type->annual_allocation,
                        "is_active" => $type->is_active ? 1 : 0,
                        "requires_approval" => $type->requires_approval ? 1 : 0,
                        "requires_proof" => $type->requires_proof ? 1 : 0,
                        "proof_rules" => $type->proof_rules,
                        "action" => route("hr.leave-types.update", $type),
                    ]))'>
                        Edit Configuration
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal-overlay" id="leaveTypeModal" onclick="closeLeaveTypeModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="leaveTypeModalTitle">Add Leave Type</h3>
            <button class="modal-close" type="button" onclick="closeLeaveTypeModal(event)">✕</button>
        </div>
        <form class="modal-body form" method="POST" id="leaveTypeForm" action="{{ route('hr.leave-types.store') }}">
            @csrf
            <input type="hidden" name="_method" id="leaveTypeMethod" value="POST">
            <div class="full">
                <label>Name</label>
                <input name="name" id="leaveTypeName" placeholder="Bereavement Leave" required>
            </div>
            <div>
                <label>Annual Allocation</label>
                <input type="number" name="annual_allocation" id="leaveTypeAllocation" value="15" required>
            </div>
            <div>
                <label>Status</label>
                <select name="is_active" id="leaveTypeStatus">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div>
                <label>Requires Approval</label>
                <select name="requires_approval" id="leaveTypeApproval">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div>
                <label>Requires Proof</label>
                <select name="requires_proof" id="leaveTypeProof">
                    <option value="0">No / Conditional</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="full">
                <label>Proof Rules</label>
                <input name="proof_rules" id="leaveTypeRules" placeholder="Sick leave needs medical certificate for 3+ days">
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeLeaveTypeModal(event)">Cancel</button>
            <button class="btn btn-primary" type="submit" form="leaveTypeForm" id="leaveTypeSubmit">Add Leave Type</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showLeaveType(id, btn){
    document.querySelectorAll('.lt-detail-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.lt-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('lt-' + id).classList.add('active');
    btn.classList.add('active');
}

function openLeaveTypeModal(data = null) {
    const form = document.getElementById('leaveTypeForm');
    const method = document.getElementById('leaveTypeMethod');
    document.getElementById('leaveTypeModal').classList.add('open');

    if (!data) {
        document.getElementById('leaveTypeModalTitle').textContent = 'Add Leave Type';
        document.getElementById('leaveTypeSubmit').textContent = 'Add Leave Type';
        form.action = '{{ route('hr.leave-types.store') }}';
        method.value = 'POST';
        document.getElementById('leaveTypeName').value = '';
        document.getElementById('leaveTypeAllocation').value = 15;
        document.getElementById('leaveTypeStatus').value = '1';
        document.getElementById('leaveTypeApproval').value = '1';
        document.getElementById('leaveTypeProof').value = '0';
        document.getElementById('leaveTypeRules').value = '';
        return;
    }

    document.getElementById('leaveTypeModalTitle').textContent = 'Edit Configuration';
    document.getElementById('leaveTypeSubmit').textContent = 'Save Changes';
    form.action = data.action;
    method.value = 'PUT';
    document.getElementById('leaveTypeName').value = data.name || '';
    document.getElementById('leaveTypeAllocation').value = data.annual_allocation || 15;
    document.getElementById('leaveTypeStatus').value = String(data.is_active ?? 1);
    document.getElementById('leaveTypeApproval').value = String(data.requires_approval ?? 1);
    document.getElementById('leaveTypeProof').value = String(data.requires_proof ?? 0);
    document.getElementById('leaveTypeRules').value = data.proof_rules || '';
}

function closeLeaveTypeModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('leaveTypeModal').classList.remove('open');
}
</script>
@endpush
