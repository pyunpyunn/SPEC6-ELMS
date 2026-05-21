@php
    $currentUser = auth()->user();
    $employee = $currentUser?->employee;
    $initials = collect(explode(' ', trim($currentUser->name ?? 'Employee')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'EM';
    $departmentCode = $employee?->department ?? 'Employee';
    $balancesForShell = $employeeLeaveBalances ?? $leaveTypes ?? collect();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Employee Portal') - Employee Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('employee-prototype.css') }}">
    <script>
        (function () {
            const storedTheme = localStorage.getItem('employee-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = storedTheme || (prefersDark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.classList.toggle('employee-sidebar-collapsed', localStorage.getItem('employee-sidebar-collapsed') === '1');
        })();
    </script>
</head>
<body class="employee-shell-body">
<div class="app">
    <aside class="sidebar" id="employeeSidebar">
        <div class="sb-top">
            <button class="sb-logo sb-toggle" type="button" onclick="toggleEmployeeSidebar()" aria-label="Minimize sidebar" aria-expanded="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h10l4 5v13H3V3h4Z"/><path d="M7 3v5h14"/><path d="M8 14h8"/><path d="M8 18h5"/></svg>
            </button>
            <span class="sb-brand">Employee Portal</span>
        </div>
        <nav class="sb-nav" aria-label="Employee navigation">
            <div class="sb-section">Main</div>
            <a class="sb-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}" title="Dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Dashboard</span>
            </a>
            <div class="sb-section">Leave Control</div>
            <a class="sb-item {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}" href="{{ route('employee.leaves.index') }}" title="My Leave">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                <span>My Leave</span>
            </a>
            <div class="sb-section">Insights</div>
            <a class="sb-item {{ request()->routeIs('employee.reports') || request()->routeIs('employee.leave.balances') ? 'active' : '' }}" href="{{ route('employee.reports') }}" title="My Reports">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
                <span>My Reports</span>
            </a>
        </nav>
        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar">{{ $initials }}</div>
                <div class="sb-user-info">
                    <div class="sbun">{{ $currentUser->name ?? 'Employee' }}</div>
                    <div class="sbur">{{ str_replace('_', ' ', ucfirst($currentUser->role ?? 'employee')) }}</div>
                    <div class="sbdept">{{ $departmentCode }}</div>
                </div>
            </div>
            <div class="sb-leave-balance">
                <div class="sb-balance-title">
                    <span>Leave Balance</span>
                    <select class="sb-balance-filter" id="sbBalanceFilter" onchange="filterSidebarBalance(this.value)">
                        <option value="all">All</option>
                        @foreach($balancesForShell as $balance)
                            <option value="{{ \Illuminate\Support\Str::slug($balance->name) }}">{{ $balance->name }}</option>
                        @endforeach
                    </select>
                </div>
                @forelse($balancesForShell->take(4) as $balance)
                    @php
                        $used = $balance->used_days ?? 0;
                        $total = $balance->total_days ?? 0;
                        $pct = $total > 0 ? min(100, round(($used / $total) * 100)) : 0;
                    @endphp
                    <div class="sb-balance-row" data-balance-type="{{ \Illuminate\Support\Str::slug($balance->name) }}">
                    <div class="sb-balance-item">
                        <span class="sb-balance-label">{{ $balance->name }}</span>
                        <span class="sb-balance-val">{{ $used }}/{{ $total }}</span>
                    </div>
                    <div class="sb-lb-bar"><div class="sb-lb-fill {{ $pct > 70 ? 'danger' : ($pct > 45 ? 'warn' : '') }}" style="width: {{ $pct }}%"></div></div>
                    </div>
                @empty
                    <div class="sb-balance-item">
                        <span class="sb-balance-label">No balances yet</span>
                    </div>
                @endforelse
            </div>
        </div>
    </aside>

    <div class="main-area">
        <header class="header">
            <div class="header-left">
                <div class="brand">
                    <div class="brand-logo" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h10l4 5v13H3V3h4Z"/><path d="M7 3v5h14"/><path d="m8 15 2 2 5-5"/></svg>
                    </div>
                    <span class="brand-name">Employee <span>Portal</span></span>
                </div>
            </div>
            <div class="header-right">
                <button id="themeToggleBtn" class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
                    <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="5"></circle>
                        <path d="M12 1v2m0 18v2m11-11h-2M3 12H1m16.95 7.07-1.41-1.41M6.34 6.34 4.93 4.93m12.02 0-1.41 1.41M6.34 17.66l-1.41 1.41"></path>
                    </svg>
                    <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"></path>
                    </svg>
                </button>
                <div class="profile-area" onclick="toggleProfileMenu()">
                    <div class="profile-avatar">{{ $initials }}</div>
                    <div class="profile-info">
                        <div class="pname">{{ $currentUser->name ?? 'Employee' }}</div>
                        <div class="prole">{{ str_replace('_', ' ', ucfirst($currentUser->role ?? 'employee')) }}</div>
                    </div>
                </div>
                <div class="profile-dropdown" id="employeeProfileDropdown">
                    <a href="{{ route('employee.profile') }}">My Profile</a>
                    <a href="{{ route('employee.notifications') }}">Notifications</a>
                    <div class="pdivider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </div>
               @auth
    <a href="{{ route('employee.notifications') }}" class="notification-icon">
        <svg xmlns="http://www.w3.org/2000/svg"
             width="24" 
             height="24"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.8"
             stroke-linecap="round"
             stroke-linejoin="round">

            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/>
            <path d="M9 17a3 3 0 0 0 6 0"/>
        </svg>
    </a>
@endauth
            </div>
        </header>
        <main class="content">
            @yield('content')
        </main>
    </div>
</div>
<script>
function filterSidebarBalance(type) {
  document.querySelectorAll('.sb-balance-row').forEach(row => {
    row.style.display = type === 'all' || row.dataset.balanceType === type ? '' : 'none';
  });
}
function setEmployeeSidebarCollapsed(collapsed) {
  const sidebar = document.getElementById('employeeSidebar');
  const toggle = document.querySelector('.sb-toggle');

  document.documentElement.classList.toggle('employee-sidebar-collapsed', collapsed);
  sidebar?.classList.toggle('collapsed', collapsed);
  localStorage.setItem('employee-sidebar-collapsed', collapsed ? '1' : '0');

  if (toggle) {
    toggle.setAttribute('aria-expanded', String(!collapsed));
    toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Minimize sidebar');
  }
}
function toggleEmployeeSidebar() {
  setEmployeeSidebarCollapsed(!document.documentElement.classList.contains('employee-sidebar-collapsed'));
}
function toggleProfileMenu() {
  document.getElementById('employeeProfileDropdown').classList.toggle('open');
}
function openModal(id) {
  document.getElementById(id)?.classList.add('open');
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('open');
}
document.addEventListener('click', event => {
  if (!event.target.closest('.profile-area') && !event.target.closest('#employeeProfileDropdown')) {
    document.getElementById('employeeProfileDropdown')?.classList.remove('open');
  }
  if (event.target.classList.contains('modal-overlay')) {
    event.target.classList.remove('open');
  }
});
function setEmployeeTheme(theme) {
  const isDark = theme === 'dark';
  const toggle = document.getElementById('themeToggleBtn');

  document.documentElement.classList.toggle('dark', isDark);
  document.body.classList.toggle('theme-dark', isDark);
  document.body.classList.toggle('theme-light', !isDark);
  localStorage.setItem('employee-theme', theme);

  if (toggle) {
    toggle.setAttribute('aria-pressed', String(isDark));
    toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  setEmployeeSidebarCollapsed(localStorage.getItem('employee-sidebar-collapsed') === '1');

  const storedTheme = localStorage.getItem('employee-theme');
  const isDark = document.documentElement.classList.contains('dark');
  setEmployeeTheme(storedTheme || (isDark ? 'dark' : 'light'));

  document.getElementById('themeToggleBtn')?.addEventListener('click', function() {
    setEmployeeTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
  });
});
</script>

@stack('scripts')
</body>
</html>
