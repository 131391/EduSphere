<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Subject;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class SubjectController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code ?? 'N/A',
                'description' => $item->description,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = Subject::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('code', 'like', '%' . $request->input('search') . '%');
            });
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'name', 'code', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $stats = [
            'total' => Subject::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.subjects.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create([
            'school_id' => $this->getSchoolId(),
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully!',
                'data' => $subject
            ]);
        }

        return redirect()->route('school.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeTenant($subject);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $subject->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully!',
                'data' => $subject
            ]);
        }

        return redirect()->route('school.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorizeTenant($subject);
        
        // Check if subject is assigned to any classes
        if ($subject->classes()->exists()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subject as it is assigned to one or more classes.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete subject as it is assigned to one or more classes.');
        }

        $subject->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully!'
            ]);
        }

        return redirect()->route('school.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
