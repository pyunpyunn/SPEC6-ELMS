<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manager Portal') | LaraLeave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('manager-portal.css') }}">
</head>
<body>
@php
    $initials = collect(explode(' ', auth()->user()->name))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp
<div class="app">
<aside class="sidebar">
    <div class="sb-top">
        <div class="sb-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/><path d="M9 12h6M9 16h4"/></svg></div>
        <span class="sb-brand">LeaveFlow</span>
    </div>
    <nav class="sb-nav">
        <div class="sb-section">Management</div>
        <a class="sb-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}" href="{{ route('manager.dashboard') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg><span>Dashboard</span></a>
        <a class="sb-item {{ request()->routeIs('manager.approvals.*') ? 'active' : '' }}" href="{{ route('manager.approvals.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11h6M9 15h6"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/></svg><span>Approval Inbox</span>@if($pendingCount ?? 0)<span class="badge-dot">{{ $pendingCount }}</span>@endif</a>
        <a class="sb-item {{ request()->routeIs('manager.calendar') ? 'active' : '' }}" href="{{ route('manager.calendar') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><span>Team Calendar</span></a>
        <a class="sb-item {{ request()->routeIs('manager.team') ? 'active' : '' }}" href="{{ route('manager.team') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg><span>Team Overview</span></a>

        <div class="sb-section">Communication</div>
        <a class="sb-item {{ request()->routeIs('manager.notifications') ? 'active' : '' }}" href="{{ route('manager.notifications') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span>Notifications</span><span class="badge-dot" data-notification-count style="{{ ($unreadCount ?? 0) ? '' : 'display:none' }}">{{ $unreadCount ?? 0 }}</span></a>

        <div class="sb-section">Personal</div>
        <a class="sb-item {{ request()->routeIs('manager.my-leave') ? 'active' : '' }}" href="{{ route('manager.my-leave') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg><span>Apply for Leave</span></a>
        <a class="sb-item {{ request()->routeIs('manager.profile') ? 'active' : '' }}" href="{{ route('manager.profile') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>My Profile</span></a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <div class="sbun">{{ auth()->user()->name }}</div>
                <div class="sbur">{{ $manager?->position ?? 'Manager' }}</div>
                <div class="sbdept">{{ $department?->name ?? $manager?->department }}</div>
            </div>
        </div>
        <div class="sb-leave-balance">
            <div class="sb-balance-title">
                <span>My Leave Balance</span>
                <select class="sb-balance-filter" onchange="filterSidebarBalance(this.value)">
                    <option value="all">All Types</option>
                    @foreach($sidebarBalances ?? [] as $balance)
                        <option value="lt-{{ $balance->leave_type_id }}">{{ $balance->leaveType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div id="sidebarBalanceItems">
                @forelse($sidebarBalances ?? [] as $balance)
                    <div class="sb-balance-item" data-type="lt-{{ $balance->leave_type_id }}"><span class="sb-balance-label">{{ $balance->leaveType->name }}</span><span class="sb-balance-val">{{ (int) $balance->remaining_days }}/{{ (int) $balance->allocated_days }}</span></div>
                @empty
                    <div class="sb-balance-item" data-type="all"><span class="sb-balance-label">No balances yet</span><span class="sb-balance-val">0/0</span></div>
                @endforelse
            </div>
        </div>
    </div>
</aside>
<div class="main-area">
    <header class="header">
        <div class="header-left">
            <div class="brand">
                <div class="brand-logo">LF</div>
                <div class="brand-name">Leave<span>Flow</span> Manager</div>
            </div>
        </div>
        <div class="header-right">
            <div class="notif-wrap" id="notifWrap">
                <button class="notif-btn" type="button" onclick="toggleNotif()" title="Notifications">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="notif-badge" data-notification-count style="{{ ($unreadCount ?? 0) ? '' : 'display:none' }}">{{ $unreadCount ?? 0 }}</span>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header"><h4>Notifications</h4><a class="notif-mark" href="{{ route('manager.notifications') }}">View all</a></div>
                    <div class="notif-list" data-notification-list>
                        @forelse($latestNotifications ?? [] as $notice)
                            <a class="notif-item {{ $notice->read_at ? '' : 'unread' }}" href="{{ route('manager.notifications.read', $notice) }}"><div class="notif-dot"></div><div class="notif-content"><p>{{ $notice->title }}</p><span>{{ $notice->created_at->diffForHumans() }}</span></div></a>
                        @empty
                            <div class="notif-item"><div class="notif-content"><p>No notifications yet.</p></div></div>
                        @endforelse
                    </div>
                </div>
            </div>
            <button class="dark-btn" type="button" onclick="toggleDark()" title="Toggle dark mode"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
            <div class="header-divider"></div>
            <div class="profile-wrap" id="profileWrap">
                <div class="profile-area" onclick="toggleProfile()">
                    <div class="profile-info"><div class="pname">{{ auth()->user()->name }}</div><div class="prole">{{ $manager?->position ?? 'Manager' }} · {{ $department?->name ?? $manager?->department }}</div></div>
                    <div class="profile-avatar">{{ $initials }}</div>
                    <svg style="width:12px;height:12px;color:var(--text3)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('manager.profile') }}">My Profile</a>
                    <a href="{{ route('manager.my-leave') }}">My Leave</a>
                    <div class="pdivider"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Logout</button></form>
                </div>
            </div>
        </div>
    </header>
    <main class="content">
        @if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="flash flash-warning">{{ session('warning') }}</div>@endif
        @if($errors->any())<div class="flash flash-warning">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
</div>
<script>
function toggleNotif(){document.getElementById('notifDropdown')?.classList.toggle('open');document.getElementById('profileDropdown')?.classList.remove('open')}
function toggleProfile(){document.getElementById('profileDropdown')?.classList.toggle('open');document.getElementById('notifDropdown')?.classList.remove('open')}
function closeDropdowns(){document.getElementById('notifDropdown')?.classList.remove('open');document.getElementById('profileDropdown')?.classList.remove('open')}
document.addEventListener('click',function(e){if(!e.target.closest('#notifWrap')&&!e.target.closest('#profileWrap')) closeDropdowns()})
function toggleDark(){document.documentElement.classList.toggle('dark');localStorage.setItem('manager-dark',document.documentElement.classList.contains('dark')?'1':'0')}
if(localStorage.getItem('manager-dark')==='1'){document.documentElement.classList.add('dark')}
function filterSidebarBalance(type){document.querySelectorAll('#sidebarBalanceItems .sb-balance-item').forEach(function(item){item.style.display=(!type||type==='all'||item.dataset.type===type)?'flex':'none'})}
function escapeHtml(value){return String(value ?? '').replace(/[&<>"']/g,function(char){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]})}
async function refreshNotifications(){
    try{
        const response=await fetch(@json(route('notifications.feed')),{headers:{'Accept':'application/json'},credentials:'same-origin'});
        if(!response.ok)return;
        const data=await response.json();
        const count=Number(data.unread_count||0);
        document.querySelectorAll('[data-notification-count]').forEach(function(badge){
            badge.textContent=count;
            badge.style.display=count>0?'flex':'none';
        });
        document.querySelectorAll('[data-notification-list]').forEach(function(list){
            if(!Array.isArray(data.notifications)||data.notifications.length===0){
                list.innerHTML='<div class="notif-item"><div class="notif-content"><p>No notifications yet.</p></div></div>';
                return;
            }
            list.innerHTML=data.notifications.map(function(notice){
                return '<a class="notif-item '+(notice.unread?'unread':'')+'" href="'+escapeHtml(notice.read_url)+'"><div class="notif-dot"></div><div class="notif-content"><p>'+escapeHtml(notice.title)+'</p><span>'+escapeHtml(notice.created_at)+'</span></div></a>';
            }).join('');
        });
    }catch(error){
        // Keep the server-rendered notifications if the refresh cannot complete.
    }
}
refreshNotifications();
setInterval(refreshNotifications,5000);
</script>
@stack('scripts')
</body>
</html>
