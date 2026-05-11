@extends('hr.layout')

@section('content')
<div class="page active" id="page-verification">
    <div class="page-header">
        <div><h1>User Verification</h1><p>Review and activate newly registered accounts</p></div>
    </div>
    <div class="flash flash-warning">
        {{ $pendingUsers->total() }} registered account(s) are pending verification. Employees are verified by matching their <strong>Employee ID</strong> to confirm company membership before granting access.
    </div>
    <div class="tab-bar">
        <span class="tab-item active">Pending <span class="badge badge-pending" style="margin-left:4px">{{ $pendingUsers->total() }}</span></span>
        <span class="tab-item">All Users</span>
    </div>
    <div class="tab-pane active">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Employee ID</th><th>Email</th><th>Registered</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($pendingUsers as $user)
                    <tr>
                        <td><div class="td-name">{{ $user->name }}</div><div class="td-sub">Self-registered</div></td>
                        <td><span style="font-family:var(--mono);font-size:11.5px;color:var(--primary);font-weight:600">{{ $user->pending_employee_id ?: 'Needs ID' }}</span></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('M d, g:i A') }}</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                        <td>
                            <details>
                                <summary class="btn btn-sm btn-primary">Activate</summary>
                                <form method="POST" action="{{ route('hr.users.activate', $user) }}" class="form" style="margin-top:12px;min-width:520px">@csrf
                                    <div><label>Employee ID</label><input name="employee_id" value="{{ $user->pending_employee_id }}" required></div>
                                    <div><label>Email</label><input value="{{ $user->email }}" readonly style="background:var(--surface2)"></div>
                                    <div><label>Assign Role</label><select name="role" required><option value="employee">Employee</option><option value="manager">Manager</option><option value="hr_admin">HR Admin</option></select></div>
                                    <div><label>Department</label><select name="department_id" required>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></div>
                                    <div><label>Position</label><input name="position" placeholder="e.g. Senior Developer" required></div>
                                    <div><label>Date Hired</label><input type="date" name="date_hired" value="{{ now()->toDateString() }}" required></div>
                                    <input type="hidden" name="manager_id" value="">
                                    <div class="full"><button class="btn btn-primary btn-sm">Activate Account</button></div>
                                </form>
                            </details>
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

    <div class="card" style="margin-top:18px">
        <div class="card-header"><span class="card-title">All Users</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Employee ID</th><th>Email</th><th>Role</th><th>Department · Position</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($allUsers as $user)
                    <tr>
                        <td><div class="td-name">{{ $user->name }}</div></td>
                        <td><span style="font-family:var(--mono);font-size:11px">{{ $user->employee?->employee_id ?? $user->pending_employee_id ?? 'Not assigned' }}</span></td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-{{ $user->role === 'hr_admin' ? 'hr' : ($user->role === 'manager' ? 'manager' : 'emp') }}">{{ ucfirst(str_replace('_',' ', $user->role)) }}</span></td>
                        <td><div>{{ $user->employee?->departmentRecord?->name }}</div><div class="td-pos">{{ $user->employee?->position }}</div></td>
                        <td><span class="badge badge-{{ $user->status }}">{{ ucfirst($user->status) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $allUsers->links() }}</div>
    </div>
</div>
@endsection
