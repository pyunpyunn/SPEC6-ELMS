<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusChanged extends Notification
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
            ->subject('Leave request '.$leave->status)
            ->line('Your '.$leave->leaveType?->name.' request was '.$leave->status.'.')
            ->line('Dates: '.$leave->start_date?->toFormattedDateString().' to '.$leave->end_date?->toFormattedDateString())
            ->line('Remarks: '.($leave->remarks ?: 'No remarks provided.'));
    }
}
