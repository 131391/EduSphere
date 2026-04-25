<?php

namespace Tests\Unit\Observers;

use App\Enums\FeeStatus;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\School;
use App\Models\Student;
use App\Models\Waiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaiverObserverTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Student $student;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $this->student = Student::factory()->create([
            'school_id'        => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'status'           => StudentStatus::Active,
        ]);
    }

    private function makeFee(int $payable, int $paid = 0, string $period = 'April 2026'): Fee
    {
        return Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => $period,
            'payable_amount'   => $payable,
            'paid_amount'      => $paid,
            'due_amount'       => $payable - $paid,
            'waiver_amount'    => 0,
            'discount_amount'  => 0,
            'payment_status'   => $paid >= $payable ? FeeStatus::Paid : ($paid > 0 ? FeeStatus::Partial : FeeStatus::Pending),
        ]);
    }

    public function test_waiver_distributes_largest_fee_first(): void
    {
        $small = $this->makeFee(1000);
        $large = $this->makeFee(5000);

        Waiver::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => 'April 2026',
            'actual_fee'       => 6000,
            'waiver_amount'    => 3000,
        ]);

        $small->refresh();
        $large->refresh();

        // Largest fee gets the full 3000.
        $this->assertEquals('3000.00', (string) $large->waiver_amount);
        $this->assertEquals('2000.00', (string) $large->due_amount);
        $this->assertEquals('0.00', (string) $small->waiver_amount);
        $this->assertEquals('1000.00', (string) $small->due_amount);
    }

    public function test_waiver_overflow_cascades_to_smaller_fees(): void
    {
        $small = $this->makeFee(1000);
        $large = $this->makeFee(2000);

        Waiver::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => 'April 2026',
            'actual_fee'       => 3000,
            'waiver_amount'    => 2500,
        ]);

        $small->refresh();
        $large->refresh();

        // Large fee absorbs 2000 (its full capacity), remaining 500 goes to small.
        $this->assertEquals('2000.00', (string) $large->waiver_amount);
        $this->assertEquals('0.00', (string) $large->due_amount);
        $this->assertEquals('500.00', (string) $small->waiver_amount);
        $this->assertEquals('500.00', (string) $small->due_amount);
    }

    public function test_waiver_skips_already_paid_fees(): void
    {
        $paid = $this->makeFee(5000, 5000);
        $pending = $this->makeFee(3000);

        Waiver::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => 'April 2026',
            'actual_fee'       => 8000,
            'waiver_amount'    => 2000,
        ]);

        $paid->refresh();
        $pending->refresh();

        $this->assertEquals('0.00', (string) $paid->waiver_amount);
        $this->assertEquals('2000.00', (string) $pending->waiver_amount);
        $this->assertEquals('1000.00', (string) $pending->due_amount);
    }

    public function test_waiver_only_targets_matching_period(): void
    {
        $april = $this->makeFee(2000, 0, 'April 2026');
        $may = $this->makeFee(2000, 0, 'May 2026');

        Waiver::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => 'April 2026',
            'actual_fee'       => 2000,
            'waiver_amount'    => 1000,
        ]);

        $april->refresh();
        $may->refresh();

        $this->assertEquals('1000.00', (string) $april->waiver_amount);
        $this->assertEquals('0.00', (string) $may->waiver_amount);
    }

    public function test_deleting_waiver_removes_distribution(): void
    {
        $fee = $this->makeFee(5000);

        $waiver = Waiver::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => 'April 2026',
            'actual_fee'       => 5000,
            'waiver_amount'    => 2000,
        ]);
        $fee->refresh();
        $this->assertEquals('2000.00', (string) $fee->waiver_amount);

        $waiver->delete();
        $fee->refresh();

        $this->assertEquals('0.00', (string) $fee->waiver_amount);
        $this->assertEquals('5000.00', (string) $fee->due_amount);
    }

    public function test_updating_waiver_amount_redistributes(): void
    {
        $fee = $this->makeFee(10000);

        $waiver = Waiver::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'fee_period'       => 'April 2026',
            'actual_fee'       => 10000,
            'waiver_amount'    => 2000,
        ]);

        $fee->refresh();
        $this->assertEquals('2000.00', (string) $fee->waiver_amount);
        $this->assertEquals('8000.00', (string) $fee->due_amount);

        $waiver->update(['waiver_amount' => 5000]);

        $fee->refresh();
        $this->assertEquals('5000.00', (string) $fee->waiver_amount);
        $this->assertEquals('5000.00', (string) $fee->due_amount);
    }
}
