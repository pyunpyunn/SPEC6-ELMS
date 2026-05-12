@extends('hr.layout')

@section('content')
@php
    $view = request('view', 'month');
    $month = request('month') ? \Carbon\Carbon::parse(request('month').'-01') : now();
    $focus = request('date') ? \Carbon\Carbon::parse(request('date')) : now();
    $start = $view === 'week' ? $focus->copy()->startOfWeek() : $month->copy()->startOfMonth()->startOfWeek();
    $end = $view === 'week' ? $focus->copy()->endOfWeek() : $month->copy()->endOfMonth()->endOfWeek();
    $days = [];
    for ($day = $start->copy(); $day <= $end; $day->addDay()) {
        $days[] = $day->copy();
    }
    $leavesByDate = [];
    $selectedTypes = collect((array) request('leave_type_ids'))->map(fn($id)=>(int)$id)->all();
    foreach ($approvedLeaves as $leave) {
        for ($date = $leave->start_date->copy(); $date <= $leave->end_date; $date->addDay()) {
            $leavesByDate[$date->toDateString()][] = $leave;
        }
    }
@endphp

<div class="page active" id="page-calendar">
    <div class="page-header">
        <div><h1>Company Calendar</h1><p>View all approved leaves across the organization</p></div>
        <div class="page-actions">
            <form method="GET" class="page-actions">
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <input type="hidden" name="view" value="{{ $view }}">
                <select name="department_id" onchange="this.form.submit()" style="padding:8px 11px;min-width:140px">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </form>
            <a class="btn btn-outline btn-sm" href="{{ route('hr.reports.export', ['type' => 'leaves', 'department_id' => $selectedDepartmentId]) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </a>
        </div>
    </div>

    <div class="cal-page-header">
        <div class="cal-nav">
            <a class="btn btn-icon" href="{{ route('hr.calendar', ['department_id' => $selectedDepartmentId, 'month' => $month->copy()->subMonth()->format('Y-m'), 'date' => $focus->copy()->subWeek()->toDateString(), 'view' => $view, 'leave_type_ids' => $selectedTypes]) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></a>
            <span class="cal-nav-month">{{ $view === 'week' ? $start->format('M d').' - '.$end->format('M d, Y') : $month->format('F Y') }}</span>
            <a class="btn btn-icon" href="{{ route('hr.calendar', ['department_id' => $selectedDepartmentId, 'month' => $month->copy()->addMonth()->format('Y-m'), 'date' => $focus->copy()->addWeek()->toDateString(), 'view' => $view, 'leave_type_ids' => $selectedTypes]) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></a>
        </div>
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
            <input type="hidden" name="date" value="{{ $focus->toDateString() }}">
            <select name="view" onchange="this.form.submit()" style="padding:6px 10px;font-size:15px"><option value="month" @selected($view==='month')>Monthly</option><option value="week" @selected($view==='week')>Weekly</option></select>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @foreach($leaveTypes as $type)
                    @php($slug = str($type->name)->slug())
                    <label style="font-size:15px;display:flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;border:1px solid var(--border);background:var(--surface)">
                        <input type="checkbox" name="leave_type_ids[]" value="{{ $type->id }}" onchange="this.form.submit()" @checked(empty($selectedTypes) || in_array($type->id, $selectedTypes))>
                        <span class="badge badge-{{ $slug === 'sick-leave' ? 'pending' : ($slug === 'vacation-leave' ? 'approved' : ($slug === 'emergency-leave' ? 'rejected' : 'info')) }}">{{ $type->name }}</span>
                    </label>
                @endforeach
            </div>
        </form>
    </div>

    <div class="full-cal-grid">
        <div class="full-cal-dow">
            @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $dow)
                <span>{{ $dow }}</span>
            @endforeach
        </div>
        <div class="full-cal-body" style="{{ $view === 'week' ? 'grid-template-columns:repeat(7,1fr)' : '' }}">
            @foreach($days as $day)
                @php($items = collect($leavesByDate[$day->toDateString()] ?? []))
                <div class="cal-cell {{ $day->month !== $month->month ? 'other-month' : '' }} {{ $day->isToday() ? 'today' : '' }}">
                    <div class="cal-cell-num">{{ $day->day }}</div>
                    @foreach($items->take(3) as $leave)
                        @php($slug = str($leave->leaveType->name)->lower()->contains('sick') ? 'sick' : (str($leave->leaveType->name)->lower()->contains('emergency') ? 'emergency' : (str($leave->leaveType->name)->lower()->contains('maternity') ? 'maternity' : (str($leave->leaveType->name)->lower()->contains('bereavement') ? 'bereavement' : 'vacation'))))
                        <div class="cal-event {{ $slug }}" title="{{ $leave->employee->full_name }} - {{ $leave->leaveType->name }}">{{ $leave->employee->full_name }}</div>
                    @endforeach
                    @if($items->count() > 3)
                        <div class="cal-event">+{{ $items->count() - 3 }} more</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
