<?php

namespace Tests\Unit\Console;

use App\Enums\FeeStatus;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\LateFee;
use App\Models\School;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplyLateFeesTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Student $student;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->school = School::factory()->create();
        $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $this->student = Student::factory()->create([
            'school_id'        => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Tiered late-fee config: 5+ days → ₹50, 15+ days → ₹100, 30+ days → ₹200
        LateFee::create(['school_id' => $this->school->id, 'fine_date' => 5, 'late_fee_amount' => 50]);
        LateFee::create(['school_id' => $this->school->id, 'fine_date' => 15, 'late_fee_amount' => 100]);
        LateFee::create(['school_id' => $this->school->id, 'fine_date' => 30, 'late_fee_amount' => 200]);
    }

    private function makeFee(int $daysOverdue, int $existingLateFee = 0): Fee
    {
        $payable = 1000;
        return Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount'   => $payable + $existingLateFee,
            'paid_amount'      => 0,
            'due_amount'       => $payable + $existingLateFee,
            'late_fee'         => $existingLateFee,
            'due_date'         => now()->subDays($daysOverdue)->toDateString(),
            'payment_status'   => FeeStatus::Pending,
        ]);
    }

    public function test_picks_highest_applicable_tier(): void
    {
        $fee = $this->makeFee(20); // 20 days late → tier 15 ₹100

        $this->artisan('fees:apply-late')->assertSuccessful();

        $fee->refresh();
        $this->assertEquals('100.00', (string) $fee->late_fee);
        $this->assertEquals('1100.00', (string) $fee->payable_amount);
        $this->assertEquals('1100.00', (string) $fee->due_amount);
        $this->assertEquals(FeeStatus::Overdue, $fee->payment_status);
    }

    public function test_skips_fees_not_yet_overdue(): void
    {
        $fee = $this->makeFee(2); // less than first tier

        $this->artisan('fees:apply-late')->assertSuccessful();

        $fee->refresh();
        $this->assertEquals('0.00', (string) $fee->late_fee);
        $this->assertEquals(FeeStatus::Pending, $fee->payment_status);
    }

    public function test_idempotent_on_repeat_run(): void
    {
        $fee = $this->makeFee(20);

        $this->artisan('fees:apply-late')->assertSuccessful();
        $this->artisan('fees:apply-late')->assertSuccessful();

        $fee->refresh();
        // Should remain at the tier-15 amount, not stack to 200.
        $this->assertEquals('100.00', (string) $fee->late_fee);
        $this->assertEquals('1100.00', (string) $fee->payable_amount);
    }

    public function test_upgrades_tier_when_more_overdue(): void
    {
        $fee = $this->makeFee(20, 50); // already had ₹50 from earlier run, now 20 days late

        $this->artisan('fees:apply-late')->assertSuccessful();

        $fee->refresh();
        $this->assertEquals('100.00', (string) $fee->late_fee);
        // payable was 1050; delta of 50 added → 1100
        $this->assertEquals('1100.00', (string) $fee->payable_amount);
    }

    public function test_skips_paid_fees(): void
    {
        $fee = Fee::factory()->paid()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'payable_amount'   => 1000,
            'due_date'         => now()->subDays(20)->toDateString(),
        ]);

        $this->artisan('fees:apply-late')->assertSuccessful();

        $fee->refresh();
        $this->assertEquals('0.00', (string) $fee->late_fee);
    }
}
