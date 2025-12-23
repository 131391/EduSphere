<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admission - {{ $student->admission_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #4f46e5;
            margin-bottom: 30px;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
        }
        
        .school-name {
            font-size: 24pt;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 10pt;
            color: #666;
        }
        
        .document-title {
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .student-photo-section {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .student-photo {
            width: 120px;
            height: 120px;
            border: 2px solid #ddd;
            padding: 3px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        
        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
            background-color: #f3f4f6;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        @if($school->logo)
            <img src="{{ public_path('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="school-logo">
        @endif
        <div class="school-name">{{ strtoupper($school->name) }}</div>
        <div class="school-address">
            {{ $school->address }}, {{ $school->city }}, {{ $school->state }} - {{ $school->pin_code }}<br>
            Phone: {{ $school->phone }} | Email: {{ $school->email }}
        </div>
    </div>

    <div class="document-title">Student Admission Details</div>

    {{-- Student Photo --}}
    @if($student->photo)
    <div class="student-photo-section">
        <img src="{{ public_path('storage/' . $student->photo) }}" 
             alt="Student Photo" 
             class="student-photo">
    </div>
    @endif

    {{-- Admission Information --}}
    <div class="section">
        <div class="section-title">Admission Information</div>
        <table class="info-table">
            <tr>
                <td>Admission Number</td>
                <td><strong>{{ $student->admission_no }}</strong></td>
            </tr>
            <tr>
                <td>Admission Date</td>
                <td>{{ $student->admission_date ? $student->admission_date->format('d F, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Academic Year</td>
                <td>{{ $student->academicYear->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Class - Section</td>
                <td>{{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Roll Number</td>
                <td>{{ $student->roll_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Registration Number</td>
                <td>{{ $student->registration_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <span class="status-badge">Admitted</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Student Personal Information --}}
    <div class="section">
        <div class="section-title">Student Personal Information</div>
        <table class="info-table">
            <tr>
                <td>Full Name</td>
                <td><strong>{{ $student->full_name }}</strong></td>
            </tr>
            <tr>
                <td>Gender</td>
                <td>{{ $student->gender_label }}</td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d F, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Blood Group</td>
                <td>{{ $student->blood_group ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>{{ $student->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td>{{ $student->email ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Parent Information --}}
    <div class="section">
        <div class="section-title">Parent Information</div>
        <table class="info-table">
            <tr>
                <td colspan="2" style="background-color: #e5e7eb; font-weight: bold;">Father's Details</td>
            </tr>
            <tr>
                <td>Name</td>
                <td><strong>{{ $student->father_name }}</strong></td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>{{ $student->father_mobile ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #e5e7eb; font-weight: bold;">Mother's Details</td>
            </tr>
            <tr>
                <td>Name</td>
                <td><strong>{{ $student->mother_name }}</strong></td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>{{ $student->mother_mobile ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Address Information --}}
    <div class="section">
        <div class="section-title">Address Information</div>
        <table class="info-table">
            <tr>
                <td colspan="2" style="background-color: #e5e7eb; font-weight: bold;">Permanent Address</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>{{ $student->permanent_address }}</td>
            </tr>
            <tr>
                <td>City, State</td>
                <td>{{ $student->permanent_city }}, {{ $student->permanent_state }}</td>
            </tr>
            <tr>
                <td>PIN Code</td>
                <td>{{ $student->permanent_pin }}</td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #e5e7eb; font-weight: bold;">Correspondence Address</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>{{ $student->correspondence_address }}</td>
            </tr>
            <tr>
                <td>City, State</td>
                <td>{{ $student->correspondence_city }}, {{ $student->correspondence_state }}</td>
            </tr>
            <tr>
                <td>PIN Code</td>
                <td>{{ $student->correspondence_pin }}</td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ now()->format('d F, Y \a\t h:i A') }} | {{ $school->name }}
    </div>
</body>
</html>
