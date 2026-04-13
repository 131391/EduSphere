<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\HostelRoom;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Enums\YesNo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HostelRoomController extends TenantController
{
    /**
     * Display a listing of hostel rooms.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = HostelRoom::where('school_id', $schoolId)
            ->with(['hostel', 'floor']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                  ->orWhereHas('hostel', function($hostelQuery) use ($search) {
                      $hostelQuery->where('hostel_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('floor', function($floorQuery) use ($search) {
                      $floorQuery->where('floor_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $rooms = $query->paginate($perPage)->withQueryString();

        // Get all hostels and floors for filters
        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        $floors = HostelFloor::where('school_id', $schoolId)->with('hostel')->orderBy('floor_name')->get();

        // Calculate statistics for the UI
        $stats = [
            'total_room' => HostelRoom::where('school_id', $schoolId)->count(),
            'ac_rooms' => HostelRoom::where('school_id', $schoolId)->where('ac', YesNo::Yes->value)->count(),
            'total_beds' => \App\Models\HostelBedAssignment::whereHas('room', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count(), // Note: In a full implementation, you might count actual beds if that model exists
        ];

        return view('receptionist.hostel-rooms.index', compact('rooms', 'hostels', 'floors', 'stats'));
    }

    /**
     * Store a newly created hostel room.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
                'hostel_floor_id' => 'required|exists:hostel_floors,id',
                'room_name' => 'required|string|max:255',
                'ac' => ['nullable', 'integer', Rule::enum(YesNo::class)],
                'cooler' => ['nullable', 'integer', Rule::enum(YesNo::class)],
                'fan' => ['nullable', 'integer', Rule::enum(YesNo::class)],
                'room_create_date' => 'nullable|date',
            ]);

            $schoolId = $this->getSchoolId();
            
            // Verify hostel belongs to same school
            $hostel = Hostel::find($validated['hostel_id']);
            if (!$hostel || $hostel->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hostel resource',
                    'errors' => ['hostel_id' => ['The selected hostel is invalid or unauthorized.']]
                ], 422);
            }

            // Verify floor belongs to same school and hostel
            $floor = HostelFloor::where('id', $validated['hostel_floor_id'])
                ->where('school_id', $schoolId)
                ->where('hostel_id', $validated['hostel_id'])
                ->first();
                
            if (!$floor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Structural anomaly detected',
                    'errors' => ['hostel_floor_id' => ['Selected floor does not reside within the chosen hostel block.']]
                ], 422);
            }

            // Set default values for enum fields if not provided
            $validated['ac'] = $validated['ac'] ?? YesNo::No->value;
            $validated['cooler'] = $validated['cooler'] ?? YesNo::No->value;
            $validated['fan'] = $validated['fan'] ?? YesNo::No->value;

            $validated['school_id'] = $schoolId;

            $room = HostelRoom::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Room node established successfully.',
                'data' => $room->load(['hostel', 'floor'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize room node: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified hostel room.
     */
    public function update(Request $request, HostelRoom $hostelRoom)
    {
        $this->authorizeTenant($hostelRoom);

        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
                'hostel_floor_id' => 'required|exists:hostel_floors,id',
                'room_name' => 'required|string|max:255',
                'ac' => ['nullable', 'integer', Rule::enum(YesNo::class)],
                'cooler' => ['nullable', 'integer', Rule::enum(YesNo::class)],
                'fan' => ['nullable', 'integer', Rule::enum(YesNo::class)],
                'room_create_date' => 'nullable|date',
            ]);

            $schoolId = $this->getSchoolId();
            
            // Verify hostel belongs to same school
            $hostel = Hostel::find($validated['hostel_id']);
            if (!$hostel || $hostel->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hostel resource',
                    'errors' => ['hostel_id' => ['Target hostel node resides outside authorized perimeter.']]
                ], 422);
            }

            // Verify floor belongs to same school and hostel
            $floor = HostelFloor::where('id', $validated['hostel_floor_id'])
                ->where('school_id', $schoolId)
                ->where('hostel_id', $validated['hostel_id'])
                ->first();
                
            if (!$floor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Structural mismatch',
                    'errors' => ['hostel_floor_id' => ['Floor node is not a child of the selected hostel block.']]
                ], 422);
            }

            // Set default values for enum fields if not provided
            $validated['ac'] = $validated['ac'] ?? YesNo::No->value;
            $validated['cooler'] = $validated['cooler'] ?? YesNo::No->value;
            $validated['fan'] = $validated['fan'] ?? YesNo::No->value;

            $hostelRoom->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Room specifications updated successfully.',
                'data' => $hostelRoom->load(['hostel', 'floor'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update transmission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified hostel room.
     */
    public function destroy(HostelRoom $hostelRoom)
    {
        $this->authorizeTenant($hostelRoom);

        try {
            // Check if room has beds assigned before deletion
            if ($hostelRoom->bedAssignments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot decommission room node. Active dependency assignments detected.',
                    'errors' => ['room' => ['Active beds are assigned to this room.']]
                ], 422);
            }

            $hostelRoom->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room node successfully struck from registry.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Decommissioning failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export hostel rooms to Excel.
     */
    public function export()
    {
        // Excel export functionality - to be implemented with Laravel Excel
        return back()->with('info', 'Export functionality coming soon.');
    }

    /**
     * Get floors for a selected hostel (AJAX).
     */
    public function getFloors(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
            ]);

            $hostel = Hostel::findOrFail($request->hostel_id);
            
            // Verify tenant ownership
            if ($hostel->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['hostel_id' => ['The selected hostel block is not part of this institutional registry.']]
                ], 422);
            }

            // Get floors for this hostel
            $floors = HostelFloor::where('school_id', $schoolId)
                ->where('hostel_id', $request->hostel_id)
                ->orderBy('floor_name')
                ->get(['id', 'floor_name']);
            
            $floorsArray = $floors->map(function($floor) {
                    return [
                        'id' => $floor->id,
                        'floor_name' => $floor->floor_name,
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'floors' => $floorsArray,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Structural retrieval failure: ' . $e->getMessage()
            ], 500);
        }
    }
}

