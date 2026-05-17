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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('hr-prototype.css') }}">
    <style>
        .dark .sidebar{background:#1b241f}
        .dark .sb-item:hover{background:rgba(255,255,255,.08)}
        .dark .sb-item.active{background:rgba(255,255,255,.13)}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="sb-top">
            <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round">
                    <line x1="4" y1="6" x2="20" y2="6"></line>
                    <line x1="4" y1="12" x2="20" y2="12"></line>
                    <line x1="4" y1="18" x2="20" y2="18"></line>
                </svg>
            </button>
            <div class="sb-brand">NAV ELMS</div>
        </div>

        <nav class="sb-nav">
            <div class="sb-section">Main</div>

            @if($accountApproved)
                <a class="sb-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span>Dashboard</span>
                    <span class="sb-item-tooltip">Dashboard</span>
                </a>

                <a class="sb-item {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}" href="{{ route('employee.leaves.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/>
                        <path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>
                    </svg>
                    <span>My Leave</span>
                    <span class="sb-item-tooltip">My Leave</span>
                </a>

                <a class="sb-item {{ request()->routeIs('employee.reports') || request()->routeIs('employee.leave-balances') ? 'active' : '' }}" href="{{ route('employee.reports') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 2v4"/><path d="M16 2v4"/>
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/>
                    </svg>
                    <span>My Reports</span>
                    <span class="sb-item-tooltip">My Reports</span>
                </a>
            @endif

            <div class="sb-section">Personal</div>
            <a class="sb-item {{ request()->routeIs('employee.profile') ? 'active' : '' }}" href="{{ route('employee.profile') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>My Profile</span>
                <span class="sb-item-tooltip">My Profile</span>
            </a>
        </nav>

        <div class="sb-footer">
            <div class="sb-leave-balance">
                <div class="sb-balance-title">
                    <span>Leave Balance</span>
                    <select class="sb-balance-filter" id="sbBalanceFilter" onchange="filterSidebarBalance(this.value)">
                        <option value="all">All Types</option>
                        @foreach($balancesForShell as $balance)
                            <option value="{{ \Illuminate\Support\Str::slug($balance->name) }}">{{ $balance->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="sidebarBalanceItems">
                    @if($accountApproved)
                        @forelse($balancesForShell->take(4) as $balance)
                            <div class="sb-balance-item" data-balance-type="{{ \Illuminate\Support\Str::slug($balance->name) }}">
                                @php
                                    $used = $balance->used_days ?? 0;
                                    $total = $balance->total_days ?? 0;
                                    $pct = $total > 0 ? min(100, round(($used / $total) * 100)) : 0;
                                @endphp
                                <span class="sb-balance-label">{{ $balance->name }}</span>
                                <span class="sb-balance-val">{{ $used }}/{{ $total }}</span>
                                <div class="sb-lb-bar">
                                    <div class="sb-lb-fill {{ $pct > 70 ? 'danger' : ($pct > 45 ? 'warn' : '') }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="sb-balance-item" data-balance-type="all">
                                <span class="sb-balance-label">No balances yet</span>
                                <span class="sb-balance-val">0/0</span>
                            </div>
                        @endforelse
                    @else
                        <div class="flash flash-warn" style="margin:12px 0 0">Waiting for HR approval.</div>
                    @endif
                </div>
            </div>
        </div>
    </aside>

    <div class="main-area">
        <header class="header">
            <div class="header-left">
                <div class="brand">
                    <div class="brand-logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                    <div class="brand-name">NAV Employee Leave Management System</div>
                </div>
            </div>

            <div class="header-right">
                <div class="notif-wrap">
                    <button class="notif-btn" type="button" onclick="toggleNotifications()" title="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notif-badge" data-notification-count style="{{ $unreadNotifications ? '' : 'display:none' }}">{{ $unreadNotifications }}</span>
                    </button>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>Notifications</h4>
                            @if($accountApproved)
                                <a class="notif-mark" href="{{ route('employee.notifications') }}">View all</a>
                            @else
                                <span class="notif-mark" style="opacity:.7;pointer-events:none">View all</span>
                            @endif
                        </div>

                        <div class="notif-list" data-notification-list>
                            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notice)
                                <a class="notif-item {{ $notice->read_at ? '' : 'unread' }}" href="{{ route('employee.notifications.read', $notice) }}">
                                    <span class="notif-dot"></span>
                                    <span class="notif-content">
                                        <p>{{ $notice->title }}</p>
                                        <span>{{ $notice->created_at->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="notif-item">
                                    <span class="notif-content"><p>No notifications yet.</p></span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <button class="dark-btn" type="button" onclick="toggleDark()" title="Dark Mode">
                    <svg id="dark-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>

                <div class="header-divider"></div>

                <div class="profile-area" onclick="toggleProfile()">
                    <div class="profile-avatar">{{ $initials }}</div>
                    <div class="profile-info">
                        <div class="pname">{{ $currentUser->name ?? 'Employee' }}</div>
                        <div class="prole">{{ str_replace('_', ' ', ucfirst($currentUser->role ?? 'employee')) }}</div>
                    </div>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('employee.profile') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        My Profile
                    </a>

                    <div class="pdivider"></div>

                    @if($accountApproved)
                        <a href="{{ route('employee.notifications') }}">
                            Notifications
                            <span data-notification-count-text>{{ $unreadNotifications ? ' ' . $unreadNotifications . ' ' : '' }}</span>
                        </a>
                        <div class="pdivider"></div>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout" style="width:100%;text-align:left;background:none;border:0;padding:10px 14px;font-size:15px;color:var(--danger)" type="submit">Logout</button>
                    </form>
                </div>
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
function toggleSidebar(){document.getElementById('sidebar')?.classList.toggle('collapsed')}
function toggleProfile(){document.getElementById('profileDropdown')?.classList.toggle('open')}
function toggleNotifications(){document.getElementById('notifDropdown')?.classList.toggle('open')}
function toggleDark(){
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('elms-dark', document.documentElement.classList.contains('dark') ? '1' : '0');
}
if(localStorage.getItem('elms-dark')==='1'){document.documentElement.classList.add('dark')}

function filterSidebarBalance(type){
    document.querySelectorAll('#sidebarBalanceItems .sb-balance-item').forEach(function(item){
        const balanceType = item.dataset.balancetype || item.dataset.balanceType;
        item.style.display = (!type || type === 'all' || balanceType === 'lt-' + type) ? '' : 'none';
    });
}

document.addEventListener('click',function(e){
    if(!e.target.closest('.profile-area') && !e.target.closest('#profileDropdown')){
        document.getElementById('profileDropdown')?.classList.remove('open')
    }
    if(!e.target.closest('.notif-wrap') && !e.target.closest('#notifDropdown')){
        document.getElementById('notifDropdown')?.classList.remove('open')
    }
})

document.addEventListener('DOMContentLoaded',function(){
    const filter = document.getElementById('sbBalanceFilter');
    if(filter){ filterSidebarBalance(filter.value); }
})

function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g,function(char){
        return {'&':'&','<':'<','>':'>','"':'"',"'":'&#039;'}[char];
    })
}

async function refreshNotifications(){
    try{
        if(!{{ $accountApproved ? 'true' : 'false' }}) return;
        const response = await fetch(@json(route('notifications.feed')), {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin'
        });
        if(!response.ok) return;
        const data = await response.json();
        const count = Number(data.unread_count || 0);

        document.querySelectorAll('[data-notification-count]').forEach(function(badge){
            badge.textContent = count;
            badge.style.display = count>0 ? 'flex' : 'none';
        });

        document.querySelectorAll('[data-notification-list]').forEach(function(list){
            if(!Array.isArray(data.notifications) || data.notifications.length===0){
                list.innerHTML='<div class="notif-item"><span class="notif-content"><p>No notifications yet.</p></span></div>';
                return;
            }
            list.innerHTML=data.notifications.map(function(notice){
                return '<a class="notif-item '+(notice.unread?'unread':'')+'" href="'+escapeHtml(notice.read_url)+'"><span class="notif-dot"></span><span class="notif-content"><p>'+escapeHtml(notice.title)+'</p><span>'+escapeHtml(notice.created_at)+'</span></span></a>';
            }).join('');
        });
    }catch(error){
        // Keep server-rendered notifications.
    }
}
refreshNotifications();
setInterval(refreshNotifications,5000);
</script>

@stack('scripts')
</body>
</html>
