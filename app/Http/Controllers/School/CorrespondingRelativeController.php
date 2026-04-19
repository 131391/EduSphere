<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreCorrespondingRelativeRequest;
use App\Http\Requests\School\UpdateCorrespondingRelativeRequest;
use App\Models\CorrespondingRelative;
use App\Services\School\CorrespondingRelativeService;
use Illuminate\Http\Request;

use App\Traits\HasAjaxDataTable;

class CorrespondingRelativeController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    protected CorrespondingRelativeService $service;

    public function __construct(CorrespondingRelativeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'is_active' => $row->is_active,
                'created_at' => $row->created_at->format('M d, Y'),
            ];
        };

        $query = CorrespondingRelative::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $stats = $this->getTableStats();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.corresponding-relatives.index', [
            'initialData' => $initialData,
            'stats' => $stats,
        ]);
    }

    protected function getTableStats()
    {
        return [
            'total_relatives' => CorrespondingRelative::where('school_id', $this->getSchoolId())->count(),
            'active_relatives' => CorrespondingRelative::where('school_id', $this->getSchoolId())->where('is_active', true)->count(),
        ];
    }

    public function store(StoreCorrespondingRelativeRequest $request)
    {
        try {
            $relative = $this->service->createRelative(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corresponding relative created successfully!',
                    'data' => $relative
                ]);
            }

            return $this->redirectWithSuccess(
                'school.corresponding-relatives.index',
                'Corresponding relative created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return $this->backWithError('Failed to create corresponding relative: ' . $e->getMessage());
        }
    }

    public function update(UpdateCorrespondingRelativeRequest $request, $id)
    {
        try {
            $relative = CorrespondingRelative::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateRelative($relative, $request->validated());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corresponding relative updated successfully!',
                    'data' => $relative->fresh()
                ]);
            }

            return $this->redirectWithSuccess(
                'school.corresponding-relatives.index',
                'Corresponding relative updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return $this->backWithError('Failed to update corresponding relative: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $relative = CorrespondingRelative::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteRelative($relative);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corresponding relative deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.corresponding-relatives.index',
                'Corresponding relative deleted successfully!'
            );
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return $this->redirectWithError(
                'school.corresponding-relatives.index',
                'Failed to delete corresponding relative: ' . $e->getMessage()
            );
        }
    }
}
