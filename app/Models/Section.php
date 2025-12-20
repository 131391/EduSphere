<?php

namespace App\Models;

use App\Traits\{Searchable, Sortable, Cacheable};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Section extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable, Sortable, Cacheable;

    protected $fillable = [
        'school_id',
        'class_id',
        'name',
        'capacity',
        'current_strength',
    ];

    protected $searchable = ['name'];
    protected $sortable = ['id', 'name', 'capacity', 'created_at'];
    protected $cacheTTL = 3600;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'class_id', 'capacity'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }
}

