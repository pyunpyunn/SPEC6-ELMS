@extends('hr.layout')

@section('content')
<div class="page active" id="page-departments">
    <div class="page-header">
        <div>
            <h1>Departments</h1>
            <p>View department membership, managers, and positions</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" onclick="document.getElementById('deptModal').classList.add('open')">New Department</button>
        </div>
    </div>

    <div class="depts-grid">
        @foreach($departments as $department)
            @php
                $positions = $department->employees->pluck('position')->filter()->unique()->values();
                $onLeave = $department->employees->flatMap->leaveApplications->count();
                $managerName = $department->manager?->name ?? 'Unassigned';
                $managerPosition = $department->manager?->employee?->position ?? 'No manager assigned';
            @endphp
            <div class="dept-card">
                <div class="dept-card-top">
                    <div class="dept-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1M9 13h1M9 17h1"/></svg>
                    </div>
                    <a class="dept-leave-link" href="{{ route('hr.employees.index', ['department_id' => $department->id]) }}">View All →</a>
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
            </div>
        @endforeach
    </div>
</div>

<div class="modal-overlay" id="deptModal" onclick="closeDeptModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>New Department</h3>
            <button class="modal-close" type="button" onclick="closeDeptModal(event)">✕</button>
        </div>
        <form class="modal-body form" method="POST" action="{{ route('hr.departments.store') }}">
            @csrf
            @include('hr.departments.partials.form', ['department' => null, 'button' => 'Create Department'])
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeDeptModal(event)">Cancel</button>
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
</script>
@endpush
