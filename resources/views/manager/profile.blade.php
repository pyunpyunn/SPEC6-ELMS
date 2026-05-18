@extends('manager.layout')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
@php
    $displayName = $employee?->full_name ?? auth()->user()->name;
    $balances = collect($leaveBalances ?? $employee?->leaveBalances ?? []);
@endphp

<div class="page active" id="page-profile">
    <div class="page-header">
        <div><h1>My Profile</h1><p>{{ $displayName }} - {{ $employee?->position ?? 'Manager' }} - {{ $employee?->departmentRecord?->name ?? 'N/A' }}</p></div>
    </div>

    <div style="display:grid;grid-template-columns:320px 1fr;gap:22px;align-items:start">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:26px;position:relative">
                <div style="position:absolute;right:14px;top:14px">
                    <button class="btn btn-icon" type="button" onclick="toggleProfileSettings()" title="Profile settings">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
                    </button>
                    <div class="profile-dropdown" id="profileSettingsMenu" style="top:38px;right:0">
                        <button type="button" onclick="openPasswordModal()">Forgot Password</button>
                    </div>
                </div>
                <div style="width:84px;height:84px;border-radius:50%;background:var(--primary-bg2);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:var(--primary);margin:0 auto 14px;border:3px solid var(--primary-bg)">{{ substr($displayName,0,1) }}</div>
                <div style="font-size:19px;font-weight:800;color:var(--text)">{{ $displayName }}</div>
                <div style="font-size:15px;color:var(--text3);margin-top:4px">{{ $employee?->position ?? 'Manager' }}</div>
                <span class="badge badge-manager" style="margin-top:10px">Manager</span>
                <div style="margin-top:18px;text-align:left">
                    <div class="quick-stat"><span class="quick-stat-label">Employee ID</span><span style="font-family:var(--mono);font-size:12px;color:var(--primary);font-weight:700">{{ $employee?->employee_id ?? '-' }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Gender</span><span>{{ ucfirst($employee?->gender ?? 'Unspecified') }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Department</span><span>{{ $employee?->departmentRecord?->name ?? $employee?->department ?? '-' }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Position</span><span>{{ $employee?->position ?? '-' }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Date Hired</span><span>{{ $employee?->date_hired?->format('M d, Y') ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:18px">
            <div class="card">
                <div class="card-header"><span class="card-title">Personal Information</span></div>
                <div class="card-body">
                    <form class="form-grid" method="POST" action="{{ route('manager.profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div><label>First Name</label><input name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required></div>
                        <div><label>Last Name</label><input name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required></div>
                        <div><label>Email</label><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></div>
                        <div><label>Phone</label><input name="phone" value="{{ old('phone', $employee?->phone) }}"></div>
                        <div class="full"><label>Address</label><input name="address" value="{{ old('address', $employee?->address) }}"></div>
                        <div class="full"><button class="btn btn-primary btn-sm" type="submit">Save Changes</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-header"><span class="card-title">Leave Balance</span></div>
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

    <div class="card" style="margin-top:18px">
        <div class="card-header"><span class="card-title">Leave History</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Reason</th><th>Status</th><th>Reviewed By</th></tr></thead>
                <tbody>
                @forelse($leaveApplications as $leave)
                    <tr>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('M d') }}</td>
                        <td>{{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ (int) $leave->total_days }}</td>
                        <td>{{ $leave->reason }}</td>
                        <td><span class="badge badge-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                        <td>{{ $leave->reviewer?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">No leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($leaveApplications, 'links'))<div class="pagination" style="padding:16px">{{ $leaveApplications->links('vendor.pagination.hr') }}</div>@endif
    </div>
</div>

<div class="modal-overlay" id="passwordModal" onclick="closePasswordModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button class="modal-close" type="button" onclick="closePasswordModal(event)">x</button>
        </div>
        <form class="modal-body form-grid" method="POST" action="{{ route('manager.profile.password') }}">
            @csrf
            @method('PUT')
            <div><label>Current Password</label><input type="password" name="current_password" required></div>
            <div><label>New Password</label><input type="password" name="password" required></div>
            <div class="full"><label>Confirm New Password</label><input type="password" name="password_confirmation" required></div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" type="button" onclick="closePasswordModal(event)">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="document.querySelector('#passwordModal form').submit()">Update Password</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleProfileSettings(){document.getElementById('profileSettingsMenu')?.classList.toggle('open')}
function openPasswordModal(){
    document.getElementById('profileSettingsMenu')?.classList.remove('open');
    document.getElementById('passwordModal')?.classList.add('open');
}
function closePasswordModal(event){
    if(event){event.preventDefault();event.stopPropagation();}
    document.getElementById('passwordModal')?.classList.remove('open');
}
</script>
@endpush
