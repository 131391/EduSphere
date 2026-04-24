<?php

namespace App\Console\Commands;

use App\Models\Fee;
use App\Models\Student;
use App\Models\User;
use App\Notifications\FeeDueReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendFeeReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to parents for fees due tomorrow.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $this->info("Scanning for fees due on {$tomorrow}...");

        $fees = Fee::with(['student.user', 'feeName'])
            ->where('due_date', $tomorrow)
            ->where('due_amount', '>', 0)
            ->get();

        $count = 0;

        foreach ($fees as $fee) {
            $student = $fee->student;
            
            if (!$student) continue;

            // Notify parents if they exist, or fallback to student's user account
            $notifiables = collect();

            if ($student->father_email) {
                // We create a dummy route for email since we don't have parent users strictly required
                $notifiables->push(Notification::route('mail', $student->father_email));
            }
            if ($student->mother_email) {
                $notifiables->push(Notification::route('mail', $student->mother_email));
            }

            if ($notifiables->isEmpty() && $student->user && $student->user->email) {
                $notifiables->push($student->user);
            }

            if ($notifiables->isNotEmpty()) {
                Notification::send($notifiables, new FeeDueReminder($fee));
                $count++;
            }
        }

        $this->info("Sent {$count} fee reminders.");
        Log::info("Sent {$count} fee due reminders for {$tomorrow}.");
    }
}
