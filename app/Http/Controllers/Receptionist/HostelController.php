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
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
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
        try {
            $validated = $request->validate([
                'hostel_name' => 'required|string|max:255',
                'hostel_incharge' => 'nullable|string|max:255',
                'capability' => 'nullable|integer|min:1',
                'hostel_create_date' => 'nullable|date',
            ]);

            $schoolId = $this->getSchoolId();
            $validated['school_id'] = $schoolId;

            $hostel = Hostel::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Hostel added successfully to the registry.',
                'data' => $hostel
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
                'message' => 'Failed to establish new hostel node: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified hostel.
     */
    public function update(Request $request, Hostel $hostel)
    {
        $this->authorizeTenant($hostel);

        try {
            $validated = $request->validate([
                'hostel_name' => 'required|string|max:255',
                'hostel_incharge' => 'nullable|string|max:255',
                'capability' => 'nullable|integer|min:1',
                'hostel_create_date' => 'nullable|date',
            ]);

            $hostel->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Hostel specification updated successfully.',
                'data' => $hostel
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
     * Remove the specified hostel.
     */
    public function destroy(Hostel $hostel)
    {
        $this->authorizeTenant($hostel);

        try {
            // Check if hostel has floors/rooms/beds assigned before deletion
            if (method_exists($hostel, 'floors') && $hostel->floors()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot decommission hostel. Active dependency nodes (floors) detected.',
                    'errors' => ['hostel' => ['Active floors are linked to this hostel.']]
                ], 422);
            }

            $hostel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hostel successfully struck from registry.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Decommissioning failed: ' . $e->getMessage()
            ], 500);
        }
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

