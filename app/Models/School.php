<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\SchoolStatus;

class School extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'subdomain',
        'domain',
        'email',
        'phone',
        'address',
        'city_id',
        'state_id',
        'country_id',
        'pincode',
        'logo',
        'site_icon',
        'website',
        'status',
        'subscription_start_date',
        'subscription_end_date',
        'settings',
        'features',
    ];

    protected $casts = [
        'settings' => 'array',
        'features' => 'array',
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'status' => SchoolStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'email'])
            ->logOnlyDirty();
    }

    // Relationships
    public function country()
    {
        return $this->belongsTo(\Nnjeim\World\Models\Country::class);
    }

    public function state()
    {
        return $this->belongsTo(\Nnjeim\World\Models\State::class);
    }

    public function city()
    {
        return $this->belongsTo(\Nnjeim\World\Models\City::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function parents()
    {
        return $this->hasMany(StudentParent::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', SchoolStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === SchoolStatus::Active;
    }

    public function isSubscriptionActive(): bool
    {
        if (!$this->subscription_end_date) {
            return true;
        }

        return now()->lte($this->subscription_end_date);
    }
}

