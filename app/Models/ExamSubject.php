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
        'subject_name',
        'full_marks',
        'sort_order',
    ];

    protected $casts = [
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

    public function getResolvedNameAttribute(): string
    {
        return $this->subject_name
            ?: $this->subject?->name
            ?: 'Subject';
    }
}
