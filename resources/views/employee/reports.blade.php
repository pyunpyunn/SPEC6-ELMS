@extends('layouts.employee')

@section('title', 'My Reports')

@section('content')
<div class="page-header">
    <div>
        <h1>My Reports</h1>
        <p>Yearly Compensation based on your own unused leave days.</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Yearly Compensation</span>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap" style="border:0;border-radius:0">
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Days Allowed</th>
                        <th>Days Used</th>
                        <th>Days Remaining</th>
                        <th>Daily Rate</th>
                        <th>Estimated Compensation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveTypes as $type)
                        @php($compensation = $type->is_compensable ? $dailyRate * $type->actual_remaining_days : 0)
                        <tr>
                            <td>
                                <div class="td-name">{{ $type->name }}</div>
                                <div class="td-sub">{{ $type->is_compensable ? 'Compensable' : 'Not compensable' }} · {{ $type->policy_note }}</div>
                            </td>
                            <td>{{ $type->total_days }}</td>
                            <td>{{ $type->used_days }}</td>
                            <td>{{ $type->remaining_days }}</td>
                            <td class="font-mono">₱{{ number_format($dailyRate, 2) }}</td>
                            <td class="font-mono" style="color:var(--success)">₱{{ number_format($compensation, 2) }}</td>
                        </tr>
                    @endforeach
                    @php($totalCompensation = $leaveTypes->sum(fn ($type) => $type->is_compensable ? $dailyRate * $type->actual_remaining_days : 0))
                    <tr style="font-weight:700;background:var(--surface2)">
                        <td colspan="5">Total Yearly Compensation Estimate</td>
                        <td class="font-mono" style="color:var(--success)">₱{{ number_format($totalCompensation, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="flash flash-success mt16">
    Example formula: ₱{{ number_format($dailyRate) }}/day x 5 unused days = ₱{{ number_format($dailyRate * 5) }}.
</div>
@endsection
