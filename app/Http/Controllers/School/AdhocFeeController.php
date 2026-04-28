<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeeType;
use App\Models\MiscellaneousFee;
use App\Models\Student;
use App\Models\Waiver;
use App\Services\School\NumberingService;
use App\Enums\FeeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdhocFeeController extends TenantController
{
    protected NumberingService $numberingService;

    public function __construct(NumberingService $numberingService)
    {
        parent::__construct();
        $this->numberingService = $numberingService;
    }

    public function getStudentsByClass(Request $request, $classId)
    {
        $this->ensureSchoolActive();

        $students = Student::where('school_id', $this->getSchoolId())
            ->where('class_id', $classId)
            ->active()
            ->orderByRaw('COALESCE(roll_no, 999999999)')
            ->orderBy('first_name')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'full_name' => $s->full_name,
                'admission_no' => $s->admission_no,
                'section_name' => $s->section?->name ?? '—',
            ]);

        return response()->json(['success' => true, 'data' => $students]);
    }

    public function create()
    {
        $this->ensureSchoolActive();
        $this->authorize('create', Fee::class);

        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();
        $miscFees = MiscellaneousFee::where('school_id', $this->getSchoolId())->where('is_active', true)->get();

        return view('school.fees.adhoc', compact('classes', 'academicYears', 'miscFees'));
    }

    public function store(Request $request)
    {
        $this->ensureSchoolActive();
        $this->authorize('create', Fee::class);

        $validated = $request->validate([
            'class_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())
            ],
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => [
                \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $this->getSchoolId())
            ],
            'academic_year_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())
            ],
            'miscellaneous_fee_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('miscellaneous_fees', 'id')->where('school_id', $this->getSchoolId())
            ],
            'fee_period' => 'required|string|max:100',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();

            $schoolId = $this->getSchoolId();
            $miscFee = MiscellaneousFee::where('school_id', $schoolId)->findOrFail($validated['miscellaneous_fee_id']);

            // Get or create "Miscellaneous Fees" FeeType
            $feeType = FeeType::firstOrCreate(
                ['school_id' => $schoolId, 'name' => 'Miscellaneous Fees'],
                ['code' => 'MISC', 'is_active' => true, 'description' => 'Ad-hoc and miscellaneous charges']
            );

            // Get or create FeeName based on the selected MiscellaneousFee
            $feeName = FeeName::firstOrCreate(
                ['school_id' => $schoolId, 'fee_type_id' => $feeType->id, 'name' => $miscFee->name],
                ['is_active' => \App\Enums\YesNo::Yes, 'description' => $miscFee->description ?? 'Ad-hoc charge']
            );

            $generatedCount = 0;
            $skippedCount = 0;

            foreach ($validated['student_ids'] as $studentId) {
                // Deduplicate to prevent accidentally billing the exact same fine for the exact same period
                $exists = Fee::withTrashed()
                    ->where('school_id', $schoolId)
                    ->where('student_id', $studentId)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('fee_type_id', $feeType->id)
                    ->where('fee_name_id', $feeName->id)
                    ->where('fee_period', $validated['fee_period'])
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                Fee::forceCreate([
                    'school_id' => $schoolId,
                    'student_id' => $studentId,
                    'academic_year_id' => $validated['academic_year_id'],
                    'fee_type_id' => $feeType->id,
                    'fee_name_id' => $feeName->id,
                    'class_id' => $validated['class_id'],
                    'bill_no' => $this->numberingService->nextBillNo($schoolId),
                    'fee_period' => $validated['fee_period'],
                    'payable_amount' => $miscFee->amount,
                    'due_amount' => $miscFee->amount,
                    'due_date' => $validated['due_date'],
                    'payment_status' => FeeStatus::Pending,
                    'remarks' => "Ad-hoc fee: {$miscFee->name}",
                ]);

                $generatedCount++;
            }

            DB::commit();

            // Trigger waiver synchronization for newly generated fees
            if ($generatedCount > 0) {
                $waivers = Waiver::where('school_id', $schoolId)
                    ->whereIn('student_id', $validated['student_ids'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('fee_period', $validated['fee_period'])
                    ->get();
                    
                foreach ($waivers as $waiver) {
                    $waiver->touch();
                }
            }

            $message = "Ad-hoc fee assigned successfully. Assigned: {$generatedCount}, Skipped (Duplicate): {$skippedCount}";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            return redirect()->route('school.fees.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ad-hoc Fee Assignment Failed: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment failed: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Assignment failed: ' . $e->getMessage())->withInput();
        }
    }
}
