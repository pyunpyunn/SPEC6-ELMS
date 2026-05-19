@extends('hr.layout')

@section('content')
<div class="page active" id="page-employees">
    <div class="page-header">
        <div>
            <h1>Employee Directory</h1>
            @if($selectedDepartment ?? null)
                <p>Showing employees in <strong>{{ $selectedDepartment->name }}</strong></p>
            @else
                <p>Manage employee records, view profiles, and edit details</p>
            @endif
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" data-action="employee-create">Add Employee</button>
        </div>
    </div>

    @if($selectedDepartment ?? null)
        <div class="flash flash-warning" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <span>Filtered by department: <strong>{{ $selectedDepartment->name }}</strong> ({{ $employees->total() }} {{ Str::plural('employee', $employees->total()) }})</span>
            <a class="btn btn-outline btn-sm" href="{{ route('admin.employees.index', request()->except('department_id')) }}">Clear department filter</a>
        </div>
    @endif

    <form class="filter-bar" method="GET">
        <div class="filter-bar-row">
            <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="hrEmployeeSearch" type="search" name="search" value="{{ request('search') }}" placeholder="Search employees by name, ID, or position..." aria-label="Search employees by name, ID, or position">
            </div>
        </div>
        <div class="filter-bar-row">
            <select name="department_id">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((int) ($selectedDepartmentId ?? request('department_id')) === $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            <select name="employment_status">
                <option value="">All Status</option>
                <option value="active" @selected(request('employment_status') === 'active')>Active</option>
                <option value="resigned" @selected(request('employment_status') === 'resigned')>Resigned</option>
                <option value="terminated" @selected(request('employment_status') === 'terminated')>Terminated</option>
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Date Hired</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>
                        <div class="td-name">{{ $employee->full_name }}</div>
                        <div class="td-sub">{{ $employee->user?->email }}</div>
                    </td>
                    <td><span style="font-family:var(--mono);font-size:15px">{{ $employee->employee_id }}</span></td>
                    <td>{{ $employee->departmentRecord?->name }}</td>
                    <td class="td-pos">{{ $employee->position }}</td>
                    <td>{{ $employee->date_hired?->format('M d, Y') }}</td>
                    <td><span class="badge badge-{{ $employee->employment_status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($employee->employment_status) }}</span></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-sm" href="{{ route('admin.employees.show', $employee) }}">View</a>
                        <button class="btn btn-primary btn-sm" type="button" data-action="employee-edit" data-employee='@json([
                            'id' => $employee->id,
                            'first_name' => $employee->first_name,
                            'last_name' => $employee->last_name,
                            'email' => $employee->user?->email,
                            'employee_id' => $employee->employee_id,
                            'gender' => $employee->gender,
                            'department_id' => $employee->department_id,
                            'position_id' => $employee->position_id,
                            'position' => $employee->position,
                            'manager_id' => $employee->manager_id,
                            'date_hired' => optional($employee->date_hired)->format('Y-m-d'),
                            'phone' => $employee->phone,
                            'address' => $employee->address,
                            'daily_rate' => $employee->daily_rate,
                            'employment_status' => $employee->employment_status,
                        ])'>Edit</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No employees found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $employees->links('vendor.pagination.hr', ['anchor' => 'page-employees']) }}</div>
</div>

<div class="modal-overlay" id="employeeModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="employeeModalTitle">Add Employee</h3>
            <button class="modal-close" type="button" data-action="employee-close">✕</button>
        </div>
        <form class="modal-body form" id="employeeForm" method="POST" action="{{ route('admin.employees.store') }}" data-positions-by-department-url="{{ url('/positions-by-department') }}" data-employee-base-url="{{ url('/admin/employees') }}" data-employee-store-url="{{ route('admin.employees.store') }}">
            @csrf
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group"><label for="employeeFirstName">First Name <span class="req">*</span></label><input name="first_name" id="employeeFirstName" autocomplete="given-name" required></div>
                <div class="form-group"><label for="employeeLastName">Last Name <span class="req">*</span></label><input name="last_name" id="employeeLastName" autocomplete="family-name" required></div>
                <div class="form-group"><label for="employeeGender">Gender</label>
                    <select name="gender" id="employeeGender" autocomplete="sex">
                        <option value="">Select gender</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group"><label for="employeeEmail">Email <span class="req">*</span></label><input type="email" name="email" id="employeeEmail" autocomplete="email" required></div>
                <div class="form-group"><label for="employeeAccessRole">Access Role</label>
                    <input id="employeeAccessRole" value="Auto-derived from department and position" readonly disabled style="background-color:#f0f0f0;cursor:not-allowed">
                </div>
                <div class="form-group">
                    <label for="employeeId">Employee ID</label>
                    <input type="text" id="employeeId" readonly disabled style="background-color:#f0f0f0;cursor:not-allowed;font-family:var(--mono)">
                    <small id="employeeIdHint" style="display:block;margin-top:4px;color:#666"></small>
                </div>
                <div class="form-group"><label for="employeeDepartment">Department <span class="req">*</span></label>
                    <select name="department_id" id="employeeDepartment" required>
                        <option value="">Select department...</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label for="employeePosition">Position <span class="req">*</span></label>
                    <select name="position_id" id="employeePosition" required>
                        <option value="">Select department first...</option>
                    </select>
                </div>
                <div class="form-group"><label for="employeeManager">Manager</label>
                    <select name="manager_id" id="employeeManager">
                        <option value="">No manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label for="employeeDateHired">Date Hired <span class="req">*</span></label><input type="date" name="date_hired" id="employeeDateHired" required></div>
                <div class="form-group"><label for="employeePhone">Phone</label><input name="phone" id="employeePhone"></div>
                <div class="form-group"><label for="employeeStatus">Employment Status <span class="req">*</span></label>
                    <select name="employment_status" id="employeeStatus" required>
                        <option value="active">Active</option>
                        <option value="resigned">Resigned</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>
                <div class="form-group"><label for="employeeDailyRate">Daily Rate <span class="req">*</span></label><input type="number" step="0.01" name="daily_rate" id="employeeDailyRate" value="1000" required></div>
                <div class="form-group" style="grid-column:1/-1"><label for="employeeAddress">Address</label><input name="address" id="employeeAddress"></div>
                <input type="hidden" name="contact_info" id="employeeContactInfo">
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" data-action="employee-cancel">Cancel</button>
            <button class="btn btn-primary" type="button" data-action="employee-submit">Save Employee</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/employee-directory.js') }}" defer></script>
@endpush


