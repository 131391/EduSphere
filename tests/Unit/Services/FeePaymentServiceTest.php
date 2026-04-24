<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\School;
use App\Models\Student;
use App\Models\Fee;
use App\Models\AcademicYear;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\School\FeePaymentService;
use App\Services\School\NumberingService;
use App\Enums\FeeStatus;
use App\Enums\StudentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeePaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private FeePaymentService $service;
    private School $school;
    private Student $student;
    private AcademicYear $academicYear;
    private PaymentMethod $paymentMethod;
    private Fee $fee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FeePaymentService(new NumberingService());

        $this->school = School::factory()->create();

        $this->academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->student = Student::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => StudentStatus::Active,
        ]);

        $this->paymentMethod = PaymentMethod::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Cash',
        ]);

        $this->fee = Fee::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount' => 10000,
            'paid_amount' => 0,
            'due_amount' => 10000,
            'payment_status' => FeeStatus::Pending,
        ]);

        app()->instance('currentSchool', $this->school);
    }

    public function test_collect_payment_creates_payment_record(): void
    {
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $this->actingAs($user);

        $data = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => $this->fee->id, 'amount' => 5000],
            ],
        ];

        $result = $this->service->collectPayment($this->school, $data);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('receipt_no', $result['data']);
        $this->assertEquals(5000, $result['data']['total_amount']);

        $this->assertDatabaseHas('fee_payments', [
            'fee_id' => $this->fee->id,
            'amount' => 5000,
        ]);

        $this->fee->refresh();
        $this->assertEquals(5000, $this->fee->paid_amount);
        $this->assertEquals(5000, $this->fee->due_amount);
        $this->assertEquals(FeeStatus::Partial, $this->fee->payment_status);
    }

    public function test_collect_payment_marks_fee_as_paid_when_full(): void
    {
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $this->actingAs($user);

        $data = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => $this->fee->id, 'amount' => 10000],
            ],
        ];

        $result = $this->service->collectPayment($this->school, $data);

        $this->assertTrue($result['success']);

        $this->fee->refresh();
        $this->assertEquals(10000, $this->fee->paid_amount);
        $this->assertEquals(0, $this->fee->due_amount);
        $this->assertEquals(FeeStatus::Paid, $this->fee->payment_status);
    }

    public function test_collect_payment_prevents_overpayment(): void
    {
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $this->actingAs($user);

        $data = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => $this->fee->id, 'amount' => 15000],
            ],
        ];

        $result = $this->service->collectPayment($this->school, $data);

        // Overpayment now returns failure (throws internally) instead of silently capping
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Overpayment rejected', $result['message']);

        $this->fee->refresh();
        $this->assertEquals(0, $this->fee->paid_amount);
    }

    public function test_collect_payment_handles_zero_amount(): void
    {
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $this->actingAs($user);

        $data = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => $this->fee->id, 'amount' => 0],
            ],
        ];

        $result = $this->service->collectPayment($this->school, $data);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['data']['total_amount']);

        $this->fee->refresh();
        $this->assertEquals(0, $this->fee->paid_amount);
    }

    public function test_collect_payment_throws_exception_for_invalid_fee(): void
    {
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $this->actingAs($user);

        $data = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => 99999, 'amount' => 1000],
            ],
        ];

        $result = $this->service->collectPayment($this->school, $data);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Fee record not found', $result['message']);
    }

    public function test_generate_receipt_number_is_unique(): void
    {
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $this->actingAs($user);

        $data1 = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => $this->fee->id, 'amount' => 1000],
            ],
        ];

        $result1 = $this->service->collectPayment($this->school, $data1);
        $receipt1 = $result1['data']['receipt_no'];

        $fee2 = Fee::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount' => 2000,
        ]);

        $data2 = [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments' => [
                ['fee_id' => $fee2->id, 'amount' => 2000],
            ],
        ];

        $result2 = $this->service->collectPayment($this->school, $data2);
        $receipt2 = $result2['data']['receipt_no'];

        $this->assertNotEquals($receipt1, $receipt2);
    }

    public function test_get_student_pending_fees(): void
    {
        Fee::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'payment_status' => FeeStatus::Pending,
        ]);

        Fee::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'payment_status' => FeeStatus::Paid,
        ]);

        Fee::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'payment_status' => FeeStatus::Partial,
        ]);

        $pendingFees = $this->service->getStudentPendingFees($this->student);

        $this->assertCount(2, $pendingFees);
    }
}
