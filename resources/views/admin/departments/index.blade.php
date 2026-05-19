@extends('admin.layout')

@section('content')
<div class="page active" id="page-departments">
    <div class="page-header">
        <div>
            <h1>Departments</h1>
            <p>Click a department to open the employee directory filtered to that team</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" id="newDeptBtn">New Department</button>
        </div>
    </div>

    <div class="depts-grid">
        @foreach($departments as $department)
            @php
                $positions = $department->employees->pluck('position')->filter()->unique()->values();
                $onLeave = $department->employees->flatMap->leaveApplications->count();
                $managerName = $department->manager?->full_name ?? 'Unassigned';
                $managerPosition = $department->manager?->position ?? 'No manager assigned';
            @endphp
            <a
                href="{{ route('admin.employees.index', ['department_id' => $department->id]) }}"
                class="dept-card"
                aria-label="View {{ $department->name }} employees"
            >
                <div class="dept-card-top">
                    <div class="dept-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1M9 13h1M9 17h1"/></svg>
                    </div>
                    <span class="dept-leave-link dept-card-action">View employees →</span>
                </div>
                <div class="dept-name">{{ $department->name }}</div>
                <div class="dept-mgr">Manager: {{ $managerName }} · {{ $managerPosition }}</div>
                <div class="dept-meta">
                    <div class="dept-meta-item"><strong>{{ $department->employees_count }}</strong> employees</div>
                    <div class="dept-meta-item"><strong>{{ $onLeave }}</strong> on leave</div>
                </div>
                <div class="dept-positions">
                    @forelse($positions->take(4) as $position)
                        <span class="dept-pos-badge">{{ $position }}</span>
                    @empty
                        <span class="dept-pos-badge">No positions yet</span>
                    @endforelse
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="modal-overlay" id="deptModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title">New Department</h3>
            <button class="modal-close" type="button" id="closeDeptBtn">✕</button>
        </div>
        <form class="modal-body" method="POST" id="deptForm" action="{{ route('admin.departments.store') }}">
            @csrf
            @include('admin.departments.partials.form', ['department' => null, 'button' => 'Create Department'])
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" id="cancelDeptBtn">Cancel</button>
            <button class="btn btn-primary" type="button" id="submitDeptBtn">Create Department</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closeDeptModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('deptModal').classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function() {
    const deptModal = document.getElementById('deptModal');
    const newDeptBtn = document.getElementById('newDeptBtn');
    const closeDeptBtn = document.getElementById('closeDeptBtn');
    const cancelDeptBtn = document.getElementById('cancelDeptBtn');
    const submitDeptBtn = document.getElementById('submitDeptBtn');
    const deptForm = document.getElementById('deptForm');
    
    newDeptBtn?.addEventListener('click', function() {
        deptModal.classList.add('open');
    });
    
    closeDeptBtn?.addEventListener('click', function(event) {
        closeDeptModal(event);
    });
    
    cancelDeptBtn?.addEventListener('click', function(event) {
        closeDeptModal(event);
    });
    
    submitDeptBtn?.addEventListener('click', function() {
        deptForm.submit();
    });
    
    deptModal?.addEventListener('click', function(event) {
        if (event.target === deptModal) {
            closeDeptModal(event);
        }
    });
});
</script>
@endpush
