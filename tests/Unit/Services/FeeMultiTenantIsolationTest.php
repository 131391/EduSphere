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

class FeeMultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private FeePaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeePaymentService(new NumberingService());
        $this->actingAs(User::factory()->create());
    }

    public function test_cannot_collect_payment_against_fee_from_another_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $yearA = AcademicYear::factory()->create(['school_id' => $schoolA->id]);
        $yearB = AcademicYear::factory()->create(['school_id' => $schoolB->id]);

        $studentA = Student::factory()->create([
            'school_id'        => $schoolA->id,
            'academic_year_id' => $yearA->id,
            'status'           => StudentStatus::Active,
        ]);
        $studentB = Student::factory()->create([
            'school_id'        => $schoolB->id,
            'academic_year_id' => $yearB->id,
            'status'           => StudentStatus::Active,
        ]);

        $methodA = PaymentMethod::factory()->create(['school_id' => $schoolA->id]);

        // Fee belongs to school B's student
        $feeB = Fee::factory()->create([
            'school_id'        => $schoolB->id,
            'student_id'       => $studentB->id,
            'academic_year_id' => $yearB->id,
            'payable_amount'   => 5000,
            'due_amount'       => 5000,
        ]);

        // Attempt to collect a payment in school A's context against B's fee.
        $result = $this->service->collectPayment($schoolA, [
            'student_id'        => $studentA->id,
            'academic_year_id'  => $yearA->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $methodA->id,
            'payments'          => [['fee_id' => $feeB->id, 'amount' => 1000]],
        ]);

        $this->assertFalse($result['success']);
        $feeB->refresh();
        $this->assertEquals('0.00', (string) $feeB->paid_amount);
        $this->assertCount(0, FeePayment::where('fee_id', $feeB->id)->get());
    }

    public function test_cannot_collect_against_other_students_fee_within_same_school(): void
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $school->id]);
        $alice = Student::factory()->create(['school_id' => $school->id, 'academic_year_id' => $year->id]);
        $bob = Student::factory()->create(['school_id' => $school->id, 'academic_year_id' => $year->id]);
        $method = PaymentMethod::factory()->create(['school_id' => $school->id]);

        $bobsFee = Fee::factory()->create([
            'school_id'        => $school->id,
            'student_id'       => $bob->id,
            'academic_year_id' => $year->id,
            'payable_amount'   => 5000,
            'due_amount'       => 5000,
        ]);

        $result = $this->service->collectPayment($school, [
            'student_id'        => $alice->id,   // claim Alice but submit Bob's fee
            'academic_year_id'  => $year->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $method->id,
            'payments'          => [['fee_id' => $bobsFee->id, 'amount' => 1000]],
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('does not belong', $result['message']);
        $bobsFee->refresh();
        $this->assertEquals('0.00', (string) $bobsFee->paid_amount);
    }

    public function test_receipt_numbers_are_isolated_per_school(): void
    {
        [$schoolA, $schoolB] = [School::factory()->create(), School::factory()->create()];

        $payloadFor = function (School $school) {
            $year = AcademicYear::factory()->create(['school_id' => $school->id]);
            $student = Student::factory()->create([
                'school_id'        => $school->id,
                'academic_year_id' => $year->id,
                'status'           => StudentStatus::Active,
            ]);
            $method = PaymentMethod::factory()->create(['school_id' => $school->id]);
            $fee = Fee::factory()->create([
                'school_id'        => $school->id,
                'student_id'       => $student->id,
                'academic_year_id' => $year->id,
                'payable_amount'   => 1000,
                'due_amount'       => 1000,
            ]);

            return [
                'student_id'        => $student->id,
                'academic_year_id'  => $year->id,
                'payment_date'      => now()->toDateString(),
                'payment_method_id' => $method->id,
                'payments'          => [['fee_id' => $fee->id, 'amount' => 1000]],
            ];
        };

        $rA = $this->service->collectPayment($schoolA, $payloadFor($schoolA));
        $rB = $this->service->collectPayment($schoolB, $payloadFor($schoolB));

        $this->assertTrue($rA['success']);
        $this->assertTrue($rB['success']);
        // Both should be RCPT-{school_id}-{year}-000001 — receipts are per-school sequences.
        $this->assertStringContainsString("-{$schoolA->id}-", $rA['receipt_no']);
        $this->assertStringContainsString("-{$schoolB->id}-", $rB['receipt_no']);
        $this->assertStringEndsWith('-000001', $rA['receipt_no']);
        $this->assertStringEndsWith('-000001', $rB['receipt_no']);
    }

    public function test_revert_only_finds_receipt_in_correct_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $schoolA->id]);
        $student = Student::factory()->create(['school_id' => $schoolA->id, 'academic_year_id' => $year->id]);
        $method = PaymentMethod::factory()->create(['school_id' => $schoolA->id]);
        $fee = Fee::factory()->create([
            'school_id'        => $schoolA->id,
            'student_id'       => $student->id,
            'academic_year_id' => $year->id,
            'payable_amount'   => 5000,
            'due_amount'       => 5000,
        ]);

        $r = $this->service->collectPayment($schoolA, [
            'student_id'        => $student->id,
            'academic_year_id'  => $year->id,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => $method->id,
            'payments'          => [['fee_id' => $fee->id, 'amount' => 5000]],
        ]);

        // Reverting from the wrong school context must fail.
        $bad = $this->service->revertPayment($schoolB, $r['receipt_no']);
        $this->assertFalse($bad['success']);

        // From the right school: succeeds.
        $good = $this->service->revertPayment($schoolA, $r['receipt_no']);
        $this->assertTrue($good['success']);
    }
}
