<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAdmissionCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Student $student,
        public readonly string $tempPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $school = $this->student->school;

        return (new MailMessage)
            ->subject("Welcome to {$school->name} — Your Login Credentials")
            ->greeting("Hello {$this->student->full_name},")
            ->line("You have been successfully admitted to **{$school->name}**.")
            ->line("Your admission number is: **{$this->student->admission_no}**")
            ->line('Use the credentials below to log in for the first time:')
            ->line("**Email:** {$notifiable->email}")
            ->line("**Password:** {$this->tempPassword}")
            ->action('Login Now', url('/login'))
            ->line('You will be asked to change your password on first login.')
            ->salutation("Regards,\n{$school->name}");
    }
}
