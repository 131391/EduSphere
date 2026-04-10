<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Services\School\FeeService;
use App\Models\ClassModel;
use App\Models\FeeType;
use App\Models\FeeName;
use App\Models\AcademicYear;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends TenantController
{
    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        parent::__construct();
        $this->feeService = $feeService;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        
        $filters = $request->only(['class_id', 'search']);
        $fees = $this->feeService->getPendingFees($this->school, $filters);
        
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();

        return view('school.fees.index', compact('fees', 'classes'));
    }

    public function create()
    {
        $this->ensureSchoolActive();

        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $feeTypes = FeeType::where('school_id', $this->getSchoolId())->active()->get();
        $feeNames = FeeName::where('school_id', $this->getSchoolId())->active()->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();

        return view('school.fees.generate', compact('classes', 'feeTypes', 'feeNames', 'academicYears'));
    }

    public function store(Request $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'class_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())
            ],
            'academic_year_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())
            ],
            'fee_type_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('fee_types', 'id')->where('school_id', $this->getSchoolId())
            ],
            'fee_name_ids' => 'required|array',
            'fee_name_ids.*' => [
                \Illuminate\Validation\Rule::exists('fee_names', 'id')->where('school_id', $this->getSchoolId())
            ],
            'fee_period' => 'required|string|max:100',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        try {
            $result = $this->feeService->generateClassFees($this->school, $validated);

            if ($result['success']) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message']
                    ]);
                }
                return redirect()->route('school.fees.index')->with('success', $result['message']);
            }

            throw new \Exception($result['message']);

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generation failed: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Generation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Fee $fee)
    {
        $this->authorizeTenant($fee);
        
        try {
            $fee->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fee record removed successfully!'
                ]);
            }

            return redirect()->route('school.fees.index')->with('success', 'Fee deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove fee: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to remove fee: ' . $e->getMessage());
        }
    }

    public function show(Fee $fee)
    {
        $this->authorizeTenant($fee);
        return view('school.fees.show', compact('fee'));
    }
}

