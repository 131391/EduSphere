<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LibraryOverdueDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * @param Collection<\App\Models\BookIssue> $issues
     * @param 'borrower'|'parent' $audience
     */
    public function __construct(
        public readonly Collection $issues,
        public readonly string $audience = 'borrower',
        public readonly ?string $studentName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->issues->count();
        $subject = $this->audience === 'parent' && $this->studentName
            ? "{$this->studentName} has {$count} overdue library book(s)"
            : ($count === 1
                ? 'Library book overdue'
                : "{$count} library books are overdue");

        $mail = (new MailMessage)
            ->subject($subject)
            ->line($this->audience === 'parent' && $this->studentName
                ? "{$this->studentName} has the following overdue library book(s):"
                : 'You have the following overdue library book(s):');

        foreach ($this->issues as $issue) {
            $days = (int) $issue->due_date->copy()->startOfDay()->diffInDays(now()->startOfDay());
            $mail->line(sprintf(
                '• "%s" — was due %s (%d day%s overdue)',
                $issue->book?->title ?? 'Unknown title',
                $issue->due_date->format('d M Y'),
                $days,
                $days === 1 ? '' : 's'
            ));
        }

        return $mail->line('Please return the book(s) to the library at the earliest. A late fine may be applied.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'library_overdue_digest',
            'audience'     => $this->audience,
            'student_name' => $this->studentName,
            'count'        => $this->issues->count(),
            'issue_ids'    => $this->issues->pluck('id')->all(),
        ];
    }
}
