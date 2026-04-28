<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HostelRoomController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($room) {
            return [
                'id'            => $room->id,
                'room_name'     => $room->room_name,
                'floor_name'    => $room->floor?->floor_name,
                'hostel_name'   => $room->hostel?->hostel_name,
                'assignment_count' => $room->assignments()->count(),
                'raw' => [
                    'room_name'       => $room->room_name,
                    'hostel_floor_id' => $room->hostel_floor_id,
                    'hostel_id'       => $room->hostel_id,
                ],
            ];
        };

        $query = HostelRoom::with(['hostel', 'floor'])->where('school_id', $schoolId);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }
        if ($request->filled('hostel_floor_id')) {
            $query->where('hostel_floor_id', $request->hostel_floor_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('room_name', 'like', "%{$search}%");
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        
        $initialData = $this->getHydrationData($query, $transformer, [
            'hostels' => $hostels,
        ]);

        return view('school.hostel.rooms', [
            'initialData' => $initialData,
            'hostels' => $hostels,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'hostel_floor_id' => 'required|exists:hostel_floors,id,school_id,' . $this->getSchoolId(),
                'room_name' => 'required|string|max:255',
            ]);
            $validated['school_id'] = $this->getSchoolId();

            $room = HostelRoom::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Room added successfully.',
                'data' => $room,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, HostelRoom $room)
    {
        $this->authorizeTenant($room);

        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'hostel_floor_id' => 'required|exists:hostel_floors,id,school_id,' . $this->getSchoolId(),
                'room_name' => 'required|string|max:255',
            ]);

            $room->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully.',
                'data' => $room,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HostelRoom $room)
    {
        $this->authorizeTenant($room);

        try {
            if ($room->assignments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this room.',
                    'errors' => ['room' => ['This room has students assigned. Please remove them first.']],
                ], 422);
            }

            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getByFloor($floorId)
    {
        $rooms = HostelRoom::where('school_id', $this->getSchoolId())
            ->where('hostel_floor_id', $floorId)
            ->orderBy('room_name')
            ->get();

        return response()->json($rooms);
    }
}
