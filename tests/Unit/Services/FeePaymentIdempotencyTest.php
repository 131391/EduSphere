<?php

namespace Tests\Unit\Services;

use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\School\FeePaymentService;
use App\Services\School\NumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeePaymentIdempotencyTest extends TestCase
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
        $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $this->student = Student::factory()->create([
            'school_id'        => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'status'           => StudentStatus::Active,
        ]);
        $this->paymentMethod = PaymentMethod::factory()->create(['school_id' => $this->school->id]);
        $this->fee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount'   => 10000,
            'paid_amount'      => 0,
            'due_amount'       => 10000,
        ]);

        app()->instance('currentSchool', $this->school);
        $this->actingAs(User::factory()->create(['school_id' => $this->school->id]));
    }

    public function test_idempotency_key_replay_does_not_double_charge(): void
    {
        $idempotencyKey = 'pay_test_' . uniqid();
        $payload = [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'idempotency_key'   => $idempotencyKey,
            'payments'          => [['fee_id' => $this->fee->id, 'amount' => 4000]],
        ];

        $first = $this->service->collectPayment($this->school, $payload);
        $second = $this->service->collectPayment($this->school, $payload);

        $this->assertTrue($first['success']);
        $this->assertTrue($second['success']);

        // Both calls return the same receipt; only one underlying payment row.
        $this->assertEquals($first['receipt_no'], $second['receipt_no']);
        $this->assertEquals(1, FeePayment::where('fee_id', $this->fee->id)->count());

        $this->fee->refresh();
        $this->assertEquals('4000.00', (string) $this->fee->paid_amount);
        $this->assertEquals('6000.00', (string) $this->fee->due_amount);
    }

    public function test_idempotency_replay_returns_total_amount(): void
    {
        $idempotencyKey = 'pay_test_' . uniqid();
        $payload = [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'idempotency_key'   => $idempotencyKey,
            'payments'          => [['fee_id' => $this->fee->id, 'amount' => 7500]],
        ];

        $first = $this->service->collectPayment($this->school, $payload);
        $second = $this->service->collectPayment($this->school, $payload);

        $this->assertEquals('7500.00', (string) $first['data']['total_amount']);
        $this->assertEquals('7500.00', (string) $second['data']['total_amount']);
    }

    public function test_different_idempotency_keys_collect_separately(): void
    {
        $base = [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
        ];

        $a = $this->service->collectPayment($this->school, array_merge($base, [
            'idempotency_key' => 'key-a',
            'payments'        => [['fee_id' => $this->fee->id, 'amount' => 3000]],
        ]));
        $b = $this->service->collectPayment($this->school, array_merge($base, [
            'idempotency_key' => 'key-b',
            'payments'        => [['fee_id' => $this->fee->id, 'amount' => 2500]],
        ]));

        $this->assertNotEquals($a['receipt_no'], $b['receipt_no']);
        $this->assertEquals(2, FeePayment::where('fee_id', $this->fee->id)->count());

        $this->fee->refresh();
        $this->assertEquals('5500.00', (string) $this->fee->paid_amount);
    }
}
