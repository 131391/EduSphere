<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\RouteStatus;
use Illuminate\Validation\ValidationException;

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
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $routes = $query->paginate($perPage)->withQueryString();

        // Statistics for the premium dashboard
        $stats = [
            'total_routes' => TransportRoute::where('school_id', $schoolId)->count(),
            'active_routes' => TransportRoute::where('school_id', $schoolId)->where('status', RouteStatus::Active)->count(),
            'mapped_vehicles' => TransportRoute::where('school_id', $schoolId)->distinct('vehicle_id')->count(),
            'total_capacity' => TransportRoute::where('school_id', $schoolId)->with('vehicle')->get()->sum(function($route) {
                return $route->vehicle->capacity ?? 0;
            }),
        ];

        return view('receptionist.routes.index', compact('routes', 'stats'));
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'route_name' => 'required|string|max:255',
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_create_date' => 'required|date',
            ]);

            $schoolId = $this->getSchoolId();
            $validated['school_id'] = $schoolId;
            $validated['status'] = RouteStatus::Active;

            // Verify vehicle belongs to same school
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                ->where('school_id', $schoolId)
                ->first();
                
            if (!$vehicle) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Integrity violation',
                    'errors' => ['vehicle_id' => ['The selected vehicle is not part of this institutional fleet.']]
                ], 422);
            }

            $route = TransportRoute::create($validated);

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
        $this->authorizeTenant($route);

        try {
            $validated = $request->validate([
                'route_name' => 'required|string|max:255',
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_create_date' => 'required|date',
                'status' => ['required', 'integer', Rule::enum(RouteStatus::class)],
            ]);

            $schoolId = $this->getSchoolId();

            // Verify vehicle belongs to same school
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                ->where('school_id', $schoolId)
                ->first();
                
            if (!$vehicle) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Integrity violation',
                    'errors' => ['vehicle_id' => ['The selected vehicle is not part of this institutional fleet.']]
                ], 422);
            }

            $route->update($validated);

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
        $this->authorizeTenant($route);

        try {
            // Future check: ensure no students are assigned (once that relation exists)
            
            $route->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Route record struck from registry.'
            ]);
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
