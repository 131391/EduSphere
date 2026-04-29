<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $return_date
 * @property \Illuminate\Support\Carbon|null $fine_paid_at
 */
class BookIssue extends Model
{
    use HasFactory, Tenantable, SoftDeletes;

    // school_id is intentionally excluded — set automatically by Tenantable.
    protected $fillable = [
        'book_id',
        'student_id',
        'staff_id',
        'issue_date',
        'due_date',
        'return_date',
        'fine_amount',
        'fine_paid_at',
        'fine_paid_amount',
        'fine_payment_method',
        'fine_collected_by',
        'fine_settlement_note',
        'last_notified_at',
        'status',
        'renewal_count',
    ];

    protected $casts = [
        'issue_date'       => 'date',
        'due_date'         => 'date',
        'return_date'      => 'date',
        'fine_amount'      => 'decimal:2',
        'fine_paid_amount' => 'decimal:2',
        'fine_paid_at'     => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;
        $query = $this->where($this->getRouteKeyName(), $value);
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        return $query->firstOrFail();
    }

    public function isFineSettled(): bool
    {
        return $this->fine_paid_at !== null;
    }

    public function isOverdue(): bool
    {
        if ($this->status !== 'issued') return false;
        return \Illuminate\Support\Carbon::parse($this->due_date)->isPast();
    }

    public function getBeneficiaryNameAttribute(): string
    {
        if ($this->student_id) return $this->student?->full_name ?? 'Unknown Student';
        if ($this->staff_id) return $this->staff?->name ?? 'Unknown Staff';
        return 'N/A';
    }

    public function getBeneficiaryIdAttribute(): string
    {
        if ($this->student_id) return $this->student?->admission_no ?? 'N/A';
        if ($this->staff_id) {
            $staff = $this->staff;
            return $staff?->mobile ?? $staff?->email ?? 'Staff #' . $this->staff_id;
        }
        return 'N/A';
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
