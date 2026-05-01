<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Enums\FeeStatus;
use Illuminate\Support\Facades\Auth;

class FeeController extends Controller
{
    public function index()
    {
        $this->authorize('student:operate');

        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found. Please contact the administrator.');
        }

        $fees = Fee::where('student_id', $student->id)
            ->where('school_id', $student->school_id)
            ->with(['feeName', 'feeType', 'academicYear'])
            ->orderByDesc('due_date')
            ->get();

        $summary = [
            'total_payable' => $fees->sum('payable_amount'),
            'total_paid'    => $fees->sum('paid_amount'),
            'total_due'     => $fees->sum('due_amount'),
            'paid_count'    => $fees->filter(fn($f) => $f->payment_status?->value === FeeStatus::Paid->value)->count(),
            'pending_count' => $fees->filter(fn($f) => $f->payment_status?->value !== FeeStatus::Paid->value)->count(),
        ];

        return view('student.fees.index', compact('fees', 'summary', 'student'));
    }

    public function show($id)
    {
        $this->authorize('student:operate');

        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $fee = Fee::where('student_id', $student->id)
            ->where('school_id', $student->school_id)
            ->with(['feeName', 'feeType', 'academicYear'])
            ->findOrFail($id);

        $payments = FeePayment::where('fee_id', $fee->id)
            ->where('student_id', $student->id)
            ->with(['paymentMethod', 'creator'])
            ->orderByDesc('payment_date')
            ->get();

        return view('student.fees.show', compact('fee', 'payments', 'student'));
    }
}
