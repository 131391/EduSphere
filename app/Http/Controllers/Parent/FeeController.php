<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use App\Models\Fee;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeeController extends TenantController
{
    use ResolvesParent;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $children = $parentProfile->students()
            ->where('students.school_id', $this->getSchoolId())
            ->with(['class:id,name', 'section:id,name'])
            ->get();

        $studentIds = $children->pluck('id');

        $selectedChildId = $request->filled('student_id') ? (int) $request->student_id : null;

        $query = Fee::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->with(['feeName:id,name', 'feeType:id,name', 'academicYear:id,name', 'student:id,first_name,last_name']);

        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            $query->where('student_id', $selectedChildId);
        }

        // Aggregate the *whole* result set so the summary matches even when paginated.
        $totals = (clone $query)
            ->selectRaw('SUM(payable_amount) as payable, SUM(paid_amount) as paid, SUM(due_amount) as due')
            ->first();

        $summary = [
            'total_payable' => (float) ($totals->payable ?? 0),
            'total_paid'    => (float) ($totals->paid ?? 0),
            'total_due'     => (float) ($totals->due ?? 0),
        ];

        $fees = $query->orderByDesc('due_date')->paginate(20)->withQueryString();

        return view('parent.fees.index', compact('children', 'fees', 'summary', 'selectedChildId', 'parentProfile'));
    }

    public function show($id)
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $studentIds = $this->ownedStudentIds($parentProfile);

        $fee = Fee::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->with(['feeName', 'feeType', 'academicYear', 'student.class', 'student.section'])
            ->findOrFail($id);

        $payments = FeePayment::where('fee_id', $fee->id)
            ->where('school_id', $this->getSchoolId())
            ->with(['paymentMethod:id,name', 'creator:id,name'])
            ->orderByDesc('payment_date')
            ->get();

        return view('parent.fees.show', compact('fee', 'payments', 'parentProfile'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $studentIds = $this->ownedStudentIds($parentProfile);

        $query = Fee::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->with(['feeName:id,name', 'student:id,first_name,last_name', 'academicYear:id,name']);

        if ($request->filled('student_id') && $studentIds->contains((int) $request->student_id)) {
            $query->where('student_id', (int) $request->student_id);
        }

        $fees = $query->orderByDesc('due_date')->get();

        $filename = 'fee-statement-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($fees) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Bill No', 'Student', 'Fee', 'Period', 'Due Date', 'Payable', 'Paid', 'Due', 'Status']);

            foreach ($fees as $fee) {
                fputcsv($out, [
                    $fee->bill_no,
                    optional($fee->student)->full_name,
                    optional($fee->feeName)->name,
                    $fee->fee_period,
                    optional($fee->due_date)->format('Y-m-d'),
                    number_format((float) $fee->payable_amount, 2, '.', ''),
                    number_format((float) $fee->paid_amount, 2, '.', ''),
                    number_format((float) $fee->due_amount, 2, '.', ''),
                    $fee->payment_status?->label() ?? 'Pending',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Display a printable receipt for a payment owned by the parent's child.
     */
    public function receipt(string $receiptNo)
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $studentIds = $this->ownedStudentIds($parentProfile);

        $payments = FeePayment::with([
                'fee.feeName',
                'student.class',
                'student.section',
                'paymentMethod:id,name',
                'academicYear:id,name',
                'creator:id,name',
            ])
            ->where('school_id', $this->getSchoolId())
            ->whereIn('student_id', $studentIds)
            ->where('receipt_no', $receiptNo)
            ->orderBy('id')
            ->get();

        if ($payments->isEmpty()) {
            abort(404);
        }

        $student = $payments->first()->student;
        $school  = $this->getSchool();

        return view('parent.fees.receipt', compact('payments', 'student', 'school', 'receiptNo'));
    }
}
