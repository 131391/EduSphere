<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AdmissionStatus;

class StudentRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        // Registration Form Information
        'school_id', 'enquiry_id', 'registration_no', 'academic_year_id', 'class_id',
        'registration_fee', 'registration_date',
        
        // Personal Information
        'first_name', 'middle_name', 'last_name', 'gender', 'dob', 'email', 'mobile_no',
        'student_type', 'blood_group', 'dob_certificate_no', 'aadhar_no', 'place_of_birth',
        'nationality', 'religion', 'category', 'special_needs', 'mother_tongue', 'remarks',
        
        // Family Information
        'number_of_brothers', 'number_of_sisters', 'is_single_parent', 'corresponding_relative',
        'is_transport_required', 'bus_stop', 'other_stop', 'boarding_type',
        
        // Father's Details
        'father_name_prefix', 'father_first_name', 'father_middle_name', 'father_last_name',
        'father_email', 'father_mobile_no', 'father_landline_no', 'father_occupation',
        'father_organization', 'father_office_address', 'father_qualification',
        'father_department', 'father_designation', 'father_aadhar_no', 'father_annual_income',
        'father_age',
        
        // Mother's Details
        'mother_name_prefix', 'mother_first_name', 'mother_middle_name', 'mother_last_name',
        'mother_email', 'mother_mobile_no', 'mother_landline_no', 'mother_occupation',
        'mother_organization', 'mother_office_address', 'mother_qualification',
        'mother_department', 'mother_designation', 'mother_aadhar_no', 'mother_annual_income',
        'mother_age',
        
        // Permanent Address
        'permanent_latitude', 'permanent_longitude', 'permanent_address', 'permanent_country', 'permanent_country_id',
        'permanent_state', 'permanent_city', 'permanent_pin', 'permanent_state_of_domicile',
        'permanent_railway_airport', 'permanent_correspondence_address',
        
        // Correspondence Address
        'correspondence_address', 'correspondence_country', 'correspondence_country_id', 'correspondence_state',
        'correspondence_city', 'correspondence_pin', 'correspondence_location',
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
        'is_single_parent' => 'boolean',
        'is_transport_required' => 'boolean',
        'number_of_brothers' => 'integer',
        'number_of_sisters' => 'integer',
        'father_age' => 'integer',
        'mother_age' => 'integer',
        'permanent_country_id' => 'integer',
        'correspondence_country_id' => 'integer',
        'admission_status' => AdmissionStatus::class,
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
     * Generate unique registration number
     */
    private static function generateRegistrationNumber($schoolId)
    {
        $year = date('Y');
        $lastRegistration = self::where('school_id', $schoolId)
            ->where('registration_no', 'like', "REG-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRegistration) {
            $lastNumber = (int) substr($lastRegistration->registration_no, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('REG-%s-%05d', $year, $newNumber);
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
        return match($this->gender) {
            'Male', 'male', 1 => 'Male',
            'Female', 'female', 2 => 'Female',
            'Other', 'other', 3 => 'Other',
            default => $this->gender ?? 'Not Specified'
        };
    }
}
