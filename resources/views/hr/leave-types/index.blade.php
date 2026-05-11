@extends('hr.layout')

@section('content')
<div class="page active" id="page-leavetypes">
    <div class="page-header">
        <div><h1>Leave Configuration</h1><p>Configure leave types, allocations, approval, and proof requirements</p></div>
        <div class="page-actions"><details><summary class="btn btn-primary">Add Leave Type</summary><div class="card" style="position:absolute;right:26px;z-index:20;width:min(560px,calc(100vw - 60px));margin-top:10px;box-shadow:var(--shadow-md)"><div class="card-header"><span class="card-title">Add Leave Type</span></div><div class="card-body"><form class="form" method="POST" action="{{ route('hr.leave-types.store') }}">@csrf @include('hr.leave-types.partials.form', ['type' => null, 'button' => 'Add Leave Type'])</form></div></div></details></div>
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
                    <div class="lt-stat-box"><div class="lt-stat-label">Annual Allocation</div><div class="lt-stat-val">{{ (int) $type->annual_allocation }}</div><div class="lt-stat-sub">days per year</div></div>
                    <div class="lt-stat-box"><div class="lt-stat-label">Requires Approval</div><div class="lt-stat-val">{{ $type->requires_approval ? 'Yes' : 'No' }}</div></div>
                    <div class="lt-stat-box"><div class="lt-stat-label">Requires Proof</div><div class="lt-stat-val">{{ $type->requires_proof ? 'Yes' : 'Conditional' }}</div></div>
                </div>
                <div class="lt-flags">
                    <span class="lt-flag approval">{{ $type->requires_approval ? 'Approval Required' : 'No Approval' }}</span>
                    <span class="lt-flag proof">{{ $type->requires_proof ? 'Proof Required' : 'Proof Conditional' }}</span>
                    <span class="lt-flag {{ $type->is_active ? 'noapproval' : 'proof' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="lt-rules">{{ $type->proof_rules ?: 'No special proof rules configured.' }}</div>
                <details style="margin-top:16px">
                    <summary class="btn btn-outline btn-sm">Edit {{ $type->name }}</summary>
                    <form class="form" method="POST" action="{{ route('hr.leave-types.update', $type) }}" style="margin-top:14px">@csrf @method('PUT') @include('hr.leave-types.partials.form', ['type' => $type, 'button' => 'Save Changes'])</form>
                </details>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
function showLeaveType(id, btn){
    document.querySelectorAll('.lt-detail-pane').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.lt-tab').forEach(t=>t.classList.remove('active'));
    document.getElementById('lt-'+id).classList.add('active');
    btn.classList.add('active');
}
</script>
@endpush
