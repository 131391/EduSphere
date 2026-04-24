<?php

namespace App\Models;

use App\Traits\Tenantable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\FeeStatus;

/**
 * @property string|null $due_amount
 * @property string|null $payment_date
 */
class Fee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Tenantable;

    protected $fillable = [
        'school_id',
        'student_id',
        'registration_id',
        'academic_year_id',
        'fee_type_id',
        'fee_name_id',
        'class_id',
        'bill_no',
        'fee_period',
        'payable_amount',
        'due_date',
        'remarks',
    ];

    protected $casts = [
        'payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'waiver_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'payment_status' => FeeStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['bill_no', 'payment_status', 'paid_amount', 'student.first_name'])
            ->logOnlyDirty();
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function registration()
    {
        return $this->belongsTo(StudentRegistration::class, 'registration_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function feeName()
    {
        return $this->belongsTo(FeeName::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', FeeStatus::Paid);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', FeeStatus::Pending);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', FeeStatus::Overdue)
            ->orWhere(function ($q) {
                $q->where('payment_status', FeeStatus::Pending)
                  ->where('due_date', '<', now());
            });
    }

}

