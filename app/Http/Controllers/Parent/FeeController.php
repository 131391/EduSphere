<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found. Please contact the administrator.');
        }

        $children = $parentProfile->students()->with(['class', 'section'])->get();
        $studentIds = $children->pluck('id');

        $selectedChildId = $request->filled('student_id') ? $request->student_id : null;

        $query = Fee::whereIn('student_id', $studentIds)
            ->with(['feeName', 'feeType', 'academicYear', 'student']);

        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            $query->where('student_id', $selectedChildId);
        }

        $fees = $query->orderByDesc('due_date')->get();

        $summary = [
            'total_payable' => $fees->sum('payable_amount'),
            'total_paid'    => $fees->sum('paid_amount'),
            'total_due'     => $fees->sum('due_amount'),
        ];

        return view('parent.fees.index', compact('children', 'fees', 'summary', 'selectedChildId', 'parentProfile'));
    }

    public function show($id)
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        $studentIds = $parentProfile->students()->pluck('students.id');

        $fee = Fee::whereIn('student_id', $studentIds)
            ->with(['feeName', 'feeType', 'academicYear', 'student.class', 'student.section'])
            ->findOrFail($id);

        $payments = FeePayment::where('fee_id', $fee->id)
            ->with(['paymentMethod', 'creator'])
            ->orderByDesc('payment_date')
            ->get();

        return view('parent.fees.show', compact('fee', 'payments', 'parentProfile'));
    }
}
