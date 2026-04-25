<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\StudentStatus;
use App\Enums\Gender;
use App\Enums\GeneralStatus;
use App\Enums\YesNo;
use App\Models\StudentRegistration;
use App\Models\StudentEnquiry;

class Student extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Tenantable;

    protected $fillable = [
        'school_id',
        'user_id',
        'academic_year_id',
        'admission_no',
        'registration_no',
        'enquiry_id',
        'admission_payment_method_id',
        'roll_no',
        'receipt_no',
        'admission_fee',
        'referred_by',
        'first_name',
        'middle_name',
        'last_name',
        'father_name',
        'mother_name',
        'dob',
        'gender',
        'blood_group_id',
        'religion_id',
        'category_id',
        'address',
        'permanent_address',
        'permanent_country_id',
        'permanent_state_id',
        'permanent_city_id',
        'permanent_pin',
        'latitude',
        'longitude',
        'mobile_no',
        'email',
        'student_photo',
        'father_photo',
        'mother_photo',
        'student_signature',
        'father_signature',
        'mother_signature',
        'class_id',
        'section_id',
        'student_type_id',
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
        'corresponding_relative_id',
        'is_transport_required',
        'bus_stop',
        'other_stop',
        'boarding_type_id',
        'father_aadhaar_no',
        'father_pan',
        'father_email',
        'father_mobile_no',
        'father_occupation',
        'father_qualification_id',
        'father_annual_income',
        'mother_aadhaar_no',
        'mother_pan',
        'mother_email',
        'mother_mobile_no',
        'mother_occupation',
        'mother_qualification_id',
        'mother_annual_income',
        'state_of_domicile',
        'railway_airport',
        'correspondence_address',
        'correspondence_country_id',
        'correspondence_state_id',
        'correspondence_city_id',
        'correspondence_pin',
        'correspondence_location',
        'distance_from_school',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'additional_info' => 'array',
        'permanent_country_id' => 'integer',
        'permanent_state_id' => 'integer',
        'permanent_city_id' => 'integer',
        'correspondence_country_id' => 'integer',
        'correspondence_state_id' => 'integer',
        'correspondence_city_id' => 'integer',
        'status' => StudentStatus::class,
        'gender' => Gender::class,
        'is_single_parent' => YesNo::class,
        'is_transport_required' => YesNo::class,
        'blood_group_id' => 'integer',
        'religion_id' => 'integer',
        'category_id' => 'integer',
        'student_type_id' => 'integer',
        'corresponding_relative_id' => 'integer',
        'boarding_type_id' => 'integer',
        'father_qualification_id' => 'integer',
        'mother_qualification_id' => 'integer',
    ];

    protected function initializeEncryption(): void
    {
        $this->encryptable = [
            'aadhaar_no',
            'father_aadhaar_no',
            'mother_aadhaar_no',
            'father_pan',
            'mother_pan',
        ];
    }

    public function getAadhaarNoAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setAadhaarNoAttribute($value): void
    {
        $this->attributes['aadhaar_no'] = $value ? encrypt($value) : null;
    }

    public function getFatherAadhaarNoAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setFatherAadhaarNoAttribute($value): void
    {
        $this->attributes['father_aadhaar_no'] = $value ? encrypt($value) : null;
    }

    public function getMotherAadhaarNoAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setMotherAadhaarNoAttribute($value): void
    {
        $this->attributes['mother_aadhaar_no'] = $value ? encrypt($value) : null;
    }

    public function getFatherPanAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setFatherPanAttribute($value): void
    {
        $this->attributes['father_pan'] = $value ? encrypt($value) : null;
    }

    public function getMotherPanAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setMotherPanAttribute($value): void
    {
        $this->attributes['mother_pan'] = $value ? encrypt($value) : null;
    }

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

    public function bloodGroup()
    {
        return $this->belongsTo(\App\Models\BloodGroup::class, 'blood_group_id');
    }

    public function religion()
    {
        return $this->belongsTo(\App\Models\Religion::class, 'religion_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function studentType()
    {
        return $this->belongsTo(\App\Models\StudentType::class, 'student_type_id');
    }

    public function correspondingRelative()
    {
        return $this->belongsTo(\App\Models\CorrespondingRelative::class, 'corresponding_relative_id');
    }

    public function boardingType()
    {
        return $this->belongsTo(\App\Models\BoardingType::class, 'boarding_type_id');
    }

    public function fatherQualification()
    {
        return $this->belongsTo(\App\Models\Qualification::class, 'father_qualification_id');
    }

    public function motherQualification()
    {
        return $this->belongsTo(\App\Models\Qualification::class, 'mother_qualification_id');
    }

    public function parents()
    {
        return $this->belongsToMany(StudentParent::class, 'student_parent', 'student_id', 'parent_id')
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

    public function registration()
    {
        return $this->hasOne(StudentRegistration::class, 'registration_no', 'registration_no');
    }

    public function enquiry()
    {
        return $this->belongsTo(StudentEnquiry::class, 'enquiry_id');
    }

    public function transportAssignment()
    {
        return $this->hasOne(StudentTransportAssignment::class)->where('status', GeneralStatus::Active);
    }

    public function hostelAssignment()
    {
        return $this->hasOne(HostelBedAssignment::class)->where('status', GeneralStatus::Active);
    }

    // Location Relationships
    public function permanentCountry()
    {
        return $this->belongsTo(Country::class, 'permanent_country_id');
    }

    public function permanentState()
    {
        return $this->belongsTo(State::class, 'permanent_state_id');
    }

    public function permanentCity()
    {
        return $this->belongsTo(City::class, 'permanent_city_id');
    }

    public function correspondenceCountry()
    {
        return $this->belongsTo(Country::class, 'correspondence_country_id');
    }

    public function correspondenceState()
    {
        return $this->belongsTo(State::class, 'correspondence_state_id');
    }

    public function correspondenceCity()
    {
        return $this->belongsTo(City::class, 'correspondence_city_id');
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

    public function getFatherFirstNameAttribute(): string
    {
        return $this->splitName($this->father_name)['first'];
    }

    public function getFatherMiddleNameAttribute(): string
    {
        return $this->splitName($this->father_name)['middle'];
    }

    public function getFatherLastNameAttribute(): string
    {
        return $this->splitName($this->father_name)['last'];
    }

    public function getMotherFirstNameAttribute(): string
    {
        return $this->splitName($this->mother_name)['first'];
    }

    public function getMotherMiddleNameAttribute(): string
    {
        return $this->splitName($this->mother_name)['middle'];
    }

    public function getMotherLastNameAttribute(): string
    {
        return $this->splitName($this->mother_name)['last'];
    }

    public function getGenderLabelAttribute()
    {
        if ($this->gender instanceof Gender) {
            return $this->gender->label();
        }

        if ($this->gender === null) {
            return 'Not Specified';
        }

        // Handle legacy integer values
        $genderValue = (int) $this->gender;

        return match ($genderValue) {
            1 => 'Male',
            2 => 'Female',
            3 => 'Other',
            default => 'Not Specified'
        };
    }
}

