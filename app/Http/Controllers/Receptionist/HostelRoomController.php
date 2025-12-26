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
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $rooms = $query->paginate($perPage)->withQueryString();

        // Get all hostels and floors for filters
        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        $floors = HostelFloor::where('school_id', $schoolId)->with('hostel')->orderBy('floor_name')->get();

        return view('receptionist.hostel-rooms.index', compact('rooms', 'hostels', 'floors'));
    }

    /**
     * Store a newly created hostel room.
     */
    public function store(Request $request)
    {
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
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        // Verify floor belongs to same school and hostel
        $floor = HostelFloor::where('id', $validated['hostel_floor_id'])
            ->where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->first();
            
        if (!$floor) {
            return back()->withErrors(['hostel_floor_id' => 'The selected floor does not belong to the selected hostel.'])->withInput();
        }

        // Set default values for enum fields if not provided
        $validated['ac'] = $validated['ac'] ?? YesNo::No->value;
        $validated['cooler'] = $validated['cooler'] ?? YesNo::No->value;
        $validated['fan'] = $validated['fan'] ?? YesNo::No->value;

        $validated['school_id'] = $schoolId;

        try {
            HostelRoom::create($validated);
            return redirect()->route('receptionist.hostel-rooms.index')->with('success', 'Hostel room added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create hostel room. Please try again.'])->withInput();
        }
    }

    /**
     * Update the specified hostel room.
     */
    public function update(Request $request, HostelRoom $hostelRoom)
    {
        $this->authorizeTenant($hostelRoom);

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
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        // Verify floor belongs to same school and hostel
        $floor = HostelFloor::where('id', $validated['hostel_floor_id'])
            ->where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->first();
            
        if (!$floor) {
            return back()->withErrors(['hostel_floor_id' => 'The selected floor does not belong to the selected hostel.'])->withInput();
        }

        // Set default values for enum fields if not provided
        $validated['ac'] = $validated['ac'] ?? YesNo::No->value;
        $validated['cooler'] = $validated['cooler'] ?? YesNo::No->value;
        $validated['fan'] = $validated['fan'] ?? YesNo::No->value;

        try {
            $hostelRoom->update($validated);
            return redirect()->route('receptionist.hostel-rooms.index')->with('success', 'Hostel room updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update hostel room. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified hostel room.
     */
    public function destroy(HostelRoom $hostelRoom)
    {
        $this->authorizeTenant($hostelRoom);

        // TODO: Check if room has beds assigned
        // if ($hostelRoom->beds()->count() > 0) {
        //     return redirect()->route('receptionist.hostel-rooms.index')
        //         ->with('error', 'Cannot delete room. It has assigned beds.');
        // }

        $hostelRoom->delete();

        return redirect()->route('receptionist.hostel-rooms.index')->with('success', 'Hostel room deleted successfully.');
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
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
        ]);

        $hostel = Hostel::findOrFail($request->hostel_id);
        
        // Verify tenant ownership
        if ($hostel->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
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
    }
}

