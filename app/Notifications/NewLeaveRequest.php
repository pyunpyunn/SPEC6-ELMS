<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeaveRequest extends Notification
{
    use Queueable;

    public function __construct(private readonly LeaveApplication $leaveApplication)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $leave = $this->leaveApplication;

        return (new MailMessage)
            ->subject('New leave request')
            ->line($leave->employee?->full_name.' submitted a '.$leave->leaveType?->name.' request.')
            ->line('Dates: '.$leave->start_date?->toFormattedDateString().' to '.$leave->end_date?->toFormattedDateString());
    }
}
