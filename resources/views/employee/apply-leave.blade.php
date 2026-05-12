@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto mt-10 card">
    <div class="card-header">
        <span class="card-title">Apply for Leave</span>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="flash flash-success mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="flash flash-error mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('employee.leave.store') }}" class="form-grid single">
            @csrf
            <div class="form-group">
                <label for="leave_type_id">Leave Type</label>
                <select name="leave_type_id" id="leave_type_id" required>
                    <option value="">Select leave type</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" required>
            </div>
            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full mt-2">Submit Request</button>
        </form>
    </div>
</div>
@endsection
