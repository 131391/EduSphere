<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\BusStop;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BusStopController extends TenantController
{
    public function index()
    {
        $schoolId = $this->getSchoolId();
        $busStops = BusStop::where('school_id', $schoolId)
            ->with(['route', 'vehicle'])
            ->latest()
            ->paginate(15);
        
        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();

        return view('receptionist.bus-stops.index', compact('busStops', 'routes', 'vehicles'));
    }

    public function store(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $validated = $request->validate([
                'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
                'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
                'bus_stop_no' => ['required', 'string', 'max:255'],
                'bus_stop_name' => ['required', 'string', 'max:255'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'distance_from_institute' => ['nullable', 'numeric', 'min:0'],
                'charge_per_month' => ['nullable', 'numeric', 'min:0'],
                'area_pin_code' => ['nullable', 'string', 'max:10'],
            ]);

            // Verify route belongs to school
            $routeCheck = TransportRoute::where('id', $validated['route_id'])->where('school_id', $schoolId)->exists();
            if (!$routeCheck) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['route_id' => ['The selected route does not belong to your institutional fleet.']]
                ], 422);
            }
            
            if (!empty($validated['vehicle_id'])) {
                $vehicleCheck = Vehicle::where('id', $validated['vehicle_id'])->where('school_id', $schoolId)->exists();
                if (!$vehicleCheck) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Integrity violation',
                        'errors' => ['vehicle_id' => ['The selected vehicle is not part of this institutional registry.']]
                    ], 422);
                }
            }

            $validated['school_id'] = $schoolId;
            $busStop = BusStop::create($validated);

            return response()->json([
                'success' => true, 
                'message' => 'Bus stop node successfully commissioned.',
                'bus_stop' => $busStop
            ]);
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

    public function update(Request $request, BusStop $busStop)
    {
        $this->authorizeTenant($busStop);

        try {
            $schoolId = $this->getSchoolId();
            
            $validated = $request->validate([
                'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
                'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
                'bus_stop_no' => ['required', 'string', 'max:255'],
                'bus_stop_name' => ['required', 'string', 'max:255'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'distance_from_institute' => ['nullable', 'numeric', 'min:0'],
                'charge_per_month' => ['nullable', 'numeric', 'min:0'],
                'area_pin_code' => ['nullable', 'string', 'max:10'],
            ]);

            // Verify route belongs to school
            $routeCheck = TransportRoute::where('id', $validated['route_id'])->where('school_id', $schoolId)->exists();
            if (!$routeCheck) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['route_id' => ['The selected route does not belong to your institutional fleet.']]
                ], 422);
            }
            
            if (!empty($validated['vehicle_id'])) {
                $vehicleCheck = Vehicle::where('id', $validated['vehicle_id'])->where('school_id', $schoolId)->exists();
                if (!$vehicleCheck) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Integrity violation',
                        'errors' => ['vehicle_id' => ['The selected vehicle is not part of this institutional registry.']]
                    ], 422);
                }
            }

            $busStop->update($validated);

            return response()->json([
                'success' => true, 
                'message' => 'Node configuration updated successfully.',
                'bus_stop' => $busStop
            ]);
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

    public function destroy(BusStop $busStop)
    {
        $this->authorizeTenant($busStop);

        try {
            // Future check: ensure no students are assigned to this stop
            
            $busStop->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Bus stop node removed from network.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
    }
}
