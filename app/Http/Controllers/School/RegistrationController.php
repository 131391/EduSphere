<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\StudentRegistration;
use App\Models\ClassModel;
use App\Enums\Gender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends TenantController
{
    public function index()
    {
        return redirect()->route('school.student-registrations.index');
    }

    /**
     * Import student registrations from a CSV file.
     *
     * WHY this rewrite is necessary:
     * - The original used Registration::create() (wrong model — Registration is for
     *   exam registrations, not student intake).
     * - No row-level validation: malformed dates, invalid genders, and class IDs from
     *   other schools were silently accepted.
     * - No duplicate detection: re-uploading the same CSV created duplicate records.
     * - No transaction: a failure mid-import left partial data.
     * - class_id came directly from CSV with no school_id scope check — a school admin
     *   could inject a class_id belonging to another school.
     */
    public function import(Request $request)
    {
        // Step 1: Validate the uploaded file itself
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $school    = $this->getSchool();
        $schoolId  = $school->id;

        // Step 2: Pre-load valid class IDs for this school to avoid per-row DB hits
        // and to prevent cross-tenant class_id injection from the CSV.
        $validClassIds = ClassModel::where('school_id', $schoolId)
            ->pluck('id')
            ->toArray();

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (empty($rows)) {
            return back()->withErrors(['file' => 'The CSV file is empty.']);
        }

        // Step 3: Strip the header row
        array_shift($rows);

        $imported = 0;
        $skipped  = 0;
        $rowErrors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $lineNumber => $row) {
                $csvLine = $lineNumber + 2; // +2: 1-based + header row

                // Step 4: Ensure minimum column count before accessing indices
                if (count($row) < 7) {
                    $rowErrors[] = "Line {$csvLine}: insufficient columns (expected 7, got " . count($row) . ").";
                    $skipped++;
                    continue;
                }

                [$studentName, $fatherName, $motherName, $dob, $genderStr, $classId, $registrationFee] = $row;

                // Step 5: Row-level validation using Laravel Validator
                $rowData = [
                    'student_name'     => trim($studentName),
                    'father_name'      => trim($fatherName),
                    'mother_name'      => trim($motherName),
                    'dob'              => trim($dob),
                    'gender'           => trim($genderStr),
                    'class_id'         => trim($classId),
                    'registration_fee' => trim($registrationFee),
                ];

                $validator = Validator::make($rowData, [
                    'student_name'     => 'required|string|max:255',
                    'father_name'      => 'required|string|max:255',
                    'mother_name'      => 'required|string|max:255',
                    'dob'              => 'required|date_format:Y-m-d',
                    'gender'           => 'required|in:Male,Female,Other',
                    'class_id'         => 'required|integer',
                    'registration_fee' => 'nullable|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $rowErrors[] = "Line {$csvLine}: " . implode(', ', $validator->errors()->all());
                    $skipped++;
                    continue;
                }

                // Step 6: Tenant scope — reject class IDs that don't belong to this school
                $classIdInt = (int) $rowData['class_id'];
                if (!in_array($classIdInt, $validClassIds)) {
                    $rowErrors[] = "Line {$csvLine}: class_id {$classIdInt} does not belong to this school.";
                    $skipped++;
                    continue;
                }

                // Step 7: Map gender string to enum integer value
                $genderValue = match (strtolower($rowData['gender'])) {
                    'male'   => Gender::Male->value,
                    'female' => Gender::Female->value,
                    default  => null,
                };

                // Step 8: Duplicate detection — same student name + DOB + class in this school
                $duplicate = StudentRegistration::where('school_id', $schoolId)
                    ->where('first_name', $rowData['student_name'])
                    ->where('dob', $rowData['dob'])
                    ->where('class_id', $classIdInt)
                    ->exists();

                if ($duplicate) {
                    $rowErrors[] = "Line {$csvLine}: duplicate — {$rowData['student_name']} (DOB {$rowData['dob']}) already registered for this class.";
                    $skipped++;
                    continue;
                }

                // Step 9: Create the registration record using the correct model.
                // registration_no is auto-generated by StudentRegistration::boot().
                StudentRegistration::create([
                    'school_id'         => $schoolId,
                    'first_name'        => $rowData['student_name'],
                    'father_first_name' => $rowData['father_name'],
                    'mother_first_name' => $rowData['mother_name'],
                    'dob'               => $rowData['dob'],
                    'gender'            => $genderValue,
                    'class_id'          => $classIdInt,
                    'registration_fee'  => $rowData['registration_fee'] ?: 0,
                    'registration_date' => now(),
                ]);

                $imported++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV Registration Import Failed', [
                'school_id' => $schoolId,
                'error'     => $e->getMessage(),
            ]);
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }

        $message = "{$imported} registration(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped.";
        }

        return redirect()
            ->route('school.student-registrations.index')
            ->with('success', $message)
            ->with('import_errors', $rowErrors);
    }

    /**
     * Download a CSV template showing the expected column format.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="registration_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Student Name',
                'Father Name',
                'Mother Name',
                'Date of Birth (YYYY-MM-DD)',
                'Gender (Male/Female/Other)',
                'Class ID',
                'Registration Fee',
            ]);
            // Example row
            fputcsv($file, ['John Doe', 'Richard Doe', 'Jane Doe', '2015-05-15', 'Male', '1', '500']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
