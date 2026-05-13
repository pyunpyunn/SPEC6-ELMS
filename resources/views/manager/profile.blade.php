@extends('manager.layout')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
@php($initials = collect(explode(' ', auth()->user()->name))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode(''))
<div class="page-header"><div><h1>My Profile</h1><p>Manage your personal information and settings.</p></div></div>

<div class="profile-grid">
    <div class="card">
        <div class="card-body" style="text-align:center;padding:26px">
            <div class="profile-avatar" style="width:74px;height:74px;font-size:25px;margin:0 auto 14px;border:3px solid var(--primary-bg)">{{ $initials }}</div>
            <div style="font-size:17px;font-weight:700;color:var(--text)">{{ auth()->user()->name }}</div>
            <div style="font-size:12px;color:var(--text3);margin-top:3px">{{ $employee?->position ?? 'Manager' }}</div>
            <span class="badge badge-info" style="margin-top:8px">Manager</span>
            <div style="margin-top:18px;font-size:11.5px;color:var(--text3);text-align:left">
                <div class="quick-stat"><span class="quick-stat-label">Employee ID</span><span style="font-family:var(--mono);font-size:11.5px;color:var(--primary);font-weight:600">{{ $employee?->employee_id ?? 'N/A' }}</span></div>
                <div class="quick-stat"><span class="quick-stat-label">Department</span><span>{{ $employee?->departmentRecord?->name ?? $employee?->department ?? 'N/A' }}</span></div>
                <div class="quick-stat"><span class="quick-stat-label">Position</span><span>{{ $employee?->position ?? 'N/A' }}</span></div>
                <div class="quick-stat"><span class="quick-stat-label">Date Hired</span><span>{{ $employee?->date_hired?->format('M d, Y') ?? 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px">
        <div class="card">
            <div class="card-header"><span class="card-title">Personal Information</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('manager.profile.update') }}">
                    @csrf @method('PUT')
                    <div class="form-grid">
                        <div><label>First Name</label><input name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required></div>
                        <div><label>Last Name</label><input name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required></div>
                        <div><label>Email</label><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></div>
                        <div><label>Phone</label><input name="phone" value="{{ old('phone', $employee?->phone) }}"></div>
                        <div><label>Employee ID</label><input value="{{ $employee?->employee_id }}" disabled></div>
                        <div><label>Role</label><input value="Manager" disabled></div>
                        <div><label>Department</label><input value="{{ $employee?->departmentRecord?->name ?? $employee?->department }}" disabled></div>
                        <div><label>Position</label><input value="{{ $employee?->position }}" disabled></div>
                        <div class="span2"><label>Address</label><input name="address" value="{{ old('address', $employee?->address) }}"></div>
                    </div>
                    <div style="margin-top:16px"><button class="btn btn-primary btn-sm">Save Changes</button></div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Change Password</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('manager.profile.password') }}">
                    @csrf @method('PUT')
                    <div class="form-grid">
                        <div><label>Current Password</label><input type="password" name="current_password" required></div>
                        <div><label>New Password</label><input type="password" name="password" required></div>
                        <div class="span2"><label>Confirm New Password</label><input type="password" name="password_confirmation" required></div>
                    </div>
                    <div style="margin-top:16px"><button class="btn btn-outline btn-sm">Update Password</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
