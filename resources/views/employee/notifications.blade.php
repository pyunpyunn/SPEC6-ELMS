@extends('layouts.employee')

@section('title', 'Notifications')

@section('content')
<div class="page-header">
    <div>
        <h1>Notifications</h1>
        <p>{{ $notifications->where('unread', true)->count() }} unread notifications</p>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">All Notifications</span></div>
    @forelse($notifications as $notification)
        <div class="notif-item">
            <div class="notif-dot-badge {{ $notification->unread ? 'unread' : '' }}"></div>
            <div class="notif-body">
                <strong>{{ $notification->title }}</strong> - {{ $notification->body }}
                <div class="notif-time">{{ $notification->time }}</div>
            </div>
            <span class="badge badge-{{ $notification->status }}">{{ $notification->status }}</span>
        </div>
    @empty
        <div class="empty-state">No notifications yet.</div>
    @endforelse
</div>
@endsection
