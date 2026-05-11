@extends('hr.layout')

@section('content')
<div class="page active" id="page-departments">
    <div class="page-header">
        <div><h1>Department Management</h1><p>Create departments, assign managers, and view department employees</p></div>
        <div class="page-actions">
            <details><summary class="btn btn-primary">Add Department</summary><div class="card" style="position:absolute;right:26px;z-index:20;width:min(640px,calc(100vw - 60px));margin-top:10px;box-shadow:var(--shadow-md)"><div class="card-header"><span class="card-title">Add Department</span></div><div class="card-body"><form class="form" method="POST" action="{{ route('hr.departments.store') }}">@csrf @include('hr.departments.partials.form', ['department' => null, 'button' => 'Create Department'])</form></div></div></details>
        </div>
    </div>
    <div class="depts-grid">
        @foreach($departments as $department)
            <div class="dept-card">
                <div class="dept-card-top">
                    <div><div class="dept-name">{{ $department->name }}</div><div class="dept-code">{{ $department->code }}</div></div>
                    <span class="badge badge-{{ $department->is_active ? 'active' : 'inactive' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div style="font-size:12px;color:var(--text3);min-height:38px">{{ $department->description }}</div>
                <div class="dept-meta">
                    <div class="dept-meta-item"><strong>{{ $department->employees_count }}</strong> employees</div>
                    <div class="dept-meta-item"><strong>{{ $department->manager?->name ?? 'Unassigned' }}</strong> manager</div>
                </div>
                <div class="dept-positions">
                    @foreach($department->employees->pluck('position')->unique()->take(6) as $position)
                        <span class="dept-pos-badge">{{ $position }}</span>
                    @endforeach
                </div>
                <details style="margin-top:14px">
                    <summary class="btn btn-outline btn-sm">View Employees</summary>
                    <div class="table-wrap" style="margin-top:10px">
                        <table><thead><tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Position</th><th>Status</th></tr></thead><tbody>
                            @foreach($department->employees as $employee)
                                <tr><td>{{ $employee->employee_id }}</td><td>{{ $employee->full_name }}</td><td>{{ $employee->user?->email }}</td><td>{{ $employee->position }}</td><td>{{ ucfirst($employee->employment_status) }}</td></tr>
                            @endforeach
                        </tbody></table>
                    </div>
                </details>
            </div>
        @endforeach
    </div>
</div>
@endsection
