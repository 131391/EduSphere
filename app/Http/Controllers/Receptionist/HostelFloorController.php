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
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
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
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'floor_name' => 'required|string|max:255',
            'total_room' => 'nullable|integer|min:0',
            'floor_create_date' => 'nullable|date',
        ]);

        $schoolId = $this->getSchoolId();
        
        // Verify hostel belongs to same school
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        $validated['school_id'] = $schoolId;

        try {
            HostelFloor::create($validated);
            return redirect()->route('receptionist.hostel-floors.index')->with('success', 'Hostel floor added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create hostel floor. Please try again.'])->withInput();
        }
    }

    /**
     * Update the specified hostel floor.
     */
    public function update(Request $request, HostelFloor $hostelFloor)
    {
        $this->authorizeTenant($hostelFloor);

        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'floor_name' => 'required|string|max:255',
            'total_room' => 'nullable|integer|min:0',
            'floor_create_date' => 'nullable|date',
        ]);

        $schoolId = $this->getSchoolId();
        
        // Verify hostel belongs to same school
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        try {
            $hostelFloor->update($validated);
            return redirect()->route('receptionist.hostel-floors.index')->with('success', 'Hostel floor updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update hostel floor. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified hostel floor.
     */
    public function destroy(HostelFloor $hostelFloor)
    {
        $this->authorizeTenant($hostelFloor);

        // TODO: Check if floor has rooms assigned
        // if ($hostelFloor->rooms()->count() > 0) {
        //     return redirect()->route('receptionist.hostel-floors.index')
        //         ->with('error', 'Cannot delete floor. It has assigned rooms.');
        // }

        $hostelFloor->delete();

        return redirect()->route('receptionist.hostel-floors.index')->with('success', 'Hostel floor deleted successfully.');
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
