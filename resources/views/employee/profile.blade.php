@extends('layouts.employee')

@section('title', 'My Profile')

@section('content')
@php
    $employeeId = $employee ? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) : 'No profile';
@endphp

<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p>View-only employee profile information.</p>
    </div>
</div>

<div class="card employee-card">
    <div class="profile-hero">
        <div class="avatar-lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div>
            <h2 style="font-size:16px;font-weight:700">{{ auth()->user()->name }}</h2>
            <p class="td-sub">{{ $employeeId }} · {{ $employee->position ?? 'Employee' }}</p>
            <span class="badge badge-active mt8">Active</span>
        </div>
    </div>
    <div class="card-body">
        <div class="cards">
            <div class="detail-list">
                <div class="detail-row"><span class="dl">Employee ID</span><span class="dv font-mono">{{ $employeeId }}</span></div>
                <div class="detail-row"><span class="dl">Full Name</span><span class="dv">{{ auth()->user()->name }}</span></div>
                <div class="detail-row"><span class="dl">Email</span><span class="dv">{{ auth()->user()->email }}</span></div>
            </div>
            <div class="detail-list">
                <div class="detail-row"><span class="dl">Department</span><span class="dv">{{ $employee->department ?? '-' }}</span></div>
                <div class="detail-row"><span class="dl">Position</span><span class="dv">{{ $employee->position ?? '-' }}</span></div>
                <div class="detail-row"><span class="dl">Date Hired</span><span class="dv">{{ $employee?->date_hired ? \Illuminate\Support\Carbon::parse($employee->date_hired)->format('F d, Y') : '-' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
