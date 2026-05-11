@extends('hr.layout')

@section('content')
<div class="page-head"><div><h1>Notifications</h1><div class="muted">Alerts for pending users and system-level events.</div></div></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Title</th><th>Message</th><th>Date</th><th>Action</th></tr></thead><tbody>@forelse($notifications as $n)<tr><td>{{ $n->title }}</td><td>{{ $n->body }}</td><td>{{ $n->created_at->format('M d, Y g:i A') }}</td><td>@if($n->action_url)<a class="btn small" href="{{ $n->action_url }}">Open</a>@endif</td></tr>@empty<tr><td colspan="4">No notifications yet.</td></tr>@endforelse</tbody></table></div><div class="card-b">{{ $notifications->links() }}</div></div>
@endsection
