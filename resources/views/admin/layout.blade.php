<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAV Employee Leave Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('hr-prototype.css') }}">
    <style>
        .page{display:block}.grid{display:grid;gap:16px}.two{grid-template-columns:1fr 340px}.cards{grid-template-columns:repeat(auto-fill,minmax(260px,1fr))}.form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.form .full{grid-column:1/-1}.form label{display:block;margin-bottom:6px;font-size:15px;font-weight:700;color:var(--text2)}.form input,.form select,.form textarea{width:100%}
        .card-h{padding:15px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:15px;font-weight:700;color:var(--text)}.card-b{padding:18px 20px}
        .page-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:26px;gap:16px;flex-wrap:wrap}.page-head h1{font-size:21px;font-weight:700;color:var(--text);letter-spacing:-0.5px;line-height:1.2;margin:0}.muted{color:var(--text3);font-size:15px}.filters{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;align-items:center}.filters select,.filters input{padding:8px 11px;min-width:130px}
        .badge.pending{background:var(--warning-bg);color:var(--warning)}.badge.approved,.badge.active{background:var(--success-bg);color:var(--success)}.badge.rejected,.badge.inactive{background:var(--danger-bg);color:var(--danger)}.badge.hr{background:var(--primary-bg);color:var(--primary)}.badge.manager{background:var(--info-bg);color:var(--info)}
        .flash.warning{background:var(--warning-bg);border:1px solid rgba(184,114,20,.22);color:var(--warning)}.flash.success{background:var(--success-bg);border:1px solid rgba(42,117,84,.22);color:var(--success)}
        .dark .sidebar{background:#1b241f}.dark .sb-item:hover{background:rgba(255,255,255,.08)}.dark .sb-item.active{background:rgba(255,255,255,.13)}
        .btn.primary{background:var(--primary);color:#fff}.btn.success{background:var(--success-bg);color:var(--success);border:1px solid rgba(42,117,84,.22)}.btn.danger{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(184,48,48,.22)}.btn.small{padding:6px 11px;font-size:15px}
        .pagination nav{display:flex;gap:6px;align-items:center}.pagination svg{width:16px;height:16px}.pagination p{font-size:15px;color:var(--text3)}
        details summary{list-style:none}details summary::-webkit-details-marker{display:none}.actions{display:flex;gap:7px;flex-wrap:wrap}
        .calendar-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}.cal-item{border-left:4px solid var(--primary);padding:10px;border-radius:8px;background:var(--surface2)}
        .full-cal-grid .cal-day{min-height:112px}.cal-event{font-size:15px;margin-top:4px;padding:3px 5px;border-radius:4px;background:var(--primary-bg);border:1px solid var(--primary);color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        @media(max-width:1100px){.two{grid-template-columns:1fr}}@media(max-width:760px){.form{grid-template-columns:1fr}.header .brand-name{display:none}}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="sb-top">
            <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
            </button>
            <div class="sb-brand">NAV ELMS</div>
        </div>

        <nav class="sb-nav">
            <div class="sb-section">Main</div>
            <a class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Dashboard</span><span class="sb-item-tooltip">Dashboard</span>
            </a>
            <a class="sb-item {{ request()->routeIs('admin.calendar') ? 'active' : '' }}" href="{{ route('admin.calendar') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><span>Company Calendar</span><span class="sb-item-tooltip">Company Calendar</span>
            </a>

            <div class="sb-section">Staff Management</div>
            <a class="sb-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.pending') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M17 11l2 2 4-4"/></svg><span>User Verification</span><span class="sb-item-tooltip">User Verification</span>
            </a>
            <a class="sb-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Employee Directory</span><span class="sb-item-tooltip">Employee Directory</span>
            </a>
            <a class="sb-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1M9 13h1M9 17h1"/></svg><span>Departments</span><span class="sb-item-tooltip">Departments</span>
            </a>

            <div class="sb-section">Leave Control</div>
            <a class="sb-item {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}" href="{{ route('admin.requests.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11h6"/><path d="M9 15h6"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/></svg><span>Master Request Log</span><span class="sb-item-tooltip">Master Request Log</span>
            </a>
            <a class="sb-item {{ request()->routeIs('admin.leave-types.*') ? 'active' : '' }}" href="{{ route('admin.leave-types.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg><span>Leave Configuration</span><span class="sb-item-tooltip">Leave Configuration</span>
            </a>
            <a class="sb-item {{ request()->routeIs('admin.my-leave') ? 'active' : '' }}" href="{{ route('admin.my-leave') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg><span>My Leave</span><span class="sb-item-tooltip">My Leave</span>
            </a>
            <div class="sb-section">Reports</div>
            <a class="sb-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Analytics & Reports</span><span class="sb-item-tooltip">Analytics & Reports</span>
            </a>
        </nav>

        <div class="sb-footer">
            @php
                $sidebarEmployee = auth()->user()->employee;
                $sidebarBalances = collect($sidebarEmployee?->leaveBalances()->with('leaveType')->where('year', now()->year)->get() ?? [])
                    ->filter(fn ($balance) => $balance->leaveType?->isVisibleForGender($sidebarEmployee?->gender))
                    ->sortBy(function ($balance) {
                        $name = strtolower($balance->leaveType?->name ?? '');

                        return match (true) {
                            str_contains($name, 'sick') => 0,
                            str_contains($name, 'vacation') => 1,
                            str_contains($name, 'emergency') => 2,
                            str_contains($name, 'bereavement') => 3,
                            str_contains($name, 'maternity') => 4,
                            str_contains($name, 'paternity') => 5,
                            default => 99,
                        };
                    })
                    ->values();
            @endphp
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
                    <div class="brand-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                    <div class="brand-name">NAV Employee Leave Management System</div>
                </div>
            </div>
            <div class="header-right">
                <div class="notif-wrap">
                    <button class="notif-btn" type="button" onclick="toggleNotifications()" title="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        @php($unread = auth()->user()->notifications()->whereNull('read_at')->count())
                        <span class="notif-badge" data-notification-count style="{{ $unread ? '' : 'display:none' }}">{{ $unread }}</span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header"><h4>Notifications</h4><a class="notif-mark" href="{{ route('admin.notifications') }}">View all</a></div>
                        <div class="notif-list" data-notification-list>
                            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notice)
                                <a class="notif-item {{ $notice->read_at ? '' : 'unread' }}" href="{{ route('admin.notifications.read', $notice) }}">
                                    <span class="notif-dot"></span><span class="notif-content"><p>{{ $notice->title }}</p><span>{{ $notice->created_at->diffForHumans() }}</span></span>
                                </a>
                            @empty
                                <div class="notif-item"><span class="notif-content"><p>No notifications yet.</p></span></div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <button class="dark-btn" onclick="toggleDark()" title="Dark Mode"><svg id="dark-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
                <div class="header-divider"></div>
                <div class="profile-area" onclick="toggleProfile()">
                    <div class="profile-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="profile-info"><div class="pname">{{ auth()->user()->name }}</div><div class="prole">HR Admin</div></div>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('admin.profile') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>My Profile</a>
                    <div class="pdivider"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" style="width:100%;text-align:left;background:none;border:0;padding:10px 14px;font-size:15px;color:var(--danger)">Logout</button></form>
                </div>
            </div>
        </header>

        <main class="content">
            @if (session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
            @if (session('warning'))<div class="flash flash-warning">{{ session('warning') }}</div>@endif
            @if (isset($errors) && $errors->any())<div class="flash flash-warning">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('collapsed')}
function toggleProfile(){document.getElementById('profileDropdown').classList.toggle('open')}
function toggleNotifications(){document.getElementById('notifDropdown').classList.toggle('open')}
function filterSidebarBalance(type){document.querySelectorAll('#sidebarBalanceItems .sb-balance-item').forEach(function(item){item.style.display=(!type||type==='all'||item.dataset.type===type)?'flex':'none'})}
function toggleDark(){document.documentElement.classList.toggle('dark');localStorage.setItem('elms-dark',document.documentElement.classList.contains('dark')?'1':'0')}
if(localStorage.getItem('elms-dark')==='1'){document.documentElement.classList.add('dark')}
document.addEventListener('DOMContentLoaded',function(){const filter=document.getElementById('sidebarBalanceFilter');if(filter){filterSidebarBalance(filter.value)}})
document.addEventListener('click',function(e){
    if(!e.target.closest('.profile-area')&&!e.target.closest('#profileDropdown')){document.getElementById('profileDropdown')?.classList.remove('open')}
    if(!e.target.closest('.notif-wrap')){document.getElementById('notifDropdown')?.classList.remove('open')}
})
function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g,function(char){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
    })
}
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
                list.innerHTML='<div class="notif-item"><span class="notif-content"><p>No notifications yet.</p></span></div>';
                return;
            }
            list.innerHTML=data.notifications.map(function(notice){
                return '<a class="notif-item '+(notice.unread?'unread':'')+'" href="'+escapeHtml(notice.read_url)+'"><span class="notif-dot"></span><span class="notif-content"><p>'+escapeHtml(notice.title)+'</p><span>'+escapeHtml(notice.created_at)+'</span></span></a>';
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
