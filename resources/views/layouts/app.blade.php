<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - LeaveFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('employee-prototype.css') }}">
</head>
<body class="app-layout-body">
    <nav class="app-topbar">
        <div class="brand">
            <div class="brand-logo" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h10l4 5v13H3V3h4Z"/><path d="M7 3v5h14"/><path d="m8 15 2 2 5-5"/></svg>
            </div>
            <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="brand-name">Leave<span>Flow</span></a>
        </div>
        <div class="page-actions">
            @auth
                <span class="badge badge-emp">{{ auth()->user()->name }} · {{ str_replace('_', ' ', auth()->user()->role) }}</span>

                @if(Route::has('dashboard.employee'))
                    <a href="{{ route('dashboard.employee') }}" class="btn btn-outline btn-sm">Dashboard</a>
                @endif

                @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                    </form>
                @endif
            @else
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Login</a>
                @endif

                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                @endif
            @endauth
        </div>
    </nav>

    <div class="app-container">
        @yield('content')
    </div>
</body>
</html>
