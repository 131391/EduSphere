<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'school_id',
        'student_id',
        'exam_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'marks_obtained',
        'total_marks',
        'percentage',
        'grade',
        'remarks',
        'is_absent',
        'entered_by',
        'locked_at',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_absent' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class)->withTrashed();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
