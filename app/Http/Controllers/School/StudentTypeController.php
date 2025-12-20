<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreStudentTypeRequest;
use App\Http\Requests\School\UpdateStudentTypeRequest;
use App\Models\StudentType;
use App\Services\School\StudentTypeService;
use Illuminate\Http\Request;

class StudentTypeController extends TenantController
{
    protected StudentTypeService $service;

    public function __construct(StudentTypeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'id'),
                'direction' => $request->input('direction', 'asc'),
            ];

            $types = $this->service->getPaginatedTypes(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.student-types.index', compact('types'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load student types.');
        }
    }

    public function store(StoreStudentTypeRequest $request)
    {
        try {
            $this->service->createType(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.student-types.index',
                'Student type created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create student type: ' . $e->getMessage());
        }
    }

    public function update(UpdateStudentTypeRequest $request, $id)
    {
        try {
            $type = StudentType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateType($type, $request->validated());

            return $this->redirectWithSuccess(
                'school.student-types.index',
                'Student type updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update student type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $type = StudentType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteType($type);

            return $this->redirectWithSuccess(
                'school.student-types.index',
                'Student type deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.student-types.index',
                'Failed to delete student type: ' . $e->getMessage()
            );
        }
    }
}
