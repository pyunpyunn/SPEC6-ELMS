@extends('manager.layout')

@section('title', 'Team Calendar')
@section('page_title', 'Team Calendar')

@section('content')
@php($selectedTypes = collect((array) request('leave_type_ids'))->map(fn($id)=>(int)$id)->all())
<div class="page-header">
    <div><h1>Team Calendar</h1><p>Approved leaves for {{ $department?->name ?? 'your team' }}.</p></div>
</div>

<div class="cal-page-header">
    <div class="cal-nav">
        <a class="btn btn-icon" href="{{ route('manager.calendar', ['year' => $previousMonth->year, 'month' => $previousMonth->month, 'leave_type_ids' => request('leave_type_ids')]) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></a>
        <span class="cal-nav-month">{{ $calendarMonth->format('F Y') }}</span>
        <a class="btn btn-icon" href="{{ route('manager.calendar', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'leave_type_ids' => request('leave_type_ids')]) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></a>
    </div>
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <input type="hidden" name="year" value="{{ $calendarMonth->year }}">
        <input type="hidden" name="month" value="{{ $calendarMonth->month }}">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @foreach($leaveTypes as $type)
                @php($slug = str($type->name)->slug())
                <label style="font-size:12px;display:flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;border:1px solid var(--border);background:var(--surface)">
                    <input type="checkbox" name="leave_type_ids[]" value="{{ $type->id }}" onchange="this.form.submit()" @checked(empty($selectedTypes) || in_array($type->id, $selectedTypes))>
                    <span class="badge badge-{{ $slug === 'sick-leave' ? 'pending' : ($slug === 'vacation-leave' ? 'approved' : ($slug === 'emergency-leave' ? 'rejected' : 'info')) }}">{{ $type->name }}</span>
                </label>
            @endforeach
        </div>
    </form>
</div>

<div class="full-cal-grid">
    <div class="full-cal-dow">
        @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $dow)<span>{{ $dow }}</span>@endforeach
    </div>
    <div class="full-cal-body">
        @foreach($calendarGrid as $day)
            <div class="cal-cell {{ $day['in_month'] ? '' : 'other-month' }} {{ $day['is_today'] ? 'today' : '' }}">
                <div class="cal-cell-num">{{ $day['date']->day }}</div>
                @foreach($day['events']->take(3) as $event)
                    <div class="cal-event {{ $event['class'] }}" data-leave-type="{{ $event['class'] }}">{{ $event['name'] }} - {{ $event['type'] }}</div>
                @endforeach
                @if($day['events']->count() > 3)
                    <div class="cal-event vacation">+{{ $day['events']->count() - 3 }} more</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-leave-filter]').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const activeTypes = Array.from(document.querySelectorAll('[data-leave-filter]:checked')).map(function (item) {
            return item.dataset.leaveFilter;
        });

        document.querySelectorAll('[data-leave-type]').forEach(function (event) {
            event.style.display = activeTypes.includes(event.dataset.leaveType) ? '' : 'none';
        });
    });
});
</script>
@endpush
