@extends('layouts.employee')

@section('title', 'My Profile')

@section('content')
@php
    $employeeId = $employee?->employee_id ?? 'No profile';
    $accountApproved = auth()->user()->status === 'active';
@endphp

<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p>{{ $accountApproved ? 'View-only employee profile information.' : 'Temporary profile access while HR reviews your account.' }}</p>
    </div>
</div>

<div class="card employee-card">
    <div class="profile-hero">
        <div class="avatar-lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div>
            <h2 style="font-size:16px;font-weight:700">{{ auth()->user()->name }}</h2>
            <p class="td-sub">{{ $employeeId }} · {{ $employee?->position ?? 'Pending HR approval' }}</p>
            <span class="badge {{ $accountApproved ? 'badge-active' : 'badge-pending' }} mt8">{{ $accountApproved ? 'Active' : 'Pending HR Approval' }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="cards">
            <div class="detail-list">
                <div class="detail-row"><span class="dl">Employee ID</span><span class="dv font-mono">{{ $employeeId }}</span></div>
                <div class="detail-row"><span class="dl">Full Name</span><span class="dv">{{ auth()->user()->name }}</span></div>
                <div class="detail-row"><span class="dl">Email</span><span class="dv">{{ auth()->user()->email }}</span></div>
                <div class="detail-row"><span class="dl">Gender</span><span class="dv">{{ ucfirst($employee?->gender ?? 'Unspecified') }}</span></div>
            </div>
            <div class="detail-list">
                <div class="detail-row"><span class="dl">Department</span><span class="dv">{{ $employee?->department ?? '-' }}</span></div>
                <div class="detail-row"><span class="dl">Position</span><span class="dv">{{ $employee?->position ?? '-' }}</span></div>
                <div class="detail-row"><span class="dl">Date Hired</span><span class="dv">{{ $employee?->date_hired ? \Illuminate\Support\Carbon::parse($employee->date_hired)->format('F d, Y') : '-' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
