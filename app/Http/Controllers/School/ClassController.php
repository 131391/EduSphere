<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreClassRequest;
use App\Http\Requests\School\UpdateClassRequest;
use App\Models\ClassModel;
use App\Services\School\ClassService;
use Illuminate\Http\Request;

class ClassController extends TenantController
{
    protected ClassService $classService;

    public function __construct(ClassService $classService)
    {
        parent::__construct();
        $this->classService = $classService;
    }

    /**
     * Display a listing of classes
     */
    public function index(Request $request)
    {
        try {
            // Get filters from request
            $filters = [
                'search' => $request->input('search'),
                'is_available' => $request->input('is_available'),
                'sort' => $request->input('sort', 'order'),
                'direction' => $request->input('direction', 'asc'),
            ];

            // Get paginated classes
            $classes = $this->classService->getPaginatedClasses(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            // Get statistics
            $stats = $this->classService->getClassStatistics($this->getSchool());

            return view('school.classes.index', compact('classes', 'stats'));
        } catch (\Exception $e) {
            $this->logActivity('class.index.error', 'Failed to load classes', [
                'error' => $e->getMessage(),
            ]);

            return $this->backWithError('Failed to load classes. Please try again.');
        }
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        return view('school.classes.create');
    }

    /**
     * Store a newly created class
     */
    public function store(StoreClassRequest $request)
    {
        try {
            $class = $this->classService->createClass(
                $this->getSchool(),
                $request->validated()
            );

            $this->logActivity('class.created', 'Class created successfully', [
                'class_id' => $class->id,
                'name' => $class->name,
            ]);

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $class->name . '" created successfully!'
            );
        } catch (\Exception $e) {
            $this->logActivity('class.create.error', 'Failed to create class', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);

            return $this->backWithError('Failed to create class: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified class
     */
    public function show($id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->with(['sections', 'students', 'subjects'])
                ->withCount(['sections', 'students'])
                ->findOrFail($id);

            return view('school.classes.show', compact('class'));
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.classes.index',
                'Class not found.'
            );
        }
    }

    /**
     * Show the form for editing the specified class
     */
    public function edit($id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            return view('school.classes.edit', compact('class'));
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.classes.index',
                'Class not found.'
            );
        }
    }

    /**
     * Update the specified class
     */
    public function update(UpdateClassRequest $request, $id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $class = $this->classService->updateClass($class, $request->validated());

            $this->logActivity('class.updated', 'Class updated successfully', [
                'class_id' => $class->id,
                'name' => $class->name,
            ]);

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $class->name . '" updated successfully!'
            );
        } catch (\Exception $e) {
            $this->logActivity('class.update.error', 'Failed to update class', [
                'class_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->backWithError('Failed to update class: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified class
     */
    public function destroy($id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $className = $class->name;

            $this->classService->deleteClass($class);

            $this->logActivity('class.deleted', 'Class deleted successfully', [
                'class_id' => $id,
                'name' => $className,
            ]);

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $className . '" deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.classes.index',
                'Failed to delete class: ' . $e->getMessage()
            );
        }
    }

    /**
     * Toggle class availability
     */
    public function toggleAvailability($id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $class = $this->classService->toggleAvailability($class);

            $status = $class->is_available ? 'available' : 'unavailable';

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $class->name . '" is now ' . $status . '!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.classes.index',
                'Failed to update class availability.'
            );
        }
    }
}
