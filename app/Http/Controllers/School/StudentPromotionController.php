<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Section;
use App\Services\School\StudentPromotionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentPromotionController extends TenantController
{
    public function __construct(protected StudentPromotionService $promotionService)
    {
        parent::__construct();
    }

    /**
     * Show the promotion setup page.
     */
    public function index()
    {
        $schoolId     = $this->getSchoolId();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderBy('start_date', 'desc')->get();
        $currentYear  = $academicYears->firstWhere('is_current', \App\Enums\YesNo::Yes);

        return view('school.student-promotions.index', compact('academicYears', 'currentYear'));
    }

    /**
     * Preview promotion — returns JSON of what will happen.
     */
    public function preview(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $request->validate([
            'from_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'to_year_id'   => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId), 'different:from_year_id'],
        ]);

        $school = $this->getSchool();

        $fromYear = AcademicYear::where('school_id', $school->id)->findOrFail($request->from_year_id);
        $toYear   = AcademicYear::where('school_id', $school->id)->findOrFail($request->to_year_id);

        $preview = $this->promotionService->preview($school, $fromYear->id, $toYear->id);

        // Attach available sections per class for the UI
        $classIds = collect($preview['classes'])->pluck('next_class_id')->filter()->unique();
        $sections = Section::where('school_id', $school->id)
            ->whereIn('class_id', $classIds)
            ->get(['id', 'name', 'class_id'])
            ->groupBy('class_id');

        $preview['sections'] = $sections;

        return response()->json(['success' => true, 'data' => $preview]);
    }

    /**
     * Execute the promotion.
     */
    public function promote(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $request->validate([
            'from_year_id'                           => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'to_year_id'                             => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId), 'different:from_year_id'],
            'promotion_data'                         => ['required', 'array', 'min:1'],
            'promotion_data.*.class_id'              => ['required', 'integer'],
            'promotion_data.*.students'              => ['required', 'array', 'min:1'],
            'promotion_data.*.students.*.student_id' => ['required', 'integer'],
            'promotion_data.*.students.*.result'     => ['required', 'integer', 'in:1,2,3,4'],
        ]);

        $school = $this->getSchool();

        // Tenant check on both years
        AcademicYear::where('school_id', $school->id)->findOrFail($request->from_year_id);
        AcademicYear::where('school_id', $school->id)->findOrFail($request->to_year_id);

        try {
            $result = $this->promotionService->promote(
                $school,
                $request->from_year_id,
                $request->to_year_id,
                $request->promotion_data
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Promotion history log.
     */
    public function history(Request $request)
    {
        $school = $this->getSchool();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderBy('start_date', 'desc')->get();

        $history = $this->promotionService->getHistory(
            $school,
            $request->filled('student_id') ? (int) $request->student_id : null,
            $request->filled('academic_year_id') ? (int) $request->academic_year_id : null
        );

        return view('school.student-promotions.history', compact('history', 'academicYears'));
    }
}
