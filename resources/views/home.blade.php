@extends('layouts.employee')

@section('title', 'Home')

@section('content')
@php
    $user = auth()->user();
    $employee = $user?->employee;
@endphp

<div class="page-header">
    <div>
        <h1>Welcome, {{ $user->name }}</h1>
        <p>Your employee workspace for leave filing, balances, notifications, and profile details.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('dashboard.employee') }}" class="btn btn-primary">Employee Dashboard</a>
        <a href="{{ route('employee.profile') }}" class="btn btn-outline">My Profile</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-value">EP</div>
                <div class="stat-label">Employee Portal</div>
            </div>
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-value">{{ $employee ? 'Yes' : 'No' }}</div>
                <div class="stat-label">Employee Profile Linked</div>
            </div>
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="cards">
    <div class="card">
        <div class="card-header"><span class="card-title">Leave Actions</span></div>
        <div class="card-body">
            <p class="td-sub">Check your balance or file a new leave request.</p>
            <div class="page-actions" style="margin-top:14px">
                <a href="{{ route('dashboard.employee') }}" class="btn btn-primary">Open Leave Dashboard</a>
                <a href="{{ route('employee.leave.create') }}" class="btn btn-outline">Apply Leave</a>
                <a href="{{ route('employee.profile') }}" class="btn btn-outline">Employee Profile</a>
            </div>
        </div>
    </div>
</div>
@endsection
