<?php

namespace App\Console\Commands;

use App\Enums\FeeStatus;
use App\Enums\GeneralStatus;
use App\Enums\YesNo;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeeType;
use App\Models\HostelBedAssignment;
use App\Models\School;
use App\Models\StudentTransportAssignment;
use App\Services\School\NumberingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateFacilityFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:generate-facility {--month= : The month to generate fees for (Y-m)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring monthly fees for transport and hostel assignments.';

    protected NumberingService $numberingService;

    public function __construct(NumberingService $numberingService)
    {
        parent::__construct();
        $this->numberingService = $numberingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monthStr = $this->option('month') ?: now()->format('Y-m');
        $targetMonth = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        $feePeriod = $targetMonth->format('F Y'); // e.g. "April 2026"
        $dueDate = $targetMonth->copy()->addDays(9)->toDateString(); // Due by the 10th

        $this->info("Generating facility fees for period: {$feePeriod}");

        $generatedCount = 0;

        School::query()->active()->chunk(50, function ($schools) use ($targetMonth, $feePeriod, $dueDate, &$generatedCount) {
            foreach ($schools as $school) {
                $generatedCount += $this->processSchool($school, $targetMonth, $feePeriod, $dueDate);
            }
        });

        $this->info("Done. Generated {$generatedCount} facility fees.");
    }

    private function processSchool(School $school, Carbon $targetMonth, string $feePeriod, string $dueDate): int
    {
        $count = 0;

        DB::transaction(function () use ($school, $targetMonth, $feePeriod, $dueDate, &$count) {
            // Get or create Facility Fee Type
            $feeType = FeeType::firstOrCreate(
                ['school_id' => $school->id, 'name' => 'Facility Fees'],
                ['code' => 'FACILITY', 'is_active' => true, 'description' => 'System-generated facility fees']
            );

            // Get or create Transport Fee Name
            $transportFeeName = FeeName::firstOrCreate(
                ['school_id' => $school->id, 'fee_type_id' => $feeType->id, 'name' => 'Transport Fee'],
                ['is_active' => YesNo::Yes, 'description' => 'Monthly transport fee']
            );

            // Get or create Hostel Fee Name
            $hostelFeeName = FeeName::firstOrCreate(
                ['school_id' => $school->id, 'fee_type_id' => $feeType->id, 'name' => 'Hostel Fee'],
                ['is_active' => YesNo::Yes, 'description' => 'Monthly hostel bed rent']
            );

            $count += $this->generateTransportFees($school, $transportFeeName, $feeType, $targetMonth, $feePeriod, $dueDate);
            $count += $this->generateHostelFees($school, $hostelFeeName, $feeType, $targetMonth, $feePeriod, $dueDate);

            if ($count > 0) {
                // Touch all waivers for this period to apply them to newly generated facility fees
                $waivers = \App\Models\Waiver::where('school_id', $school->id)
                    ->where('fee_period', $feePeriod)
                    ->get();
                foreach ($waivers as $waiver) {
                    $waiver->touch();
                }
            }
        });

        return $count;
    }

    private function generateTransportFees(School $school, FeeName $feeName, FeeType $feeType, Carbon $targetMonth, string $feePeriod, string $dueDate): int
    {
        $count = 0;
        
        $assignments = StudentTransportAssignment::where('school_id', $school->id)
            ->where('status', GeneralStatus::Active)
            ->where(function($query) use ($targetMonth) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $targetMonth->endOfMonth());
            })
            ->where(function($query) use ($targetMonth) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $targetMonth->startOfMonth());
            })
            ->with('student')
            ->get();

        foreach ($assignments as $assignment) {
            if (!$assignment->student || !$assignment->student->academic_year_id || $assignment->fee_per_month <= 0) {
                continue;
            }

            $exists = Fee::withTrashed()
                ->where('school_id', $school->id)
                ->where('student_id', $assignment->student_id)
                ->where('fee_name_id', $feeName->id)
                ->where('fee_period', $feePeriod)
                ->exists();

            if (!$exists) {
                Fee::forceCreate([
                    'school_id' => $school->id,
                    'student_id' => $assignment->student_id,
                    'academic_year_id' => $assignment->student->academic_year_id,
                    'fee_type_id' => $feeType->id,
                    'fee_name_id' => $feeName->id,
                    'class_id' => $assignment->student->class_id,
                    'bill_no' => $this->numberingService->nextBillNo($school->id),
                    'fee_period' => $feePeriod,
                    'payable_amount' => $assignment->fee_per_month,
                    'due_amount' => $assignment->fee_per_month,
                    'due_date' => $dueDate,
                    'payment_status' => FeeStatus::Pending,
                    'remarks' => "Transport fee for {$feePeriod}",
                ]);
                $count++;
            }
        }

        return $count;
    }

    private function generateHostelFees(School $school, FeeName $feeName, FeeType $feeType, Carbon $targetMonth, string $feePeriod, string $dueDate): int
    {
        $count = 0;
        
        $assignments = HostelBedAssignment::where('school_id', $school->id)
            ->where('status', GeneralStatus::Active)
            ->where(function($query) use ($targetMonth) {
                $query->whereNull('hostel_assign_date')->orWhere('hostel_assign_date', '<=', $targetMonth->endOfMonth());
            })
            ->with('student')
            ->get();

        foreach ($assignments as $assignment) {
            if (!$assignment->student || !$assignment->student->academic_year_id || $assignment->rent <= 0) {
                continue;
            }

            $exists = Fee::withTrashed()
                ->where('school_id', $school->id)
                ->where('student_id', $assignment->student_id)
                ->where('fee_name_id', $feeName->id)
                ->where('fee_period', $feePeriod)
                ->exists();

            if (!$exists) {
                Fee::forceCreate([
                    'school_id' => $school->id,
                    'student_id' => $assignment->student_id,
                    'academic_year_id' => $assignment->student->academic_year_id,
                    'fee_type_id' => $feeType->id,
                    'fee_name_id' => $feeName->id,
                    'class_id' => $assignment->student->class_id,
                    'bill_no' => $this->numberingService->nextBillNo($school->id),
                    'fee_period' => $feePeriod,
                    'payable_amount' => $assignment->rent,
                    'due_amount' => $assignment->rent,
                    'due_date' => $dueDate,
                    'payment_status' => FeeStatus::Pending,
                    'remarks' => "Hostel fee for {$feePeriod}",
                ]);
                $count++;
            }
        }

        return $count;
    }
}
