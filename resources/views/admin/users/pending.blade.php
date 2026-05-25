@extends('admin.layout')

@section('content')
<div class="page active" id="page-verification">
    <div class="page-header">
        <div>
            <h1>User Verification</h1>
            <p>Review pending registrations, then manage approved system users</p>
        </div>
    </div>

    <div class="flash flash-warning">
        {{ $pendingUsers->total() }} registered account(s) are pending verification. Employees are verified by matching their Employee ID before activation.
    </div>

    <div class="tab-bar" style="margin-bottom:20px">
        <a class="tab-item active" href="#pending" onclick="switchVerificationTab('pending'); return false;">Pending <span class="badge badge-pending" style="margin-left:6px">{{ $pendingUsers->total() }}</span></a>
        <a class="tab-item" href="#all" onclick="switchVerificationTab('all'); return false;">All Users <span class="badge badge-active" style="margin-left:6px">{{ $allUsers->total() }}</span></a>
    </div>

    <!-- PENDING USERS TAB -->
    <div class="tab-pane active" id="pending-tab">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pendingUsers as $user)
                    <tr>
                        <td>
                            <div class="td-name">{{ $user->name }}</div>
                            <div class="td-sub">Self-registered</div>
                        </td>
                        <td><span style="font-family:var(--mono);font-size:15px;color:var(--primary);font-weight:700">{{ $user->pending_employee_id ?: 'Needs ID' }}</span></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('M d, Y g:i A') }}</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                        @php
                            $activateUserData = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'employee_id' => $user->pending_employee_id,
                            ];
                        @endphp
                        <td>
                            <button class="btn btn-primary btn-sm" type="button" data-activate-user='@json($activateUserData)' onclick="openActivateModalFromElement(this)">Activate</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No pending accounts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $pendingUsers->links('vendor.pagination.hr', ['anchor' => 'pending']) }}</div>
    </div>

    <!-- ALL USERS TAB (approved / registered accounts only) -->
    <div class="tab-pane" id="all-tab" style="display:none">
        <div class="card" style="box-shadow:none;border:none">
            <form class="filter-bar" method="GET" action="{{ route('admin.users.index') }}">
                <div class="filter-bar-row">
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input id="adminUserSearch" type="search" name="search" value="{{ $userFilters['search'] ?? request('search') }}" placeholder="Search by name or employee ID" aria-label="Search users by name or employee ID">
                    </div>
                </div>
                <div class="filter-bar-row">
                    <select name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string)($userFilters['department_id'] ?? request('department_id')) === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" @selected(($userFilters['status'] ?? request('status')) === 'active')>Active</option>
                        <option value="inactive" @selected(($userFilters['status'] ?? request('status')) === 'inactive')>Inactive</option>
                    </select>
                    <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department · Position</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($allUsers as $user)
                    <tr>
                        <td><div class="td-name">{{ $user->name }}</div></td>
                        <td><span style="font-family:var(--mono);font-size:15px">{{ $user->employee?->employee_id ?? $user->pending_employee_id ?? 'Not assigned' }}</span></td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-{{ $user->role === 'hr_admin' ? 'hr' : ($user->role === 'manager' ? 'manager' : 'emp') }}">{{ ucfirst(str_replace('_',' ', $user->role)) }}</span></td>
                        <td>
                            <div>{{ $user->employee?->departmentRecord?->name ?? 'Unassigned' }}</div>
                            <div class="td-pos">{{ $user->employee?->position ?? 'No position' }}</div>
                        </td>
                    <td><span class="badge badge-{{ $user->status }}">{{ ucfirst($user->status) }}</span></td>
                    <td class="actions">
                        @php
                            $activateUserData = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'employee_id' => $user->pending_employee_id,
                                'role' => $user->role,
                                'department_id' => $user->employee?->department_id ?? $user->department_id,
                                'position_id' => $user->employee?->position_id ?? $user->position_id,
                            ];
                            $deleteUserData = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                            ];
                        @endphp
                        <div class="user-actions-menu" style="position:relative;display:inline-block">
                            <button class="btn btn-outline btn-sm" type="button" onclick="toggleUserActionsMenu(this)" aria-expanded="false" aria-label="Open user actions">⋮</button>
                            <div class="user-actions-dropdown" style="display:none;position:absolute;right:0;top:calc(100% + 6px);min-width:180px;background:#ffffff;border:1px solid rgba(0,0,0,0.12);box-shadow:0 10px 24px rgba(0,0,0,0.08);z-index:40">
                                @if($user->status === 'active')
                                    <button type="button" class="dropdown-item" data-deactivate-url="{{ route('admin.users.deactivate', $user) }}" onclick="submitDeactivateFromElement(this)" style="width:100%;padding:12px 14px;text-align:left;border:none;background:transparent;cursor:pointer">Deactivate Account</button>
                                @else
                                    <button type="button" class="dropdown-item" data-activate-user='@json($activateUserData)' onclick="openActivateModalFromElement(this)" style="width:100%;padding:12px 14px;text-align:left;border:none;background:transparent;cursor:pointer">Activate Account</button>
                                @endif
                                <button type="button" class="dropdown-item" data-delete-user='@json($deleteUserData)' onclick="openDeleteModalFromElement(this)" style="width:100%;padding:12px 14px;text-align:left;border:none;background:transparent;cursor:pointer;color:#b91c1c">Delete Account Forever</button>
                            </div>
                        </div>
                    </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No registered users found. Approved accounts will appear here after activation.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $allUsers->links('vendor.pagination.hr', ['anchor' => 'all']) }}</div>
    </div>
</div>

<div class="modal-overlay" id="activateModal" onclick="closeActivateModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Activate Account - <span id="activateTitle">User</span></h3>
            <button class="modal-close" type="button" onclick="closeActivateModal(event)">✕</button>
        </div>
        <form class="modal-body" method="POST" id="activateForm" data-positions-by-department-url="{{ url('/positions-by-department') }}" data-activate-base-url="{{ url('/admin/users') }}">
            @csrf
            <div class="flash flash-warning">
                Choose the department and position. The Employee ID and access role are derived automatically from that selection.
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="activateName">Full Name</label>
                    <input class="form-control field-readonly" type="text" id="activateName" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label" for="activateGender">Gender</label>
                    <select class="form-control" name="gender" id="activateGender">
                        <option value="">Select gender</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="activateEmployeeId">Employee ID</label>
                    <input class="form-control field-readonly" type="text" id="activateEmployeeId" readonly disabled style="font-family:var(--mono)" value="Auto-generated">
                </div>
                <div class="form-group">
                    <label class="form-label" for="activateEmail">Email</label>
                    <input class="form-control field-readonly" type="email" id="activateEmail" readonly>
                </div>
                <div class="form-group span2">
                    <label class="form-label" for="activateAccessRole">Access Role</label>
                    <input class="form-control field-readonly" id="activateAccessRole" value="Auto-derived from department and position" readonly disabled>
                </div>
                <div class="form-group">
                    <label class="form-label" for="activateDepartment">Department <span class="required-mark">*</span></label>
                    <select class="form-control" name="department_id" id="activateDepartment" required>
                        <option value="">Select department...</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="activatePosition">Position <span class="required-mark">*</span></label>
                    <select class="form-control" name="position_id" id="activatePosition" required>
                        <option value="">Select department first...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="activateDateHired">Date Hired</label>
                    <input class="form-control" type="date" name="date_hired" id="activateDateHired" value="{{ now()->toDateString() }}">
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeActivateModal(event)">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="submitActivate()">Activate Account</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteUserModal" onclick="closeDeleteModal(event)">
    <div class="modal modal-md" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Delete User Account</h3>
            <button class="modal-close" type="button" onclick="closeDeleteModal(event)">✕</button>
        </div>

        <br><p style="padding-left:22px">Name: <strong id="deleteUserName"></strong></p>
        
        <form class="modal-body form" method="POST" id="deleteUserForm" data-delete-base-url="{{ url('/admin/users') }}">
            @csrf
            @method('DELETE')

            
            <div class="form-group">

                <div class="flash flash-error" id="deleteUserError"></div>
                <p>This action permanently deletes the account and all related data. It cannot be undone.</p>
            
                <br>
                <label for="deleteUserConfirmation">Type <strong>DELETE ACCOUNT FOREVER</strong> to confirm</label>
                <input id="deleteUserConfirmation" name="confirm_delete_phrase" type="text" class="form-control" placeholder="DELETE ACCOUNT FOREVER" required>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeDeleteModal(event)">Cancel</button>
            <button class="btn btn-danger" type="button" onclick="submitDeleteUser()">Delete Account</button>
        </div>
    </div>
</div>
<form id="deactivateUserForm" method="POST" style="display:none">
    @csrf
    @method('PATCH')
</form>
@endsection

@push('scripts')
<script>
const activateState = { action: '', id: null };
const deleteState = { action: '', id: null };
const activateForm = document.getElementById('activateForm');
const deleteForm = document.getElementById('deleteUserForm');
const deactivateForm = document.getElementById('deactivateUserForm');
const positionsByDepartmentUrl = activateForm?.dataset?.positionsByDepartmentUrl || '';
const activateBaseUrl = activateForm?.dataset?.activateBaseUrl || '';
const deleteBaseUrl = deleteForm?.dataset?.deleteBaseUrl || '{{ url('/admin/users') }}';

function switchVerificationTab(tab) {
    const pendingTab = document.getElementById('pending-tab');
    const allTab = document.getElementById('all-tab');
    const tabItems = document.querySelectorAll('.tab-item');
    
    // Hide both tabs
    pendingTab.style.display = 'none';
    allTab.style.display = 'none';
    
    // Remove active class from all tabs
    tabItems.forEach(item => item.classList.remove('active'));
    
    // Show selected tab and mark as active
    if (tab === 'pending') {
        pendingTab.style.display = 'block';
        tabItems[0].classList.add('active');
    } else if (tab === 'all') {
        allTab.style.display = 'block';
        tabItems[1].classList.add('active');
    }
}

function handleVerificationHash() {
    const hash = window.location.hash.replace('#', '');
    if (hash === 'all') {
        switchVerificationTab('all');
    } else {
        switchVerificationTab('pending');
    }
}

window.addEventListener('load', handleVerificationHash);
window.addEventListener('hashchange', handleVerificationHash);

function openActivateModal(data) {
    activateState.id = data.id;
    activateState.action = data.action || activateBaseUrl + '/' + data.id + '/activate';
    document.getElementById('activateTitle').textContent = data.name || 'User';
    document.getElementById('activateName').value = data.name || '';
    document.getElementById('activateEmail').value = data.email || '';
    document.getElementById('activateEmployeeId').value = data.employee_id || 'Auto-generated after activation';
    document.getElementById('activateGender').value = data.gender || '';
    document.getElementById('activateDepartment').value = data.department_id || '';
    loadActivatePositions(data.department_id || '', data.position_id || '');
    document.getElementById('activateDateHired').value = data.date_hired || '{{ now()->toDateString() }}';
    document.getElementById('activateForm').action = activateState.action;
    document.getElementById('activateModal').classList.add('open');
}

function loadActivatePositions(departmentId, selectedPositionId = '') {
    const positionSelect = document.getElementById('activatePosition');
    positionSelect.innerHTML = '<option value="">Loading...</option>';

    if (!departmentId) {
        positionSelect.innerHTML = '<option value="">Select department first...</option>';
        return;
    }

    fetch(`${positionsByDepartmentUrl}/${departmentId}`)
        .then(response => response.json())
        .then(positions => {
            positionSelect.innerHTML = '<option value="">Select position...</option>';

            if (!positions.length) {
                positionSelect.innerHTML = '<option value="">No positions found</option>';
                return;
            }

            positions.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;
                option.textContent = position.name;
                option.selected = String(position.id) === String(selectedPositionId);
                positionSelect.appendChild(option);
            });
        })
        .catch(() => {
            positionSelect.innerHTML = '<option value="">Error loading positions</option>';
        });
}

document.getElementById('activateDepartment').addEventListener('change', function () {
    loadActivatePositions(this.value);
});

function closeActivateModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('activateModal').classList.remove('open');
}

function openActivateModalFromElement(button) {
    const payload = button?.dataset?.activateUser || '{}';
    openActivateModal(JSON.parse(payload));
}

function openDeleteModal(data) {
    deleteState.id = data.id;
    deleteState.action = deleteBaseUrl + '/' + data.id;
    document.getElementById('deleteUserName').textContent = data.name || 'User';
    document.getElementById('deleteUserConfirmation').value = '';
    document.getElementById('deleteUserError').style.display = 'none';
    document.getElementById('deleteUserForm').action = deleteState.action;
    document.getElementById('deleteUserModal').classList.add('open');
}

function openDeleteModalFromElement(button) {
    const payload = button?.dataset?.deleteUser || '{}';
    openDeleteModal(JSON.parse(payload));
}

function closeDeleteModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('deleteUserModal').classList.remove('open');
}

function submitDeleteUser() {
    const confirmationInput = document.getElementById('deleteUserConfirmation');
    const errorElement = document.getElementById('deleteUserError');

    if (!confirmationInput) {
        return;
    }

    if (confirmationInput.value.trim() !== 'DELETE ACCOUNT FOREVER') {
        errorElement.textContent = 'Please type DELETE ACCOUNT FOREVER exactly to confirm deletion.';
        errorElement.style.display = 'block';
        return;
    }

    document.getElementById('deleteUserForm').submit();
}

function toggleUserActionsMenu(button) {
    const menuContainer = button.closest('.user-actions-menu');
    if (!menuContainer) {
        return;
    }

    const dropdown = menuContainer.querySelector('.user-actions-dropdown');
    const isOpen = dropdown && dropdown.style.display === 'block';
    closeAllUserActionsMenus();

    if (!isOpen && dropdown) {
        dropdown.style.display = 'block';
        button.setAttribute('aria-expanded', 'true');
    }
}

function closeAllUserActionsMenus() {
    document.querySelectorAll('.user-actions-dropdown').forEach(dropdown => {
        dropdown.style.display = 'none';
    });
    document.querySelectorAll('.user-actions-menu button[aria-expanded="true"]').forEach(button => {
        button.setAttribute('aria-expanded', 'false');
    });
}

window.addEventListener('click', function (event) {
    if (!event.target.closest('.user-actions-menu')) {
        closeAllUserActionsMenus();
    }
});

function submitDeactivateFromElement(button) {
    const url = button?.dataset?.deactivateUrl;
    if (!url) {
        return;
    }

    if (!deactivateForm) {
        return;
    }

    deactivateForm.action = url;
    deactivateForm.submit();
}

function submitActivate() {
    document.getElementById('activateForm').submit();
}
</script>
@endpush
