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
    protected $receptionistUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->school = $this->createSchool();
        $this->adminUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first()->id,
        ]);
        $this->receptionistUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::RECEPTIONIST)->first()->id,
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

    public function test_cannot_assign_transport_if_vehicle_at_capacity()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id, 'capacity' => 1]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $busStop = BusStop::factory()->create([
            'school_id' => $this->school->id, 
            'route_id' => $route->id, 
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 2000
        ]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        // First assignment should succeed
        $student1 = Student::factory()->create(['school_id' => $this->school->id]);
        \App\Models\StudentTransportAssignment::create([
            'school_id' => $this->school->id,
            'student_id' => $student1->id,
            'route_id' => $route->id,
            'bus_stop_id' => $busStop->id,
            'vehicle_id' => $vehicle->id,
            'fee_per_month' => 2000,
            'academic_year_id' => $academicYear->id,
            'status' => GeneralStatus::Active,
        ]);

        // Second assignment should fail
        $student2 = Student::factory()->create(['school_id' => $this->school->id]);
        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/transport/' . $student2->id, [
                'action' => 'assign',
                'route_id' => $route->id,
                'bus_stop_id' => $busStop->id,
                'academic_year_id' => $academicYear->id,
                'start_date' => now()->format('Y-m-d'),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vehicle_id');
    }

    public function test_can_reassign_transport_within_same_academic_year()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id, 'capacity' => 40]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $firstStop = BusStop::factory()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 1800,
        ]);
        $secondStop = BusStop::factory()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 2200,
        ]);
        $student = Student::factory()->create(['school_id' => $this->school->id, 'is_transport_required' => YesNo::No]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/transport/' . $student->id, [
                'action' => 'assign',
                'route_id' => $route->id,
                'bus_stop_id' => $firstStop->id,
                'academic_year_id' => $academicYear->id,
                'start_date' => now()->subWeek()->format('Y-m-d'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/transport/' . $student->id, [
                'action' => 'assign',
                'route_id' => $route->id,
                'bus_stop_id' => $secondStop->id,
                'academic_year_id' => $academicYear->id,
                'start_date' => now()->format('Y-m-d'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseCount('student_transport_assignments', 2);
        $this->assertDatabaseHas('student_transport_assignments', [
            'student_id' => $student->id,
            'bus_stop_id' => $firstStop->id,
            'status' => GeneralStatus::Inactive,
        ]);
        $this->assertDatabaseHas('student_transport_assignments', [
            'student_id' => $student->id,
            'bus_stop_id' => $secondStop->id,
            'status' => GeneralStatus::Active,
            'fee_per_month' => 2200,
        ]);
    }

    public function test_cannot_assign_transport_with_bus_stop_from_another_route()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $routeA = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $routeB = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $busStopOnRouteB = BusStop::factory()->create([
            'school_id' => $this->school->id,
            'route_id' => $routeB->id,
            'vehicle_id' => $vehicle->id,
        ]);
        $student = Student::factory()->create(['school_id' => $this->school->id]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/transport/' . $student->id, [
                'action' => 'assign',
                'route_id' => $routeA->id,
                'bus_stop_id' => $busStopOnRouteB->id,
                'academic_year_id' => $academicYear->id,
                'start_date' => now()->format('Y-m-d'),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('bus_stop_id');
    }

    public function test_cannot_use_cross_tenant_foreign_keys()
    {
        $otherSchool = $this->createSchool();
        $otherVehicle = Vehicle::factory()->create(['school_id' => $otherSchool->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/routes', [
                'route_name' => 'Malicious Route',
                'vehicle_id' => $otherVehicle->id,
                'route_create_date' => now()->format('Y-m-d'),
                'status' => RouteStatus::Active->value,
            ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vehicle_id');
    }

    public function test_cannot_delete_vehicle_with_active_routes()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->delete('http://' . $this->school->subdomain . '.localhost/school/transport/vehicles/' . $vehicle->id, [], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_can_mark_attendance()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $busStop = BusStop::factory()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 1500,
        ]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $student = Student::factory()->create(['school_id' => $this->school->id]);

        \App\Models\StudentTransportAssignment::create([
            'school_id' => $this->school->id,
            'student_id' => $student->id,
            'route_id' => $route->id,
            'bus_stop_id' => $busStop->id,
            'vehicle_id' => $vehicle->id,
            'fee_per_month' => 1500,
            'academic_year_id' => $academicYear->id,
            'start_date' => now()->format('Y-m-d'),
            'status' => GeneralStatus::Active,
        ]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/attendance', [
                'vehicle_id' => $vehicle->id,
                'route_id' => $route->id,
                'academic_year_id' => $academicYear->id,
                'attendance_date' => now()->format('Y-m-d'),
                'attendance_type' => \App\Enums\TransportAttendanceType::PickupFromBusStop->value,
                'attendance_data' => [
                    [
                        'student_id' => $student->id,
                        'is_present' => 1,
                        'remarks' => 'On time',
                    ]
                ]
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('transport_attendances', [
            'student_id' => $student->id,
            'attendance_type' => \App\Enums\TransportAttendanceType::PickupFromBusStop->value,
            'is_present' => 1,
        ]);
    }

    public function test_cannot_mark_attendance_for_unassigned_student_on_route()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $student = Student::factory()->create(['school_id' => $this->school->id]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/attendance', [
                'vehicle_id' => $vehicle->id,
                'route_id' => $route->id,
                'academic_year_id' => $academicYear->id,
                'attendance_date' => now()->format('Y-m-d'),
                'attendance_type' => \App\Enums\TransportAttendanceType::PickupFromBusStop->value,
                'attendance_data' => [
                    [
                        'student_id' => $student->id,
                        'is_present' => 1,
                    ],
                ],
            ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('students');
    }

    public function test_school_transport_assignments_endpoint_defaults_academic_year_and_start_date()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $busStop = BusStop::factory()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 1600,
        ]);
        $student = Student::factory()->create(['school_id' => $this->school->id, 'is_transport_required' => YesNo::No]);
        $academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'is_current' => true,
        ]);

        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/transport/assignments', [
                'student_id' => $student->id,
                'route_id' => $route->id,
                'bus_stop_id' => $busStop->id,
            ], ['Accept' => 'application/json']);

        $response->assertOk();
        $this->assertDatabaseHas('student_transport_assignments', [
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'fee_per_month' => 1600,
        ]);
    }

    public function test_school_transport_pages_render_successfully()
    {
        $this->actingAsUser($this->adminUser)
            ->get('http://' . $this->school->subdomain . '.localhost/school/transport/attendance')
            ->assertOk();

        $this->actingAsUser($this->adminUser)
            ->get('http://' . $this->school->subdomain . '.localhost/school/transport/assignments')
            ->assertOk();
    }

    public function test_receptionist_can_assign_transport_using_shared_integrity_rules()
    {
        $vehicle = Vehicle::factory()->create(['school_id' => $this->school->id]);
        $route = TransportRoute::factory()->create(['school_id' => $this->school->id, 'vehicle_id' => $vehicle->id]);
        $busStop = BusStop::factory()->create([
            'school_id' => $this->school->id,
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'charge_per_month' => 1750,
        ]);
        $academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
            'is_current' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $this->school->id, 'is_transport_required' => YesNo::No]);

        $response = $this->actingAsUser($this->receptionistUser)
            ->post('http://' . $this->school->subdomain . '.localhost/receptionist/transport-assignments', [
                'student_id' => $student->id,
                'route_id' => $route->id,
                'bus_stop_id' => $busStop->id,
            ], ['Accept' => 'application/json']);

        $response->assertOk();
        $this->assertDatabaseHas('student_transport_assignments', [
            'student_id' => $student->id,
            'route_id' => $route->id,
            'bus_stop_id' => $busStop->id,
            'vehicle_id' => $vehicle->id,
            'academic_year_id' => $academicYear->id,
            'fee_per_month' => 1750,
            'status' => GeneralStatus::Active,
        ]);
        $this->assertEquals(YesNo::Yes, $student->fresh()->is_transport_required);
    }
}
