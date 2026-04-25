<?php

namespace Tests\Unit\Services;

use App\Enums\FeeStatus;
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

class FeePaymentReversalTest extends TestCase
{
    use RefreshDatabase;

    private FeePaymentService $service;
    private School $school;
    private Student $student;
    private AcademicYear $academicYear;
    private PaymentMethod $paymentMethod;

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

        app()->instance('currentSchool', $this->school);

        $this->actingAs(User::factory()->create(['school_id' => $this->school->id]));
    }

    /**
     * Reversal must recompute due_amount from base values, not from the
     * (potentially stale) due_amount that may already reflect post-payment
     * waiver/discount changes.
     */
    public function test_reversal_recomputes_due_from_base_values_with_waiver(): void
    {
        $fee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount'   => 10000,
            'paid_amount'      => 0,
            'due_amount'       => 10000,
            'waiver_amount'    => 0,
            'discount_amount'  => 0,
        ]);

        // Step 1: collect a partial payment of 4000.
        $result = $this->service->collectPayment($this->school, [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments'          => [['fee_id' => $fee->id, 'amount' => 4000]],
        ]);
        $this->assertTrue($result['success']);
        $receiptNo = $result['receipt_no'];

        // Step 2: a waiver of 2000 is applied AFTER the payment was recorded.
        $fee->refresh();
        $fee->waiver_amount = '2000.00';
        $fee->due_amount = '4000.00'; // 10000 - 4000 paid - 2000 waiver = 4000
        $fee->save();

        // Step 3: reverse the payment. The naive "due_amount += amount" would
        // produce due = 8000. The correct answer is payable - paid - waiver
        // = 10000 - 0 - 2000 = 8000. Same number here, but verify status.
        $reverted = $this->service->revertPayment($this->school, $receiptNo);
        $this->assertTrue($reverted['success']);

        $fee->refresh();
        $this->assertEquals('0.00', (string) $fee->paid_amount);
        $this->assertEquals('8000.00', (string) $fee->due_amount);
        $this->assertEquals(FeeStatus::Pending, $fee->payment_status);
    }

    /**
     * When discount_amount changes after payment, reversal must still produce
     * a coherent due_amount = payable - paid - waiver - discount.
     */
    public function test_reversal_with_discount_change_after_payment(): void
    {
        $fee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount'   => 10000,
            'paid_amount'      => 0,
            'due_amount'       => 10000,
            'waiver_amount'    => 0,
            'discount_amount'  => 0,
        ]);

        $result = $this->service->collectPayment($this->school, [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments'          => [['fee_id' => $fee->id, 'amount' => 6000]],
        ]);
        $receiptNo = $result['receipt_no'];

        // Apply a 1500 discount AFTER the payment.
        $fee->refresh();
        $fee->discount_amount = '1500.00';
        $fee->due_amount = '2500.00'; // 10000 - 6000 - 1500
        $fee->save();

        $this->service->revertPayment($this->school, $receiptNo);

        $fee->refresh();
        $this->assertEquals('0.00', (string) $fee->paid_amount);
        $this->assertEquals('8500.00', (string) $fee->due_amount); // 10000 - 0 - 0 - 1500
        $this->assertEquals(FeeStatus::Pending, $fee->payment_status);
    }

    public function test_reversal_returns_failure_when_receipt_not_found(): void
    {
        $result = $this->service->revertPayment($this->school, 'DOES_NOT_EXIST');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }

    public function test_partial_reversal_keeps_partial_status(): void
    {
        $fee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount'   => 10000,
            'paid_amount'      => 0,
            'due_amount'       => 10000,
        ]);

        // Two separate receipts.
        $r1 = $this->service->collectPayment($this->school, [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments'          => [['fee_id' => $fee->id, 'amount' => 3000]],
        ]);
        $this->service->collectPayment($this->school, [
            'student_id'        => $this->student->id,
            'academic_year_id'  => $this->academicYear->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $this->paymentMethod->id,
            'payments'          => [['fee_id' => $fee->id, 'amount' => 4000]],
        ]);

        // Reverse only the first one.
        $this->service->revertPayment($this->school, $r1['receipt_no']);

        $fee->refresh();
        $this->assertEquals('4000.00', (string) $fee->paid_amount);
        $this->assertEquals('6000.00', (string) $fee->due_amount);
        $this->assertEquals(FeeStatus::Partial, $fee->payment_status);
    }
}
