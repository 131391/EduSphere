<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\Gender;
use App\Enums\StaffPost;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'post',
        'class_id',
        'section_id',
        'name',
        'mobile',
        'email',
        'gender',
        'total_experience',
        'previous_school_salary',
        'current_salary',
        'country_id',
        'state',
        'city',
        'zip_code',
        'address',
        'aadhar_no',
        'aadhar_card',
        'staff_image',
        'joining_date',
        'higher_qualification_id',
        'previous_school_company_name',
    ];

    protected $casts = [
        'post' => StaffPost::class,
        'gender' => Gender::class,
        'total_experience' => 'integer',
        'previous_school_salary' => 'decimal:2',
        'current_salary' => 'decimal:2',
        'country_id' => 'integer',
        'joining_date' => 'date',
    ];

    /**
     * Get the school that owns the staff.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the class assigned to the staff (for teachers).
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the section assigned to the staff (for teachers).
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the higher qualification.
     */
    public function higherQualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class, 'higher_qualification_id');
    }
}
