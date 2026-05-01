<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Http\Requests\Receptionist\StoreHostelRoomRequest;
use App\Http\Requests\Receptionist\UpdateHostelRoomRequest;
use App\Models\HostelRoom;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelBedAssignment;
use App\Enums\YesNo;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class HostelRoomController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();

        $transformer = function ($room) {
            $amenities = [];
            if ($room->ac?->value === YesNo::Yes->value) $amenities[] = 'AC';
            if ($room->cooler?->value === YesNo::Yes->value) $amenities[] = 'Cooler';
            if ($room->fan?->value === YesNo::Yes->value) $amenities[] = 'Fan';

            return [
                'id'                => $room->id,
                'room_name'         => $room->room_name,
                'hostel_id'         => $room->hostel_id,
                'hostel_floor_id'   => $room->hostel_floor_id,
                'hostel_name'       => $room->hostel->hostel_name ?? 'N/A',
                'floor_name'        => $room->floor->floor_name ?? 'N/A',
                'ac'                => $room->ac?->value ?? YesNo::No->value,
                'cooler'            => $room->cooler?->value ?? YesNo::No->value,
                'fan'               => $room->fan?->value ?? YesNo::No->value,
                'amenities'         => $amenities,
                'amenities_label'   => empty($amenities) ? 'None' : implode(', ', $amenities),
                'no_of_beds'        => $room->no_of_beds ?? 0,
                'occupancy_count'   => $room->occupancy_count,
                'available_beds'    => $room->available_beds,
                'room_create_date'  => $room->room_create_date ? $room->room_create_date->format('d M, Y') : 'N/A',
                'raw' => [
                    'hostel_id'        => $room->hostel_id,
                    'hostel_floor_id'  => $room->hostel_floor_id,
                    'room_name'        => $room->room_name,
                    'ac'               => $room->ac?->value ?? YesNo::No->value,
                    'cooler'           => $room->cooler?->value ?? YesNo::No->value,
                    'fan'              => $room->fan?->value ?? YesNo::No->value,
                    'no_of_beds'       => $room->no_of_beds ?? 0,
                    'room_create_date' => $room->room_create_date ? $room->room_create_date->format('Y-m-d') : '',
                ],
            ];
        };

        $query = HostelRoom::where('school_id', $schoolId)->with(['hostel', 'floor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                  ->orWhereHas('hostel', fn($h) => $h->where('hostel_name', 'like', "%{$search}%"))
                  ->orWhereHas('floor', fn($f) => $f->where('floor_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->filled('hostel_floor_id')) {
            $query->where('hostel_floor_id', $request->hostel_floor_id);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();

        $stats = [
            'total_rooms'  => HostelRoom::where('school_id', $schoolId)->count(),
            'ac_rooms'     => HostelRoom::where('school_id', $schoolId)->where('ac', YesNo::Yes->value)->count(),
            'total_beds'   => HostelBedAssignment::where('school_id', $schoolId)->whereNull('deleted_at')->count(),
            'fan_rooms'    => HostelRoom::where('school_id', $schoolId)->where('fan', YesNo::Yes->value)->count(),
        ];

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('receptionist.hostel-rooms.index', [
            'initialData' => $initialData,
            'stats'       => $stats,
            'hostels'     => $hostels,
        ]);
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hostel_rooms_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Hostel', 'Floor', 'Room Name', 'Capacity', 'Occupancy', 'AC', 'Cooler', 'Fan', 'Created On']);
            $query->orderBy('created_at', 'desc')->cursor()->each(function ($room) use ($file) {
                fputcsv($file, [
                    $room->hostel->hostel_name ?? '',
                    $room->floor->floor_name ?? '',
                    $room->room_name,
                    $room->no_of_beds ?? 0,
                    $room->occupancy_count,
                    $room->ac === YesNo::Yes ? 'Yes' : 'No',
                    $room->cooler === YesNo::Yes ? 'Yes' : 'No',
                    $room->fan === YesNo::Yes ? 'Yes' : 'No',
                    $room->room_create_date ? $room->room_create_date->format('Y-m-d') : '',
                ]);
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(StoreHostelRoomRequest $request)
    {
        $this->authorize('receptionist:operate');

        try {
            $validated = $request->validated();
            $schoolId = $this->getSchoolId();

            $validated['ac'] = $validated['ac'] ?? YesNo::No->value;
            $validated['cooler'] = $validated['cooler'] ?? YesNo::No->value;
            $validated['fan'] = $validated['fan'] ?? YesNo::No->value;
            $validated['school_id'] = $schoolId;

            $room = DB::transaction(function () use ($validated) {
                return HostelRoom::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Room added successfully.',
                'data' => $room->load(['hostel', 'floor']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateHostelRoomRequest $request, HostelRoom $hostelRoom)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($hostelRoom);

        try {
            $validated = $request->validated();

            $validated['ac'] = $validated['ac'] ?? YesNo::No->value;
            $validated['cooler'] = $validated['cooler'] ?? YesNo::No->value;
            $validated['fan'] = $validated['fan'] ?? YesNo::No->value;

            DB::transaction(function () use ($hostelRoom, $validated) {
                $hostelRoom->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully.',
                'data' => $hostelRoom->load(['hostel', 'floor']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HostelRoom $hostelRoom)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($hostelRoom);

        try {
            if ($hostelRoom->bedAssignments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this room.',
                    'errors' => ['room' => ['This room has students assigned. Please unassign them first.']],
                ], 422);
            }

            $hostelRoom->delete();

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

    public function export()
    {
        $this->authorize('receptionist:operate');

        return redirect()->route('receptionist.hostel-rooms.index', ['export' => 'csv']);
    }

    /**
     * Get floors for a selected hostel (AJAX dependent dropdown).
     */
    public function getFloors(Request $request)
    {
        $this->authorize('receptionist:operate');

        try {
            $schoolId = $this->getSchoolId();

            $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
            ]);

            $hostel = Hostel::findOrFail($request->hostel_id);

            if ($hostel->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hostel selection.',
                    'errors' => ['hostel_id' => ['The selected hostel is not available.']],
                ], 422);
            }

            $floors = HostelFloor::where('school_id', $schoolId)
                ->where('hostel_id', $request->hostel_id)
                ->orderBy('floor_name')
                ->get(['id', 'floor_name']);

            return response()->json([
                'success' => true,
                'floors' => $floors->map(fn($f) => ['id' => $f->id, 'floor_name' => $f->floor_name])->values(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the highlighted fields.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not load floors: ' . $e->getMessage(),
            ], 500);
        }
    }
}
