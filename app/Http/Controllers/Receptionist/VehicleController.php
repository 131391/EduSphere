<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\FuelType;
use Illuminate\Validation\ValidationException;

use App\Traits\HasAjaxDataTable;

class VehicleController extends TenantController
{
    use HasAjaxDataTable;

    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        // 1. Row Transformer (Gold Standard UI consistency)
        $transformer = function ($vehicle) {
            $fuelLabels = [
                Vehicle::FUEL_TYPE_DIESEL => ['label' => 'Diesel', 'color' => 'slate'],
                Vehicle::FUEL_TYPE_PETROL => ['label' => 'Petrol', 'color' => 'blue'],
                Vehicle::FUEL_TYPE_CNG    => ['label' => 'CNG Core', 'color' => 'purple'],
                Vehicle::FUEL_TYPE_ELECTRIC => ['label' => 'Electric EV', 'color' => 'teal'],
            ];
            $fuelInfo = $fuelLabels[$vehicle->fuel_type] ?? ['label' => 'Unknown', 'color' => 'gray'];

            return [
                'id'                => $vehicle->id,
                'registration_no'   => $vehicle->registration_no,
                'vehicle_no'        => $vehicle->vehicle_no,
                'capacity'          => $vehicle->capacity . ' Units',
                'fuel_label'        => $fuelInfo['label'],
                'fuel_color'        => $fuelInfo['color'],
                'engine_no'         => $vehicle->engine_no ?? 'N/A',
                'model_no'          => $vehicle->model_no ?? 'N/A',
                'purchase_date'     => $vehicle->date_of_purchase ? $vehicle->date_of_purchase->format('d M, Y') : 'N/A',
                'tracking_url'      => $vehicle->tracking_url,
                'status_label'      => 'Active Fleet',
                'status_color'      => 'emerald',
            ];
        };

        // 2. Build Query
        $query = Vehicle::where('school_id', $schoolId);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('vehicle_no', 'like', "%{$search}%")
                  ->orWhere('chassis_no', 'like', "%{$search}%")
                  ->orWhere('engine_no', 'like', "%{$search}%");
            });
        }

        // 3. Handle AJAX or CSV Export vs Blade Hydration
        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        // 4. Blade Hydration
        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => Vehicle::where('school_id', $schoolId)->count(),
                'diesel' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_DIESEL)->count(),
                'petrol' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_PETROL)->count(),
                'cng' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_CNG)->count(),
                'electric' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_ELECTRIC)->count(),
            ]
        ]);

        return view('receptionist.vehicles.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats']
        ]);
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="institutional_fleet_registry_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Registration No', 'Vehicle No', 'Fuel Type', 'Capacity', 'Engine No', 'Model No', 'Purchase Date']);

            $query->orderBy('created_at', 'desc')->cursor()->each(function ($vehicle) use ($file) {
                fputcsv($file, [
                    $vehicle->registration_no,
                    $vehicle->vehicle_no,
                    $vehicle->getFuelTypeLabel(),
                    $vehicle->capacity,
                    $vehicle->engine_no,
                    $vehicle->model_no,
                    $vehicle->date_of_purchase ? $vehicle->date_of_purchase->format('Y-m-d') : 'N/A'
                ]);
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        try {
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
            
            if (empty($validated['vehicle_no'])) {
                $validated['vehicle_no'] = Vehicle::generateVehicleNo($schoolId);
            }

            $vehicle = Vehicle::create($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle registry synchronized successfully.',
                    'vehicle' => $vehicle
                ]);
            }

            return redirect()->route('receptionist.vehicles.index')->with('success', 'Vehicle registry synchronized successfully.');
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeTenant($vehicle);

        try {
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

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle metadata updated successfully.',
                    'vehicle' => $vehicle
                ]);
            }

            return redirect()->route('receptionist.vehicles.index')->with('success', 'Vehicle metadata updated successfully.');
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Request $request, Vehicle $vehicle)
    {
        $this->authorizeTenant($vehicle);

        try {
            if ($vehicle->routes()->count() > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Integrity violation',
                    'errors' => ['registration_no' => ['Cannot strike vehicle record. Active route mappings exist.']]
                ], 422);
            }

            $vehicle->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Vehicle record struck from registry.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
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
