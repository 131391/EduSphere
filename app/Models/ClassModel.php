<?php

namespace App\Models;

use App\Traits\{Searchable, Sortable, Cacheable};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ClassModel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable, Sortable, Cacheable;

    protected $table = 'classes';

    protected $fillable = [
        'school_id',
        'name',
        'order',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Searchable fields
     */
    protected $searchable = ['name'];

    /**
     * Sortable columns
     */
    protected $sortable = ['id', 'name', 'order', 'created_at'];

    /**
     * Cache TTL (1 hour)
     */
    protected $cacheTTL = 3600;

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'order', 'is_available'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function registrationFee()
    {
        return $this->hasOne(RegistrationFee::class, 'class_id');
    }

    public function admissionFee()
    {
        return $this->hasOne(AdmissionFee::class, 'class_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withPivot('teacher_id', 'weekly_hours', 'full_marks')
            ->withTimestamps();
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'class_id');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class, 'class_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}

