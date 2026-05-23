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
        .header-notif {
            position: relative;
        }
        .notif-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--danger, #b91c1c);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            line-height: 16px;
            text-align: center;
            border: 2px solid var(--surface, #fff);
        }
        .header-divider {
            width: 1px;
            height: 24px;
            background-color: rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="employee-shell-body">
<div class="app">
    <aside class="sidebar" id="employeeSidebar">
        <div class="sb-top">
            <button class="sb-logo sb-toggle" type="button" onclick="toggleEmployeeSidebar()" title="Toggle navigation" aria-label="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
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
                <div class="header-actions">
                    @auth
                        <a href="{{ route('employee.notifications') }}" class="header-icon-btn header-notif" title="Notifications">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            <span class="notif-badge" style="{{ ($unread_notification_count ?? 0) ? '' : 'display:none' }}">{{ $unread_notification_count ?? 0 }}</span>
                        </a>
                    @endauth
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
            @if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="flash flash-warning">{{ session('warning') }}</div>@endif
            @if(session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="flash flash-error">{{ $errors->first() }}</div>@endif
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
function setEmployeeSidebarCollapsed(collapsed) {
  const sidebar = document.getElementById('employeeSidebar');
  document.documentElement.classList.toggle('employee-sidebar-collapsed', collapsed);
  sidebar?.classList.toggle('collapsed', collapsed);
  localStorage.setItem('employee-sidebar-collapsed', collapsed ? 'true' : 'false');
}
function toggleEmployeeSidebar() {
  setEmployeeSidebarCollapsed(!document.documentElement.classList.contains('employee-sidebar-collapsed'));
}
function toggleSidebar() {
  toggleEmployeeSidebar();
}
window.addEventListener('DOMContentLoaded', function() {
  const stored = localStorage.getItem('employee-sidebar-collapsed') ?? localStorage.getItem('sidebarCollapsed');
  setEmployeeSidebarCollapsed(stored === 'true');
  localStorage.removeItem('sidebarCollapsed');
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
