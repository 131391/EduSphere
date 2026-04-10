<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;

class RegistrationController extends TenantController
{
    public function index()
    {
        // Redirect to the actual student registrations page
        return redirect()->route('school.student-registrations.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $school = $this->getSchool();
        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        $header = array_shift($data); // first row is header
        
        $count = 0;
        foreach ($data as $row) {
            if (count($row) < 5) continue; // Basic validation
            
            // Expected columns: Student Name, Father Name, Mother Name, Date of Birth (YYYY-MM-DD), Gender (Male/Female), Class ID, Registration Fee
            \App\Models\Registration::create([
                'school_id' => $this->getSchoolId(),
                'registration_no' => 'REG-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'student_name' => $row[0],
                'father_name' => $row[1],
                'mother_name' => $row[2],
                'date_of_birth' => $row[3],
                'gender' => strtolower($row[4]),
                'class_id' => $row[5],
                'registration_fee' => $row[6] ?? 0,
                'registration_date' => now(),
                'status' => \App\Enums\RegistrationStatus::Pending,
            ]);
            $count++;
        }

        return redirect()->route('school.student-registrations.index')
            ->with('success', "$count registrations imported successfully.");
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="registration_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student Name', 'Father Name', 'Mother Name', 'Date of Birth (YYYY-MM-DD)', 'Gender (Male/Female/Other)', 'Class ID', 'Registration Fee']);
            fputcsv($file, ['John Doe', 'Richard Doe', 'Jane Doe', '2015-05-15', 'Male', '1', '500']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

