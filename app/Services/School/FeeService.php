<?php

namespace App\Services\School;

use App\Models\Fee;
use App\Models\FeeMaster;
use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeeService
{
    /**
     * Generate fees for a class of students
     */
    public function generateClassFees(School $school, array $data): array
    {
        $classId = $data['class_id'];
        $academicYearId = $data['academic_year_id'];
        $feeTypeId = $data['fee_type_id'];
        $feeNameIds = $data['fee_name_ids']; // Array of fee name IDs
        $feePeriod = $data['fee_period'];     // e.g., "April 2025"
        $dueDate = $data['due_date'];

        $students = Student::where('school_id', $school->id)
            ->where('class_id', $classId)
            ->active()
            ->get();

        if ($students->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active students found in the selected class.'
            ];
        }

        $feeMasters = FeeMaster::where('school_id', $school->id)
            ->where('class_id', $classId)
            ->where('fee_type_id', $feeTypeId)
            ->whereIn('fee_name_id', $feeNameIds)
            ->get();

        if ($feeMasters->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No fee configurations found for the selection.'
            ];
        }

        $generatedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                foreach ($feeMasters as $feeMaster) {
                    // Check if fee already generated for this student, name, and period
                    $exists = Fee::where('school_id', $school->id)
                        ->where('student_id', $student->id)
                        ->where('fee_name_id', $feeMaster->fee_name_id)
                        ->where('fee_period', $feePeriod)
                        ->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }

                    Fee::create([
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYearId,
                        'fee_type_id' => $feeTypeId,
                        'fee_name_id' => $feeMaster->fee_name_id,
                        'class_id' => $classId,
                        'bill_no' => $this->generateBillNumber($school, $student),
                        'fee_period' => $feePeriod,
                        'payable_amount' => $feeMaster->amount,
                        'due_amount' => $feeMaster->amount,
                        'due_date' => $dueDate,
                        'payment_status' => 1, // Pending
                    ]);

                    $generatedCount++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Fee generation complete. Generated: {$generatedCount}, Skipped: {$skippedCount}",
                'data' => [
                    'generated' => $generatedCount,
                    'skipped' => $skippedCount
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during fee generation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate a unique bill number
     */
    protected function generateBillNumber(School $school, Student $student): string
    {
        $key = 'bill_seq_' . $school->id;
        return \Illuminate\Support\Facades\Cache::lock($key, 5)->block(3, function () use ($school, $student) {
            $prefix = 'INV';
            $year   = date('Y');
            $month  = date('m');
            $last   = Fee::withTrashed()
                ->where('school_id', $school->id)
                ->where('bill_no', 'like', "{$prefix}-{$school->id}-{$year}{$month}-%")
                ->max('bill_no');
            $count = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
            return "{$prefix}-{$school->id}-{$year}{$month}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Get students with pending fees
     */
    public function getPendingFees(School $school, array $filters = [])
    {
        $query = Fee::where('school_id', $school->id)
            ->with(['student', 'feeName', 'class'])
            ->where('payment_status', '!=', 3); // Not Paid

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['search'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%")
                  ->orWhere('admission_no', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate(15);
    }
}
