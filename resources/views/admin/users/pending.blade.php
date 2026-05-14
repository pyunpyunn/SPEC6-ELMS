@extends('admin.layout')

@section('content')
<div class="page active" id="page-verification">
    <div class="page-header">
        <div>
            <h1>User Verification</h1>
            <p>Review pending accounts and manage active users</p>
        </div>
    </div>

    <div class="flash flash-warning">
        {{ $pendingUsers->total() }} registered account(s) are pending verification. Employees are verified by matching their Employee ID before activation.
    </div>

    <div class="tab-bar" style="margin-bottom:20px">
        <a class="tab-item active" href="#" onclick="switchVerificationTab('pending'); return false;">Pending <span class="badge badge-pending" style="margin-left:6px">{{ $pendingUsers->total() }}</span></a>
        <a class="tab-item" href="#" onclick="switchVerificationTab('all'); return false;">All Users</a>
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
                        <td>
                            <button class="btn btn-primary btn-sm" type="button" onclick="openActivateModal(@js([
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'employee_id' => $user->pending_employee_id,
                            ]))">Activate</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No pending accounts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $pendingUsers->links() }}</div>
    </div>

    <!-- ALL USERS TAB -->
    <div class="tab-pane" id="all-tab" style="display:none">
        <div class="card" style="box-shadow:none;border:none">
            <form class="filter-bar" method="GET" action="{{ route('admin.users.index') }}">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input name="search" value="{{ $userFilters['search'] ?? request('search') }}" placeholder="Search by name or employee ID">
                </div>
                <select name="department_id">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string)($userFilters['department_id'] ?? request('department_id')) === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" @selected(($userFilters['status'] ?? request('status')) === 'active')>Active</option>
                    <option value="pending" @selected(($userFilters['status'] ?? request('status')) === 'pending')>Pending</option>
                    <option value="inactive" @selected(($userFilters['status'] ?? request('status')) === 'inactive')>Inactive</option>
                </select>
                <button class="btn btn-primary btn-sm" type="submit">Filter</button>
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
                            @if($user->status === 'active')
                                <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate this account?')">Deactivate</button>
                                </form>
                            @else
                                <button class="btn btn-outline btn-sm" type="button" onclick="openActivateModal(@js([
                                    'id' => $user->id,
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'employee_id' => $user->pending_employee_id,
                                    'role' => $user->role,
                                    'department_id' => $user->employee?->department_id ?? $user->department_id,
                                    'position_id' => $user->employee?->position_id ?? $user->position_id,
                                    'manager_id' => $user->employee?->manager_id,
                                ]))">Activate</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $allUsers->links() }}</div>
    </div>
</div>

<div class="modal-overlay" id="activateModal" onclick="closeActivateModal(event)">
    <div class="modal modal-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Activate Account - <span id="activateTitle">User</span></h3>
            <button class="modal-close" type="button" onclick="closeActivateModal(event)">✕</button>
        </div>
        <form class="modal-body form" method="POST" id="activateForm">
            @csrf
            <div class="flash flash-warning">
                Verify the employee by confirming their Employee ID, then assign role, department, position, and manager before activating.
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="activateName" readonly>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" id="activateGender">
                    <option value="">Select gender</option>
                    <option value="female">Female</option>
                    <option value="male">Male</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label>Employee ID <span class="req">*</span></label>
                    <input type="text" name="employee_id" id="activateEmployeeId" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="activateEmail" readonly>
                </div>
            </div>
            <div class="form-group">
                <label>Assign Role <span class="req">*</span></label>
                <select name="role" id="activateRole" required>
                    <option value="">Select role...</option>
                    <option value="employee">Employee</option>
                    <option value="manager">Manager</option>
                    <option value="hr_admin">HR Admin</option>
                </select>
            </div>
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label>Department <span class="req">*</span></label>
                    <select name="department_id" id="activateDepartment" required>
                        <option value="">Select department...</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Position <span class="req">*</span></label>
                    <select name="position_id" id="activatePosition" required>
                        <option value="">Select department first...</option>
                    </select>
                </div>
            </div>
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label>Date Hired</label>
                    <input type="date" name="date_hired" id="activateDateHired" value="{{ now()->toDateString() }}">
                </div>
                <div class="form-group">
                    <label>Direct Manager</label>
                    <select name="manager_id" id="activateManager">
                        <option value="">Select manager...</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->full_name }} · {{ $manager->position }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closeActivateModal(event)">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="submitActivate()">Activate Account</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const activateState = { action: '', id: null };
const positionsByDepartmentUrl = '{{ url('/positions-by-department') }}';

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

function openActivateModal(data) {
    activateState.id = data.id;
    activateState.action = data.action || '{{ url("/admin/users") }}/' + data.id + '/activate';
    document.getElementById('activateTitle').textContent = data.name || 'User';
    document.getElementById('activateName').value = data.name || '';
    document.getElementById('activateEmail').value = data.email || '';
    document.getElementById('activateEmployeeId').value = data.employee_id || '';
    document.getElementById('activateGender').value = data.gender || '';
    document.getElementById('activateRole').value = data.role || 'employee';
    document.getElementById('activateDepartment').value = data.department_id || '';
    loadActivatePositions(data.department_id || '', data.position_id || '');
    document.getElementById('activateDateHired').value = data.date_hired || '{{ now()->toDateString() }}';
    document.getElementById('activateManager').value = data.manager_id || '';
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

function submitActivate() {
    document.getElementById('activateForm').submit();
}
</script>
@endpush
