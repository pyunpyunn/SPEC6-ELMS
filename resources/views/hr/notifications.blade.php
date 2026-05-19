@extends('hr.layout')

@section('content')
<div class="page active" id="page-notifications">
    <div class="page-header">
        <div>
            <h1>Notifications</h1>
            <p>Alerts for pending users and system-level events</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Activity</span>
            <span class="muted">{{ $notifications->total() }} total</span>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
            @forelse($notifications as $n)
                <a href="{{ route('admin.notifications.read', $n) }}" class="dept-leave-row" style="padding:14px 16px;border:1px solid var(--border);border-radius:16px;background:var(--surface);display:block">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start">
                        <div>
                            <div class="td-name" style="display:flex;align-items:center;gap:8px">
                                <span>{{ $n->title }}</span>
                                @if(!$n->read_at)
                                    <span class="badge badge-pending">New</span>
                                @endif
                            </div>
                            <div class="td-sub" style="margin-top:6px;line-height:1.6">{{ $n->body }}</div>
                        </div>
                        <div style="white-space:nowrap;text-align:right">
                            <div class="td-sub">{{ $n->created_at->format('M d, Y') }}</div>
                            <div class="td-pos">{{ $n->created_at->format('g:i A') }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <p>No notifications yet.</p>
                </div>
            @endforelse
        </div>
        <div class="card-b">{{ $notifications->links() }}</div>
    </div>
</div>
@endsection


