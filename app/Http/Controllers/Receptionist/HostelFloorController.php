<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\HostelFloor;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelFloorController extends TenantController
{
    /**
     * Display a listing of hostel floors.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = HostelFloor::where('school_id', $schoolId)->with('hostel');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('floor_name', 'like', "%{$search}%")
                  ->orWhereHas('hostel', function($hostelQuery) use ($search) {
                      $hostelQuery->where('hostel_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $floors = $query->paginate($perPage)->withQueryString();

        // Get all hostels for filter
        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();

        // Statistics for the page
        $stats = [
            'total_floor' => HostelFloor::where('school_id', $schoolId)->count(),
            'total_room' => HostelFloor::where('school_id', $schoolId)->sum('total_room'),
        ];

        return view('receptionist.hostel-floors.index', compact('floors', 'hostels', 'stats'));
    }

    /**
     * Store a newly created hostel floor.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
                'floor_name' => 'required|string|max:255',
                'total_room' => 'nullable|integer|min:0',
                'floor_create_date' => 'nullable|date',
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

            $validated['school_id'] = $schoolId;

            $floor = HostelFloor::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Hostel floor established successfully.',
                'data' => $floor->load('hostel')
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
                'message' => 'Failed to create floor level: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified hostel floor.
     */
    public function update(Request $request, HostelFloor $hostelFloor)
    {
        $this->authorizeTenant($hostelFloor);

        try {
            $validated = $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
                'floor_name' => 'required|string|max:255',
                'total_room' => 'nullable|integer|min:0',
                'floor_create_date' => 'nullable|date',
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

            $hostelFloor->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Floor specifications updated successfully.',
                'data' => $hostelFloor->load('hostel')
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
     * Remove the specified hostel floor.
     */
    public function destroy(HostelFloor $hostelFloor)
    {
        $this->authorizeTenant($hostelFloor);

        try {
            // Check if floor has rooms assigned before deletion
            if (method_exists($hostelFloor, 'rooms') && $hostelFloor->rooms()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot decommission floor level. Active dependency nodes (rooms) detected.',
                    'errors' => ['floor' => ['Active rooms are linked to this floor.']]
                ], 422);
            }

            $hostelFloor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hostel floor successfully struck from registry.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Decommissioning failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export hostel floors to Excel.
     */
    public function export()
    {
        // Excel export functionality - to be implemented with Laravel Excel
        return back()->with('info', 'Export functionality coming soon.');
    }
}
