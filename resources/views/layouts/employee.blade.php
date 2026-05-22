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
    <style>
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            color: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            width: 36px;
            height: 36px;
        }
        .header-icon-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .header-icon-btn svg {
            width: 20px;
            height: 20px;
        }
        .header-divider {
            width: 1px;
            height: 24px;
            background-color: rgba(0, 0, 0, 0.1);
        }
        body.dark .header-icon-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        body.dark .header-divider {
            background-color: rgba(255, 255, 255, 0.1);
        }
        /* Sidebar Toggle Styles */
        .sidebar {
            transition: width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 96px;
        }
        .sidebar.collapsed .sb-brand,
        .sidebar.collapsed .sb-section,
        .sidebar.collapsed .sb-item span:not(.sb-item-tooltip),
        .sidebar.collapsed .sb-balance-title span,
        .sidebar.collapsed .sb-balance-label,
        .sidebar.collapsed .sb-balance-val,
        .sidebar.collapsed .sb-user-info {
            display: none;
        }
        .sidebar.collapsed .sb-item-tooltip {
            display: block;
        }
        .sb-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .menu-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: inherit;
            transition: opacity 0.2s;
            flex-shrink: 0;
            border-radius: 6px;
        }
        .menu-toggle:hover {
            opacity: 0.7;
            background-color: rgba(0, 0, 0, 0.05);
        }
        .menu-toggle svg {
            width: 24px;
            height: 24px;
        }
        .sb-item {
            position: relative;
        }
        .sb-item-tooltip {
            display: none;
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            white-space: nowrap;
            margin-left: 12px;
            padding: 6px 12px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            border-radius: 4px;
            font-size: 12px;
            z-index: 50;
            pointer-events: none;
        }
        .sidebar.collapsed .sb-item:hover .sb-item-tooltip {
            display: block;
        }
        .sb-balance-row {
            transition: opacity 0.3s ease;
        }
        .sidebar.collapsed .sb-balance-row {
            opacity: 0;
            pointer-events: none;
        }
        body.dark .menu-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="employee-shell-body">
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="sb-top">
            <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
            </button>
            <span class="sb-brand">Employee Portal</span>
        </div>
        <nav class="sb-nav" aria-label="Employee navigation">
            <div class="sb-section">Main</div>
            <a class="sb-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Dashboard</span><span class="sb-item-tooltip">Dashboard</span>
            </a>
            <div class="sb-section">Leave Control</div>
            <a class="sb-item {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}" href="{{ route('employee.leaves.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                <span>My Leave</span><span class="sb-item-tooltip">My Leave</span>
            </a>
            <div class="sb-section">Insights</div>
            <a class="sb-item {{ request()->routeIs('employee.reports') || request()->routeIs('employee.leave.balances') ? 'active' : '' }}" href="{{ route('employee.reports') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
                <span>My Reports</span><span class="sb-item-tooltip">My Reports</span>
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
                <div class="header-actions">
                    @auth
                        <a href="{{ route('employee.notifications') }}" class="header-icon-btn" title="Notifications">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </a>
                    @endauth
                    <button class="header-icon-btn" onclick="toggleDarkMode()" title="Toggle dark mode" id="darkModeToggle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="moonIcon">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="sunIcon" style="display:none;">
                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                    </button>
                </div>
                <div class="header-divider"></div>
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
function toggleProfileMenu() {
  document.getElementById('employeeProfileDropdown').classList.toggle('open');
}
function openModal(id) {
  document.getElementById(id)?.classList.add('open');
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('open');
}
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('collapsed');
  localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
}
function toggleDarkMode() {
  const body = document.body;
  const isDark = body.classList.toggle('dark');
  const moonIcon = document.getElementById('moonIcon');
  const sunIcon = document.getElementById('sunIcon');
  
  if (isDark) {
    moonIcon.style.display = 'none';
    sunIcon.style.display = 'block';
    localStorage.setItem('theme', 'dark');
  } else {
    moonIcon.style.display = 'block';
    sunIcon.style.display = 'none';
    localStorage.setItem('theme', 'light');
  }
}
// Initialize dark mode from localStorage and restore sidebar state
window.addEventListener('DOMContentLoaded', function() {
  const savedTheme = localStorage.getItem('theme');
  const moonIcon = document.getElementById('moonIcon');
  const sunIcon = document.getElementById('sunIcon');
  
  if (savedTheme === 'dark') {
    document.body.classList.add('dark');
    moonIcon.style.display = 'none';
    sunIcon.style.display = 'block';
  }
  
  // Restore sidebar collapsed state
  const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (sidebarCollapsed) {
    document.getElementById('sidebar').classList.add('collapsed');
  }
});
document.addEventListener('click', event => {
  if (!event.target.closest('.profile-area') && !event.target.closest('#employeeProfileDropdown')) {
    document.getElementById('employeeProfileDropdown')?.classList.remove('open');
  }
  if (event.target.classList.contains('modal-overlay')) {
    event.target.classList.remove('open');
  }
});
</script>

@stack('scripts')
</body>
</html>
