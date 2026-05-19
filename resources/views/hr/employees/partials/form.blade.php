<div><label for="first_name">First Name</label><input id="first_name" name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required></div>
<div><label for="last_name">Last Name</label><input id="last_name" name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required></div>
<div><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $employee?->user?->email) }}" required></div>
<div><label for="employeeAccessRole">Access Role</label><input id="employeeAccessRole" value="Auto-derived from department and position" readonly disabled style="background-color:#f0f0f0;cursor:not-allowed"></div>
<div>
    <label for="employeeId">Employee ID</label>
    @if($employee)
        <input id="employeeId" type="text" value="{{ $employee->employee_id }}" readonly disabled style="background-color:#f0f0f0;cursor:not-allowed">
        <small style="display:block;margin-top:4px;color:#666">Cannot be changed for existing employees</small>
    @else
        <input id="employeeId" type="text" value="{{ $nextEmployeeId }}" readonly disabled style="background-color:#f0f0f0;cursor:not-allowed">
        <small style="display:block;margin-top:4px;color:#666">Format: DEPT-POSID-COUNT (auto-generated)</small>
    @endif
</div>
<div><label for="department_id">Department</label><select name="department_id" id="department_id" required><option value="">-- Select Department --</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $employee?->department_id) == $d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div><label for="position_id">Position</label><select name="position_id" id="position_id" required><option value="">-- Select Department First --</option></select></div>
<div><label for="manager_id">Manager</label><select name="manager_id" id="manager_id"><option value="">No manager</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected(old('manager_id', $employee?->manager_id) == $m->id)>{{ $m->full_name }}</option>@endforeach</select></div>
<div><label for="date_hired">Date Hired</label><input type="date" id="date_hired" name="date_hired" value="{{ old('date_hired', optional($employee?->date_hired)->toDateString() ?? now()->toDateString()) }}" required></div>
<div><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone', $employee?->phone) }}"></div>
<div class="full"><label for="address">Address</label><input id="address" name="address" value="{{ old('address', $employee?->address) }}"></div>
<div><label for="daily_rate">Daily Rate</label><input id="daily_rate" type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate', $employee?->daily_rate ?? 1000) }}" required></div>
<div><label for="employment_status">Employment Status</label><select id="employment_status" name="employment_status"><option value="active" @selected(old('employment_status', $employee?->employment_status ?? 'active') === 'active')>Active</option><option value="resigned" @selected(old('employment_status', $employee?->employment_status) === 'resigned')>Resigned</option><option value="terminated" @selected(old('employment_status', $employee?->employment_status) === 'terminated')>Terminated</option></select></div>
<input type="hidden" name="contact_info" value="{{ old('contact_info', $employee?->contact_info) }}">
<div class="full"><button class="btn primary">{{ $button }}</button></div>
<div id="employeeFormData" data-position-config='@json($positionConfig ?? [])' data-department-prefixes='@json($departmentPrefixes ?? [])' data-departments='@json($departments ?? [])' data-selected-position-id="{{ old('position_id', $employee?->position_id) }}" style="display:none"></div>

<script>
const dataHolder = document.getElementById('employeeFormData');
const positionConfig = JSON.parse(dataHolder.dataset.positionConfig || '{}');
const departmentPrefixes = JSON.parse(dataHolder.dataset.departmentPrefixes || '{}');
const departments = JSON.parse(dataHolder.dataset.departments || '[]');
const defaultSelectedPositionId = dataHolder.dataset.selectedPositionId || '';

document.getElementById('department_id').addEventListener('change', function () {
    const departmentId = this.value;
    const positionSelect = document.getElementById('position_id');

    positionSelect.innerHTML = '<option value="">Loading...</option>';

    if (!departmentId) {
        positionSelect.innerHTML = '<option value="">-- Select Department First --</option>';
        updateIdPreview();
        return;
    }

    fetch(`/positions-by-department/${departmentId}`)
        .then(res => res.json())
        .then(data => {
            positionSelect.innerHTML = '<option value="">-- Select Position --</option>';

            if (data.length === 0) {
                positionSelect.innerHTML = '<option value="">No positions found</option>';
                updateIdPreview();
                return;
            }

            data.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;
                option.textContent = position.name;
                option.selected = String(position.id) === String(defaultSelectedPositionId);
                positionSelect.appendChild(option);
            });
            
            updateIdPreview();
        })
        .catch(() => {
            positionSelect.innerHTML = '<option value="">Error loading positions</option>';
            updateIdPreview();
        });
});

document.getElementById('position_id').addEventListener('change', updateIdPreview);

function updateIdPreview() {
    const departmentId = document.getElementById('department_id').value;
    const positionId = document.getElementById('position_id').value;
    
    if (!departmentId || !positionId) {
        return;
    }
    
    // Find department code
    const dept = departments.find(d => String(d.id) === String(departmentId));
    if (!dept) return;
    
    const deptCode = dept.code;
    const prefix = departmentPrefixes[deptCode];
    const posIds = positionConfig[deptCode];
    
    // Find position ID
    let posId = null;
    const position = dept.positions?.find(p => String(p.id) === String(positionId));
    if (position && posIds) {
        for (const [id, name] of Object.entries(posIds)) {
            if (name === position.name) {
                posId = id;
                break;
            }
        }
    }
    
    if (prefix && posId !== null) {
        // Here you could fetch count from server if needed
        // For now just show format preview
        console.log(`ID Format: ${prefix}-${posId}-###`);
    }
}

document.getElementById('department_id').dispatchEvent(new Event('change'));
</script>

