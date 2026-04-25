<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\TransportRoute;
use Illuminate\Support\Facades\DB;

class TransportRouteService
{
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
            $route = new TransportRoute($data);
            $route->school_id = $school->id;
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
            $route->update($data);
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
        // Add check if route has any bus stops or active assignments
        if (DB::table('bus_stops')->where('route_id', $route->id)->exists()) {
            throw new \Exception('Cannot delete route. It has associated bus stops.');
        }

        if (DB::table('student_transport_assignments')->where('route_id', $route->id)->where('status', \App\Enums\GeneralStatus::Active)->exists()) {
            throw new \Exception('Cannot delete route. It has active student assignments.');
        }

        return $route->delete();
    }
}
