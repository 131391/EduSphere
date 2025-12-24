<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\FuelType;

class VehicleController extends TenantController
{
    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = Vehicle::where('school_id', $schoolId);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('vehicle_no', 'like', "%{$search}%")
                  ->orWhere('chassis_no', 'like', "%{$search}%")
                  ->orWhere('engine_no', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $vehicles = $query->paginate($perPage)->withQueryString();

        // Statistics for the page
        $stats = [
            'total' => Vehicle::where('school_id', $schoolId)->count(),
            'diesel' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_DIESEL)->count(),
            'petrol' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_PETROL)->count(),
            'cng' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_CNG)->count(),
            'electric' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_ELECTRIC)->count(),
        ];

        return view('receptionist.vehicles.index', compact('vehicles', 'stats'));
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_no' => 'required|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'fuel_type' => ['required', 'integer', Rule::enum(FuelType::class)],
            'capacity' => 'nullable|integer|min:1',
            'initial_reading' => 'nullable|integer|min:0',
            'engine_no' => 'nullable|string|max:255',
            'chassis_no' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'model_no' => 'nullable|string|max:255',
            'date_of_purchase' => 'nullable|date',
            'vehicle_group' => 'nullable|string|max:255',
            'imei_gps_device' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_create_date' => 'nullable|date',
        ]);

        $schoolId = $this->getSchoolId();
        $validated['school_id'] = $schoolId;
        
        // Auto-generate vehicle number if not provided
        if (empty($validated['vehicle_no'])) {
            $validated['vehicle_no'] = Vehicle::generateVehicleNo($schoolId);
        }

        Vehicle::create($validated);

        return redirect()->route('receptionist.vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeTenant($vehicle);

        $validated = $request->validate([
            'registration_no' => 'required|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'fuel_type' => ['required', 'integer', Rule::enum(FuelType::class)],
            'capacity' => 'nullable|integer|min:1',
            'initial_reading' => 'nullable|integer|min:0',
            'engine_no' => 'nullable|string|max:255',
            'chassis_no' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'model_no' => 'nullable|string|max:255',
            'date_of_purchase' => 'nullable|date',
            'vehicle_group' => 'nullable|string|max:255',
            'imei_gps_device' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_create_date' => 'nullable|date',
        ]);

        $vehicle->update($validated);

        return redirect()->route('receptionist.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorizeTenant($vehicle);

        // Check if vehicle has assigned routes
        if ($vehicle->routes()->count() > 0) {
            return redirect()->route('receptionist.vehicles.index')
                ->with('error', 'Cannot delete vehicle. It has assigned routes.');
        }

        $vehicle->delete();

        return redirect()->route('receptionist.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * Export vehicles to Excel.
     */
    public function export()
    {
        // Excel export functionality - to be implemented with Laravel Excel
        return back()->with('info', 'Export functionality coming soon.');
    }
}
