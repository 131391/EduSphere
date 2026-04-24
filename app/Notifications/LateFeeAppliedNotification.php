<?php

namespace App\Notifications;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LateFeeAppliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Fee $fee;
    public string $lateFeeAmount;

    public function __construct(Fee $fee, string $lateFeeAmount)
    {
        $this->fee = $fee;
        $this->lateFeeAmount = $lateFeeAmount;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->fee->student->full_name;
        $amount = number_format($this->fee->due_amount, 2);
        $feeName = $this->fee->feeName->name;
        $lateAmountFormatted = number_format($this->lateFeeAmount, 2);

        return (new MailMessage)
                    ->subject("Late Fee Applied: {$feeName} for {$studentName}")
                    ->greeting("Dear Parent/Guardian,")
                    ->line("This is to inform you that a late fee of ₹{$lateAmountFormatted} has been applied to your ward's {$feeName} due to non-payment by the due date.")
                    ->line("Current Amount Due: ₹{$amount}")
                    ->action('View Student Portal', url('/'))
                    ->line('Please clear the pending dues immediately to avoid further penalties.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'fee_id' => $this->fee->id,
            'late_amount' => $this->lateFeeAmount,
            'total_due' => $this->fee->due_amount,
        ];
    }
}
