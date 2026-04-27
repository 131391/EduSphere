<?php

namespace App\Models;

use Carbon\CarbonInterface;
use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\Enums\ExamStatus;

class Exam extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'exam_type_id',
        'name',
        'code',
        'month',
        'start_date',
        'end_date',
        'description',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => ExamStatus::class,
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class)->orderBy('sort_order');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if (filled($this->name)) {
            return $this->name;
        }

        $examType = $this->examType?->name;
        $window = $this->assessment_window;

        if ($examType && $window) {
            return "{$examType} ({$window})";
        }

        return $examType ?: $window ?: 'Exam';
    }

    public function getAssessmentWindowAttribute(): ?string
    {
        if ($this->start_date && $this->end_date) {
            $startDate = $this->start_date;
            $endDate = $this->end_date;

            if ($startDate->isSameMonth($endDate)) {
                return $startDate->format('M d') . ' - ' . $endDate->format('d, Y');
            }

            return $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        }

        return $this->month;
    }

    public function ensureSubjectSnapshot(): void
    {
        $class = $this->class()->with(['subjects' => function ($query) {
            $query->orderBy('subjects.order')->orderBy('subjects.name');
        }])->first();

        if (!$class || $class->subjects->isEmpty()) {
            return;
        }

        $existingSubjectIds = $this->examSubjects()->pluck('subject_id')->filter()->all();
        $rows = [];
        $timestamp = now();

        foreach ($class->subjects as $index => $subject) {
            if (in_array($subject->id, $existingSubjectIds, true)) {
                continue;
            }

            $rows[] = [
                'exam_id' => $this->id,
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'full_marks' => (int) ($subject->pivot->full_marks ?: 100),
                'sort_order' => (int) ($subject->order ?: $index),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if ($rows !== []) {
            DB::table('exam_subjects')->insert($rows);
        }
    }

    public function resolveStatus(): ExamStatus
    {
        if ($this->status === ExamStatus::Cancelled) {
            return ExamStatus::Cancelled;
        }

        $this->ensureSubjectSnapshot();

        $resultsCount = $this->results()->count();
        $subjectCount = $this->examSubjects()->count();
        $studentCount = Student::where('school_id', $this->school_id)
            ->where('class_id', $this->class_id)
            ->active()
            ->count();
        $expectedResultCount = $subjectCount * $studentCount;

        if ($expectedResultCount > 0 && $resultsCount >= $expectedResultCount) {
            return ExamStatus::Completed;
        }

        if ($resultsCount > 0) {
            return ExamStatus::Ongoing;
        }

        if ($this->start_date instanceof CarbonInterface && now()->startOfDay()->gte($this->start_date->copy()->startOfDay())) {
            return ExamStatus::Ongoing;
        }

        return ExamStatus::Scheduled;
    }

    public function syncStatus(): bool
    {
        $resolvedStatus = $this->resolveStatus();

        if ($this->status === $resolvedStatus) {
            return false;
        }

        $this->forceFill(['status' => $resolvedStatus])->save();

        return true;
    }
}
