<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentEnquiry;
use App\Models\StudentRegistration;
use App\Models\Student;
use App\Models\Religion;
use App\Models\Category;
use App\Models\BloodGroup;
use App\Models\StudentType;
use App\Models\CorrespondingRelative;
use App\Models\BoardingType;
use App\Models\Qualification;

class SyncMasterIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edusphere:sync-master-ids {--dry-run : Only simulate the changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill *_id columns from corresponding string columns for normalization across the student lifecycle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting student master data ID synchronization...");
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("RUNNING IN DRY-RUN MODE - No changes will be saved.");
        }

        $mappings = [
            'religion_id'               => ['model' => Religion::class,               'field' => 'religion'],
            'category_id'               => ['model' => Category::class,               'field' => 'category'],
            'blood_group_id'            => ['model' => BloodGroup::class,             'field' => 'blood_group'],
            'student_type_id'           => ['model' => StudentType::class,            'field' => 'student_type'],
            'corresponding_relative_id' => ['model' => CorrespondingRelative::class,  'field' => 'corresponding_relative'],
            'boarding_type_id'          => ['model' => BoardingType::class,           'field' => 'boarding_type'],
            'father_qualification_id'   => ['model' => Qualification::class,          'field' => 'father_qualification'],
            'mother_qualification_id'   => ['model' => Qualification::class,          'field' => 'mother_qualification'],
        ];

        $tables = [
            'Enquiries'     => StudentEnquiry::class,
            'Registrations' => StudentRegistration::class,
            'Students'      => Student::class,
        ];

        foreach ($tables as $label => $modelClass) {
            $this->info("\nProcessing {$label}...");
            
            $count = 0;
            $updated = 0;
            $missingMaster = [];

            $modelClass::chunk(100, function ($records) use ($mappings, $dryRun, &$count, &$updated, &$missingMaster) {
                foreach ($records as $record) {
                    $count++;
                    $changesMade = false;

                    foreach ($mappings as $idField => $config) {
                        // Check if the record has the id column (defensive)
                        if (!array_key_exists($idField, $record->getAttributes()) && !in_array($idField, $record->getFillable())) {
                            continue;
                        }

                        $stringValue = trim($record->{$config['field']} ?? '');
                        $currentId = $record->{$idField};

                        // Only sync if we have a string value but no ID yet
                        if (!empty($stringValue) && empty($currentId)) {
                            // Cache lookups slightly to avoid repeated queries for the same strings
                            $masterRecord = $config['model']::where('school_id', $record->school_id)
                                ->where('name', $stringValue)
                                ->first();

                            if ($masterRecord) {
                                $record->{$idField} = $masterRecord->id;
                                $changesMade = true;
                            } else {
                                $missingKey = "{$config['model']}:{$stringValue}";
                                $missingMaster[$missingKey] = ($missingMaster[$missingKey] ?? 0) + 1;
                            }
                        }
                    }

                    if ($changesMade) {
                        $updated++;
                        if (!$dryRun) {
                            $record->save();
                        }
                    }
                }
            });

            $this->info("Completed {$label}: Found {$count} records, " . ($dryRun ? "successfully matched" : "updated") . " {$updated} records.");
            
            if (!empty($missingMaster)) {
                $this->warn("Missing master records for " . count($missingMaster) . " unique values in {$label}. Match failed for these strings.");
                if ($this->getOutput()->isVerbose()) {
                    foreach ($missingMaster as $key => $occurrences) {
                        $this->line("  - {$key} ({$occurrences} occurrences)");
                    }
                }
            }
        }

        $this->info("\nSynchronization completed" . ($dryRun ? " (simulated)" : "") . ".");
        return self::SUCCESS;
    }
}
