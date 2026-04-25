<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\School;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\TransportRoute;
use App\Models\BusStop;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Enums\GeneralStatus;
use App\Enums\YesNo;
use App\Enums\RouteStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransportModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->school = $this->createSchool();
        $this->adminUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first()->id,
        ]);
        
        $this->setCurrentSchool($this->school);
    }

    public function test_can_create_vehicle()
    {
        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/vehicles', [
                'registration_no' => 'MH12AB1234',
                'fuel_type' => 1,
                'capacity' => 40,
                'vehicle_type' => 'Bus',
                'model_no' => 'Tata Marcopolo',
                'manufacturing_year' => 2022,
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vehicles', [
            'registration_no' => 'MH12AB1234',
            'capacity' => 40,
        ]);
    }

    public function test_can_create_transport_route()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/routes', [
                'route_name' => 'Route 1 - East',
                'vehicle_id' => $vehicle->id,
                'route_create_date' => now()->format('Y-m-d'),
                'status' => RouteStatus::Active->value,
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('transport_routes', [
            'route_name' => 'Route 1 - East',
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_can_create_bus_stop()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/bus-stops', [
                'route_id' => $route->id,
                'vehicle_id' => $vehicle->id,
                'bus_stop_no' => 'S1',
                'bus_stop_name' => 'Main Gate',
                'distance_from_institute' => 5.5,
                'charge_per_month' => 1500,
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('bus_stops', [
            'bus_stop_name' => 'Main Gate',
            'charge_per_month' => 1500,
        ]);
    }

    public function test_can_assign_transport_to_student()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $busStop = BusStop::factory()->create([
            'school_id' => $this->school->id, 
            'route_id' => $route->id, 
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 2000
        ]);
        $student = Student::factory()->create(['school_id' => $this->school->id, 'is_transport_required' => YesNo::No]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/transport/' . $student->id, [
                'action' => 'assign',
                'route_id' => $route->id,
                'bus_stop_id' => $busStop->id,
                'academic_year_id' => $academicYear->id,
                'start_date' => now()->format('Y-m-d'),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('student_transport_assignments', [
            'student_id' => $student->id,
            'bus_stop_id' => $busStop->id,
            'fee_per_month' => 2000,
            'status' => GeneralStatus::Active,
        ]);

        $this->assertEquals(YesNo::Yes, $student->fresh()->is_transport_required);
    }
}
