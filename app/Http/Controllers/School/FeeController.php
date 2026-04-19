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

use App\Traits\HasAjaxDataTable;
use App\Enums\FeeStatus;

class FeeController extends TenantController
{
    use HasAjaxDataTable;

    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        parent::__construct();
        $this->feeService = $feeService;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();

        if ($request->expectsJson()) {
            return $this->handleAjaxTable($request);
        }

        $hydrationData = $this->getHydrationData($request);

        return view('school.fees.index', array_merge($hydrationData, [
            'classes' => ClassModel::where('school_id', $this->getSchoolId())->get()
        ]));
    }

    protected function handleAjaxTable(Request $request)
    {
        $query = Fee::where('school_id', $this->getSchoolId())
            ->with(['student', 'feeName', 'class'])
            ->where('payment_status', '!=', FeeStatus::Paid);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('bill_no', 'like', "%{$search}%");
            });
        }

        return $this->processAjaxTable($query, $request, [
            'bill_no' => 'bill_no',
            'due_date' => 'due_date',
            'payable_amount' => 'payable_amount',
            'due_amount' => 'due_amount'
        ], function($row) {
            return [
                'id' => $row->id,
                'bill_no' => $row->bill_no,
                'student_name' => $row->student?->full_name ?? 'N/A',
                'admission_no' => $row->student?->admission_no ?? 'N/A',
                'class_name' => $row->class?->name ?? 'N/A',
                'fee_name' => $row->feeName?->name ?? 'N/A',
                'fee_period' => $row->fee_period,
                'payable_amount' => number_format($row->payable_amount, 2),
                'due_amount' => number_format($row->due_amount, 2),
                'due_date' => $row->due_date?->format('d M, Y') ?? 'N/A',
                'status' => $row->payment_status->label(),
                'status_color' => $row->payment_status->color(),
                'is_overdue' => $row->due_date && $row->due_date->isPast() && $row->payment_status != FeeStatus::Paid,
            ];
        });
    }

    protected function getTableStats()
    {
        $pendingQuery = Fee::where('school_id', $this->getSchoolId())
            ->where('payment_status', '!=', FeeStatus::Paid);

        return [
            'total_receivable' => number_format($pendingQuery->sum('due_amount'), 2),
            'pending_records' => $pendingQuery->count(),
            'overdue_count' => Fee::where('school_id', $this->getSchoolId())
                ->where('payment_status', '!=', FeeStatus::Paid)
                ->where('due_date', '<', now())
                ->count(),
            'partial_payments' => Fee::where('school_id', $this->getSchoolId())
                ->where('payment_status', FeeStatus::Partial)
                ->count(),
        ];
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

