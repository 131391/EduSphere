<?php

namespace App\Services\School;

use App\Enums\FeeStatus;
use App\Models\Fee;
use App\Models\FeeMaster;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeService
{
    protected NumberingService $numbering;

    public function __construct(NumberingService $numbering)
    {
        $this->numbering = $numbering;
    }

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
                    // Dedup matches the uq_fees_student_period unique index (includes soft-deleted rows
                    // because MySQL enforces uniqueness across all rows regardless of deleted_at).
                    $exists = Fee::withTrashed()
                        ->where('school_id', $school->id)
                        ->where('student_id', $student->id)
                        ->where('academic_year_id', $academicYearId)
                        ->where('fee_type_id', $feeTypeId)
                        ->where('fee_name_id', $feeMaster->fee_name_id)
                        ->where('fee_period', $feePeriod)
                        ->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }

                    Fee::forceCreate([
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYearId,
                        'fee_type_id' => $feeTypeId,
                        'fee_name_id' => $feeMaster->fee_name_id,
                        'class_id' => $classId,
                        'bill_no' => $this->numbering->nextBillNo($school->id),
                        'fee_period' => $feePeriod,
                        'payable_amount' => $feeMaster->amount,
                        'due_amount' => $feeMaster->amount,
                        'due_date' => $dueDate,
                        'payment_status' => FeeStatus::Pending,
                    ]);

                    $generatedCount++;
                }
            }

            // Trigger waiver redistribution for the newly generated fees
            // BEFORE committing so the whole generation+waiver run is atomic.
            $studentIds = $students->pluck('id');
            $waivers = \App\Models\Waiver::where('school_id', $school->id)
                ->whereIn('student_id', $studentIds)
                ->where('academic_year_id', $academicYearId)
                ->where('fee_period', $feePeriod)
                ->get();

            foreach ($waivers as $waiver) {
                $waiver->touch();
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
     * Get students with pending fees
     */
    public function getPendingFees(School $school, array $filters = [])
    {
        $query = Fee::where('school_id', $school->id)
            ->with(['student', 'feeName', 'class'])
            ->where('payment_status', '!=', FeeStatus::Paid->value);

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
