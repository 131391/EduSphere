<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use App\Enums\StudentStatus;
use App\Traits\HasAjaxDataTable;

class StudentController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function($row) {
            return [
                'id' => $row->id,
                'full_name' => $row->full_name,
                'initials' => strtoupper(substr($row->first_name, 0, 1) . substr($row->last_name, 0, 1)),
                'admission_no' => $row->admission_no,
                'admission_date' => $row->admission_date ? $row->admission_date->format('M d, Y') : 'N/A',
                'class_name' => $row->class->name ?? 'N/A',
                'section_name' => $row->section->name ?? 'N/A',
                'status' => $row->status,
                'phone' => $row->mobile_no ?? 'N/A',
                'photo' => $row->student_photo ? \Storage::url($row->student_photo) : null,
            ];
        };

        $query = \App\Models\Student::where('students.school_id', $schoolId)
            ->with(['class', 'section']);

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        $stats = $this->getTableStats();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);
        
        return view('school.students.index', array_merge($initialData, [
            'initialData' => $initialData,
            'stats' => $stats,
            'classes' => \App\Models\ClassModel::where('school_id', $schoolId)->get(),
        ]));
    }

    protected function getTableStats(): array
    {
        $schoolId = $this->getSchoolId();
        $startOfMonth = now()->startOfMonth();

        $stats = \App\Models\Student::where('school_id', $schoolId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as admissions_this_month
            ', [
                StudentStatus::Active->value,
                StudentStatus::Inactive->value,
                $startOfMonth
            ])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'total_formatted' => number_format($stats->total ?? 0),
            'active' => $stats->active ?? 0,
            'active_formatted' => number_format($stats->active ?? 0),
            'inactive' => $stats->inactive ?? 0,
            'inactive_formatted' => number_format($stats->inactive ?? 0),
            'admissions_this_month' => $stats->admissions_this_month ?? 0,
            'admissions_this_month_formatted' => number_format($stats->admissions_this_month ?? 0),
        ];
    }

    public function create()
    {
        // Admission and Registration flows handle student creation.
        // Direct creation can be added here if needed.
        return redirect()->route('school.admission.index')
            ->with('info', 'Please use the Admission module to register new students.');
    }

    public function show($id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())
            ->with(['class', 'section', 'fees' => function($q) {
                $q->latest()->take(10);
            }, 'attendance' => function($q) {
                $q->latest()->take(30);
            }])
            ->findOrFail($id);

        $this->authorize('view', $student);

        return view('school.students.show', compact('student'));
    }

    public function edit($id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())->findOrFail($id);
        $this->authorize('update', $student);

        $classes = \App\Models\ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = \App\Models\Section::where('class_id', $student->class_id)->get();
        
        return view('school.students.edit', compact('student', 'classes', 'sections'));
    }

    public function update(Request $request, $id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())->findOrFail($id);

        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'class_id'    => ['required', \Illuminate\Validation\Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())],
            'section_id'  => ['required', \Illuminate\Validation\Rule::exists('sections', 'id')->where('school_id', $this->getSchoolId())],
            'status'      => ['required', \Illuminate\Validation\Rule::in(array_column(StudentStatus::cases(), 'value'))],
            'address'     => 'nullable|string|max:500',
        ]);

        // Ensure section belongs to the selected class
        $sectionValid = \App\Models\Section::where('id', $validated['section_id'])
            ->where('class_id', $validated['class_id'])
            ->where('school_id', $this->getSchoolId())
            ->exists();
        if (!$sectionValid) {
            return back()->withErrors(['section_id' => 'The selected section does not belong to the chosen class.'])->withInput();
        }

        $student->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'mobile_no' => $validated['phone'] ?? null,
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'status' => $validated['status'],
            'address' => $validated['address'] ?? null,
        ]);

        $student->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Student information updated successfully.',
                'student' => $student
            ]);
        }

        return redirect()->route('school.students.index')
            ->with('success', 'Student information updated successfully.');
    }

    public function destroy($id)
    {
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())->findOrFail($id);
        $this->authorize('delete', $student);

        // Guard: prevent deletion if student has fee payment records
        if ($student->fees()->whereHas('payments')->exists()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete student with existing fee payment records.'
                ], 422);
            }
            return redirect()->route('school.students.index')
                ->with('error', 'Cannot delete student with existing fee payment records.');
        }

        $student->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student record moved to archives successfully.'
            ]);
        }

        return redirect()->route('school.students.index')
            ->with('success', 'Student record moved to archives.');
    }
}
