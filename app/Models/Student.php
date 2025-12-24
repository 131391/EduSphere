<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\StudentStatus;

class Student extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'school_id',
        'user_id',
        'academic_year_id',
        'admission_no',
        'registration_no',
        'roll_no',
        'receipt_no',
        'admission_fee',
        'referred_by',
        'first_name',
        'middle_name',
        'last_name',
        'father_name',
        'mother_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'category',
        'address',
        'phone',
        'email',
        'photo',
        'class_id',
        'section_id',
        'student_type',
        'status',
        'admission_date',
        'additional_info',
        'dob_certificate_no',
        'place_of_birth',
        'aadhaar_no',
        'nationality',
        'mother_tongue',
        'special_needs',
        'remarks',
        'number_of_brothers',
        'number_of_sisters',
        'is_single_parent',
        'corresponding_relative',
        'transport_required',
        'bus_stop',
        'other_stop',
        'boarding_type',
        'father_occupation',
        'father_organization',
        'father_office_address',
        'father_qualification',
        'father_email',
        'father_mobile',
        'father_landline',
        'father_aadhaar',
        'father_annual_income',
        'father_designation',
        'mother_occupation',
        'mother_organization',
        'mother_office_address',
        'mother_qualification',
        'mother_email',
        'mother_mobile',
        'mother_landline',
        'mother_aadhaar',
        'mother_annual_income',
        'mother_designation',
        'permanent_address',
        'permanent_country_id',
        'permanent_state',
        'permanent_city',
        'permanent_pin',
        'state_of_domicile',
        'latitude',
        'longitude',
        'correspondence_address',
        'correspondence_country_id',
        'correspondence_state',
        'correspondence_city',
        'correspondence_pin',
        'father_pan',
        'mother_pan',
        'railway_airport',
        'distance_from_school',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'additional_info' => 'array',
        'permanent_country_id' => 'integer',
        'correspondence_country_id' => 'integer',
        'status' => StudentStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['admission_no', 'first_name', 'class_id', 'section_id', 'status'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function parents()
    {
        return $this->belongsToMany(StudentParent::class, 'student_parent')
            ->using(StudentParentPivot::class)
            ->withPivot('relation', 'is_primary')
            ->withTimestamps();
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'admission_no', 'admission_no');
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', StudentStatus::Active);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Helper to split name
    private function splitName($fullName)
    {
        $parts = explode(' ', $fullName);
        $count = count($parts);
        
        if ($count === 1) {
            return ['first' => $parts[0], 'middle' => '', 'last' => ''];
        } elseif ($count === 2) {
            return ['first' => $parts[0], 'middle' => '', 'last' => $parts[1]];
        } else {
            $first = array_shift($parts);
            $last = array_pop($parts);
            $middle = implode(' ', $parts);
            return ['first' => $first, 'middle' => $middle, 'last' => $last];
        }
    }

    public function getFatherFirstNameAttribute()
    {
        return $this->splitName($this->father_name)['first'];
    }

    public function getFatherMiddleNameAttribute()
    {
        return $this->splitName($this->father_name)['middle'];
    }

    public function getFatherLastNameAttribute()
    {
        return $this->splitName($this->father_name)['last'];
    }

    public function getMotherFirstNameAttribute()
    {
        return $this->splitName($this->mother_name)['first'];
    }

    public function getMotherMiddleNameAttribute()
    {
        return $this->splitName($this->mother_name)['middle'];
    }

    public function getMotherLastNameAttribute()
    {
        return $this->splitName($this->mother_name)['last'];
    }

    public function getGenderLabelAttribute()
    {
        return match((int)$this->gender) {
            1 => 'Male',
            2 => 'Female',
            3 => 'Other',
            default => 'Not Specified'
        };
    }
}

