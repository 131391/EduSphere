<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'subject_id',
        'teacher_id',
        'subject_name',
        'exam_date',
        'start_time',
        'end_time',
        'room_no',
        'full_marks',
        'sort_order',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'full_marks' => 'integer',
        'sort_order' => 'integer',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getResolvedNameAttribute(): string
    {
        return $this->subject_name
            ?: $this->subject?->name
            ?: 'Subject';
    }
}
