<div><label>First Name</label><input name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required></div>
<div><label>Last Name</label><input name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required></div>
<div><label>Email</label><input type="email" name="email" value="{{ old('email', $employee?->user?->email) }}" required></div>
<div><label>Role</label><select name="role" required><option value="employee" @selected(old('role', $employee?->user?->role) === 'employee')>Employee</option><option value="manager" @selected(old('role', $employee?->user?->role) === 'manager')>Manager</option><option value="hr_admin" @selected(old('role', $employee?->user?->role) === 'hr_admin')>HR Admin</option></select></div>
<div><label>Employee ID</label><input name="employee_id" value="{{ old('employee_id', $employee?->employee_id ?? $nextEmployeeId) }}" required></div>
<div><label>Department</label><select name="department_id" id="department_id" required><option value="">-- Select Department --</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $employee?->department_id) == $d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div><label>Position</label><select name="position_id" id="position_id" required><option value="">-- Select Department First --</option></select></div>
<div><label>Manager</label><select name="manager_id"><option value="">No manager</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected(old('manager_id', $employee?->manager_id) == $m->id)>{{ $m->full_name }}</option>@endforeach</select></div>
<div><label>Date Hired</label><input type="date" name="date_hired" value="{{ old('date_hired', optional($employee?->date_hired)->toDateString() ?? now()->toDateString()) }}" required></div>
<div><label>Phone</label><input name="phone" value="{{ old('phone', $employee?->phone) }}"></div>
<div class="full"><label>Address</label><input name="address" value="{{ old('address', $employee?->address) }}"></div>
<div><label>Daily Rate</label><input type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate', $employee?->daily_rate ?? 1000) }}" required></div>
<div><label>Employment Status</label><select name="employment_status"><option value="active" @selected(old('employment_status', $employee?->employment_status ?? 'active') === 'active')>Active</option><option value="resigned" @selected(old('employment_status', $employee?->employment_status) === 'resigned')>Resigned</option><option value="terminated" @selected(old('employment_status', $employee?->employment_status) === 'terminated')>Terminated</option></select></div>
<input type="hidden" name="contact_info" value="{{ old('contact_info', $employee?->contact_info) }}">
<div class="full"><button class="btn primary">{{ $button }}</button></div>

<script>
document.getElementById('department_id').addEventListener('change', function () {
    const departmentId = this.value;
    const positionSelect = document.getElementById('position_id');

    positionSelect.innerHTML = '<option value="">Loading...</option>';

    if (!departmentId) {
        positionSelect.innerHTML = '<option value="">-- Select Department First --</option>';
        return;
    }

    fetch(`/positions-by-department/${departmentId}`)
        .then(res => res.json())
        .then(data => {
            positionSelect.innerHTML = '<option value="">-- Select Position --</option>';

            if (data.length === 0) {
                positionSelect.innerHTML = '<option value="">No positions found</option>';
                return;
            }

            data.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;
                option.textContent = position.name;
                option.selected = String(position.id) === String('{{ old('position_id', $employee?->position_id) }}');
                positionSelect.appendChild(option);
            });
        })
        .catch(() => {
            positionSelect.innerHTML = '<option value="">Error loading positions</option>';
        });
});

document.getElementById('department_id').dispatchEvent(new Event('change'));
</script>
