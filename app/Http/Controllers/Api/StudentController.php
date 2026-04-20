<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * List all students for the current school.
     */
    public function index(Request $request)
    {
        $school = app('currentSchool');
        
        $query = Student::where('school_id', $school->id)
            ->with(['class', 'section']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->per_page ?? 25, 100);
        $students = $query->paginate($perPage);

        return response()->json([
            'data' => $students->items(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /**
     * Get a specific student.
     */
    public function show($id)
    {
        $school = app('currentSchool');
        
        $student = Student::where('school_id', $school->id)
            ->with(['class', 'section', 'parents'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => $student->full_name,
                'gender' => $student->gender?->value,
                'dob' => $student->dob?->toDateString(),
                'mobile_no' => $student->mobile_no,
                'email' => $student->email,
                'class' => $student->class?->name,
                'section' => $student->section?->name,
                'status' => $student->status?->value,
                'admission_date' => $student->admission_date?->toDateString(),
            ],
        ]);
    }
}