<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enums\FeeStatus;

class Fee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'fee_type_id',
        'class_id',
        'bill_no',
        'fee_period',
        'payable_amount',
        'paid_amount',
        'due_amount',
        'waiver_amount',
        'late_fee',
        'discount_amount',
        'due_date',
        'payment_date',
        'payment_status',
        'payment_mode',
        'transaction_id',
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
            ->logOnly(['bill_no', 'payment_status', 'paid_amount'])
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

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
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

    // Helper methods
    public function markAsPaid($amount, $paymentMode, $transactionId = null): void
    {
        $this->paid_amount = $amount;
        $this->due_amount = $this->payable_amount - $amount - ($this->waiver_amount ?? 0) - ($this->discount_amount ?? 0);
        $this->payment_status = $this->due_amount > 0 ? FeeStatus::Partial : FeeStatus::Paid;
        $this->payment_date = now();
        $this->payment_mode = $paymentMode;
        $this->transaction_id = $transactionId;
        $this->save();
    }
}

