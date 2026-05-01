@extends('layouts.app')

@section('content')
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Welcome, {{ auth()->user()->name }}!</h1>
        
        <div class="mb-6 p-4 bg-gray-50 rounded border">
            <strong>Account Type:</strong> 
            <span class="capitalize text-blue-600">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- 1. HR ADMIN ONLY SECTION --}}
            @if(auth()->user()->role == 'hr_admin')
                <div class="p-6 bg-green-50 border border-green-200 rounded-lg">
                    <h2 class="font-bold text-green-800">HR Management</h2>
                    <p class="text-sm text-green-700 mb-4">Manage employee profiles and system settings.</p>
                    <a href="{{ route('employees.index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">View Employees</a>
                </div>
            @endif

            {{-- 2. MANAGER ONLY SECTION --}}
            @if(auth()->user()->role == 'manager')
                <div class="p-6 bg-purple-50 border border-purple-200 rounded-lg">
                    <h2 class="font-bold text-purple-800">Department Approval</h2>
                    <p class="text-sm text-purple-700 mb-4">Review pending leave applications from your team.</p>
                    <button class="bg-purple-600 text-white px-4 py-2 rounded">Review Requests</button>
                </div>
            @endif

            {{-- 3. EVERYONE (EMPLOYEE FEATURES) --}}
            <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                <h2 class="font-bold text-blue-800">Leave Actions</h2>
                <p class="text-sm text-blue-700 mb-4">Check your balance or file a new leave request.</p>
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Apply for Leave</button>
            </div>
        </div>
    </div>
@endsection