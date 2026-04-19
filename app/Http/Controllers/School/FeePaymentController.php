<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\PaymentMethod;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Services\School\FeePaymentService;
use App\Enums\FeeStatus;
use Illuminate\Http\Request;

use App\Traits\HasAjaxDataTable;

class FeePaymentController extends TenantController
{
    use HasAjaxDataTable;

    protected $paymentService;

    public function __construct(FeePaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();

        if ($request->expectsJson()) {
            return $this->handleAjaxTable($request);
        }

        $hydrationData = $this->getHydrationData($request);

        return view('school.fee-payments.index', array_merge($hydrationData, [
            'classes' => ClassModel::where('school_id', $this->getSchoolId())->get()
        ]));
    }

    protected function handleAjaxTable(Request $request)
    {
        $query = Student::where('school_id', $this->getSchoolId())
            ->active()
            ->with(['class', 'section'])
            ->withCount(['fees as pending_fees_count' => function($q) {
                $q->where('payment_status', '!=', FeeStatus::Paid);
            }])
            ->withSum(['fees as total_due' => function($q) {
                $q->where('payment_status', '!=', FeeStatus::Paid);
            }], 'due_amount');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        return $this->processAjaxTable($query, $request, [
            'first_name' => 'first_name',
            'admission_no' => 'admission_no'
        ], function($row) {
            return [
                'id' => $row->id,
                'full_name' => $row->full_name,
                'admission_no' => $row->admission_no,
                'class_name' => $row->class?->name ?? 'N/A',
                'section_name' => $row->section?->name ?? 'N/A',
                'pending_count' => $row->pending_fees_count ?? 0,
                'total_due' => number_format($row->total_due ?? 0, 2),
                'collect_url' => route('school.fee-payments.collect', $row->id),
            ];
        });
    }

    protected function getTableStats()
    {
        $today = now()->toDateString();
        
        return [
            'collected_today' => number_format(FeePayment::where('school_id', $this->getSchoolId())
                ->whereDate('payment_date', $today)
                ->sum('amount'), 2),
            'pending_students' => Student::where('school_id', $this->getSchoolId())
                ->active()
                ->whereHas('fees', function($q) {
                    $q->where('payment_status', '!=', FeeStatus::Paid);
                })
                ->count(),
            'total_collections_month' => number_format(FeePayment::where('school_id', $this->getSchoolId())
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'), 2),
            'mode_distribution' => FeePayment::where('school_id', $this->getSchoolId())
                ->whereDate('payment_date', $today)
                ->distinct('payment_method_id')
                ->count('payment_method_id')
        ];
    }

    /**
     * Show pending fees for a specific student.
     */
    public function collect(Student $student)
    {
        $this->authorizeTenant($student);
        $this->ensureSchoolActive();

        $pendingFees = $this->paymentService->getStudentPendingFees($student);
        $paymentMethods = PaymentMethod::where('school_id', $this->getSchoolId())->active()->get();
        $academicYear = AcademicYear::where('school_id', $this->getSchoolId())->where('is_current', true)->first();

        return view('school.fee-payments.collect', compact('student', 'pendingFees', 'paymentMethods', 'academicYear'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request, Student $student)
    {
        $this->authorizeTenant($student);
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'payment_date'       => 'required|date',
            'payment_method_id'  => ['required', \Illuminate\Validation\Rule::exists('payment_methods', 'id')->where('school_id', $this->getSchoolId())],
            'transaction_id'     => 'nullable|string|max:100',
            'remarks'            => 'nullable|string|max:500',
            'academic_year_id'   => ['required', \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())],
            'payments'           => 'required|array|min:1',
            'payments.*.fee_id'  => ['required', \Illuminate\Validation\Rule::exists('fees', 'id')->where('school_id', $this->getSchoolId())],
            'payments.*.amount'  => 'required|numeric|min:0.01',
        ]);

        $data = $validated;
        $data['student_id'] = $student->id;

        $result = $this->paymentService->collectPayment($this->school, $data);

        if ($result['success']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'receipt_no' => $result['receipt_no'] ?? null,
                    'redirect' => route('school.fee-payments.receipt', $result['receipt_no'] ?? 0)
                ]);
            }
            return redirect()->route('school.fee-payments.index')->with('success', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        return back()->with('error', $result['message'])->withInput();
    }

    /**
     * Display the receipt.
     */
    public function receipt($receipt_no)
    {
        $this->ensureSchoolActive();

        $payments = FeePayment::where('school_id', $this->getSchoolId())
            ->where('receipt_no', $receipt_no)
            ->with(['student.class', 'fee.feeName', 'paymentMethod', 'creator'])
            ->get();

        if ($payments->isEmpty()) {
            abort(404, 'Receipt not found');
        }

        $student = $payments->first()->student;
        $school = $this->school;

        return view('school.fee-payments.receipt', compact('payments', 'student', 'school', 'receipt_no'));
    }
}
