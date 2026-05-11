@extends('hr.layout')

@section('content')
<div class="page active" id="page-employees">
    <div class="page-header">
        <div><h1>Employee Directory</h1><p>Manage all employee records — HR can edit any employee profile</p></div>
        <div class="page-actions">
            <details>
                <summary class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Employee
                </summary>
                <div class="card" style="position:absolute;right:26px;z-index:20;width:min(760px,calc(100vw - 60px));margin-top:10px;box-shadow:var(--shadow-md)">
                    <div class="card-header"><span class="card-title">Add Employee</span></div>
                    <div class="card-body"><form class="form" method="POST" action="{{ route('hr.employees.store') }}">@csrf @include('hr.employees.partials.form', ['employee' => null, 'button' => 'Add Employee'])</form></div>
                </div>
            </details>
        </div>
    </div>
    <form class="filter-bar" method="GET">
        <div class="search-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input name="search" value="{{ request('search') }}" placeholder="Search employees..."></div>
        <select name="department_id"><option value="">All Departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</select>
        <select name="position"><option value="">All Positions</option>@foreach($positions as $position)<option @selected(request('position') === $position)>{{ $position }}</option>@endforeach</select>
        <select name="employment_status"><option value="">All Status</option><option value="active" @selected(request('employment_status')==='active')>Active</option><option value="resigned" @selected(request('employment_status')==='resigned')>Resigned</option><option value="terminated" @selected(request('employment_status')==='terminated')>Terminated</option></select>
        <button class="btn btn-outline btn-sm">Filter</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Role</th><th>Department · Position</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td><span style="font-family:var(--mono);font-size:11px">{{ $employee->employee_id }}</span></td>
                    <td><div class="td-name">{{ $employee->full_name }}</div><div class="td-sub">{{ $employee->phone }}</div></td>
                    <td>{{ $employee->user->email }}</td>
                    <td><span class="badge badge-{{ $employee->user->role === 'hr_admin' ? 'hr' : ($employee->user->role === 'manager' ? 'manager' : 'emp') }}">{{ ucfirst(str_replace('_',' ', $employee->user->role)) }}</span></td>
                    <td><div>{{ $employee->departmentRecord?->name }}</div><div class="td-pos">{{ $employee->position }}</div></td>
                    <td><span class="badge badge-{{ $employee->employment_status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($employee->employment_status) }}</span></td>
                    <td class="actions">
                        <details><summary class="btn btn-sm btn-outline">Edit</summary><form class="form" method="POST" action="{{ route('hr.employees.update', $employee) }}" style="margin-top:10px;min-width:560px">@csrf @method('PUT') @include('hr.employees.partials.form', ['employee' => $employee, 'button' => 'Save Changes'])</form></details>
                        @if($employee->employment_status === 'active')<form method="POST" action="{{ route('hr.employees.deactivate', $employee) }}">@csrf @method('PATCH')<input type="hidden" name="employment_status" value="resigned"><button class="btn btn-danger btn-sm">Deactivate</button></form>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No employees found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $employees->links() }}</div>
</div>
@endsection
