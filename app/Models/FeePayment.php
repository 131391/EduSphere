<?php

namespace App\Models;


use App\Traits\{Searchable, Sortable, Tenantable};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FeePayment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable, Sortable, Tenantable;

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_id',
        'academic_year_id',
        'amount',
        'payment_date',
        'payment_method_id',
        'receipt_no',
        'transaction_id',
        'idempotency_key',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $searchable = ['receipt_no', 'transaction_id'];
    protected $sortable = ['id', 'payment_date', 'amount', 'created_at'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'receipt_no', 'transaction_id'])
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

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
