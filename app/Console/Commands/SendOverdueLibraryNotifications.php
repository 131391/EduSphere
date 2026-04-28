<?php

namespace App\Console\Commands;

use App\Models\BookIssue;
use App\Notifications\LibraryOverdueNotification;
use Illuminate\Console\Command;

class SendOverdueLibraryNotifications extends Command
{
    protected $signature   = 'library:notify-overdue';
    protected $description = 'Notify students with overdue library books';

    public function handle(): int
    {
        $overdueIssues = BookIssue::query()
            ->where('status', 'issued')
            ->where('due_date', '<', now()->toDateString())
            ->with(['student.user', 'book'])
            ->get();

        $notified = 0;

        foreach ($overdueIssues as $issue) {
            $user = $issue->student?->user;
            if (!$user) {
                continue;
            }

            try {
                $user->notify(new LibraryOverdueNotification($issue));
                $notified++;
            } catch (\Exception $e) {
                $this->warn("Failed to notify user {$user->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$notified} overdue library notification(s).");

        return self::SUCCESS;
    }
}
