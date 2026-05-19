@extends('admin.layout')

@section('content')
<div class="page active" id="page-leavetypes">
    <div class="page-header">
        <div>
            <h1>Leave Configuration</h1>
            <p>Manage leave type allocations and rules</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" id="addLeaveTypeBtn">
                Add Leave Type
            </button>

            <div class="lt-kebab" style="position:relative">
                <button class="btn btn-outline btn-sm" type="button" id="ltPolicyMenuBtn" aria-label="Leave compensation policy menu">⋮</button>
                <div class="lt-kebab-menu" id="ltPolicyMenu" style="display:none;position:absolute;right:0;top:100%;min-width:280px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:8px;z-index:50">
                    <button type="button" class="btn btn-outline btn-sm" id="ltViewPolicyBtn" style="width:100%">
                        View Yearly Leave Compensation Policy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- moved from page body into a modal (opened via 3-dots menu) -->
    <div class="modal-overlay" id="leaveCompPolicyModal" style="display:none">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Yearly Leave Compensation Policy</h3>
                <button class="modal-close" type="button" id="leaveCompPolicyClose" aria-label="Close">✕</button>
            </div>

            <div class="modal-body">
                <div class="card" style="margin-bottom:18px">
                    <div class="card-header">
                        <span class="card-title">Yearly Leave Compensation Policy</span>
                    </div>
                    <div class="card-body" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px">
                        <div class="info-card" style="margin:0">
                            <div class="info-card-header">Configurable</div>
                            <div class="info-card-body" style="font-size:15px;line-height:1.6">
                                Only leave types marked as compensable are included in year-end conversion.
                            </div>
                        </div>
                        <div class="info-card" style="margin:0">
                            <div class="info-card-header">Non-compensable</div>
                            <div class="info-card-body" style="font-size:15px;line-height:1.6">
                                Non-compensable leave keeps its balance rules but is excluded from compensation reports.
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

                <div class="td-sub" style="margin-top:10px">
                    Tip: close with the ✕ button.
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline" type="button" id="leaveCompPolicyBack">Back</button>
            </div>
        </div>
    </div>

    <div class="lt-config-card">
        <div class="lt-config-filter">
            @foreach($leaveTypes as $index => $type)
                <button class="lt-tab {{ $index === 0 ? 'active' : '' }}" type="button" data-leave-tab="{{ $type->id }}">{{ $type->name }}</button>
            @endforeach
        </div>

        @foreach($leaveTypes as $index => $type)
            <div class="lt-detail-pane {{ $index === 0 ? 'active' : '' }}" id="lt-{{ $type->id }}" data-leave-active="{{ $type->is_active ? 1 : 0 }}">
                <div class="lt-detail-top">
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Annual Allocation</div>
                        <div class="lt-stat-val">{{ (int) $type->annual_allocation }}</div>
                        <div class="lt-stat-sub">days per year</div>
                    </div>
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Compensation</div>
                        <div class="lt-stat-val">{{ $type->is_compensable ? 'Yes' : 'No' }}</div>
                        <div class="lt-stat-sub">{{ $type->is_compensable ? 'Unused days paid yearly' : 'Not convertible' }}</div>
                    </div>
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Requires Approval</div>
                        <div class="lt-stat-val">{{ $type->requires_approval ? 'Yes' : 'No' }}</div>
                        <div class="lt-stat-sub">{{ $type->requires_approval ? 'Manager / HR approval' : 'Auto-approved' }}</div>
                    </div>
                    <div class="lt-stat-box">
                        <div class="lt-stat-label">Gender Restriction</div>
                        <div class="lt-stat-val">{{ $type->gender ? ucfirst($type->gender) : 'All' }}</div>
                        <div class="lt-stat-sub">{{ $type->gender ? 'Restricted to ' . ucfirst($type->gender) : 'Available to all' }}</div>
                    </div>
                </div>

                <div class="lt-flags">
                    <span class="lt-flag approval">{{ $type->requires_approval ? 'Approval Required' : 'No Approval' }}</span>
                    <span class="lt-flag {{ $type->is_compensable ? 'noapproval' : 'proof' }}">{{ $type->is_compensable ? 'Compensable' : 'Not Compensable' }}</span>
                    <span class="lt-flag proof">{{ $type->requires_proof ? 'Proof Required' : 'Proof Conditional' }}</span>
                    <span class="lt-flag {{ $type->is_active ? 'noapproval' : 'proof' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                    @if($type->gender)
                        <span class="lt-flag noapproval">{{ ucfirst($type->gender) }} Only</span>
                    @endif
                </div>

                <div class="lt-rules">
                    <div style="font-weight:700;color:var(--text);margin-bottom:8px">Rules & Notes</div>
                    <div style="line-height:1.8">{{ $type->proof_rules ?: 'No special proof rules configured.' }}</div>

                    <div style="margin-top:10px">
                        <div style="font-weight:700;color:var(--text);margin-bottom:6px">Document Upload Requirement</div>
                        <div style="line-height:1.8">
                            {{ $type->max_document_days !== null
                                ? 'Maximum days before submitting document: ' . (int) $type->max_document_days . ' day(s).'
                                : 'No max document days configured.' }}
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px;display:flex;justify-content:flex-end;gap:8px">
                    @php
                        $leaveTypeEditConfig = [
                            'id' => $type->id,
                            'name' => $type->name,
                            'annual_allocation' => $type->annual_allocation,
                            'gender' => $type->gender,
                            'is_active' => $type->is_active ? 1 : 0,
                            'requires_approval' => $type->requires_approval ? 1 : 0,
                            'is_compensable' => $type->is_compensable ? 1 : 0,
                            'requires_proof' => $type->requires_proof ? 1 : 0,
                            'proof_rules' => $type->proof_rules,
                            'max_document_days' => $type->max_document_days,
                            'action' => route('admin.leave-types.update', $type),
                            'delete_action' => route('admin.leave-types.destroy', $type),
                        ];
                    @endphp

                    <button
                        class="btn btn-outline btn-sm lt-edit-btn"
                        type="button"
                        data-config='@json($leaveTypeEditConfig)'
                        @if(!$type->is_active) disabled style="opacity:.55;cursor:not-allowed" @endif
                    >
                        Edit Configuration
                    </button>

                    @if(!$type->is_active)
                        <button
                            class="btn btn-primary btn-sm lt-enable-btn"
                            type="button"
                            data-config='@json($leaveTypeEditConfig)'
                            style="margin-left:6px"
                        >
                            Enable
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal-overlay" id="leaveTypeModal" style="display:none">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="leaveTypeModalTitle">Add Leave Type</h3>
            <button class="modal-close" type="button" data-close-modal>✕</button>
        </div>

        <form class="modal-body" method="POST" id="leaveTypeForm" action="{{ route('admin.leave-types.store') }}" data-store-url="{{ route('admin.leave-types.store') }}" autocomplete="off">
            @csrf
            <input type="hidden" id="leaveTypeCsrfToken" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" id="leaveTypeMethod" value="POST">

            <div class="form-grid">
                <div class="form-group span2">
                    <label class="form-label" for="leaveTypeName">Name <span class="required-mark">*</span></label>
                    <input class="form-control" type="text" name="name" id="leaveTypeName" autocomplete="off" placeholder="Bereavement Leave" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="leaveTypeAllocation">Annual Allocation <span class="required-mark">*</span></label>
                    <input class="form-control" type="number" name="annual_allocation" id="leaveTypeAllocation" autocomplete="off" min="1" max="365" value="15" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="leaveTypeGender">Gender</label>
                    <select class="form-control" name="gender" id="leaveTypeGender" autocomplete="off">
                        <option value="">All Genders</option>
                        <option value="male">Male Only</option>
                        <option value="female">Female Only</option>
                        <option value="other">Other</option>
                    </select>
                    <div class="form-hint">
                        Leave this empty to make available to all genders.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="leaveTypeStatus">Status</label>
                    <select class="form-control" name="is_active" id="leaveTypeStatus" autocomplete="off">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="leaveTypeApproval">Requires Approval</label>
                    <select class="form-control" name="requires_approval" id="leaveTypeApproval" autocomplete="off">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="leaveTypeCompensable">Compensable</label>
                    <select class="form-control" name="is_compensable" id="leaveTypeCompensable" autocomplete="off">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="leaveTypeProof">Requires Proof</label>
                    <select class="form-control" name="requires_proof" id="leaveTypeProof" autocomplete="off">
                        <option value="0">No / Conditional</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div class="form-group span2">
                    <label class="form-label" for="leaveTypeRules">Proof Rules</label>
                    <textarea class="form-control" name="proof_rules" id="leaveTypeRules" placeholder="Sick leave needs medical certificate for 3+ days" rows="3"></textarea>
                </div>

                <div class="form-group span2" id="maxDocumentDaysField" style="display:none;">
                    <label class="form-label" for="leaveTypeMaxDocumentDays">Max Document Days <span class="required-mark">*</span></label>
                    <input
                        class="form-control"
                        type="number"
                        name="max_document_days"
                        id="leaveTypeMaxDocumentDays"
                        autocomplete="off"
                        min="0"
                        max="365"
                        value="0"
                        placeholder="0 = no max / immediate"
                    >
                    <div class="form-hint">
                        Maximum days before a user must upload a document (optional).
                    </div>
                </div>
            </div>
        </form>

        <div class="modal-footer">
            <button class="btn btn-outline" type="button" data-close-modal>Cancel</button>
            <button class="btn btn-danger btn-sm" type="button" id="deleteButton" style="display:none">Delete</button>
            <button class="btn btn-primary" type="submit" form="leaveTypeForm" id="leaveTypeSubmit">Add Leave Type</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentLeaveTypeData = null;
let enableMode = false;

function showLeaveType(id, btn) {
    document.querySelectorAll('.lt-detail-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.lt-tab').forEach(t => t.classList.remove('active'));
    const pane = document.getElementById('lt-' + id);
    pane?.classList.add('active');
    btn?.classList.add('active');
}

function openLeaveCompPolicy() {
    const overlay = document.getElementById('leaveCompPolicyModal');
    overlay.style.display = 'flex';
}

function closeLeaveCompPolicy() {
    const overlay = document.getElementById('leaveCompPolicyModal');
    overlay.style.display = 'none';
}

function openLeaveTypeModal(data = null) {
    const form = document.getElementById('leaveTypeForm');
    const method = document.getElementById('leaveTypeMethod');
    const deleteButton = document.getElementById('deleteButton');

    document.getElementById('leaveTypeModal').style.display = 'flex';
    currentLeaveTypeData = data;
    enableMode = data?.enable_mode === true;

    if (!data || enableMode) {
        document.getElementById('leaveTypeModalTitle').textContent = enableMode ? 'Enable Leave Type' : 'Add Leave Type';
        document.getElementById('leaveTypeSubmit').textContent = enableMode ? 'Enable' : 'Add Leave Type';

            form.action = enableMode ? data.action : form.dataset.storeUrl;
            document.getElementById('leaveTypeAllocation').value = data.annual_allocation ?? 15;
            document.getElementById('leaveTypeGender').value = data.gender || '';
            document.getElementById('leaveTypeStatus').value = '1';
            document.getElementById('leaveTypeApproval').value = String(data.requires_approval ?? 1);
            document.getElementById('leaveTypeCompensable').value = String(data.is_compensable ?? 0);
            document.getElementById('leaveTypeProof').value = String(data.requires_proof ?? 0);
            document.getElementById('leaveTypeRules').value = data.proof_rules || '';
            document.getElementById('leaveTypeMaxDocumentDays').value = data.max_document_days ?? '';
        } else {
            document.getElementById('leaveTypeName').value = '';
            document.getElementById('leaveTypeAllocation').value = 15;
            document.getElementById('leaveTypeGender').value = '';
            document.getElementById('leaveTypeStatus').value = '1';
            document.getElementById('leaveTypeApproval').value = '1';
            document.getElementById('leaveTypeCompensable').value = '0';
            document.getElementById('leaveTypeProof').value = '0';
            document.getElementById('leaveTypeRules').value = '';
            document.getElementById('leaveTypeMaxDocumentDays').value = '';
        }
        deleteButton.style.display = 'none';
        toggleLeaveTypeDocumentDays();

        return;
    }

    document.getElementById('leaveTypeModalTitle').textContent = 'Edit Configuration';
    document.getElementById('leaveTypeSubmit').textContent = 'Save Changes';

    form.action = data.action;
    method.value = 'PUT';

    document.getElementById('leaveTypeName').value = data.name || '';
    document.getElementById('leaveTypeAllocation').value = data.annual_allocation ?? 15;
    document.getElementById('leaveTypeGender').value = data.gender || '';
    document.getElementById('leaveTypeStatus').value = String(data.is_active ?? 1);
    document.getElementById('leaveTypeApproval').value = String(data.requires_approval ?? 1);
    document.getElementById('leaveTypeCompensable').value = String(data.is_compensable ?? 0);
    document.getElementById('leaveTypeProof').value = String(data.requires_proof ?? 0);
    document.getElementById('leaveTypeRules').value = data.proof_rules || '';
    document.getElementById('leaveTypeMaxDocumentDays').value = data.max_document_days ?? '';
    toggleLeaveTypeDocumentDays();

    deleteButton.style.display = 'inline-block';
}

function toggleLeaveTypeDocumentDays() {
    const proofField = document.getElementById('leaveTypeProof');
    const documentDays = document.getElementById('maxDocumentDaysField');
    const documentInput = document.getElementById('leaveTypeMaxDocumentDays');

    if (!proofField || !documentDays || !documentInput) {
        return;
    }

    const show = proofField.value === '1';
    documentDays.style.display = show ? '' : 'none';

    if (!show) {
        documentInput.value = '';
    }
}

function closeLeaveTypeModal() {
    document.getElementById('leaveTypeModal').style.display = 'none';
    currentLeaveTypeData = null;
    enableMode = false;
}

function deleteLeaveType() {
    if (!currentLeaveTypeData?.delete_action) return;

    if (!confirm('Are you sure? This will permanently delete the leave type from the system.')) {
        return;
    }

    const csrfToken = document.getElementById('leaveTypeCsrfToken')?.value || '';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = currentLeaveTypeData.delete_action;

    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = csrfToken;
    form.appendChild(tokenInput);

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}

document.getElementById('addLeaveTypeBtn')?.addEventListener('click', () => openLeaveTypeModal());

document.querySelectorAll('.lt-edit-btn').forEach((button) => {
    button.addEventListener('click', () => openLeaveTypeModal(JSON.parse(button.dataset.config)));
});

document.querySelectorAll('.lt-enable-btn').forEach((button) => {
    button.addEventListener('click', () => {
        const cfg = JSON.parse(button.dataset.config);
        cfg.enable_mode = true;
        openLeaveTypeModal(cfg);
    });
});

document.querySelectorAll('[data-leave-tab]').forEach((tab) => {
    tab.addEventListener('click', () => showLeaveType(Number(tab.dataset.leaveTab), tab));
});

document.getElementById('leaveTypeProof')?.addEventListener('change', toggleLeaveTypeDocumentDays);

document.getElementById('deleteButton')?.addEventListener('click', deleteLeaveType);

document.getElementById('leaveTypeModal')?.addEventListener('click', (event) => {
    if (event.target.id === 'leaveTypeModal') closeLeaveTypeModal();
});

document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', closeLeaveTypeModal);
});

document.getElementById('ltPolicyMenuBtn')?.addEventListener('click', () => {
    const menu = document.getElementById('ltPolicyMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('ltViewPolicyBtn')?.addEventListener('click', () => {
    document.getElementById('ltPolicyMenu').style.display = 'none';
    openLeaveCompPolicy();
});

document.getElementById('leaveCompPolicyClose')?.addEventListener('click', closeLeaveCompPolicy);
document.getElementById('leaveCompPolicyBack')?.addEventListener('click', closeLeaveCompPolicy);

document.addEventListener('click', (e) => {
    if (!e.target.closest('#ltPolicyMenuBtn') && !e.target.closest('#ltPolicyMenu')) {
        const menu = document.getElementById('ltPolicyMenu');
        if (menu) menu.style.display = 'none';
    }
});
</script>
@endpush
