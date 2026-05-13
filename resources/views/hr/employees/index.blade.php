@extends('hr.layout')

@section('content')
<div class="page active" id="page-employees">
    <div class="page-header">
        <div>
            <h1>Employee Directory</h1>
            <p>Manage employee records, view profiles, and edit details</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm" type="button" onclick="openEmployeeModal('create')">Add Employee</button>
        </div>
    </div>

    <form class="filter-bar" method="GET">
        <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search employees...">
        </div>
        <select name="department_id">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <select name="employment_status">
            <option value="">All Status</option>
            <option value="active" @selected(request('employment_status') === 'active')>Active</option>
            <option value="resigned" @selected(request('employment_status') === 'resigned')>Resigned</option>
            <option value="terminated" @selected(request('employment_status') === 'terminated')>Terminated</option>
        </select>
        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
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
                        <a class="btn btn-outline btn-sm" href="{{ route('hr.employees.show', $employee) }}">View</a>
                        <button class="btn btn-primary btn-sm" type="button" onclick="openEmployeeModal('edit', @js([
                            'id' => $employee->id,
                            'first_name' => $employee->first_name,
                            'last_name' => $employee->last_name,
                            'email' => $employee->user?->email,
                            'role' => $employee->user?->role,
                            'employee_id' => $employee->employee_id,
                            'gender' => $employee->gender,
                            'department_id' => $employee->department_id,
                            'position' => $employee->position,
                            'manager_id' => $employee->manager_id,
                            'date_hired' => optional($employee->date_hired)->format('Y-m-d'),
                            'phone' => $employee->phone,
                            'address' => $employee->address,
                            'daily_rate' => $employee->daily_rate,
                            'employment_status' => $employee->employment_status,
                        ]))">Edit</button>
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

<div class="modal-overlay" id="employeeModal" onclick="closeEmployeeModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="employeeModalTitle">Add Employee</h3>
            <button class="modal-close" type="button" onclick="closeEmployeeModal(event)">✕</button>
        </div>
        <form class="modal-body form" id="employeeForm" method="POST" action="{{ route('hr.employees.store') }}">
            @csrf
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group"><label>First Name <span class="req">*</span></label><input name="first_name" id="employeeFirstName" required></div>
                <div class="form-group"><label>Last Name <span class="req">*</span></label><input name="last_name" id="employeeLastName" required></div>
                <div class="form-group"><label>Gender</label>
                    <select name="gender" id="employeeGender">
                        <option value="">Select gender</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="email" id="employeeEmail" required></div>
                <div class="form-group"><label>Role <span class="req">*</span></label>
                    <select name="role" id="employeeRole" required>
                        <option value="employee">Employee</option>
                        <option value="manager">Manager</option>
                        <option value="hr_admin">HR Admin</option>
                    </select>
                </div>
                <div class="form-group"><label>Employee ID <span class="req">*</span></label><input name="employee_id" id="employeeId" required style="font-family:var(--mono)"></div>
                <div class="form-group"><label>Department <span class="req">*</span></label>
                    <select name="department_id" id="employeeDepartment" required onchange="updatePositionOptions(this.value)">
                        <option value="">Select department...</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" data-name="{{ $department->name }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Position <span class="req">*</span></label>
                    <select name="position" id="employeePosition" required>
                        <option value="">Select position...</option>
                    </select>
                </div>
                <div class="form-group"><label>Manager</label>
                    <select name="manager_id" id="employeeManager">
                        <option value="">No manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Date Hired <span class="req">*</span></label><input type="date" name="date_hired" id="employeeDateHired" required></div>
                <div class="form-group"><label>Phone</label><input name="phone" id="employeePhone"></div>
                <div class="form-group"><label>Employment Status <span class="req">*</span></label>
                    <select name="employment_status" id="employeeStatus" required>
                        <option value="active">Active</option>
                        <option value="resigned">Resigned</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>
                <div class="form-group"><label>Daily Rate <span class="req">*</span></label><input type="number" step="0.01" name="daily_rate" id="employeeDailyRate" value="1000" required></div>
                <div class="form-group" style="grid-column:1/-1"><label>Address</label><input name="address" id="employeeAddress"></div>
                <input type="hidden" name="contact_info" id="employeeContactInfo">
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeEmployeeModal(event)">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="submitEmployee()">Save Employee</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const departmentPositions = @json($departmentPositions);
const departmentMap = @json($departments->pluck('name', 'id'));

function updatePositionOptions(departmentId) {
    const departmentName = document.querySelector(`#employeeDepartment option[value="${departmentId}"]`)?.dataset.name;
    const positionSelect = document.getElementById('employeePosition');
    const positions = departmentName && departmentPositions[departmentName] ? departmentPositions[departmentName] : [];
    
    positionSelect.innerHTML = '<option value="">Select position...</option>';
    positions.forEach(position => {
        const option = document.createElement('option');
        option.value = position;
        option.text = position;
        positionSelect.appendChild(option);
    });
}

function openEmployeeModal(mode, employee) {
    const form = document.getElementById('employeeForm');
    form.reset();
    form.action = mode === 'edit' ? '{{ url('/hr/employees') }}/' + employee.id : '{{ route('hr.employees.store') }}';
    const methodField = form.querySelector('input[name="_method"]') || document.createElement('input');
    if (mode === 'edit') {
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';
        if (!methodField.parentNode) form.appendChild(methodField);
    } else if (methodField.parentNode) {
        methodField.parentNode.removeChild(methodField);
    }

    document.getElementById('employeeModalTitle').textContent = mode === 'edit' ? 'Edit Employee' : 'Add Employee';
    document.getElementById('employeeFirstName').value = employee?.first_name || '';
    document.getElementById('employeeLastName').value = employee?.last_name || '';
    document.getElementById('employeeEmail').value = employee?.email || '';
    document.getElementById('employeeRole').value = employee?.role || 'employee';
    document.getElementById('employeeId').value = employee?.employee_id || '{{ $nextEmployeeId }}';
    document.getElementById('employeeGender').value = employee?.gender || '';
    document.getElementById('employeeDepartment').value = employee?.department_id || '{{ $departments->first()->id ?? '' }}';
    
    // Update positions based on selected department
    updatePositionOptions(employee?.department_id || '{{ $departments->first()->id ?? '' }}');
    document.getElementById('employeePosition').value = employee?.position || '';
    
    document.getElementById('employeeManager').value = employee?.manager_id || '';
    document.getElementById('employeeDateHired').value = employee?.date_hired || '{{ now()->toDateString() }}';
    document.getElementById('employeePhone').value = employee?.phone || '';
    document.getElementById('employeeStatus').value = employee?.employment_status || 'active';
    document.getElementById('employeeDailyRate').value = employee?.daily_rate || 1000;
    document.getElementById('employeeAddress').value = employee?.address || '';
    document.getElementById('employeeContactInfo').value = employee?.email || '';
    document.getElementById('employeeModal').classList.add('open');
}

function closeEmployeeModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('employeeModal').classList.remove('open');
}

function submitEmployee() {
    document.getElementById('employeeContactInfo').value = document.getElementById('employeeEmail').value;
    document.getElementById('employeeForm').submit();
}
</script>
@endpush
