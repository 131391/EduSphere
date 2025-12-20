<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waiver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'fee_period',
        'actual_fee',
        'waiver_percentage',
        'waiver_amount',
        'upto_months',
        'reason',
    ];

    protected $casts = [
        'actual_fee' => 'decimal:2',
        'waiver_percentage' => 'decimal:2',
        'waiver_amount' => 'decimal:2',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}

