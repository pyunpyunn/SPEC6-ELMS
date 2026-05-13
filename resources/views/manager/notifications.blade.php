@extends('manager.layout')

@section('title', 'Notifications')
@section('page_title', 'Notifications')

@section('content')
<div class="card" style="max-width:860px;margin:0 auto;padding:0">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border)"><h3>All Notifications</h3></div>
    @forelse($notifications as $notice)
        <a class="notif-item {{ $notice->read_at ? '' : 'unread' }}" href="{{ route('manager.notifications.read', $notice) }}">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--body-bg);display:grid;place-items:center;font-weight:800">!</div>
            <div>
                <strong>{{ $notice->title }}</strong>
                <div>{{ $notice->body }}</div>
                <div class="muted">{{ $notice->created_at->diffForHumans() }}</div>
            </div>
        </a>
    @empty
        <div class="notif-item"><div><strong>No notifications yet.</strong></div></div>
    @endforelse
    <div class="pagination" style="padding:16px">{{ $notifications->links() }}</div>
</div>
@endsection
