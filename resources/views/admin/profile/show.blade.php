@extends('admin.layout')

@section('content')
@php
    $isViewingEmployee = $viewingEmployee ?? false;
    $displayName = $employee?->full_name ?? auth()->user()->name;
    $displayRole = $isViewingEmployee ? ucfirst(str_replace('_', ' ', $employee?->user?->role ?? 'Employee')) : 'HR Admin';
    $displayDepartment = $employee?->departmentRecord?->name ?? 'Human Resources';
    $balances = collect($leaveBalances ?? $employee?->leaveBalances ?? []);
    $profileEditMode = old('first_name') !== null
        || old('last_name') !== null
        || old('gender') !== null
        || old('email') !== null
        || old('phone') !== null
        || old('address') !== null;
@endphp

<div class="page active" id="page-profile">
    <div class="page-header">
        <div>
            <h1>{{ $isViewingEmployee ? 'Employee Profile' : 'My Profile' }}</h1>
            <p>{{ $displayName }} · {{ $employee?->position ?? 'HR Administrator' }} · {{ $displayDepartment }}</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:320px 1fr;gap:22px;align-items:start">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:26px;position:relative">
                @if(! $isViewingEmployee)
                    <div class="profile-card-actions" style="position:absolute;right:14px;top:14px">
                        <button class="btn btn-icon" id="hrProfileActionsButton" type="button" onclick="toggleHrProfileActions()" title="Profile actions" aria-label="Profile actions" aria-expanded="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
                        </button>
                        <div class="profile-dropdown hr-profile-actions-menu" id="hrProfileActionsMenu" style="top:38px;right:0">
                            <button type="button" onclick="openHrPasswordModal()">Change Password</button>
                            <button class="danger" type="button" onclick="openHrDeleteModal()">Delete Account</button>
                        </div>
                    </div>
                @endif
                <div style="width:84px;height:84px;border-radius:50%;background:var(--primary-bg2);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:var(--primary);margin:0 auto 14px;border:3px solid var(--primary-bg)">{{ substr($displayName,0,1) }}</div>
                <div style="font-size:19px;font-weight:800;color:var(--text)">{{ $displayName }}</div>
                <div style="font-size:15px;color:var(--text3);margin-top:4px">{{ $employee?->position ?? 'HR Administrator' }}</div>
                <span class="badge badge-{{ $isViewingEmployee ? ($employee?->user?->role === 'manager' ? 'manager' : 'emp') : 'hr' }}" style="margin-top:10px">{{ $displayRole }}</span>
                <div style="margin-top:18px;text-align:left">
                    <div class="quick-stat"><span class="quick-stat-label">Employee ID</span><span style="font-family:var(--mono);font-size:15px;color:var(--primary);font-weight:700">{{ $employee?->employee_id ?? '—' }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Gender</span><span>{{ ucfirst($employee?->gender ?? 'Unspecified') }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Department</span><span>{{ $displayDepartment }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Position</span><span>{{ $employee?->position ?? 'HR Administrator' }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Date Hired</span><span>{{ $employee?->date_hired?->format('M d, Y') ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:18px">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Personal Information</span>
                </div>
                <div class="card-body">
                    @if(! $isViewingEmployee)
                        <form class="form-grid" id="hrProfileInfoForm" method="POST" action="{{ route('admin.profile.update') }}" data-editing="{{ $profileEditMode ? '1' : '0' }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group"><label class="form-label" for="first_name">First Name</label><input class="form-control" data-profile-field id="first_name" name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required @readonly(! $profileEditMode)></div>
                            <div class="form-group"><label class="form-label" for="last_name">Last Name</label><input class="form-control" data-profile-field id="last_name" name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required @readonly(! $profileEditMode)></div>
                            <div class="form-group"><label class="form-label" for="gender">Gender</label>
                                <select class="form-control" data-profile-field id="gender" name="gender" @disabled(! $profileEditMode)>
                                    <option value="">Select gender</option>
                                    <option value="female" @selected(old('gender', $employee?->gender) === 'female')>Female</option>
                                    <option value="male" @selected(old('gender', $employee?->gender) === 'male')>Male</option>
                                    <option value="other" @selected(old('gender', $employee?->gender) === 'other')>Other</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" data-profile-field id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required @readonly(! $profileEditMode)></div>
                            <div class="form-group"><label class="form-label" for="phone">Phone</label><input class="form-control" data-profile-field id="phone" name="phone" value="{{ old('phone', $employee?->phone) }}" @readonly(! $profileEditMode)></div>
                            <div class="form-group span2"><label class="form-label" for="address">Address</label><input class="form-control" data-profile-field id="address" name="address" value="{{ old('address', $employee?->address) }}" @readonly(! $profileEditMode)></div>
                            <input type="hidden" name="contact_info" value="{{ $employee?->contact_info }}">
                            <div class="form-group span2 profile-edit-actions">
                                <button class="btn btn-primary btn-sm" type="button" id="hrProfileEditButton" onclick="enableHrProfileEdit()" style="display:{{ $profileEditMode ? 'none' : 'inline-flex' }}" @if($profileEditMode) hidden @endif>Edit</button>
                                <div id="hrProfileSaveActions" style="display:{{ $profileEditMode ? 'flex' : 'none' }};gap:8px;justify-content:flex-end">
                                    <button class="btn btn-outline btn-sm" type="button" onclick="cancelHrProfileEdit()">Cancel</button>
                                    <button class="btn btn-primary btn-sm" type="submit">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="grid" style="grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
                            <div class="quick-stat"><span class="quick-stat-label">Email</span><span>{{ $employee?->user?->email }}</span></div>
                            <div class="quick-stat"><span class="quick-stat-label">Phone</span><span>{{ $employee?->phone ?? '—' }}</span></div>
                            <div class="quick-stat"><span class="quick-stat-label">Address</span><span>{{ $employee?->address ?? '—' }}</span></div>
                            <div class="quick-stat"><span class="quick-stat-label">Manager</span><span>{{ $employee?->manager?->full_name ?? '—' }}</span></div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-header">
            <span class="card-title">Leave Balance</span>
            <form method="GET" class="page-actions">
                <select name="leave_type_id" onchange="this.form.submit()">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" @selected($selectedTypeId === $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()">
                    @foreach($years ?? [now()->year] as $availableYear)
                        <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th></tr></thead>
                <tbody>
                @forelse($balances as $balance)
                    <tr>
                        <td>{{ $balance->leaveType->name }}</td>
                        <td>{{ (int) $balance->allocated_days }}</td>
                        <td>{{ (int) $balance->used_days }}</td>
                        <td>{{ (int) $balance->remaining_days }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No leave balances available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(! $isViewingEmployee)
        <div class="card" style="margin-top:18px">
            <div class="card-header"><span class="card-title">Leave Requests</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Reason</th><th>Status</th><th>Reviewed By</th></tr></thead>
                    <tbody>
                    @forelse($employee?->leaveApplications ?? [] as $leave)
                        <tr>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>{{ $leave->start_date->format('M d') }}</td>
                            <td>{{ $leave->end_date->format('M d, Y') }}</td>
                            <td>{{ (int) $leave->total_days }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                            <td>{{ $leave->reviewer?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No leave requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@if(! $isViewingEmployee)
    <div class="modal-overlay" id="hrPasswordModal" onclick="closeHrPasswordModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 class="modal-title">Change Password</h3>
                <button class="modal-close" type="button" onclick="closeHrPasswordModal(event)">x</button>
            </div>
            <form class="modal-body" method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group"><label class="form-label" for="current_password">Current Password <span class="required-mark">*</span></label><input class="form-control" id="current_password" type="password" name="current_password" required></div>
                    <div class="form-group"><label class="form-label" for="password">New Password <span class="required-mark">*</span></label><input class="form-control" id="password" type="password" name="password" required></div>
                    <div class="form-group span2"><label class="form-label" for="password_confirmation">Confirm New Password <span class="required-mark">*</span></label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required></div>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeHrPasswordModal(event)">Cancel</button>
                <button class="btn btn-primary" type="button" onclick="document.querySelector('#hrPasswordModal form').submit()">Update Password</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="hrDeleteAccountModal" onclick="closeHrDeleteModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 class="modal-title">Delete Account</h3>
                <button class="modal-close" type="button" onclick="closeHrDeleteModal(event)">x</button>
            </div>
            <form class="modal-body" method="POST" action="{{ route('admin.profile.destroy') }}" id="hrDeleteAccountForm">
                @csrf
                @method('DELETE')
                <div class="flash flash-error" id="hrDeleteAccountError" style="display:none"></div>
                <p class="muted" style="margin:0 0 16px">This permanently deletes your account and related employee records. It cannot be undone.</p>
                <div class="form-group">
                    <label class="form-label" for="hrDeleteConfirmation">Type <strong>DELETE ACCOUNT FOREVER</strong> to confirm</label>
                    <input class="form-control" id="hrDeleteConfirmation" name="confirm_delete_phrase" type="text" placeholder="DELETE ACCOUNT FOREVER" required>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeHrDeleteModal(event)">Cancel</button>
                <button class="btn btn-danger" type="button" onclick="submitHrDeleteAccount()">Delete Account</button>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function toggleHrProfileActions() {
    const menu = document.getElementById('hrProfileActionsMenu');
    const button = document.getElementById('hrProfileActionsButton');
    const isOpen = menu?.classList.toggle('open');
    button?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function closeHrProfileActions() {
    document.getElementById('hrProfileActionsMenu')?.classList.remove('open');
    document.getElementById('hrProfileActionsButton')?.setAttribute('aria-expanded', 'false');
}

function openHrPasswordModal() {
    closeHrProfileActions();
    document.getElementById('hrPasswordModal')?.classList.add('open');
}

function closeHrPasswordModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('hrPasswordModal')?.classList.remove('open');
}

function openHrDeleteModal() {
    closeHrProfileActions();
    document.getElementById('hrDeleteAccountError').style.display = 'none';
    document.getElementById('hrDeleteConfirmation').value = '';
    document.getElementById('hrDeleteAccountModal')?.classList.add('open');
}

function closeHrDeleteModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('hrDeleteAccountModal')?.classList.remove('open');
}

function submitHrDeleteAccount() {
    const confirmationInput = document.getElementById('hrDeleteConfirmation');
    const errorElement = document.getElementById('hrDeleteAccountError');

    if (confirmationInput.value.trim() !== 'DELETE ACCOUNT FOREVER') {
        errorElement.textContent = 'Please type DELETE ACCOUNT FOREVER exactly to confirm deletion.';
        errorElement.style.display = 'block';
        return;
    }

    document.getElementById('hrDeleteAccountForm').submit();
}

function setHrProfileEditMode(enabled) {
    const form = document.getElementById('hrProfileInfoForm');
    const editButton = document.getElementById('hrProfileEditButton');
    const saveActions = document.getElementById('hrProfileSaveActions');

    form?.querySelectorAll('[data-profile-field]').forEach(function (field) {
        if (field.tagName === 'SELECT') {
            field.disabled = !enabled;
        } else {
            field.readOnly = !enabled;
        }

        field.classList.toggle('profile-field-readonly', !enabled);
    });

    if (editButton) {
        editButton.hidden = enabled;
        editButton.style.display = enabled ? 'none' : 'inline-flex';
    }

    if (saveActions) {
        saveActions.style.display = enabled ? 'flex' : 'none';
    }
}

function enableHrProfileEdit() {
    setHrProfileEditMode(true);
    document.getElementById('first_name')?.focus();
}

function cancelHrProfileEdit() {
    document.getElementById('hrProfileInfoForm')?.reset();
    setHrProfileEditMode(false);
}

document.addEventListener('click', function (event) {
    if (!event.target.closest('.profile-card-actions')) {
        closeHrProfileActions();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('hrProfileInfoForm');
    setHrProfileEditMode(form?.dataset.editing === '1');
});
</script>
@endpush
