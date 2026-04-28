<?php

namespace Tests\Feature;

use App\Enums\ExamStatus;
use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Enums\TeacherStatus;
use App\Enums\YesNo;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\Result;
use App\Models\Role;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\School\Examination\ResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
            $this->tenantUrl($school, '/school/examination/marks'),
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
            $this->tenantUrl($school, '/school/examination/marks'),
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
            $this->tenantUrl($school, '/school/examination/marks'),
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

    public function test_school_admin_can_update_exam_dates_and_name(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-update-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam] = $this->seedScheduledExam($school);

        $response = $this->actingAs($admin)->putJson(
            $this->tenantUrl($school, "/school/examination/exams/{$exam->id}"),
            [
                'name' => 'Renamed Exam',
                'start_date' => '2026-11-02',
                'end_date' => '2026-11-08',
            ]
        );

        $response->assertOk()->assertJsonPath('success', true);

        $exam->refresh();
        $this->assertSame('Renamed Exam', $exam->name);
        $this->assertSame('2026-11-02', $exam->start_date->toDateString());
        $this->assertSame('2026-11-08', $exam->end_date->toDateString());
    }

    public function test_school_admin_can_cancel_exam_and_mark_entry_is_blocked(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-cancel-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam, $student, $examSubject] = $this->seedScheduledExam($school, withStudent: true);

        $cancel = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, "/school/examination/exams/{$exam->id}/cancel")
        );

        $cancel->assertOk();
        $exam->refresh();
        $this->assertSame(ExamStatus::Cancelled, $exam->status);

        $markEntry = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/marks'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
                'marks' => [
                    ['student_id' => $student->id, 'marks_obtained' => 70],
                ],
            ]
        );

        // Service responds with a validation error wrapped in a 422.
        $markEntry->assertStatus(422);
        $this->assertDatabaseMissing('results', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_locking_exam_freezes_results_with_lock_timestamp(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-lock-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam, $student, $examSubject] = $this->seedScheduledExam($school, withStudent: true);

        // Enter marks first.
        $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/marks'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
                'marks' => [
                    ['student_id' => $student->id, 'marks_obtained' => 88],
                ],
            ]
        )->assertOk();

        $lock = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, "/school/examination/exams/{$exam->id}/lock")
        );

        $lock->assertOk();
        $exam->refresh();
        $this->assertSame(ExamStatus::Locked, $exam->status);

        $result = Result::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();
        $this->assertNotNull($result->locked_at);
    }

    public function test_absent_marking_persists_with_zero_marks_and_skips_grade(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-absent-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam, $student, $examSubject] = $this->seedScheduledExam($school, withStudent: true);

        // Configure a basic grade band so we can verify absent rows do NOT receive a grade.
        Grade::create([
            'school_id' => $school->id,
            'range_start' => 0,
            'range_end' => 100,
            'grade' => 'A',
        ]);

        $response = $this->actingAs($admin)->postJson(
            $this->tenantUrl($school, '/school/examination/marks'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
                'marks' => [
                    ['student_id' => $student->id, 'is_absent' => true],
                ],
            ]
        );

        $response->assertOk();
        $result = Result::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->assertTrue((bool) $result->is_absent);
        $this->assertEquals(0, (float) $result->marks_obtained);
        $this->assertNull($result->grade);
    }

    public function test_grade_fallback_clamps_percentage_outside_configured_bands(): void
    {
        $school = $this->createSchool(['subdomain' => 'grade-fallback-school']);
        Grade::create([
            'school_id' => $school->id,
            'range_start' => 40,
            'range_end' => 60,
            'grade' => 'C',
        ]);
        Grade::create([
            'school_id' => $school->id,
            'range_start' => 61,
            'range_end' => 100,
            'grade' => 'A',
        ]);

        $service = app(ResultService::class);

        // Below the lowest band -> floor band.
        $this->assertSame('C', $service->calculateGrade($school, 25.0));
        // Above the highest band (composite > 100) -> ceiling band.
        $this->assertSame('A', $service->calculateGrade($school, 120.0));
        // In the gap between 60 and 61 -> still falls back to ceiling.
        $this->assertSame('A', $service->calculateGrade($school, 60.5));
    }

    public function test_grade_calculation_returns_null_when_no_bands_configured(): void
    {
        $school = $this->createSchool(['subdomain' => 'grade-none-school']);
        $this->assertNull(app(ResultService::class)->calculateGrade($school, 75.0));
    }

    public function test_scheduled_command_transitions_exam_from_scheduled_to_ongoing_after_start_date(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-cron-school']);
        $academicYear = $this->createAcademicYear($school);
        $class = $this->createClass($school, 'Class 5');
        $examType = ExamType::create(['school_id' => $school->id, 'name' => 'Cron Term']);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Math',
            'code' => 'MAT',
            'is_active' => true,
        ]);
        $class->subjects()->attach($subject->id, ['full_marks' => 100]);

        // Backdate start to yesterday — status sync should flip Scheduled → Ongoing.
        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'name' => 'Cron Test',
            'month' => Carbon::yesterday()->format('F Y'),
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'status' => ExamStatus::Scheduled,
        ]);

        $this->artisan('exams:sync-statuses')->assertSuccessful();

        $exam->refresh();
        $this->assertSame(ExamStatus::Ongoing, $exam->status);
    }

    public function test_scheduled_command_skips_cancelled_and_locked_exams(): void
    {
        $school = $this->createSchool(['subdomain' => 'exam-cron-skip-school']);
        $academicYear = $this->createAcademicYear($school);
        $class = $this->createClass($school, 'Class 6');
        $examType = ExamType::create(['school_id' => $school->id, 'name' => 'X']);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'M', 'code' => 'M', 'is_active' => true]);
        $class->subjects()->attach($subject->id, ['full_marks' => 100]);

        $cancelled = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'name' => 'C',
            'month' => 'X',
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'status' => ExamStatus::Cancelled,
        ]);

        $locked = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'name' => 'L',
            'month' => 'X',
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'status' => ExamStatus::Locked,
        ]);

        $this->artisan('exams:sync-statuses')->assertSuccessful();

        $this->assertSame(ExamStatus::Cancelled, $cancelled->fresh()->status);
        $this->assertSame(ExamStatus::Locked, $locked->fresh()->status);
    }

    public function test_grade_band_coverage_report_flags_gaps_and_returns_complete_when_full(): void
    {
        $school = $this->createSchool(['subdomain' => 'grade-cov-school']);
        $admin = $this->createSchoolAdmin($school);

        // Bind the current school so the GradeController helper can read it
        // without needing the tenant middleware to run.
        app()->instance('currentSchool', $school);
        $this->actingAs($admin);

        Grade::create(['school_id' => $school->id, 'range_start' => 0, 'range_end' => 49, 'grade' => 'F']);
        Grade::create(['school_id' => $school->id, 'range_start' => 70, 'range_end' => 100, 'grade' => 'A']);

        $controller = app(\App\Http\Controllers\School\Examination\GradeController::class);
        $report = $controller->coverageReport();

        $this->assertFalse($report['is_complete']);
        $this->assertCount(1, $report['gaps']);
        $this->assertSame(['from' => 50, 'to' => 69], $report['gaps'][0]);

        Grade::create(['school_id' => $school->id, 'range_start' => 50, 'range_end' => 69, 'grade' => 'C']);
        $report = $controller->coverageReport();
        $this->assertTrue($report['is_complete']);
        $this->assertSame([], $report['gaps']);
    }

    public function test_admin_can_assign_teacher_to_exam_subject(): void
    {
        $school = $this->createSchool(['subdomain' => 'assign-teacher-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam, $unused, $examSubject, $teacher] = $this->seedScheduledExam($school, withStudent: true, withTeacher: true);
        unset($unused);

        $response = $this->actingAs($admin)->patchJson(
            $this->tenantUrl($school, "/school/examination/exams/{$exam->id}/subjects/{$examSubject->id}/teacher"),
            ['teacher_id' => $teacher->id]
        );

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame((int) $teacher->id, (int) $examSubject->fresh()->teacher_id);

        // Clearing
        $clear = $this->actingAs($admin)->patchJson(
            $this->tenantUrl($school, "/school/examination/exams/{$exam->id}/subjects/{$examSubject->id}/teacher"),
            ['teacher_id' => null]
        );
        $clear->assertOk();
        $this->assertNull($examSubject->fresh()->teacher_id);
    }

    public function test_teacher_can_only_see_and_save_marks_for_assigned_exam_subjects(): void
    {
        $school = $this->createSchool(['subdomain' => 'teacher-marks-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam, $student, $examSubject, $teacher] = $this->seedScheduledExam($school, withStudent: true, withTeacher: true);

        // Assign teacher to the subject.
        $examSubject->forceFill(['teacher_id' => $teacher->id])->save();

        $teacherUser = $teacher->user;

        // Index lists the assignment.
        $listing = $this->actingAs($teacherUser)
            ->getJson($this->tenantUrl($school, '/teacher/marks'));
        $listing->assertOk()
            ->assertJsonPath('data.0.exam_subject_id', $examSubject->id);

        // Teacher can save marks for assigned subject.
        $save = $this->actingAs($teacherUser)->postJson(
            $this->tenantUrl($school, '/teacher/marks'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
                'marks' => [
                    ['student_id' => $student->id, 'marks_obtained' => 77],
                ],
            ]
        );
        $save->assertOk();
        $this->assertDatabaseHas('results', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'marks_obtained' => 77,
            'entered_by' => $teacherUser->id,
        ]);
    }

    public function test_teacher_cannot_save_marks_for_unassigned_exam_subject(): void
    {
        $school = $this->createSchool(['subdomain' => 'teacher-unassigned-school']);
        [$exam, $student, $examSubject, $teacher] = $this->seedScheduledExam($school, withStudent: true, withTeacher: true);
        // Note: teacher_id intentionally NOT set on examSubject.

        $teacherUser = $teacher->user;

        $save = $this->actingAs($teacherUser)->postJson(
            $this->tenantUrl($school, '/teacher/marks'),
            [
                'exam_id' => $exam->id,
                'exam_subject_id' => $examSubject->id,
                'marks' => [
                    ['student_id' => $student->id, 'marks_obtained' => 77],
                ],
            ]
        );

        // The teacher route's findOrFail filters by teacher_id, so unassigned -> 404.
        $save->assertStatus(404);
        $this->assertDatabaseMissing('results', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_results_are_scoped_to_school_for_listing(): void
    {
        $schoolA = $this->createSchool(['subdomain' => 'results-iso-a']);
        $schoolB = $this->createSchool(['subdomain' => 'results-iso-b']);

        [$examA, $studentA, $examSubjectA] = $this->seedScheduledExam($schoolA, withStudent: true);
        [$examB, $studentB] = $this->seedScheduledExam($schoolB, withStudent: true);

        Result::create([
            'school_id' => $schoolA->id,
            'student_id' => $studentA->id,
            'exam_id' => $examA->id,
            'subject_id' => $examSubjectA->subject_id,
            'class_id' => $examA->class_id,
            'academic_year_id' => $examA->academic_year_id,
            'marks_obtained' => 80,
            'total_marks' => 100,
            'percentage' => 80,
        ]);
        Result::create([
            'school_id' => $schoolB->id,
            'student_id' => $studentB->id,
            'exam_id' => $examB->id,
            'subject_id' => $examB->examSubjects()->firstOrFail()->subject_id,
            'class_id' => $examB->class_id,
            'academic_year_id' => $examB->academic_year_id,
            'marks_obtained' => 60,
            'total_marks' => 100,
            'percentage' => 60,
        ]);

        // School A's student listing must not surface School B's results.
        $studentUserA = $studentA->user;
        $response = $this->actingAs($studentUserA)
            ->get($this->tenantUrl($schoolA, '/student/results'));
        $response->assertOk();

        // Bypass the Tenantable global scope here — we want raw counts, not the
        // request-bound scope. The next block exercises the scope itself.
        $rowsForA = Result::withoutGlobalScopes()->where('school_id', $schoolA->id)->count();
        $rowsForB = Result::withoutGlobalScopes()->where('school_id', $schoolB->id)->count();
        $this->assertSame(1, $rowsForA);
        $this->assertSame(1, $rowsForB);

        // Bare belt-and-braces: the global Tenantable scope on Result must only
        // return school A's row when the school A context is bound.
        app()->instance('currentSchool', $schoolA);
        $this->assertSame(1, Result::query()->count());
        app()->instance('currentSchool', $schoolB);
        $this->assertSame(1, Result::query()->count());
    }

    public function test_concurrent_mark_save_is_idempotent_via_upsert(): void
    {
        $school = $this->createSchool(['subdomain' => 'mark-race-school']);
        $admin = $this->createSchoolAdmin($school);
        [$exam, $student, $examSubject] = $this->seedScheduledExam($school, withStudent: true);

        // Save twice in quick succession; second save should overwrite, not duplicate.
        for ($i = 0; $i < 2; $i++) {
            $this->actingAs($admin)->postJson(
                $this->tenantUrl($school, '/school/examination/marks'),
                [
                    'exam_id' => $exam->id,
                    'exam_subject_id' => $examSubject->id,
                    'marks' => [['student_id' => $student->id, 'marks_obtained' => 80 + $i]],
                ]
            )->assertOk();
        }

        $rows = Result::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('subject_id', $examSubject->subject_id)
            ->get();
        $this->assertCount(1, $rows);
        $this->assertEquals(81, (float) $rows->first()->marks_obtained);
    }

    /**
     * @return array{0: Exam, 1?: Student, 2?: ExamSubject, 3?: Teacher}
     */
    protected function seedScheduledExam(School $school, bool $withStudent = false, bool $withTeacher = false): array
    {
        $academicYear = $this->createAcademicYear($school);
        $class = $this->createClass($school, 'Class 9');
        $section = $this->createSection($school, $class, 'A');
        $examType = ExamType::create([
            'school_id' => $school->id,
            'name' => 'Term',
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'English',
            'code' => 'ENG',
            'is_active' => true,
        ]);

        $class->subjects()->attach($subject->id, ['full_marks' => 100]);

        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'name' => 'Term Exam',
            'month' => 'November 2026',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'status' => ExamStatus::Scheduled,
        ]);
        $exam->ensureSubjectSnapshot();

        if (!$withStudent) {
            return [$exam];
        }

        $studentUser = $this->createUser([
            'school_id' => $school->id,
            'role_id' => Role::where('slug', Role::STUDENT)->firstOrFail()->id,
            'email' => uniqid('student-', true) . '@example.test',
        ]);

        $student = Student::create([
            'school_id' => $school->id,
            'user_id' => $studentUser->id,
            'academic_year_id' => $academicYear->id,
            'admission_no' => 'STU-' . uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'dob' => '2012-01-01',
            'gender' => Gender::Male,
            'father_name' => 'F',
            'mother_name' => 'M',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'status' => StudentStatus::Active,
            'admission_date' => '2026-04-01',
            'mobile_no' => '9000000000',
            'is_single_parent' => YesNo::No,
            'is_transport_required' => YesNo::No,
        ]);

        $examSubject = $exam->examSubjects()->firstOrFail();

        if (!$withTeacher) {
            return [$exam, $student, $examSubject];
        }

        $teacherUser = $this->createUser([
            'school_id' => $school->id,
            'role_id' => Role::where('slug', Role::TEACHER)->firstOrFail()->id,
            'email' => uniqid('teacher-', true) . '@example.test',
        ]);

        // Note: teachers.gender is an enum('male','female','other') string column,
        // not the Gender int enum used by students. Insert a string directly.
        $teacher = Teacher::create([
            'school_id' => $school->id,
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH-' . uniqid(),
            'first_name' => 'Pat',
            'last_name' => 'Teacher',
            'date_of_birth' => '1985-01-01',
            'gender' => 'female',
            'phone' => '9000000001',
            'email' => $teacherUser->email,
            'address' => 'N/A',
            'qualification' => 'BEd',
            'experience_years' => 5,
            'photo' => null,
            'status' => TeacherStatus::Active,
            'joining_date' => '2024-04-01',
        ]);

        return [$exam, $student, $examSubject, $teacher];
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
