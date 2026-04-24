<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * List fees scoped to the caller's role.
     *
     * - student: only their own fees
     * - parent:  only their linked children's fees
     * - school_admin / receptionist: all school fees
     */
    public function index(Request $request)
    {
        $school = app('currentSchool');
        $user   = $request->user();
        $role   = $user->role?->slug;

        $query = Fee::where('school_id', $school->id)
            ->with(['student', 'feeName', 'feeType']);

        // ── Role-based scoping ───────────────────────────────────────
        if ($role === Role::STUDENT) {
            $student = Student::where('school_id', $school->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$student) {
                return response()->json(['data' => [], 'meta' => ['total' => 0]], 200);
            }

            $query->where('student_id', $student->id);

        } elseif ($role === Role::PARENT) {
            $childIds = Student::where('school_id', $school->id)
                ->whereHas('parents', function ($q) use ($user) {
                    $q->whereHas('user', fn ($u) => $u->where('id', $user->id));
                })
                ->pluck('id');

            $query->whereIn('student_id', $childIds);

        } elseif (!in_array($role, [Role::SCHOOL_ADMIN, Role::RECEPTIONIST], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ── Filters ──────────────────────────────────────────────────
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
     * Get a specific fee, scoped to the caller's role.
     */
    public function show(Request $request, $id)
    {
        $school = app('currentSchool');
        $user   = $request->user();
        $role   = $user->role?->slug;

        $fee = Fee::where('school_id', $school->id)
            ->with(['student', 'feeName', 'feeType', 'academicYear'])
            ->findOrFail($id);

        // ── Role-based guard ─────────────────────────────────────────
        if ($role === Role::STUDENT) {
            $student = Student::where('school_id', $school->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$student || $fee->student_id !== $student->id) {
                abort(403, 'You can only view your own fees.');
            }

        } elseif ($role === Role::PARENT) {
            $childIds = Student::where('school_id', $school->id)
                ->whereHas('parents', function ($q) use ($user) {
                    $q->whereHas('user', fn ($u) => $u->where('id', $user->id));
                })
                ->pluck('id');

            if (!$childIds->contains($fee->student_id)) {
                abort(403, 'You can only view fees for your children.');
            }

        } elseif (!in_array($role, [Role::SCHOOL_ADMIN, Role::RECEPTIONIST], true)) {
            abort(403, 'Forbidden');
        }

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