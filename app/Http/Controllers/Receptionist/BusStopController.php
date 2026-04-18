<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\BusStop;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Traits\HasAjaxDataTable;

class BusStopController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $query = BusStop::where('school_id', $schoolId)
            ->with(['route', 'vehicle']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bus_stop_name', 'like', "%{$search}%")
                    ->orWhere('bus_stop_no', 'like', "%{$search}%")
                    ->orWhereHas('route', function ($rq) use ($search) {
                        $rq->where('route_name', 'like', "%{$search}%");
                    });
            });
        }

        // Core AJAX Data Handler
        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, [$this, 'transformRow']);
        }

        // Initial hydration for zero-blink loading
        $initialData = $this->getHydrationData($query, [$this, 'transformRow']);

        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();

        // Premium statistics for coverage cards
        $stats = [
            'total_stops' => BusStop::where('school_id', $schoolId)->count(),
            'total_routes' => TransportRoute::where('school_id', $schoolId)->count(),
            'distinct_areas' => BusStop::where('school_id', $schoolId)->whereNotNull('area_pin_code')->distinct('area_pin_code')->count(),
            'average_distance' => round(BusStop::where('school_id', $schoolId)->avg('distance_from_institute') ?? 0, 2),
            'total_mapped' => BusStop::where('school_id', $schoolId)->whereNotNull('vehicle_id')->count(),
        ];

        return view('receptionist.bus-stops.index', compact('initialData', 'routes', 'vehicles', 'stats'));
    }

    /**
     * Standard row transformer for high-performance datatables.
     */
    protected function transformRow($stop): array
    {
        return [
            'id' => $stop->id,
            'bus_stop_name' => $stop->bus_stop_name,
            'bus_stop_no' => $stop->bus_stop_no,
            'route_id' => $stop->route_id,
            'route_name' => $stop->route->route_name ?? 'Global Path',
            'vehicle_id' => $stop->vehicle_id,
            'vehicle_label' => $stop->vehicle ? ($stop->vehicle->registration_no . ' (' . ($stop->vehicle->vehicle_no ?? 'N/A') . ')') : 'Dynamic Allocation',
            'distance' => $stop->distance_from_institute . ' km',
            'charge' => $this->formatCurrency($stop->charge_per_month ?? 0),
            'coords' => ($stop->latitude && $stop->longitude) ? ($stop->latitude . ', ' . $stop->longitude) : 'Not Geocoded',
            'pin_code' => $stop->area_pin_code ?? 'N/A',
            // Raw data for edit modal
            'raw' => [
                'route_id' => $stop->route_id,
                'vehicle_id' => $stop->vehicle_id,
                'bus_stop_no' => $stop->bus_stop_no,
                'bus_stop_name' => $stop->bus_stop_name,
                'latitude' => $stop->latitude,
                'longitude' => $stop->longitude,
                'distance_from_institute' => $stop->distance_from_institute,
                'charge_per_month' => $stop->charge_per_month,
                'area_pin_code' => $stop->area_pin_code,
            ]
        ];
    }

    private function formatCurrency($amount)
    {
        return '₹' . number_format($amount, 2);
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
            // Block deletion if students are assigned to this stop
            $assignedCount = \App\Models\StudentTransportAssignment::where('bus_stop_id', $busStop->id)
                ->whereNull('deleted_at')
                ->count();
            if ($assignedCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete bus stop',
                    'errors' => ['bus_stop' => ["{$assignedCount} student(s) are assigned to this stop. Please reassign them first."]]
                ], 422);
            }

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

    /**
     * Export bus stops to CSV using high-performance streaming.
     */
    public function export()
    {
        $schoolId = $this->getSchoolId();
        $fileName = 'institutional_transit_nodes_manifest_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($schoolId) {
            $handle = fopen('php://output', 'w');

            // CSV Standard Headers
            fputcsv($handle, [
                'NODE ID',
                'STOP NO',
                'STOP NAME',
                'ROUTE DESIGNATION',
                'MAPPED VEHICLE (REG)',
                'DISTANCE (KM)',
                'MONTHLY CHARGE',
                'PIN CODE',
                'LATITUDE',
                'LONGITUDE'
            ]);

            // Chunked processing for memory efficiency
            BusStop::where('school_id', $schoolId)
                ->with(['route', 'vehicle'])
                ->chunk(200, function ($stops) use ($handle) {
                    foreach ($stops as $stop) {
                        fputcsv($handle, [
                            $stop->id,
                            $stop->bus_stop_no,
                            $stop->bus_stop_name,
                            $stop->route->route_name ?? 'UNASSIGNED',
                            $stop->vehicle->registration_no ?? 'DYNAMIC',
                            $stop->distance_from_institute,
                            $stop->charge_per_month,
                            $stop->area_pin_code,
                            $stop->latitude,
                            $stop->longitude
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
