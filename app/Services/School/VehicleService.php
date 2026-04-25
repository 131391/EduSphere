<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    /**
     * Create a new vehicle.
     *
     * @param School $school
     * @param array $data
     * @return Vehicle
     */
    public function createVehicle(School $school, array $data): Vehicle
    {
        return DB::transaction(function () use ($school, $data) {
            $vehicle = new Vehicle($data);
            $vehicle->school_id = $school->id;
            
            // Generate a unique vehicle number for this school
            $vehicle->vehicle_no = Vehicle::generateVehicleNo($school->id);
            
            // Set create date if not provided
            if (empty($data['vehicle_create_date'])) {
                $vehicle->vehicle_create_date = now()->format('Y-m-d');
            }
            
            $vehicle->save();

            return $vehicle;
        });
    }

    /**
     * Update an existing vehicle.
     *
     * @param Vehicle $vehicle
     * @param array $data
     * @return Vehicle
     */
    public function updateVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $data) {
            $vehicle->update($data);
            return $vehicle->fresh();
        });
    }

    /**
     * Delete a vehicle.
     *
     * @param Vehicle $vehicle
     * @return bool|null
     * @throws \Exception
     */
    public function deleteVehicle(Vehicle $vehicle): ?bool
    {
        // Add check if vehicle is assigned to any active routes or transport assignments
        if ($vehicle->routes()->exists()) {
            throw new \Exception('Cannot delete vehicle. It is assigned to one or more active routes.');
        }

        return $vehicle->delete();
    }
}
