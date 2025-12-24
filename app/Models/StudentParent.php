<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ParentStatus;
use App\Enums\RelationType;

class StudentParent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'school_id',
        'user_id',
        'first_name',
        'last_name',
        'relation',
        'phone',
        'email',
        'occupation',
        'address',
        'photo',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'additional_info' => 'array',
        'status' => ParentStatus::class,
        'relation' => RelationType::class,
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_parent')
            ->using(StudentParentPivot::class)
            ->withPivot('relation', 'is_primary')
            ->withTimestamps();
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}

