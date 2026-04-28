<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HostelFloorController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($floor) {
            return [
                'id'            => $floor->id,
                'floor_name'    => $floor->floor_name,
                'hostel_name'   => $floor->hostel?->hostel_name,
                'room_count'    => $floor->rooms()->count(),
                'raw' => [
                    'floor_name' => $floor->floor_name,
                    'hostel_id'  => $floor->hostel_id,
                ],
            ];
        };

        $query = HostelFloor::with('hostel')->where('school_id', $schoolId);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('floor_name', 'like', "%{$search}%");
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        
        $initialData = $this->getHydrationData($query, $transformer, [
            'hostels' => $hostels,
        ]);

        return view('school.hostel.floors', [
            'initialData' => $initialData,
            'hostels' => $hostels,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'floor_name' => 'required|string|max:255',
            ]);
            $validated['school_id'] = $this->getSchoolId();

            $floor = HostelFloor::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Floor added successfully.',
                'data' => $floor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, HostelFloor $floor)
    {
        $this->authorizeTenant($floor);

        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'floor_name' => 'required|string|max:255',
            ]);

            $floor->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Floor updated successfully.',
                'data' => $floor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HostelFloor $floor)
    {
        $this->authorizeTenant($floor);

        try {
            if ($floor->rooms()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this floor.',
                    'errors' => ['floor' => ['This floor has rooms assigned. Please remove them first.']],
                ], 422);
            }

            $floor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Floor deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete floor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getByHostel($hostelId)
    {
        $floors = HostelFloor::where('school_id', $this->getSchoolId())
            ->where('hostel_id', $hostelId)
            ->orderBy('floor_name')
            ->get();

        return response()->json($floors);
    }
}
