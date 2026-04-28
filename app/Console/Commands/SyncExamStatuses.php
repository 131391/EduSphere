<?php

namespace App\Console\Commands;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\School;
use App\Services\School\Examination\ExamService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncExamStatuses extends Command
{
    protected $signature = 'exams:sync-statuses {--school= : Limit to a single school id}';

    protected $description = 'Synchronise exam statuses (Scheduled/Ongoing/Completed) from dates and result counts. Skips Cancelled and Locked exams.';

    public function handle(ExamService $service): int
    {
        $schoolFilter = $this->option('school');

        $query = School::query();
        if ($schoolFilter) {
            $query->whereKey($schoolFilter);
        }

        $totalExams = 0;
        $updated = 0;

        $query->cursor()->each(function (School $school) use ($service, &$totalExams, &$updated) {
            $exams = Exam::where('school_id', $school->id)
                ->whereNotIn('status', [ExamStatus::Cancelled, ExamStatus::Locked])
                ->with(['class', 'examSubjects'])
                ->cursor();

            foreach ($exams as $exam) {
                $totalExams++;
                if ($exam->syncStatus()) {
                    $updated++;
                }
            }
        });

        $this->info("Synced statuses on {$totalExams} exam(s); {$updated} transitioned.");
        Log::info('exams:sync-statuses completed', [
            'scanned' => $totalExams,
            'updated' => $updated,
            'school_filter' => $schoolFilter,
        ]);

        return self::SUCCESS;
    }
}
