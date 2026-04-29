<?php

namespace App\Console\Commands;

use App\Models\BookIssue;
use App\Notifications\LibraryOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueLibraryNotifications extends Command
{
    protected $signature = 'library:notify-overdue {--cooldown=1 : Days to wait before re-notifying the same issue}';
    protected $description = 'Notify students and staff with overdue library books';

    public function handle(): int
    {
        $cooldownDays = max(0, (int) $this->option('cooldown'));
        $cooldownThreshold = now()->subDays($cooldownDays);

        $notified = 0;
        $skipped = 0;

        BookIssue::query()
            ->where('status', 'issued')
            ->where('due_date', '<', now()->toDateString())
            ->where(function ($q) use ($cooldownThreshold) {
                $q->whereNull('last_notified_at')
                    ->orWhere('last_notified_at', '<=', $cooldownThreshold);
            })
            ->with(['student.user', 'staff.user', 'book'])
            ->chunkById(200, function ($issues) use (&$notified, &$skipped) {
                foreach ($issues as $issue) {
                    $user = $issue->student?->user ?? $issue->staff?->user;

                    if (!$user) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $user->notify(new LibraryOverdueNotification($issue));
                        $issue->forceFill(['last_notified_at' => now()])->saveQuietly();
                        $notified++;
                    } catch (\Throwable $e) {
                        Log::warning('Library overdue notification failed', [
                            'user_id' => $user->id,
                            'issue_id' => $issue->id,
                            'exception' => $e,
                        ]);
                        $skipped++;
                    }
                }
            });

        $this->info("Sent {$notified} overdue library notification(s); skipped {$skipped}.");

        return self::SUCCESS;
    }
}
