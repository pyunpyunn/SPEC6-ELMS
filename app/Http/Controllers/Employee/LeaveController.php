<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeaveRequestSubmitted;
use App\Models\User;

class LeaveController extends Controller
{
    public function create()
    {
        $leaveTypes = LeaveType::all();
        return view('employee.apply-leave', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $leave = LeaveApplication::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        // Notify HR/Admin users
        $hrUsers = User::where('role', 'hr_admin')->orWhere('role', 'admin')->get();
        Notification::send($hrUsers, new LeaveRequestSubmitted($leave));

        return redirect()->route('employee.leave.create')->with('success', 'Leave request submitted and sent to HR/Admin.');
    }
}
