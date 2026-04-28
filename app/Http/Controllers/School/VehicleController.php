<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreVehicleRequest;
use App\Http\Requests\School\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Services\School\VehicleService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class VehicleController extends TenantController
{
    use HasAjaxDataTable;

    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        parent::__construct();
        $this->vehicleService = $vehicleService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($vehicle) {
            $fuelLabels = [
                Vehicle::FUEL_TYPE_DIESEL => ['label' => 'Diesel', 'color' => 'slate'],
                Vehicle::FUEL_TYPE_PETROL => ['label' => 'Petrol', 'color' => 'blue'],
                Vehicle::FUEL_TYPE_CNG    => ['label' => 'CNG Core', 'color' => 'purple'],
                Vehicle::FUEL_TYPE_ELECTRIC => ['label' => 'Electric EV', 'color' => 'teal'],
            ];
            $fuelInfo = $fuelLabels[$vehicle->fuel_type] ?? ['label' => 'Unknown', 'color' => 'gray'];

            return [
                'id' => $vehicle->id,
                'vehicle_no' => $vehicle->vehicle_no,
                'registration_no' => $vehicle->registration_no,
                'capacity' => $vehicle->capacity . ' Units',
                'model_no' => $vehicle->model_no ?? 'N/A',
                'fuel_label' => $fuelInfo['label'],
                'fuel_color' => $fuelInfo['color'],
                'created_at' => $vehicle->created_at?->format('M d, Y'),
            ];
        };

        $query = Vehicle::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('vehicle_no', 'like', '%' . $search . '%')
                  ->orWhere('registration_no', 'like', '%' . $search . '%')
                  ->orWhere('model_no', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->input('sort', 'vehicle_no');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        
        $allowedSorts = ['id', 'vehicle_no', 'registration_no', 'model_no', 'created_at'];
        if (\in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('vehicle_no', 'asc');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, []);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => Vehicle::where('school_id', $schoolId)->count(),
                'diesel' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_DIESEL)->count(),
                'petrol' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_PETROL)->count(),
                'cng' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_CNG)->count(),
                'electric' => Vehicle::where('school_id', $schoolId)->where('fuel_type', Vehicle::FUEL_TYPE_ELECTRIC)->count(),
            ]
        ]);

        return view('school.transport.vehicles', [
            'initialData' => $initialData,
            'title' => 'Vehicles Management',
            'stats' => $initialData['stats']
        ]);
    }

    public function export()
    {
        $schoolId = $this->getSchoolId();
        $query = Vehicle::where('school_id', $schoolId);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="institutional_fleet_registry_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Registration No', 'Vehicle No', 'Fuel Type', 'Capacity', 'Engine No', 'Model No', 'Purchase Date']);

            $query->orderBy('created_at', 'desc')->cursor()->each(function (Vehicle $vehicle) use ($file) {
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

    public function store(StoreVehicleRequest $request)
    {
        try {
            $vehicle = $this->vehicleService->createVehicle(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle "' . $vehicle->vehicle_no . '" added successfully!',
                    'data' => $vehicle
                ]);
            }

            return $this->redirectWithSuccess(
                'school.vehicles.index',
                'Vehicle "' . $vehicle->vehicle_no . '" added successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add vehicle: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to add vehicle: ' . $e->getMessage());
        }
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        try {
            $vehicle = Vehicle::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $vehicle = $this->vehicleService->updateVehicle($vehicle, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle "' . $vehicle->vehicle_no . '" updated successfully!',
                    'data' => $vehicle
                ]);
            }

            return $this->redirectWithSuccess(
                'school.vehicles.index',
                'Vehicle "' . $vehicle->vehicle_no . '" updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update vehicle: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update vehicle: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $vehicleNo = $vehicle->vehicle_no;
            $this->vehicleService->deleteVehicle($vehicle);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle "' . $vehicleNo . '" deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.vehicles.index',
                'Vehicle "' . $vehicleNo . '" deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete vehicle: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.vehicles.index',
                'Failed to delete vehicle: ' . $e->getMessage()
            );
        }
    }
}
