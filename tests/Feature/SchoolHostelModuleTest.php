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

class SchoolHostelModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $adminUser;
    protected $student;

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

        $class = ClassModel::factory()->create(['school_id' => $this->school->id]);
        $this->student = Student::factory()->create([
            'school_id' => $this->school->id,
            'class_id' => $class->id,
            'status' => GeneralStatus::Active->value,
        ]);
    }

    public function test_school_admin_can_manage_hostels()
    {
        $response = $this->actingAsUser($this->adminUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/school/hostel/hostels', [
                'hostel_name' => 'Admin Hostel',
                'capability' => 100,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('hostels', ['hostel_name' => 'Admin Hostel']);
    }

    public function test_school_admin_can_manage_floors()
    {
        $hostel = Hostel::create([
            'school_id' => $this->school->id,
            'hostel_name' => 'Hostel A',
        ]);

        $response = $this->actingAsUser($this->adminUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/school/hostel/floors', [
                'hostel_id' => $hostel->id,
                'floor_name' => 'Floor 1',
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('hostel_floors', ['floor_name' => 'Floor 1']);
    }

    public function test_school_admin_can_manage_rooms()
    {
        $hostel = Hostel::create(['school_id' => $this->school->id, 'hostel_name' => 'Hostel A']);
        $floor = HostelFloor::create(['school_id' => $this->school->id, 'hostel_id' => $hostel->id, 'floor_name' => 'Floor 1']);

        $response = $this->actingAsUser($this->adminUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/school/hostel/rooms', [
                'hostel_id' => $hostel->id,
                'hostel_floor_id' => $floor->id,
                'room_name' => 'Room 101',
                'no_of_beds' => 2,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('hostel_rooms', ['room_name' => 'Room 101']);
    }

    public function test_school_admin_can_assign_student_and_mark_attendance()
    {
        $hostel = Hostel::create(['school_id' => $this->school->id, 'hostel_name' => 'Hostel A', 'capability' => 10]);
        $floor = HostelFloor::create(['school_id' => $this->school->id, 'hostel_id' => $hostel->id, 'floor_name' => 'Floor 1']);
        $room = HostelRoom::create(['school_id' => $this->school->id, 'hostel_id' => $hostel->id, 'hostel_floor_id' => $floor->id, 'room_name' => 'Room 101', 'no_of_beds' => 2]);
        $academicYear = \App\Models\AcademicYear::factory()->create(['school_id' => $this->school->id, 'is_current' => true]);

        // Assignment
        $response = $this->actingAsUser($this->adminUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/school/hostel/assignments', [
                'student_id' => $this->student->id,
                'hostel_id' => $hostel->id,
                'hostel_floor_id' => $floor->id,
                'hostel_room_id' => $room->id,
                'start_date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('hostel_bed_assignments', ['student_id' => $this->student->id]);

        // Attendance
        $response = $this->actingAsUser($this->adminUser)
            ->postJson('http://' . $this->school->subdomain . '.localhost/school/hostel/attendance', [
                'hostel_id' => $hostel->id,
                'hostel_floor_id' => $floor->id,
                'hostel_room_id' => $room->id,
                'attendance_date' => now()->format('Y-m-d'),
                'academic_year_id' => $academicYear->id,
                'attendance_data' => [
                    [
                        'student_id' => $this->student->id,
                        'is_present' => true,
                        'remarks' => 'Good'
                    ]
                ]
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('hostel_attendances', ['student_id' => $this->student->id, 'is_present' => true]);
    }
}
