<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\LeaveApplication;

class LeaveRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public $leave;

    public function __construct(LeaveApplication $leave)
    {
        $this->leave = $leave;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Leave Request Submitted')
            ->line('A new leave request has been submitted by ' . $this->leave->user->name)
            ->line('Type: ' . $this->leave->leaveType->name)
            ->line('Dates: ' . $this->leave->start_date . ' to ' . $this->leave->end_date)
            ->action('View Requests', url('/hr/requests'));
    }

    public function toArray($notifiable)
    {
        return [
            'leave_id' => $this->leave->id,
            'user' => $this->leave->user->name,
            'type' => $this->leave->leaveType->name,
            'dates' => $this->leave->start_date . ' to ' . $this->leave->end_date,
        ];
    }
}
