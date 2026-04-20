<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\StudentParent;
use App\Models\StudentRegistration;
use App\Services\School\AdmissionService;
use App\Enums\StudentStatus;
use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Enums\AdmissionStatus;
use App\Http\Requests\School\StoreAdmissionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;

class AdmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionService $service;
    private School $school;
    private AcademicYear $academicYear;
    private ClassModel $classModel;
    private Section $section;
    private Role $studentRole;
    private Role $parentRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AdmissionService();

        $this->school = School::factory()->create();

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->classModel = ClassModel::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->section = Section::factory()->create([
            'school_id' => $this->school->id,
            'class_id' => $this->classModel->id,
        ]);

        $this->studentRole = Role::factory()->create([
            'slug' => 'student',
        ]);

        $this->parentRole = Role::factory()->create([
            'slug' => 'parent',
        ]);

        app()->instance('currentSchool', $this->school);
    }

    private function createMockRequest(array $data = []): StoreAdmissionRequest
    {
        $default = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'dob' => '2015-01-15',
            'gender' => 1,
            'class_id' => $this->classModel->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'father_name' => 'Robert Doe',
            'father_mobile_no' => '9876543210',
            'mother_name' => 'Mary Doe',
            'mother_mobile_no' => '9876543211',
            'mobile_no' => '9876543200',
            'admission_no' => null,
        ];

        $request = new StoreAdmissionRequest(array_merge($default, $data));
        
        return $request;
    }

    public function test_admit_creates_student_with_correct_data(): void
    {
        $request = $this->createMockRequest([
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);

        $student = $this->service->admit($request, $this->school);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('Test', $student->first_name);
        $this->assertEquals('Student', $student->last_name);
        $this->assertEquals($this->school->id, $student->school_id);
        $this->assertEquals(StudentStatus::Active, $student->status);
    }

    public function test_admit_creates_student_user_account(): void
    {
        $request = $this->createMockRequest();

        $student = $this->service->admit($request, $this->school);

        $this->assertNotNull($student->user_id);
        
        $user = User::find($student->user_id);
        $this->assertNotNull($user);
        $this->assertEquals('student', $user->role?->slug);
        $this->assertTrue($user->must_change_password);
    }

    public function test_admit_generates_unique_admission_number(): void
    {
        $request1 = $this->createMockRequest();
        $student1 = $this->service->admit($request1, $this->school);
        
        $request2 = $this->createMockRequest(['first_name' => 'Second']);
        $student2 = $this->service->admit($request2, $this->school);

        $this->assertNotEquals($student1->admission_no, $student2->admission_no);
    }

    public function test_admit_throws_exception_on_duplicate_aadhaar(): void
    {
        $request1 = $this->createMockRequest(['aadhaar_no' => '123456789012']);
        $this->service->admit($request1, $this->school);

        $request2 = $this->createMockRequest(['aadhaar_no' => '123456789012']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Aadhaar number');
        
        $this->service->admit($request2, $this->school);
    }

    public function test_admit_throws_exception_on_duplicate_mobile(): void
    {
        $request1 = $this->createMockRequest(['mobile_no' => '9999999999']);
        $this->service->admit($request1, $this->school);

        $request2 = $this->createMockRequest(['mobile_no' => '9999999999']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('mobile number');
        
        $this->service->admit($request2, $this->school);
    }

    public function test_admit_throws_exception_on_duplicate_admission_number(): void
    {
        $admissionNo = 100001;
        
        $request1 = $this->createMockRequest(['admission_no' => $admissionNo]);
        $this->service->admit($request1, $this->school);

        $request2 = $this->createMockRequest(['admission_no' => $admissionNo]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Admission number');
        
        $this->service->admit($request2, $this->school);
    }

    public function test_admit_creates_parent_accounts(): void
    {
        $request = $this->createMockRequest([
            'father_first_name' => 'Father',
            'father_last_name' => 'Name',
            'father_mobile_no' => '8888888888',
            'father_email' => 'father@test.com',
            'mother_first_name' => 'Mother',
            'mother_last_name' => 'Name',
            'mother_mobile_no' => '7777777777',
        ]);

        $student = $this->service->admit($request, $this->school);

        $parents = $student->parents;
        
        $this->assertCount(2, $parents);
    }

    public function test_admit_reuses_existing_parent_with_same_email(): void
    {
        $fatherEmail = 'father@reuse.com';
        
        User::factory()->create([
            'school_id' => $this->school->id,
            'email' => $fatherEmail,
            'role_id' => $this->parentRole->id,
        ]);

        $request1 = $this->createMockRequest([
            'father_email' => $fatherEmail,
        ]);
        
        $student1 = $this->service->admit($request1, $this->school);

        $request2 = $this->createMockRequest([
            'father_email' => $fatherEmail,
        ]);
        
        $student2 = $this->service->admit($request2, $this->school);

        $parentCount = User::where('email', $fatherEmail)->count();
        
        $this->assertEquals(1, $parentCount);
    }

    public function test_admit_links_to_registration_when_provided(): void
    {
        $registration = StudentRegistration::factory()->create([
            'school_id' => $this->school->id,
            'registration_no' => 'REG001',
            'admission_status' => AdmissionStatus::Registered,
        ]);

        $request = $this->createMockRequest([
            'registration_no' => 'REG001',
        ]);

        $student = $this->service->admit($request, $this->school);

        $registration->refresh();
        $this->assertEquals(AdmissionStatus::Admitted, $registration->admission_status);
    }

    public function test_admit_skips_admission_fee_when_not_provided(): void
    {
        $request = $this->createMockRequest([
            'admission_fee' => null,
        ]);

        $student = $this->service->admit($request, $this->school);

        $fees = $student->fees;
        
        $this->assertCount(0, $fees);
    }

    public function test_admit_handles_missing_father_name(): void
    {
        $request = $this->createMockRequest([
            'father_first_name' => null,
            'father_last_name' => null,
            'father_name' => 'Named Father',
        ]);

        $student = $this->service->admit($request, $this->school);

        $this->assertNotNull($student);
    }

    public function test_admit_handles_missing_mother_name(): void
    {
        $request = $this->createMockRequest([
            'mother_first_name' => null,
            'mother_last_name' => null,
            'mother_name' => 'Named Mother',
        ]);

        $student = $this->service->admit($request, $this->school);

        $this->assertNotNull($student);
    }
}
