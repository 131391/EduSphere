<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelController extends TenantController
{
    /**
     * Display a listing of hostels.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = Hostel::where('school_id', $schoolId);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('hostel_name', 'like', "%{$search}%")
                  ->orWhere('hostel_incharge', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $hostels = $query->paginate($perPage)->withQueryString();

        // Statistics for the page
        $stats = [
            'total_hostel' => Hostel::where('school_id', $schoolId)->count(),
            'total_floor' => 0, // Will be calculated when floors table is created
            'total_room' => 0, // Will be calculated when rooms table is created
            'total_bed' => 0, // Will be calculated when beds table is created
            'hosteler_students' => 0, // Will be calculated when student hostel assignments are created
        ];

        return view('receptionist.hostels.index', compact('hostels', 'stats'));
    }

    /**
     * Store a newly created hostel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostel_name' => 'required|string|max:255',
            'hostel_incharge' => 'nullable|string|max:255',
            'capability' => 'nullable|integer|min:1',
            'hostel_create_date' => 'nullable|date',
        ]);

        $schoolId = $this->getSchoolId();
        $validated['school_id'] = $schoolId;

        try {
            Hostel::create($validated);
            return redirect()->route('receptionist.hostels.index')->with('success', 'Hostel added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create hostel. Please try again.'])->withInput();
        }
    }

    /**
     * Update the specified hostel.
     */
    public function update(Request $request, Hostel $hostel)
    {
        $this->authorizeTenant($hostel);

        $validated = $request->validate([
            'hostel_name' => 'required|string|max:255',
            'hostel_incharge' => 'nullable|string|max:255',
            'capability' => 'nullable|integer|min:1',
            'hostel_create_date' => 'nullable|date',
        ]);

        try {
            $hostel->update($validated);
            return redirect()->route('receptionist.hostels.index')->with('success', 'Hostel updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update hostel. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified hostel.
     */
    public function destroy(Hostel $hostel)
    {
        $this->authorizeTenant($hostel);

        // TODO: Check if hostel has floors/rooms/beds assigned
        // if ($hostel->floors()->count() > 0) {
        //     return redirect()->route('receptionist.hostels.index')
        //         ->with('error', 'Cannot delete hostel. It has assigned floors.');
        // }

        $hostel->delete();

        return redirect()->route('receptionist.hostels.index')->with('success', 'Hostel deleted successfully.');
    }

    /**
     * Export hostels to Excel.
     */
    public function export()
    {
        // Excel export functionality - to be implemented with Laravel Excel
        return back()->with('info', 'Export functionality coming soon.');
    }
}

