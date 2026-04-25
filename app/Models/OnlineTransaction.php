<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineTransaction extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_id',
        'amount',
        'gateway_name',
        'gateway_order_id',
        'gateway_transaction_id',
        'status',
        'payload',
        'failed_at',
        'error_message',
    ];

    protected $casts = [
        'payload'   => 'array',
        'amount'    => 'decimal:2',
        'failed_at' => 'datetime',
    ];

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
}
