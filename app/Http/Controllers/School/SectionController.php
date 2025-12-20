<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreSectionRequest;
use App\Http\Requests\School\UpdateSectionRequest;
use App\Models\Section;
use App\Models\ClassModel;
use App\Services\School\SectionService;
use Illuminate\Http\Request;

class SectionController extends TenantController
{
    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        parent::__construct();
        $this->sectionService = $sectionService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'class_id' => $request->input('class_id'),
                'sort' => $request->input('sort', 'id'),
                'direction' => $request->input('direction', 'asc'),
            ];

            $sections = $this->sectionService->getPaginatedSections(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            $stats = $this->sectionService->getSectionStatistics($this->getSchool());
            
            $classes = ClassModel::where('school_id', $this->getSchoolId())
                ->orderBy('order')
                ->get();

            return view('school.sections.index', compact('sections', 'stats', 'classes'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load sections.');
        }
    }

    public function create()
    {
        $classes = ClassModel::where('school_id', $this->getSchoolId())
            ->orderBy('order')
            ->get();
            
        return view('school.sections.create', compact('classes'));
    }

    public function store(StoreSectionRequest $request)
    {
        try {
            $section = $this->sectionService->createSection(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.sections.index',
                'Section created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create section: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $section = Section::where('school_id', $this->getSchoolId())
                ->with(['class', 'students'])
                ->withCount(['students'])
                ->findOrFail($id);

            return view('school.sections.show', compact('section'));
        } catch (\Exception $e) {
            return $this->redirectWithError('school.sections.index', 'Section not found.');
        }
    }

    public function edit($id)
    {
        try {
            $section = Section::where('school_id', $this->getSchoolId())
                ->findOrFail($id);
                
            $classes = ClassModel::where('school_id', $this->getSchoolId())
                ->orderBy('order')
                ->get();

            return view('school.sections.edit', compact('section', 'classes'));
        } catch (\Exception $e) {
            return $this->redirectWithError('school.sections.index', 'Section not found.');
        }
    }

    public function update(UpdateSectionRequest $request, $id)
    {
        try {
            $section = Section::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $section = $this->sectionService->updateSection($section, $request->validated());

            return $this->redirectWithSuccess(
                'school.sections.index',
                'Section updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update section: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $section = Section::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->sectionService->deleteSection($section);

            return $this->redirectWithSuccess(
                'school.sections.index',
                'Section deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.sections.index',
                'Failed to delete section: ' . $e->getMessage()
            );
        }
    }
}
