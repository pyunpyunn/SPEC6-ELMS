@extends('layouts.app')

@section('content')
<style>
    body { background: #f3f6f4; }
    .form-dark { background: #fff; color: #172b22; border: 1px solid #dde5e0; }
    .form-dark input, .form-dark select {
        background: #eef2ef; color: #172b22; border: 1px solid #c6d5cc; border-radius: 7px;
    }
    .form-dark input:focus, .form-dark select:focus {
        border-color: #2a6349; outline: none;
    }
    .form-label { color: #42614f; font-weight: 600; }
    .form-title { color: #2a6349; font-size: 2rem; font-weight: bold; }
    .form-link { color: #2a6349; }
    .form-link:hover { text-decoration: underline; }
</style>
<div class="flex min-h-screen items-center justify-center" style="background: #f3f6f4;">
    <div class="form-dark p-10 rounded-lg shadow-lg w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="form-title mb-2">Register</div>
            <div class="td-sub" style="color:#78957f">Create your employee account for verification</div>
        </div>
        @if ($errors->any())
            <div class="badge badge-rejected mb-4">Please review the highlighted fields</div>
            <ul class="mb-4 text-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <form method="POST" action="{{ route('register') }}" class="form-grid single">
            @csrf
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" required autofocus value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <div class="form-group">
                <label class="form-label">Preferred Department</label>
                <select name="department" required>
                    <option value="">Select department</option>
                    <option value="People Operations">People Operations</option>
                    <option value="IT">IT</option>
                    <option value="Finance">Finance</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Operations">Operations</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-full mt-2">Register</button>
        </form>
        <div class="td-sub mt-6 text-center">
            <a href="{{ route('login') }}" class="form-link">Already have an account? Login</a>
        </div>
    </div>
</div>
@endsection
