<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreAdmissionCodeRequest;
use App\Http\Requests\School\UpdateAdmissionCodeRequest;
use App\Models\AdmissionCode;
use App\Services\School\AdmissionCodeService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class AdmissionCodeController extends TenantController
{
    use HasAjaxDataTable;

    protected AdmissionCodeService $service;

    public function __construct(AdmissionCodeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($code) {
            return [
                'id' => $code->id,
                'code' => $code->code,
                'is_active' => (bool)$code->is_active,
                'created_at' => $code->created_at?->format('M d, Y'),
            ];
        };

        $query = AdmissionCode::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'code', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $stats = [
            'total' => AdmissionCode::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.admission-codes.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreAdmissionCodeRequest $request)
    {
        try {
            $code = $this->service->createCode(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Admission code created successfully!',
                    'data' => $code
                ]);
            }

            return $this->redirectWithSuccess(
                'school.admission-codes.index',
                'Admission code created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create admission code: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create admission code: ' . $e->getMessage());
        }
    }

    public function update(UpdateAdmissionCodeRequest $request, $id)
    {
        try {
            $code = AdmissionCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateCode($code, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Admission code updated successfully!',
                    'data' => $code
                ]);
            }

            return $this->redirectWithSuccess(
                'school.admission-codes.index',
                'Admission code updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update admission code: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update admission code: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $code = AdmissionCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteCode($code);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Admission code deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.admission-codes.index',
                'Admission code deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete admission code: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.admission-codes.index',
                'Failed to delete admission code: ' . $e->getMessage()
            );
        }
    }
}
