<?php

namespace App\Exports;

use App\Models\Fee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DefaultersExport implements FromCollection, WithHeadings, WithMapping
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
        return Fee::with(['student.class', 'feeName'])
            ->where('school_id', $this->schoolId)
            ->where('due_amount', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $this->date)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Admission No',
            'Student Name',
            'Class',
            'Contact No',
            'Fee Particular',
            'Due Date',
            'Due Amount (INR)',
            'Late Fee Applied (INR)'
        ];
    }

    public function map($fee): array
    {
        return [
            $fee->student->admission_no ?? 'N/A',
            $fee->student->full_name ?? 'N/A',
            $fee->student->class->name ?? 'N/A',
            $fee->student->father_mobile_no ?? $fee->student->mobile_no ?? 'N/A',
            $fee->feeName->name ?? 'N/A',
            $fee->due_date->format('Y-m-d'),
            $fee->due_amount,
            $fee->late_fee ?? '0.00'
        ];
    }
}
