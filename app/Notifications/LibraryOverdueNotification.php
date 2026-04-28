<?php

namespace App\Notifications;

use App\Models\BookIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LibraryOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly BookIssue $issue) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysOverdue = $this->issue->due_date->diffInDays(now());

        return (new MailMessage)
            ->subject('Library Book Overdue — ' . $this->issue->book?->title)
            ->line("The book \"{$this->issue->book?->title}\" was due on {$this->issue->due_date->format('d M Y')}.")
            ->line("It is now {$daysOverdue} day(s) overdue. Please return it to the library at your earliest convenience.")
            ->line('A late fine may be applied upon return.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'library_overdue',
            'issue_id'    => $this->issue->id,
            'book_title'  => $this->issue->book?->title,
            'due_date'    => $this->issue->due_date->toDateString(),
            'days_overdue'=> $this->issue->due_date->diffInDays(now()),
        ];
    }
}
