<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\YesNo;

class StudentRegistration extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        // Registration Form Information
        'school_id', 'enquiry_id', 'registration_no', 'academic_year_id', 'class_id',
        'registration_fee', 'registration_date',
        
        // Personal Information
        'first_name', 'middle_name', 'last_name', 'gender', 'dob', 'email', 'mobile_no',
        'student_type_id',
        'blood_group_id',
        'dob_certificate_no', 'aadhaar_no', 'place_of_birth',
        'nationality',
        'religion_id',
        'category_id',
        'special_needs', 'mother_tongue', 'remarks',

        // Family Information
        'number_of_brothers', 'number_of_sisters', 'is_single_parent',
        'corresponding_relative_id',
        'is_transport_required', 'bus_stop', 'other_stop',
        'boarding_type_id',

        // Father's Details
        'father_name_prefix', 'father_first_name', 'father_middle_name', 'father_last_name',
        'father_email', 'father_mobile_no', 'father_landline_no', 'father_occupation',
        'father_organization', 'father_office_address',
        'father_qualification_id',
        'father_department', 'father_designation', 'father_aadhaar_no', 'father_annual_income',
        'father_age',

        // Mother's Details
        'mother_name_prefix', 'mother_first_name', 'mother_middle_name', 'mother_last_name',
        'mother_email', 'mother_mobile_no', 'mother_landline_no', 'mother_occupation',
        'mother_organization', 'mother_office_address',
        'mother_qualification_id',
        'mother_department', 'mother_designation', 'mother_aadhaar_no', 'mother_annual_income',
        'mother_age',
        
        // Permanent Address
        'permanent_latitude', 'permanent_longitude', 'permanent_address', 'permanent_country_id',
        'permanent_state_id', 'permanent_city_id', 'permanent_pin', 'permanent_state_of_domicile',
        'permanent_railway_airport', 'permanent_correspondence_address',
        
        // Correspondence Address
        'correspondence_address', 'correspondence_country_id', 'correspondence_state_id',
        'correspondence_city_id', 'correspondence_pin', 'correspondence_location',
        'correspondence_landmark', 'distance_from_school',
        
        // Photo Details
        'father_photo', 'mother_photo', 'student_photo',
        
        // Signature Details
        'father_signature', 'mother_signature', 'student_signature',
        
        // Status
        'admission_status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'dob' => 'date',
        'registration_fee' => 'decimal:2',
        'father_annual_income' => 'decimal:2',
        'mother_annual_income' => 'decimal:2',
        'is_single_parent' => YesNo::class,
        'is_transport_required' => YesNo::class,
        'number_of_brothers' => 'integer',
        'number_of_sisters' => 'integer',
        'father_age' => 'integer',
        'mother_age' => 'integer',
        'permanent_country_id' => 'integer',
        'permanent_state_id' => 'integer',
        'permanent_city_id' => 'integer',
        'correspondence_country_id' => 'integer',
        'correspondence_state_id' => 'integer',
        'correspondence_city_id' => 'integer',
        'admission_status' => AdmissionStatus::class,
        'gender' => Gender::class,
        'blood_group_id' => 'integer',
        'religion_id' => 'integer',
        'category_id' => 'integer',
        'student_type_id' => 'integer',
        'corresponding_relative_id' => 'integer',
        'boarding_type_id' => 'integer',
        'father_qualification_id' => 'integer',
        'mother_qualification_id' => 'integer',
    ];

    /**
     * Boot method to auto-generate registration number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->registration_no)) {
                $registration->registration_no = self::generateRegistrationNumber($registration->school_id);
            }
            
            if (empty($registration->registration_date)) {
                $registration->registration_date = now();
            }
        });
    }

    /**
     * Generate unique registration number with an atomic lock
     */
    private static function generateRegistrationNumber($schoolId)
    {
        return Cache::lock("registration_no_generation_{$schoolId}", 10)->block(5, function () use ($schoolId) {
            $year = date('Y');
            $lastRegistration = self::where('school_id', $schoolId)
                ->where('registration_no', 'like', "REG-{$year}-%")
                ->orderBy('registration_no', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastRegistration) {
                // Extract number from format REG-YYYY-XXXXX
                $parts = explode('-', $lastRegistration->registration_no);
                $lastNumber = (int) end($parts);
                $nextNumber = $lastNumber + 1;
            }

            return sprintf('REG-%s-%05d', $year, $nextNumber);
        });
    }

    /**
     * Relationships
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(StudentEnquiry::class, 'enquiry_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BloodGroup::class, 'blood_group_id');
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Religion::class, 'religion_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function studentType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\StudentType::class, 'student_type_id');
    }

    public function correspondingRelative(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CorrespondingRelative::class, 'corresponding_relative_id');
    }

    public function boardingType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BoardingType::class, 'boarding_type_id');
    }

    public function fatherQualification(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Qualification::class, 'father_qualification_id');
    }

    public function motherQualification(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Qualification::class, 'mother_qualification_id');
    }

    // Location Relationships
    public function permanentCountry()
    {
        return $this->belongsTo(\Nnjeim\World\Models\Country::class, 'permanent_country_id');
    }

    public function permanentState()
    {
        return $this->belongsTo(\Nnjeim\World\Models\State::class, 'permanent_state_id');
    }

    public function permanentCity()
    {
        return $this->belongsTo(\Nnjeim\World\Models\City::class, 'permanent_city_id');
    }

    public function correspondenceCountry()
    {
        return $this->belongsTo(\Nnjeim\World\Models\Country::class, 'correspondence_country_id');
    }

    public function correspondenceState()
    {
        return $this->belongsTo(\Nnjeim\World\Models\State::class, 'correspondence_state_id');
    }

    public function correspondenceCity()
    {
        return $this->belongsTo(\Nnjeim\World\Models\City::class, 'correspondence_city_id');
    }

    /**
     * Query Scopes
     */
    public function scopePending($query)
    {
        return $query->where('admission_status', AdmissionStatus::Pending);
    }

    public function scopeAdmitted($query)
    {
        return $query->where('admission_status', AdmissionStatus::Admitted);
    }

    public function scopeCancelled($query)
    {
        return $query->where('admission_status', AdmissionStatus::Cancelled);
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getFatherFullNameAttribute()
    {
        return trim("{$this->father_name_prefix} {$this->father_first_name} {$this->father_middle_name} {$this->father_last_name}");
    }

    public function getMotherFullNameAttribute()
    {
        return trim("{$this->mother_name_prefix} {$this->mother_first_name} {$this->mother_middle_name} {$this->mother_last_name}");
    }

    public function getGenderLabelAttribute()
    {
        if ($this->gender instanceof Gender) {
            return $this->gender->label();
        }
        
        if ($this->gender === null) {
            return 'Not Specified';
        }
        
        // Handle legacy string or integer values
        $genderValue = is_numeric($this->gender) ? (int)$this->gender : strtolower((string)$this->gender);
        
        return match($genderValue) {
            1, 'male' => 'Male',
            2, 'female' => 'Female',
            3, 'other' => 'Other',
            default => 'Not Specified'
        };
    }
}
