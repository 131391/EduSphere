<?php

namespace App\Console\Commands;

use App\Models\BookIssue;
use App\Notifications\LibraryOverdueDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueLibraryNotifications extends Command
{
    protected $signature = 'library:notify-overdue {--cooldown=1 : Days to wait before re-notifying the same issue}';
    protected $description = 'Notify students/staff (and parents) with overdue library books';

    public function handle(): int
    {
        $cooldownDays      = max(0, (int) $this->option('cooldown'));
        $cooldownThreshold = now()->subDays($cooldownDays);

        $studentNotified = 0;
        $staffNotified   = 0;
        $parentNotified  = 0;
        $skipped         = 0;
        $touchedIssueIds = [];

        // Pull all currently-overdue issues whose cooldown has elapsed.
        $issues = BookIssue::query()
            ->where('status', 'issued')
            ->where('due_date', '<', now()->toDateString())
            ->where(fn($q) => $q->whereNull('last_notified_at')->orWhere('last_notified_at', '<=', $cooldownThreshold))
            ->with(['student.user', 'student.parents.user', 'staff.user', 'book'])
            ->get();

        // Group per student / per staff so each recipient gets a single digest.
        $byStudent = $issues->whereNotNull('student_id')->groupBy('student_id');
        $byStaff   = $issues->whereNull('student_id')->whereNotNull('staff_id')->groupBy('staff_id');

        foreach ($byStudent as $studentIssues) {
            $first   = $studentIssues->first();
            $student = $first->student;
            if (!$student) { $skipped += $studentIssues->count(); continue; }

            $studentName = $student->full_name ?? null;

            // Borrower digest
            if ($student->user) {
                try {
                    $student->user->notify(new LibraryOverdueDigestNotification($studentIssues, 'borrower', $studentName));
                    $studentNotified++;
                } catch (\Throwable $e) {
                    Log::warning('Library overdue digest (student) failed', ['student_id' => $student->id, 'exception' => $e]);
                    $skipped++;
                    continue;
                }
            } else {
                $skipped++;
            }

            // Parent digest (one per linked parent who has a user account)
            foreach ($student->parents ?? [] as $parent) {
                if (!$parent->user) continue;
                try {
                    $parent->user->notify(new LibraryOverdueDigestNotification($studentIssues, 'parent', $studentName));
                    $parentNotified++;
                } catch (\Throwable $e) {
                    Log::warning('Library overdue digest (parent) failed', [
                        'student_id' => $student->id,
                        'parent_id'  => $parent->id,
                        'exception'  => $e,
                    ]);
                }
            }

            $touchedIssueIds = array_merge($touchedIssueIds, $studentIssues->pluck('id')->all());
        }

        foreach ($byStaff as $staffIssues) {
            $first = $staffIssues->first();
            $staff = $first->staff;
            if (!$staff || !$staff->user) { $skipped += $staffIssues->count(); continue; }

            try {
                $staff->user->notify(new LibraryOverdueDigestNotification($staffIssues, 'borrower', $staff->name ?? null));
                $staffNotified++;
                $touchedIssueIds = array_merge($touchedIssueIds, $staffIssues->pluck('id')->all());
            } catch (\Throwable $e) {
                Log::warning('Library overdue digest (staff) failed', ['staff_id' => $staff->id, 'exception' => $e]);
                $skipped++;
            }
        }

        // Stamp last_notified_at in a single batched query — updates only the
        // issues we actually attempted to notify on.
        if (!empty($touchedIssueIds)) {
            BookIssue::whereIn('id', $touchedIssueIds)->update(['last_notified_at' => now()]);
        }

        $this->info("Library overdue digests sent — students: {$studentNotified}, staff: {$staffNotified}, parents: {$parentNotified}; skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
