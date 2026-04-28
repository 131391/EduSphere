<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\StudentTransportAssignment;
use App\Models\TransportAttendance;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            $this->ensureUniqueRegistrationNumber($school, $data['registration_no']);

            $vehicle = new Vehicle($data);
            $vehicle->school_id = $school->id;
            
            // Generate a unique vehicle number for this school
            $vehicle->vehicle_no = $data['vehicle_no'] ?? Vehicle::generateVehicleNo($school->id);
            $this->ensureUniqueVehicleNumber($school, $vehicle->vehicle_no);
            
            // Set create date if not provided
            if (empty($data['vehicle_create_date'])) {
                $vehicle->vehicle_create_date = now();
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
            $this->ensureUniqueRegistrationNumber($vehicle->school, $data['registration_no'], $vehicle->id);

            if (array_key_exists('vehicle_no', $data) && filled($data['vehicle_no'])) {
                $this->ensureUniqueVehicleNumber($vehicle->school, $data['vehicle_no'], $vehicle->id);
            }

            if (
                array_key_exists('is_active', $data)
                && !$data['is_active']
                && $vehicle->is_active
                && $vehicle->routes()->exists()
            ) {
                throw ValidationException::withMessages([
                    'is_active' => ['This vehicle is still mapped to one or more routes. Unmap those routes before deactivating the vehicle.'],
                ]);
            }

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
        if ($vehicle->routes()->exists()) {
            throw ValidationException::withMessages([
                'vehicle' => ['Cannot delete this vehicle while routes are still assigned to it.'],
            ]);
        }

        if ($vehicle->busStops()->exists()) {
            throw ValidationException::withMessages([
                'vehicle' => ['Cannot delete this vehicle while bus stops are still mapped to it.'],
            ]);
        }

        if (
            StudentTransportAssignment::withTrashed()
                ->where('vehicle_id', $vehicle->id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'vehicle' => ['Cannot delete this vehicle because transport assignment history exists for it. Deactivate it instead.'],
            ]);
        }

        if (TransportAttendance::where('vehicle_id', $vehicle->id)->exists()) {
            throw ValidationException::withMessages([
                'vehicle' => ['Cannot delete this vehicle because attendance history exists for it. Deactivate it instead.'],
            ]);
        }

        return $vehicle->delete();
    }

    private function ensureUniqueRegistrationNumber(School $school, string $registrationNo, ?int $ignoreVehicleId = null): void
    {
        $query = Vehicle::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('registration_no', $registrationNo);

        if ($ignoreVehicleId !== null) {
            $query->where('id', '!=', $ignoreVehicleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'registration_no' => ['A vehicle with this registration number already exists in this school.'],
            ]);
        }
    }

    private function ensureUniqueVehicleNumber(School $school, string $vehicleNo, ?int $ignoreVehicleId = null): void
    {
        $query = Vehicle::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('vehicle_no', $vehicleNo);

        if ($ignoreVehicleId !== null) {
            $query->where('id', '!=', $ignoreVehicleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'vehicle_no' => ['A vehicle with this internal vehicle number already exists in this school.'],
            ]);
        }
    }
}
