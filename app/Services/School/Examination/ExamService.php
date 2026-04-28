<?php

namespace App\Services\School\Examination;

use App\Enums\ExamStatus;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Result;
use App\Models\School;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function schedule(School $school, array $data): Exam
    {
        $activeYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        if (!$activeYear) {
            throw ValidationException::withMessages([
                'academic_year_id' => ['No current academic year is configured. Please activate an academic year first.'],
            ]);
        }

        $class = ClassModel::where('school_id', $school->id)
            ->whereKey($data['class_id'])
            ->firstOrFail();

        $examType = ExamType::where('school_id', $school->id)
            ->whereKey($data['exam_type_id'])
            ->firstOrFail();

        if (!$class->subjects()->exists()) {
            throw ValidationException::withMessages([
                'class_id' => ['Assign at least one subject to the selected class before scheduling an exam.'],
            ]);
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        $duplicate = Exam::where('school_id', $school->id)
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $class->id)
            ->where('exam_type_id', $examType->id)
            ->whereDate('start_date', $startDate->toDateString())
            ->whereDate('end_date', $endDate->toDateString())
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'start_date' => ['An exam with the same class, type, and schedule already exists.'],
            ]);
        }

        return DB::transaction(function () use ($school, $activeYear, $class, $examType, $data, $startDate, $endDate) {
            $window = $this->buildAssessmentWindow($startDate, $endDate);

            $exam = Exam::create([
                'school_id' => $school->id,
                'academic_year_id' => $activeYear->id,
                'class_id' => $class->id,
                'exam_type_id' => $examType->id,
                'name' => trim((string) (($data['name'] ?? null) ?: "{$examType->name} ({$window})")),
                'month' => $window,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => $startDate->isPast() || $startDate->isToday()
                    ? ExamStatus::Ongoing
                    : ExamStatus::Scheduled,
            ]);

            $exam->ensureSubjectSnapshot();

            return $exam->load(['academicYear', 'class', 'examType', 'examSubjects']);
        });
    }

    public function update(Exam $exam, array $data): Exam
    {
        if ($exam->status?->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => ['A cancelled or locked exam cannot be edited.'],
            ]);
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        return DB::transaction(function () use ($exam, $data, $startDate, $endDate) {
            $window = $this->buildAssessmentWindow($startDate, $endDate);

            $exam->forceFill([
                'name' => trim((string) (($data['name'] ?? null) ?: ($exam->examType?->name . ' (' . $window . ')'))),
                'month' => $window,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ])->save();

            $exam->syncStatus();

            return $exam->fresh(['academicYear', 'class', 'examType', 'examSubjects']);
        });
    }

    public function cancel(Exam $exam): Exam
    {
        $exam->cancel();

        return $exam->fresh();
    }

    public function lock(Exam $exam): Exam
    {
        $exam->lock();

        Result::where('school_id', $exam->school_id)
            ->where('exam_id', $exam->id)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        return $exam->fresh();
    }

    public function delete(Exam $exam): void
    {
        if (Result::withTrashed()->where('exam_id', $exam->id)->exists()) {
            throw ValidationException::withMessages([
                'exam_id' => ['This exam already has recorded marks and cannot be removed.'],
            ]);
        }

        $exam->delete();
    }

    public function syncSchoolStatuses(School $school): void
    {
        Exam::with(['class', 'examSubjects'])
            ->where('school_id', $school->id)
            ->get()
            ->each(function (Exam $exam) {
                $exam->ensureSubjectSnapshot();
                $exam->syncStatus();
            });
    }

    public function buildAssessmentWindow(Carbon $startDate, Carbon $endDate): string
    {
        if ($startDate->isSameMonth($endDate)) {
            return $startDate->format('F Y');
        }

        if ($startDate->isSameYear($endDate)) {
            return $startDate->format('M') . ' - ' . $endDate->format('M Y');
        }

        return $startDate->format('M Y') . ' - ' . $endDate->format('M Y');
    }
}
