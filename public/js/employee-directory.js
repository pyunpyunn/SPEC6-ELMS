const employeeDirectoryForm = document.getElementById('employeeForm');

function updatePositionOptions(departmentId, selectedPositionId = '', selectedPositionName = '') {
    const positionSelect = document.getElementById('employeePosition');

    if (!positionSelect) {
        return;
    }

    positionSelect.innerHTML = '<option value="">Loading...</option>';

    if (!departmentId) {
        positionSelect.innerHTML = '<option value="">Select department first...</option>';
        return;
    }

    const positionsByDepartmentUrl = employeeDirectoryForm?.dataset?.positionsByDepartmentUrl || '';

    fetch(`${positionsByDepartmentUrl}/${departmentId}`)
        .then(response => response.json())
        .then(positions => {
            positionSelect.innerHTML = '<option value="">Select position...</option>';

            if (!Array.isArray(positions) || positions.length === 0) {
                positionSelect.innerHTML = '<option value="">No positions found</option>';
                return;
            }

            positions.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;
                option.textContent = position.name;
                option.selected = String(position.id) === String(selectedPositionId)
                    || (!selectedPositionId && position.name === selectedPositionName);
                positionSelect.appendChild(option);
            });
        })
        .catch(() => {
            positionSelect.innerHTML = '<option value="">Error loading positions</option>';
        });
}

function openEmployeeModal(mode, employee = {}) {
    if (!employeeDirectoryForm) {
        return;
    }

    const baseEmployeeUrl = employeeDirectoryForm.dataset.employeeBaseUrl || '';
    const createEmployeeUrl = employeeDirectoryForm.dataset.employeeStoreUrl || '';

    employeeDirectoryForm.reset();
    employeeDirectoryForm.action = mode === 'edit' ? `${baseEmployeeUrl}/${employee.id}` : createEmployeeUrl;

    let methodField = employeeDirectoryForm.querySelector('input[name="_method"]');
    if (!methodField) {
        methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
    }

    if (mode === 'edit') {
        methodField.value = 'PUT';
        if (!methodField.parentNode) {
            employeeDirectoryForm.appendChild(methodField);
        }
    } else if (methodField.parentNode) {
        methodField.parentNode.removeChild(methodField);
    }

    document.getElementById('employeeModalTitle').textContent = mode === 'edit' ? 'Edit Employee' : 'Add Employee';
    document.getElementById('employeeFirstName').value = employee.first_name || '';
    document.getElementById('employeeLastName').value = employee.last_name || '';
    document.getElementById('employeeEmail').value = employee.email || '';

    const idField = document.getElementById('employeeId');
    const idHint = document.getElementById('employeeIdHint');
    if (mode === 'edit' && employee.employee_id) {
        idField.value = employee.employee_id;
        idHint.textContent = 'Cannot be changed for existing employees';
    } else {
        idField.value = '(Auto-generated)';
        idHint.textContent = 'Format: DEPT-POSID-COUNT (will be generated when department & position are selected)';
    }

    document.getElementById('employeeGender').value = employee.gender || '';
    document.getElementById('employeeDepartment').value = employee.department_id || document.getElementById('employeeDepartment')?.querySelector('option:not([value=""])')?.value || '';

    updatePositionOptions(
        employee.department_id || document.getElementById('employeeDepartment')?.querySelector('option:not([value=""])')?.value || '',
        employee.position_id || '',
        employee.position || ''
    );

    document.getElementById('employeeDateHired').value = employee.date_hired || document.getElementById('employeeDateHired')?.getAttribute('value') || '';
    document.getElementById('employeePhone').value = employee.phone || '';
    document.getElementById('employeeStatus').value = employee.employment_status || 'active';
    document.getElementById('employeeDailyRate').value = employee.daily_rate || 1000;
    document.getElementById('employeeAddress').value = employee.address || '';
    document.getElementById('employeeContactInfo').value = employee.email || '';
    document.getElementById('employeeModal').classList.add('open');
}

function closeEmployeeModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    document.getElementById('employeeModal')?.classList.remove('open');
}

function openEmployeeModalFromElement(button) {
    if (!button) {
        return;
    }

    const payload = button.dataset.employee || '{}';
    openEmployeeModal('edit', JSON.parse(payload));
}

function submitEmployee() {
    document.getElementById('employeeContactInfo').value = document.getElementById('employeeEmail').value;
    document.getElementById('employeeForm')?.submit();
}

function initializeEmployeeDirectory() {
    if (!employeeDirectoryForm) {
        return;
    }

    document.querySelectorAll('[data-action="employee-create"]').forEach(function (button) {
        button.addEventListener('click', function () {
            openEmployeeModal('create');
        });
    });

    document.querySelectorAll('[data-action="employee-edit"]').forEach(function (button) {
        button.addEventListener('click', function () {
            openEmployeeModalFromElement(button);
        });
    });

    document.querySelectorAll('[data-action="employee-close"], [data-action="employee-cancel"]').forEach(function (button) {
        button.addEventListener('click', closeEmployeeModal);
    });

    document.querySelector('[data-action="employee-submit"]')?.addEventListener('click', submitEmployee);
    document.getElementById('employeeDepartment')?.addEventListener('change', function () {
        updatePositionOptions(this.value);
    });

    const modalOverlay = document.getElementById('employeeModal');
    const modalContent = modalOverlay?.querySelector('.modal.modal-lg');

    modalOverlay?.addEventListener('click', closeEmployeeModal);
    modalContent?.addEventListener('click', function (event) {
        event.stopPropagation();
    });
}

document.addEventListener('DOMContentLoaded', initializeEmployeeDirectory);
