<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentPromotion extends Model
{
    use Tenantable, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'from_academic_year_id',
        'to_academic_year_id',
        'from_class_id',
        'to_class_id',
        'from_section_id',
        'to_section_id',
        'result',
        'promoted_by',
    ];

    const RESULT_PROMOTED  = 1;
    const RESULT_GRADUATED = 2;
    const RESULT_DETAINED  = 3;
    const RESULT_TRANSFERRED = 4;

    public function student(): BelongsTo  { return $this->belongsTo(Student::class); }
    public function fromAcademicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class, 'from_academic_year_id'); }
    public function toAcademicYear(): BelongsTo   { return $this->belongsTo(AcademicYear::class, 'to_academic_year_id'); }
    public function fromClass(): BelongsTo { return $this->belongsTo(ClassModel::class, 'from_class_id'); }
    public function toClass(): BelongsTo   { return $this->belongsTo(ClassModel::class, 'to_class_id'); }
    public function fromSection(): BelongsTo { return $this->belongsTo(Section::class, 'from_section_id'); }
    public function toSection(): BelongsTo   { return $this->belongsTo(Section::class, 'to_section_id'); }
    public function promotedBy(): BelongsTo  { return $this->belongsTo(User::class, 'promoted_by'); }
}
