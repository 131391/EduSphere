<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Services\School\TransportRouteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\RouteStatus;
use Illuminate\Validation\ValidationException;

use App\Traits\HasAjaxDataTable;

class RouteController extends TenantController
{
    use HasAjaxDataTable;

    public function __construct(
        protected TransportRouteService $routeService
    ) {
        parent::__construct();
    }

    /**
     * Display a listing of routes.
     */
    public function index(Request $request)
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();

        $query = TransportRoute::where('school_id', $schoolId)->with('vehicle');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('route_name', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_no', 'like', "%{$search}%")
                        ->orWhere('registration_no', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Core AJAX Data Handler
        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, [$this, 'transformRow']);
        }

        // Initial hydration for zero-blink loading
        $initialData = $this->getHydrationData($query, [$this, 'transformRow']);

        // Statistics for the premium dashboard
        $stats = [
            'total_routes' => TransportRoute::where('school_id', $schoolId)->count(),
            'active_routes' => TransportRoute::where('school_id', $schoolId)->where('status', RouteStatus::Active)->count(),
            'mapped_vehicles' => TransportRoute::where('school_id', $schoolId)->whereNotNull('vehicle_id')->distinct('vehicle_id')->count(),
            'total_capacity' => TransportRoute::where('school_id', $schoolId)->with('vehicle')->get()->sum(function($route) {
                return $route->vehicle->capacity ?? 0;
            }),
        ];

        return view('receptionist.routes.index', compact('initialData', 'stats'));
    }

    /**
     * Standard row transformer for high-performance datatables.
     */
    public function transformRow($route): array
    {
        return [
            'id' => $route->id,
            'route_name' => $route->route_name,
            'vehicle_id' => $route->vehicle_id,
            'vehicle_label' => $route->vehicle ? ($route->vehicle->registration_no . ' (' . ($route->vehicle->vehicle_no ?? 'N/A') . ')') : 'Unassigned Assets',
            'vehicle_capacity' => $route->vehicle->capacity ?? 'N/A',
            'created_at' => $route->route_create_date ? $route->route_create_date->format('M d, Y') : ($route->created_at->format('M d, Y')),
            'status' => $route->status->value,
            'status_label' => $route->status->name,
            'status_color' => $route->status === RouteStatus::Active ? 'teal' : 'slate',
            'raw_date' => $route->route_create_date ? $route->route_create_date->format('Y-m-d') : $route->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request)
    {
        $this->authorize('receptionist:operate');

        try {
            $validated = $request->validate([
                'route_name' => 'required|string|max:255',
                'vehicle_id' => [
                    'required',
                    Rule::exists('vehicles', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'route_create_date' => 'required|date',
            ]);

            $validated['status'] = RouteStatus::Active;

            $route = $this->routeService->createRoute($this->getSchool(), $validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Route mapping established successfully.',
                    'route' => $route
                ]);
            }

            return redirect()->route('receptionist.routes.index')->with('success', 'Route mapping established successfully.');
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
     * Update the specified route.
     */
    public function update(Request $request, TransportRoute $route)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($route);

        try {
            $validated = $request->validate([
                'route_name' => 'required|string|max:255',
                'vehicle_id' => [
                    'required',
                    Rule::exists('vehicles', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'route_create_date' => 'required|date',
                'status' => ['required', 'integer', Rule::enum(RouteStatus::class)],
            ]);

            $route = $this->routeService->updateRoute($route, $validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Route configuration updated successfully.',
                    'route' => $route
                ]);
            }

            return redirect()->route('receptionist.routes.index')->with('success', 'Route configuration updated successfully.');
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
     * Remove the specified route.
     */
    public function destroy(Request $request, TransportRoute $route)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($route);

        try {
            $this->routeService->deleteRoute($route);

            return response()->json([
                'success' => true, 
                'message' => 'Route record struck from registry.'
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

    /**
     * Get available vehicles for dropdown.
     */
    public function getVehicles()
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();

        $vehicles = Vehicle::where('school_id', $schoolId)
            ->select('id', 'vehicle_no', 'registration_no')
            ->orderBy('vehicle_no')
            ->get();

        return response()->json($vehicles);
    }

    /**
     * Export routes to CSV using high-performance streaming.
     */
    public function export()
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();
        $fileName = 'institutional_routes_manifest_' . now()->format('Y_m_d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function() use ($schoolId) {
            $handle = fopen('php://output', 'w');
            
            // CSV Standard Headers
            fputcsv($handle, [
                'MANIFEST ID',
                'ROUTE DESIGNATION',
                'MAPPED VEHICLE (REG)',
                'INTERNAL VEHICLE NO',
                'CAPACITY',
                'ESTABLISHMENT DATE',
                'OPERATIONAL STATUS'
            ]);

            // Chunked processing for memory efficiency
            TransportRoute::where('school_id', $schoolId)
                ->with('vehicle')
                ->chunk(200, function($routes) use ($handle) {
                    foreach ($routes as $route) {
                        fputcsv($handle, [
                            $route->id,
                            $route->route_name,
                            $route->vehicle->registration_no ?? 'UNASSIGNED',
                            $route->vehicle->vehicle_no ?? 'N/A',
                            $route->vehicle->capacity ?? 0,
                            $route->route_create_date ? $route->route_create_date->format('Y-m-d') : $route->created_at->format('Y-m-d'),
                            $route->status->name
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
