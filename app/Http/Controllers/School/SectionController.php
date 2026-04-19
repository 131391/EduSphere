<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreSectionRequest;
use App\Http\Requests\School\UpdateSectionRequest;
use App\Models\Section;
use App\Models\ClassModel;
use App\Services\School\SectionService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class SectionController extends TenantController
{
    use HasAjaxDataTable;

    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        parent::__construct();
        $this->sectionService = $sectionService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'class_name' => $item->class?->name ?? 'N/A',
                'class_id' => $item->class_id,
                'students_count' => $item->students_count ?? 0,
                'is_available' => (bool) $item->is_available,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = Section::where('sections.school_id', $schoolId)
            ->with(['class'])
            ->withCount(['students']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('class_id') && $request->input('class_id') !== 'all') {
            $query->where('class_id', $request->input('class_id'));
        }

        $sort = $request->input('sort', 'class_name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'class_name') {
            $query->join('classes', 'sections.class_id', '=', 'classes.id')
                ->orderBy('classes.name', $direction)
                ->select('sections.*');
        } elseif (\in_array($sort, ['id', 'name', 'created_at', 'students_count'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $stats = $this->sectionService->getSectionStatistics($this->getSchool());

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        $classes = ClassModel::where('school_id', $schoolId)
            ->orderBy('order')
            ->get(['id', 'name']);

        return view('school.sections.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
            'classes' => $classes,
        ]);
    }

    public function store(StoreSectionRequest $request)
    {
        try {
            $section = $this->sectionService->createSection(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Section created successfully!',
                    'data' => $section
                ]);
            }

            return $this->redirectWithSuccess(
                'school.sections.index',
                'Section created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create section: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create section: ' . $e->getMessage());
        }
    }

    public function update(UpdateSectionRequest $request, $id)
    {
        try {
            $section = Section::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $section = $this->sectionService->updateSection($section, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Section updated successfully!',
                    'data' => $section
                ]);
            }

            return $this->redirectWithSuccess(
                'school.sections.index',
                'Section updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update section: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update section: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $section = Section::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->sectionService->deleteSection($section);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Section deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.sections.index',
                'Section deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete section: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.sections.index',
                'Failed to delete section: ' . $e->getMessage()
            );
        }
    }
}
