<?php

namespace App\Services\School;

use App\Enums\GeneralStatus;
use App\Enums\RouteStatus;
use App\Models\School;
use App\Models\StudentTransportAssignment;
use App\Models\TransportAttendance;
use App\Models\TransportRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportRouteService
{
    public function __construct(
        protected TransportIntegrityService $transportIntegrityService
    ) {
    }

    /**
     * Create a new transport route.
     *
     * @param School $school
     * @param array $data
     * @return TransportRoute
     */
    public function createRoute(School $school, array $data): TransportRoute
    {
        return DB::transaction(function () use ($school, $data) {
            $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, (int) $data['vehicle_id'], true);
            $this->ensureUniqueRouteName($school, $data['route_name']);

            $route = new TransportRoute($data);
            $route->school_id = $school->id;
            $route->vehicle_id = $vehicle->id;
            $route->save();

            return $route;
        });
    }

    /**
     * Update an existing transport route.
     *
     * @param TransportRoute $route
     * @param array $data
     * @return TransportRoute
     */
    public function updateRoute(TransportRoute $route, array $data): TransportRoute
    {
        return DB::transaction(function () use ($route, $data) {
            $school = $route->school;
            $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, (int) $data['vehicle_id'], true);
            $this->ensureUniqueRouteName($school, $data['route_name'], $route->id);

            $activeAssignmentsCount = StudentTransportAssignment::query()
                ->where('school_id', $school->id)
                ->where('route_id', $route->id)
                ->where('status', GeneralStatus::Active)
                ->count();

            if ($vehicle->capacity !== null && $activeAssignmentsCount > $vehicle->capacity) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['The selected vehicle cannot be assigned to this route because its capacity is lower than the number of active student assignments on the route.'],
                ]);
            }

            if (
                isset($data['status'])
                && (int) $data['status'] === RouteStatus::Inactive->value
                && $activeAssignmentsCount > 0
            ) {
                throw ValidationException::withMessages([
                    'status' => ['This route still has active student assignments. Reassign or remove them before marking the route inactive.'],
                ]);
            }

            $route->update($data);

            if ((int) $route->vehicle_id === (int) $vehicle->id) {
                DB::table('bus_stops')
                    ->where('route_id', $route->id)
                    ->update(['vehicle_id' => $vehicle->id]);

                StudentTransportAssignment::query()
                    ->where('school_id', $school->id)
                    ->where('route_id', $route->id)
                    ->where('status', GeneralStatus::Active)
                    ->update(['vehicle_id' => $vehicle->id]);
            }

            return $route->fresh();
        });
    }

    /**
     * Delete a transport route.
     *
     * @param TransportRoute $route
     * @return bool|null
     * @throws \Exception
     */
    public function deleteRoute(TransportRoute $route): ?bool
    {
        if (DB::table('bus_stops')->where('route_id', $route->id)->exists()) {
            throw ValidationException::withMessages([
                'route' => ['Cannot delete this route while bus stops are still mapped to it.'],
            ]);
        }

        if (StudentTransportAssignment::withTrashed()->where('route_id', $route->id)->exists()) {
            throw ValidationException::withMessages([
                'route' => ['Cannot delete this route because transport assignment history exists for it.'],
            ]);
        }

        if (TransportAttendance::where('route_id', $route->id)->exists()) {
            throw ValidationException::withMessages([
                'route' => ['Cannot delete this route because attendance history exists for it.'],
            ]);
        }

        return $route->delete();
    }

    private function ensureUniqueRouteName(School $school, string $routeName, ?int $ignoreRouteId = null): void
    {
        $query = TransportRoute::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('route_name', $routeName);

        if ($ignoreRouteId !== null) {
            $query->where('id', '!=', $ignoreRouteId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'route_name' => ['A route with this name already exists in this school.'],
            ]);
        }
    }
}
