<?php

namespace App\Notifications;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeDueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public Fee $fee;

    public function __construct(Fee $fee)
    {
        $this->fee = $fee;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->fee->student->full_name;
        $amount = number_format($this->fee->due_amount, 2);
        $dueDate = $this->fee->due_date->format('F d, Y');
        $feeName = $this->fee->feeName->name;

        return (new MailMessage)
                    ->subject("Fee Reminder: {$feeName} due for {$studentName}")
                    ->greeting("Dear Parent/Guardian,")
                    ->line("This is a friendly reminder that the {$feeName} for your ward, {$studentName}, is due tomorrow.")
                    ->line("Amount Due: ₹{$amount}")
                    ->line("Due Date: {$dueDate}")
                    ->action('View Student Portal', url('/'))
                    ->line('Please ensure the payment is made on time to avoid late fees. If you have already paid, please ignore this message.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'fee_id' => $this->fee->id,
            'amount' => $this->fee->due_amount,
        ];
    }
}
