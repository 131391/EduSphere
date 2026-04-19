<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreRegistrationCodeRequest;
use App\Http\Requests\School\UpdateRegistrationCodeRequest;
use App\Models\RegistrationCode;
use App\Services\School\RegistrationCodeService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class RegistrationCodeController extends TenantController
{
    use HasAjaxDataTable;

    protected RegistrationCodeService $service;

    public function __construct(RegistrationCodeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'description' => $item->description,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = RegistrationCode::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('description', 'like', '%' . $request->input('search') . '%');
            });
        }

        $sort = $request->input('sort', 'code');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'code', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('code', 'asc');
        }

        $stats = [
            'total' => RegistrationCode::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.registration-codes.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreRegistrationCodeRequest $request)
    {
        try {
            $code = $this->service->createCode(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration code created successfully!',
                    'data' => $code
                ]);
            }

            return $this->redirectWithSuccess(
                'school.registration-codes.index',
                'Registration code created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create registration code: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create registration code: ' . $e->getMessage());
        }
    }

    public function update(UpdateRegistrationCodeRequest $request, $id)
    {
        try {
            $code = RegistrationCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateCode($code, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration code updated successfully!',
                    'data' => $code
                ]);
            }

            return $this->redirectWithSuccess(
                'school.registration-codes.index',
                'Registration code updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update registration code: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update registration code: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $code = RegistrationCode::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteCode($code);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration code deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.registration-codes.index',
                'Registration code deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete registration code: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.registration-codes.index',
                'Failed to delete registration code: ' . $e->getMessage()
            );
        }
    }
}
