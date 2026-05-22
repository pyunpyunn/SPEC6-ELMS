@extends('admin.layout')

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
            <div>
                <span class="card-title">Recent Activity</span>
                <div class="muted">System alerts and approval activity</div>
            </div>
            <span class="admin-notification-count">{{ $notifications->total() }} total</span>
        </div>
        <div class="card-body admin-notification-list">
            @forelse($notifications as $n)
                <a href="{{ route('admin.notifications.read', $n) }}" class="admin-notification-row {{ $n->read_at ? '' : 'unread' }}">
                    <span class="admin-notification-dot" aria-hidden="true"></span>
                    <span class="admin-notification-main">
                        <span class="admin-notification-title">
                            <span>{{ $n->title }}</span>
                            @if(!$n->read_at)
                                <span class="badge badge-pending">New</span>
                            @endif
                        </span>
                        <span class="admin-notification-body">{{ $n->body }}</span>
                    </span>
                    <span class="admin-notification-time">
                        <span>{{ $n->created_at->format('M d, Y') }}</span>
                        <span>{{ $n->created_at->format('g:i A') }}</span>
                    </span>
                </a>
            @empty
                <div class="empty-state admin-notification-empty">
                    <div class="admin-notification-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <p>No notifications yet.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="pagination admin-notification-pagination">
                {{ $notifications->onEachSide(1)->links('vendor.pagination.hr', ['anchor' => 'page-notifications']) }}
            </div>
        @endif
    </div>
</div>
@endsection
