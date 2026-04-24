<?php

namespace App\Exports;

use App\Models\FeePayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyCollectionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $schoolId;
    protected $date;

    public function __construct($schoolId, $date)
    {
        $this->schoolId = $schoolId;
        $this->date = $date;
    }

    public function collection()
    {
        return FeePayment::with(['student.class', 'fee.feeName', 'paymentMethod'])
            ->where('school_id', $this->schoolId)
            ->whereDate('payment_date', $this->date)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Receipt No',
            'Payment Date',
            'Admission No',
            'Student Name',
            'Class',
            'Fee Particular',
            'Payment Method',
            'Reference',
            'Amount (INR)'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->receipt_no,
            $payment->payment_date->format('Y-m-d'),
            $payment->student->admission_no ?? 'N/A',
            $payment->student->full_name ?? 'N/A',
            $payment->student->class->name ?? 'N/A',
            $payment->fee->feeName->name ?? 'N/A',
            $payment->paymentMethod->name ?? 'N/A',
            $payment->transaction_id ?? 'N/A',
            $payment->amount
        ];
    }
}
