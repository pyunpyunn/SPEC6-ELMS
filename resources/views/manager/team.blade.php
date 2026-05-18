@extends('manager.layout')

@section('title', 'Team Overview')
@section('page_title', 'Team Overview')

@section('content')
<div class="card" style="padding:0">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee</th><th>Position</th><th>Status</th><th>Vacation Remaining</th><th>Sick Remaining</th></tr></thead>
            <tbody>
            @forelse($teamMembers as $member)
                @php($todayLeave = $member->leaveApplications->first(fn($leave) => $leave->status === 'approved' && $leave->start_date->lte(now()) && $leave->end_date->gte(now())))
                @php($vacation = $member->leaveBalances->first(fn($b) => str_contains(strtolower($b->leaveType?->name ?? ''), 'vacation')))
                @php($sick = $member->leaveBalances->first(fn($b) => str_contains(strtolower($b->leaveType?->name ?? ''), 'sick')))
                <tr>
                    <td><strong>{{ $member->full_name }}</strong><div class="muted">{{ $member->employee_id }}</div></td>
                    <td>{{ $member->position }}</td>
                    <td><span class="status-pill {{ $todayLeave ? 'status-pending' : 'status-approved' }}">{{ $todayLeave ? 'On Leave' : 'Active' }}</span></td>
                    <td>{{ $vacation ? (int) $vacation->remaining_days.' / '.(int) $vacation->allocated_days : 'N/A' }}</td>
                    <td>{{ $sick ? (int) $sick->remaining_days.' / '.(int) $sick->allocated_days : 'N/A' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No active team members assigned.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">{{ $teamMembers->links() }}</div>
@endsection
