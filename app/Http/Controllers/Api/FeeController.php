<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * List all fees for the current school.
     */
    public function index(Request $request)
    {
        $school = app('currentSchool');
        
        $query = Fee::where('school_id', $school->id)
            ->with(['student', 'feeName', 'feeType']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $perPage = min($request->per_page ?? 25, 100);
        $fees = $query->paginate($perPage);

        return response()->json([
            'data' => $fees->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'bill_no' => $fee->bill_no,
                    'student_name' => $fee->student?->full_name,
                    'student_admission_no' => $fee->student?->admission_no,
                    'fee_type' => $fee->feeType?->name,
                    'fee_name' => $fee->feeName?->name,
                    'payable_amount' => $fee->payable_amount,
                    'paid_amount' => $fee->paid_amount,
                    'due_amount' => $fee->due_amount,
                    'payment_status' => $fee->payment_status?->value,
                    'due_date' => $fee->due_date?->toDateString(),
                ];
            }),
            'meta' => [
                'current_page' => $fees->currentPage(),
                'last_page' => $fees->lastPage(),
                'per_page' => $fees->perPage(),
                'total' => $fees->total(),
            ],
        ]);
    }

    /**
     * Get a specific fee.
     */
    public function show($id)
    {
        $school = app('currentSchool');
        
        $fee = Fee::where('school_id', $school->id)
            ->with(['student', 'feeName', 'feeType', 'academicYear'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $fee->id,
                'bill_no' => $fee->bill_no,
                'student' => [
                    'id' => $fee->student?->id,
                    'name' => $fee->student?->full_name,
                    'admission_no' => $fee->student?->admission_no,
                ],
                'fee_type' => $fee->feeType?->name,
                'fee_name' => $fee->feeName?->name,
                'academic_year' => $fee->academicYear?->name,
                'payable_amount' => $fee->payable_amount,
                'paid_amount' => $fee->paid_amount,
                'due_amount' => $fee->due_amount,
                'waiver_amount' => $fee->waiver_amount,
                'discount_amount' => $fee->discount_amount,
                'late_fee' => $fee->late_fee,
                'payment_status' => $fee->payment_status?->value,
                'due_date' => $fee->due_date?->toDateString(),
                'payment_date' => $fee->payment_date?->toDateString(),
                'payment_mode' => $fee->payment_mode,
            ],
        ]);
    }
}