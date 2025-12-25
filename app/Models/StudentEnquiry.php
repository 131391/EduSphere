<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\EnquiryStatus;
use App\Enums\Gender;

class StudentEnquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'enquiry_no',
        'academic_year_id',
        'class_id',
        // Enquiry Form
        'subject_name',
        'student_name',
        'gender',
        'follow_up_date',
        // Father's Details
        'father_name',
        'father_contact',
        'father_email',
        'father_qualification',
        'father_occupation',
        'father_annual_income',
        'father_organization',
        'father_office_address',
        'father_department',
        'father_designation',
        // Mother's Details
        'mother_name',
        'mother_contact',
        'mother_email',
        'mother_qualification',
        'mother_occupation',
        'mother_annual_income',
        'mother_organization',
        'mother_office_address',
        'mother_department',
        'mother_designation',
        // Contact Details
        'contact_no',
        'whatsapp_no',
        'facebook_id',
        'email_id',
        'sms_no',
        'twitter_id',
        'emergency_contact_no',
        // Personal Details
        'dob',
        'aadhar_no',
        'grand_father_name',
        'annual_income',
        'no_of_brothers',
        'no_of_sisters',
        'category',
        'minority',
        'religion',
        'transport_facility',
        'hostel_facility',
        'previous_class',
        'identity_marks',
        'permanent_address',
        'country_id',
        'previous_school_name',
        'student_roll_no',
        'passing_year',
        'exam_name',
        'board_university',
        'only_child',
        // Photos
        'father_photo',
        'mother_photo',
        'student_photo',
        // Status & Dates
        'form_status',
        'enquiry_date',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'dob' => 'date',
        'enquiry_date' => 'date',
        'father_annual_income' => 'decimal:2',
        'mother_annual_income' => 'decimal:2',
        'annual_income' => 'decimal:2',
        'no_of_brothers' => 'integer',
        'no_of_sisters' => 'integer',
        'only_child' => 'boolean',
        'passing_year' => 'integer',
        'country_id' => 'integer',
        'form_status' => EnquiryStatus::class,
        'gender' => Gender::class,
    ];

    /**
     * Boot method to auto-generate enquiry number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enquiry) {
            if (empty($enquiry->enquiry_no)) {
                $enquiry->enquiry_no = self::generateEnquiryNo($enquiry->school_id);
            }
            if (empty($enquiry->enquiry_date)) {
                $enquiry->enquiry_date = now();
            }
        });
    }

    /**
     * Generate unique enquiry number
     */
    public static function generateEnquiryNo($schoolId): string
    {
        $year = date('Y');
        $prefix = 'ENQ';
        
        $lastEnquiry = self::where('school_id', $schoolId)
            ->where('enquiry_no', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEnquiry) {
            $lastNumber = (int) substr($lastEnquiry->enquiry_no, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $newNumber);
    }

    /**
     * Relationships
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('form_status', EnquiryStatus::Pending);
    }

    public function scopeCompleted($query)
    {
        return $query->where('form_status', EnquiryStatus::Completed);
    }

    public function scopeCancelled($query)
    {
        return $query->where('form_status', EnquiryStatus::Cancelled);
    }

    public function scopeAdmitted($query)
    {
        return $query->where('form_status', EnquiryStatus::Admitted);
    }
}
