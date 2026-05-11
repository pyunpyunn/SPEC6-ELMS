@extends('hr.layout')

@section('content')
<div class="page active" id="page-profile">
    <div class="page-header"><div><h1>My Profile</h1><p>Manage your personal information and settings</p></div></div>
    <div style="display:grid;grid-template-columns:300px 1fr;gap:22px;align-items:start">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:26px">
                <div style="width:74px;height:74px;border-radius:50%;background:var(--primary-bg2);display:flex;align-items:center;justify-content:center;font-size:25px;font-weight:700;color:var(--primary);margin:0 auto 14px;border:3px solid var(--primary-bg)">{{ substr(auth()->user()->name,0,1) }}</div>
                <div style="font-size:17px;font-weight:700;color:var(--text)">{{ auth()->user()->name }}</div>
                <div style="font-size:12px;color:var(--text3);margin-top:3px">{{ $employee?->position }}</div>
                <span class="badge badge-hr" style="margin-top:8px">HR Admin</span>
                <div style="margin-top:18px;font-size:11.5px;color:var(--text3);text-align:left">
                    <div class="quick-stat"><span class="quick-stat-label">Employee ID</span><span style="font-family:var(--mono);font-size:11.5px;color:var(--primary);font-weight:600">{{ $employee?->employee_id }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Department</span><span>{{ $employee?->departmentRecord?->name }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Position</span><span>{{ $employee?->position }}</span></div>
                    <div class="quick-stat"><span class="quick-stat-label">Date Hired</span><span>{{ $employee?->date_hired?->format('M d, Y') }}</span></div>
                </div>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:18px">
            <div class="card">
                <div class="card-header"><span class="card-title">Personal Information</span></div>
                <div class="card-body">
                    <form class="form" method="POST" action="{{ route('profile.update') }}">@csrf @method('PUT')
                        <div><label>First Name</label><input name="first_name" value="{{ old('first_name', $employee?->first_name) }}" required></div>
                        <div><label>Last Name</label><input name="last_name" value="{{ old('last_name', $employee?->last_name) }}" required></div>
                        <div><label>Email</label><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></div>
                        <div><label>Phone</label><input name="phone" value="{{ old('phone', $employee?->phone) }}"></div>
                        <div class="full"><label>Address</label><input name="address" value="{{ old('address', $employee?->address) }}"></div>
                        <input type="hidden" name="contact_info" value="{{ $employee?->contact_info }}">
                        <div class="full"><button class="btn btn-primary btn-sm">Save Changes</button></div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><span class="card-title">Change Password</span></div>
                <div class="card-body">
                    <form class="form" method="POST" action="{{ route('profile.password') }}">@csrf @method('PUT')
                        <div><label>Current Password</label><input type="password" name="current_password" required></div>
                        <div><label>New Password</label><input type="password" name="password" required></div>
                        <div class="full"><label>Confirm New Password</label><input type="password" name="password_confirmation" required></div>
                        <div class="full"><button class="btn btn-outline btn-sm">Update Password</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card" style="margin-top:18px">
        <div class="card-header"><span class="card-title">Leave Balance</span>
            <form method="GET" class="page-actions">
                <select name="leave_type_id" onchange="this.form.submit()"><option value="">All Leave Types</option>@foreach($leaveTypes as $type)<option value="{{ $type->id }}" @selected($selectedTypeId === $type->id)>{{ $type->name }}</option>@endforeach</select>
                <select name="year" onchange="this.form.submit()"><option>{{ now()->year }}</option><option>{{ now()->year - 1 }}</option></select>
            </form>
        </div>
        <div class="table-wrap"><table><thead><tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th></tr></thead><tbody>
            @foreach($employee?->leaveBalances ?? [] as $balance)
                <tr><td>{{ $balance->leaveType->name }}</td><td>{{ (int) $balance->allocated_days }}</td><td>{{ (int) $balance->used_days }}</td><td>{{ (int) $balance->remaining_days }}</td></tr>
            @endforeach
        </tbody></table></div>
    </div>
</div>
@endsection
