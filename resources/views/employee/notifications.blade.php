@extends('layouts.employee')

@section('title', 'Notifications')

@section('content')
<div class="page-header">
    <div>
        <h1>Notifications</h1>
        <p>{{ $notifications->total() }} total notifications</p>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">All Notifications</span></div>
    @forelse($notifications as $notification)
        <a class="notif-item" href="{{ route('employee.notifications.read', $notification) }}">
            <div class="notif-dot-badge {{ $notification->read_at ? '' : 'unread' }}"></div>
            <div class="notif-body">
                <strong>{{ $notification->title }}</strong> - {{ $notification->body }}
                <div class="notif-time">{{ $notification->created_at->format('M d, Y h:i A') }}</div>
            </div>
            <span class="badge badge-{{ $notification->type }}">{{ str_replace('_', ' ', $notification->type) }}</span>
        </a>
    @empty
        <div class="empty-state">No notifications yet.</div>
    @endforelse
    <div class="pagination" style="padding:16px">{{ $notifications->links() }}</div>
</div>
@endsection
