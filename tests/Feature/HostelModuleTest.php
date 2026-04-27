<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\HostelBedAssignment;
use App\Models\ClassModel;
use App\Enums\GeneralStatus;

class HostelModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $adminUser;
    protected $receptionistUser;
    protected $student;
    protected $otherSchool;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->school = $this->createSchool();
        $this->otherSchool = $this->createSchool();

        $this->adminUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->first()->id,
        ]);

        $this->receptionistUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id' => \App\Models\Role::where('slug', \App\Models\Role::RECEPTIONIST)->first()->id,
        ]);
        
        $this->setCurrentSchool($this->school);

        $class = ClassModel::factory()->create(['school_id' => $this->school->id]);
        $this->student = Student::factory()->create([
            'school_id' => $this->school->id,
            'class_id' => $class->id,
            'status' => GeneralStatus::Active->value,
        ]);
    }

    public function test_can_create_hostel_with_tenant_isolation()
    {
        $response = $this->actingAsUser($this->receptionistUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/receptionist/hostels', [
                'hostel_name' => 'Boys Hostel A',
                'capability' => 50,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('hostels', [
            'hostel_name' => 'Boys Hostel A',
            'capability' => 50,
            'school_id' => $this->school->id,
        ]);
    }

    public function test_cannot_delete_hostel_with_assigned_floors()
    {
        $hostel = Hostel::create([
            'school_id' => $this->school->id,
            'hostel_name' => 'Test Hostel',
            'capability' => 50,
        ]);

        HostelFloor::create([
            'school_id' => $this->school->id,
            'hostel_id' => $hostel->id,
            'floor_name' => 'First Floor',
        ]);

        $response = $this->actingAsUser($this->receptionistUser)
            ->deleteJson('http://' . $this->school->subdomain . '.localhost/receptionist/hostels/' . $hostel->id);
            
        $response->assertStatus(422)
                 ->assertJsonValidationErrors('hostel');

        $this->assertDatabaseHas('hostels', ['id' => $hostel->id]);
    }

    public function test_cross_tenant_foreign_key_rejection_in_floor_creation()
    {
        // A hostel belonging to another school
        $otherHostel = Hostel::create([
            'school_id' => $this->otherSchool->id,
            'hostel_name' => 'Other Hostel',
            'capability' => 50,
        ]);

        $response = $this->actingAsUser($this->receptionistUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/receptionist/hostel-floors', [
                'hostel_id' => $otherHostel->id,
                'floor_name' => 'First Floor',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('hostel_id');
    }

    public function test_successful_bed_assignment_and_capacity_validation()
    {
        $hostel = Hostel::create([
            'school_id' => $this->school->id,
            'hostel_name' => 'Small Hostel',
            'capability' => 1, // Only 1 bed capacity
        ]);

        $floor = HostelFloor::create([
            'school_id' => $this->school->id,
            'hostel_id' => $hostel->id,
            'floor_name' => 'First Floor',
        ]);

        $room = HostelRoom::create([
            'school_id' => $this->school->id,
            'hostel_id' => $hostel->id,
            'hostel_floor_id' => $floor->id,
            'room_name' => 'Room 101',
        ]);

        // Assign first student (Should succeed)
        $response = $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/hostel/' . $this->student->id, [
                'action' => 'assign',
                'hostel_id' => $hostel->id,
                'hostel_floor_id' => $floor->id,
                'hostel_room_id' => $room->id,
                'start_date' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('hostel_bed_assignments', [
            'student_id' => $this->student->id,
            'hostel_id' => $hostel->id,
            'status' => GeneralStatus::Active->value,
        ]);

        // Create second student
        $student2 = Student::factory()->create([
            'school_id' => $this->school->id,
            'class_id' => $this->student->class_id,
        ]);

        // Attempt to assign second student (Should fail capacity check)
        $this->withoutExceptionHandling();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("has reached its maximum capacity");

        $this->actingAsUser($this->adminUser)
            ->post('http://' . $this->school->subdomain . '.localhost/school/facilities/hostel/' . $student2->id, [
                'action' => 'assign',
                'hostel_id' => $hostel->id,
                'hostel_floor_id' => $floor->id,
                'hostel_room_id' => $room->id,
                'start_date' => now()->format('Y-m-d'),
            ]);
    }
}
