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

class FeePaymentController extends TenantController
{
    protected $paymentService;

    public function __construct(FeePaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of students to collect fees.
     */
    public function index(Request $request)
    {
        $this->ensureSchoolActive();

        $query = Student::where('school_id', $this->getSchoolId())->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $students = $query->with(['class', 'section'])->paginate(15);
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();

        return view('school.fee-payments.index', compact('students', 'classes'));
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
        $academicYear = AcademicYear::where('school_id', $this->getSchoolId())->where('is_active', true)->first();

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
            'payment_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'transaction_id' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
            'academic_year_id' => 'required|exists:academic_years,id',
            'payments' => 'required|array|min:1',
            'payments.*.fee_id' => 'required|exists:fees,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
        ]);

        $data = $validated;
        $data['student_id'] = $student->id;

        $result = $this->paymentService->collectPayment($this->school, $data);

        if ($result['success']) {
            return redirect()->route('fee-payments.index')->with('success', $result['message']);
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
