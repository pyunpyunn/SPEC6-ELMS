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
    $unreadNotifications = $currentUser?->notifications()->whereNull('read_at')->count() ?? 0;
    $accountApproved = $currentUser?->status === 'active';
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
</head>
<body class="employee-shell-body">
<div class="app">
    <aside class="sidebar">
        <div class="sb-top">
            <div class="sb-logo" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h10l4 5v13H3V3h4Z"/><path d="M7 3v5h14"/><path d="M8 14h8"/><path d="M8 18h5"/></svg>
            </div>
            <span class="sb-brand">Employee Portal</span>
        </div>
        <nav class="sb-nav" aria-label="Employee navigation">
            <div class="sb-section">Main</div>
            @if($accountApproved)
                <a class="sb-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
                <div class="sb-section">Leave Control</div>
                <a class="sb-item {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}" href="{{ route('employee.leaves.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                    <span>My Leave</span>
                </a>
                <div class="sb-section">Insights</div>
                <a class="sb-item {{ request()->routeIs('employee.reports') || request()->routeIs('employee.leave-balances') ? 'active' : '' }}" href="{{ route('employee.reports') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
                    <span>My Reports</span>
                </a>
            @endif
            <a class="sb-item {{ request()->routeIs('employee.profile') ? 'active' : '' }}" href="{{ route('employee.profile') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>My Profile</span>
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
            @if($accountApproved)
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
            @else
                <div class="flash flash-warn" style="margin:12px 0 0">Waiting for HR approval.</div>
            @endif
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
                <div class="profile-area" onclick="toggleProfileMenu()">
                    <div class="profile-avatar">{{ $initials }}</div>
                    <div class="profile-info">
                        <div class="pname">{{ $currentUser->name ?? 'Employee' }}</div>
                        <div class="prole">{{ str_replace('_', ' ', ucfirst($currentUser->role ?? 'employee')) }}</div>
                    </div>
                </div>
                <div class="profile-dropdown" id="employeeProfileDropdown">
                    <a href="{{ route('employee.profile') }}">My Profile</a>
                    @if($accountApproved)
                        <a href="{{ route('employee.notifications') }}">Notifications <span data-notification-count-text data-prefix="(" data-suffix=")">@if($unreadNotifications)({{ $unreadNotifications }})@endif</span></a>
                    @endif
                    <div class="pdivider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </div>
                @if($accountApproved)
                    <a href="{{ route('employee.notifications') }}" class="btn btn-outline btn-sm">Notifications<span data-notification-count-text>@if($unreadNotifications) {{ $unreadNotifications }} @endif</span></a>
                @endif
            </div>
        </header>
        <main class="content">
            @if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="flash flash-warn">{{ session('warning') }}</div>@endif
            @if(! $accountApproved)
                <div class="flash flash-warn">Your account is not approved yet. You can view your profile, but HR must activate your account before dashboard, leave, reports, and notifications become available.</div>
            @endif
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
document.addEventListener('click', event => {
  if (!event.target.closest('.profile-area') && !event.target.closest('#employeeProfileDropdown')) {
    document.getElementById('employeeProfileDropdown')?.classList.remove('open');
  }
  if (event.target.classList.contains('modal-overlay')) {
    event.target.classList.remove('open');
  }
});
async function refreshNotifications() {
  if (!{{ $accountApproved ? 'true' : 'false' }}) return;

  try {
    const response = await fetch(@json(route('notifications.feed')), {
      headers: {'Accept': 'application/json'},
      credentials: 'same-origin'
    });

    if (!response.ok) return;

    const data = await response.json();
    const count = Number(data.unread_count || 0);

    document.querySelectorAll('[data-notification-count-text]').forEach(el => {
      const prefix = el.dataset.prefix || '';
      const suffix = el.dataset.suffix || '';
      el.textContent = count > 0 ? `${prefix}${count}${suffix}` : '';
    });
  } catch (error) {
    // Keep the existing rendered count if the background refresh fails.
  }
}
refreshNotifications();
setInterval(refreshNotifications, 5000);
</script>
</body>
</html>
