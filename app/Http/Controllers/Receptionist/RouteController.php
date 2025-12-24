<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\RouteStatus;

class RouteController extends TenantController
{
    /**
     * Display a listing of routes.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = TransportRoute::where('school_id', $schoolId)->with('vehicle');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('route_name', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_no', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $routes = $query->paginate($perPage)->withQueryString();

        return view('receptionist.routes.index', compact('routes'));
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_name' => 'required|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_create_date' => 'nullable|date',
        ]);

        $schoolId = $this->getSchoolId();
        $validated['school_id'] = $schoolId;
        $validated['status'] = TransportRoute::STATUS_ACTIVE;

        // Verify vehicle belongs to same school if provided
        if (!empty($validated['vehicle_id'])) {
            $vehicle = Vehicle::find($validated['vehicle_id']);
            if ($vehicle && $vehicle->school_id !== $schoolId) {
                return back()->withErrors(['vehicle_id' => 'Invalid vehicle selected.'])->withInput();
            }
        }

        TransportRoute::create($validated);

        return redirect()->route('receptionist.routes.index')->with('success', 'Route added successfully.');
    }

    /**
     * Update the specified route.
     */
    public function update(Request $request, TransportRoute $route)
    {
        $this->authorizeTenant($route);

        $validated = $request->validate([
            'route_name' => 'required|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_create_date' => 'nullable|date',
            'status' => ['required', 'integer', Rule::enum(RouteStatus::class)],
        ]);

        $schoolId = $this->getSchoolId();

        // Verify vehicle belongs to same school if provided
        if (!empty($validated['vehicle_id'])) {
            $vehicle = Vehicle::find($validated['vehicle_id']);
            if ($vehicle && $vehicle->school_id !== $schoolId) {
                return back()->withErrors(['vehicle_id' => 'Invalid vehicle selected.'])->withInput();
            }
        }

        $route->update($validated);

        return redirect()->route('receptionist.routes.index')->with('success', 'Route updated successfully.');
    }

    /**
     * Remove the specified route.
     */
    public function destroy(TransportRoute $route)
    {
        $this->authorizeTenant($route);

        $route->delete();

        return redirect()->route('receptionist.routes.index')->with('success', 'Route deleted successfully.');
    }

    /**
     * Get available vehicles for dropdown.
     */
    public function getVehicles()
    {
        $schoolId = $this->getSchoolId();
        
        $vehicles = Vehicle::where('school_id', $schoolId)
            ->select('id', 'vehicle_no', 'registration_no')
            ->orderBy('vehicle_no')
            ->get();

        return response()->json($vehicles);
    }

    /**
     * Export routes to Excel.
     */
    public function export()
    {
        // Excel export functionality - to be implemented with Laravel Excel
        return back()->with('info', 'Export functionality coming soon.');
    }
}
