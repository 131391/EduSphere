<?php

namespace Tests\Feature;

use App\Enums\ExamStatus;
use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Enums\YesNo;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\ExamType;
use App\Models\Role;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_school_admin_can_schedule_exam_and_snapshot_class_subjects(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-schedule-school']);
        $admin = $this->createSchoolAdmin($school);
        $academicYear = $this->createAcademicYear($school);
        $class = $this->createClass($school, 'Class 10');
        $examType = ExamType::create([
            'school_id' => $school->id,
            'name' => 'Mid Term',
        ]);

        $subjectOne = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'is_active' => true,
        ]);
        $subjectTwo = Subject::create([
            'school_id' => $school->id,
            'name' => 'Science',
            'code' => 'SCI',
            'is_active' => true,
        ]);

        $class->subjects()->attach($subjectOne->id, ['full_marks' => 100]);
        $class->subjects()->attach($subjectTwo->id, ['full_marks' => 80]);

        $response = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/exams'),
            [
                'class_id' => $class->id,
                'exam_type_id' => $examType->id,
                'start_date' => '2026-08-10',
                'end_date' => '2026-08-14',
            ]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Mid Term (August 2026)');

        $exam = Exam::where('school_id', $school->id)->firstOrFail();

        $this->assertSame($academicYear->id, $exam->academic_year_id);
        $this->assertSame('August 2026', $exam->month);
        $this->assertCount(2, $exam->examSubjects);
        $this->assertDatabaseHas('exam_subjects', [
            'exam_id' => $exam->id,
            'subject_id' => $subjectOne->id,
            'subject_name' => 'Mathematics',
            'full_marks' => 100,
        ]);
        $this->assertDatabaseHas('exam_subjects', [
            'exam_id' => $exam->id,
            'subject_id' => $subjectTwo->id,
            'subject_name' => 'Science',
            'full_marks' => 80,
        ]);
    }

    public function test_school_admin_cannot_schedule_exam_using_other_school_resources(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-owner-school']);
        $otherSchool = $this->createSchool(['subdomain' => 'exam-other-school']);
        $admin = $this->createSchoolAdmin($school);

        $this->createAcademicYear($school);
        $foreignClass = $this->createClass($otherSchool, 'Class X');
        $foreignExamType = ExamType::create([
            'school_id' => $otherSchool->id,
            'name' => 'Final',
        ]);

        $response = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/exams'),
            [
                'class_id' => $foreignClass->id,
                'exam_type_id' => $foreignExamType->id,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-05',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['class_id', 'exam_type_id']);

        $this->assertDatabaseCount('exams', 0);
    }

    public function test_mark_entry_uses_exam_snapshot_and_only_publishes_completed_exams_to_students(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-results-school']);
        $admin = $this->createSchoolAdmin($school);
        $studentRole = Role::where('slug', Role::STUDENT)->firstOrFail();
        $academicYear = $this->createAcademicYear($school);
        $class = $this->createClass($school, 'Class 8');
        $section = $this->createSection($school, $class, 'A');
        $examType = ExamType::create([
            'school_id' => $school->id,
            'name' => 'Unit Test',
        ]);

        $math = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'is_active' => true,
        ]);
        $science = Subject::create([
            'school_id' => $school->id,
            'name' => 'Science',
            'code' => 'SCI',
            'is_active' => true,
        ]);

        $class->subjects()->attach($math->id, ['full_marks' => 100]);
        $class->subjects()->attach($science->id, ['full_marks' => 75]);

        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'name' => 'Unit Test (July 2026)',
            'month' => 'July 2026',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-12',
            'status' => ExamStatus::Scheduled,
        ]);
        $exam->ensureSubjectSnapshot();

        $studentUser = $this->createUser([
            'school_id' => $school->id,
            'role_id' => $studentRole->id,
            'name' => 'Aarav Student',
            'email' => 'aarav@example.test',
        ]);

        $student = Student::create([
            'school_id' => $school->id,
            'user_id' => $studentUser->id,
            'academic_year_id' => $academicYear->id,
            'admission_no' => 'STU-1001',
            'first_name' => 'Aarav',
            'last_name' => 'Sharma',
            'dob' => '2012-05-01',
            'gender' => Gender::Male,
            'father_name' => 'Raj Sharma',
            'mother_name' => 'Neha Sharma',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'status' => StudentStatus::Active,
            'admission_date' => '2026-04-01',
            'mobile_no' => '9999999999',
            'is_single_parent' => YesNo::No,
            'is_transport_required' => YesNo::No,
        ]);

        $examSubjects = $exam->examSubjects()->orderBy('full_marks', 'desc')->get();
        $mathSnapshot = $examSubjects->firstWhere('subject_id', $math->id);
        $scienceSnapshot = $examSubjects->firstWhere('subject_id', $science->id);

        // Remove the live class-subject assignments to prove mark entry now relies on the exam snapshot.
        $class->subjects()->detach();

        $firstMarkResponse = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/marksGroup'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $mathSnapshot->id,
                'marks' => [
                    [
                        'student_id' => $student->id,
                        'marks_obtained' => 91,
                        'remarks' => 'Excellent',
                    ],
                ],
            ]
        );

        $firstMarkResponse->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('results', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $math->id,
            'total_marks' => 100,
        ]);

        $exam->refresh();
        $this->assertSame(ExamStatus::Ongoing, $exam->status);

        $studentResultsBeforeCompletion = $this->actingAs($studentUser)
            ->get($this->tenantUrl($school, '/student/results'));

        $studentResultsBeforeCompletion->assertOk()
            ->assertDontSee('Unit Test (July 2026)');

        $secondMarkResponse = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/marksGroup'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $scienceSnapshot->id,
                'marks' => [
                    [
                        'student_id' => $student->id,
                        'marks_obtained' => 60,
                        'remarks' => 'Strong work',
                    ],
                ],
            ]
        );

        $secondMarkResponse->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('results', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $science->id,
            'total_marks' => 75,
        ]);

        $exam->refresh();
        $this->assertSame(ExamStatus::Completed, $exam->status);

        $studentResultsAfterCompletion = $this->actingAs($studentUser)
            ->get($this->tenantUrl($school, '/student/results'));

        $studentResultsAfterCompletion->assertOk()
            ->assertSee('Unit Test (July 2026)')
            ->assertSee('Mathematics')
            ->assertSee('Science');
    }

    public function test_exam_with_recorded_results_cannot_be_deleted(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-delete-school']);
        $admin = $this->createSchoolAdmin($school);
        $academicYear = $this->createAcademicYear($school);
        $class = $this->createClass($school, 'Class 7');
        $section = $this->createSection($school, $class, 'A');
        $examType = ExamType::create([
            'school_id' => $school->id,
            'name' => 'Half Yearly',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'English',
            'code' => 'ENG',
            'is_active' => true,
        ]);

        $class->subjects()->attach($subject->id, ['full_marks' => 100]);

        $studentUser = $this->createUser([
            'school_id' => $school->id,
            'role_id' => Role::where('slug', Role::STUDENT)->firstOrFail()->id,
            'email' => 'delete-student@example.test',
        ]);

        $student = Student::create([
            'school_id' => $school->id,
            'user_id' => $studentUser->id,
            'academic_year_id' => $academicYear->id,
            'admission_no' => 'STU-2001',
            'first_name' => 'Ira',
            'last_name' => 'Mehta',
            'dob' => '2013-03-10',
            'gender' => Gender::Female,
            'father_name' => 'Amit Mehta',
            'mother_name' => 'Riya Mehta',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'status' => StudentStatus::Active,
            'admission_date' => '2026-04-01',
            'mobile_no' => '8888888888',
            'is_single_parent' => YesNo::No,
            'is_transport_required' => YesNo::No,
        ]);

        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'name' => 'Half Yearly (October 2026)',
            'month' => 'October 2026',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-03',
            'status' => ExamStatus::Scheduled,
        ]);
        $exam->ensureSubjectSnapshot();

        /** @var ExamSubject $examSubject */
        $examSubject = $exam->examSubjects()->firstOrFail();

        $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/marksGroup'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
                'marks' => [
                    [
                        'student_id' => $student->id,
                        'marks_obtained' => 72,
                    ],
                ],
            ]
        )->assertOk();

        $deleteResponse = $this->actingAs($admin)->deleteJson(
            $this->tenantUrl($school, "/school/examination/exams/{$exam->id}")
        );

        $deleteResponse->assertStatus(422)
            ->assertJsonPath('message', 'This exam already has recorded marks and cannot be removed.');

        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
    }

    protected function createSchoolAdmin(School $school): User
    {
        return $this->createUser([
            'school_id' => $school->id,
            'role_id' => Role::where('slug', Role::SCHOOL_ADMIN)->firstOrFail()->id,
            'email' => uniqid('school-admin-', true) . '@example.test',
        ]);
    }

    protected function createAcademicYear(School $school): AcademicYear
    {
        return AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_current' => true,
        ]);
    }

    protected function createClass(School $school, string $name): ClassModel
    {
        return ClassModel::create([
            'school_id' => $school->id,
            'name' => $name,
            'order' => 1,
            'is_available' => true,
        ]);
    }

    protected function createSection(School $school, ClassModel $class, string $name): Section
    {
        return Section::create([
            'school_id' => $school->id,
            'class_id' => $class->id,
            'name' => $name,
            'capacity' => 40,
            'current_strength' => 1,
        ]);
    }

    protected function tenantUrl(School $school, string $path): string
    {
        return 'http://' . $school->subdomain . '.localhost' . $path;
    }
}
