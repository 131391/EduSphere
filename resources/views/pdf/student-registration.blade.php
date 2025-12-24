<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - {{ $studentRegistration->registration_no }}</title>
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
            border-bottom: 3px solid #1e40af;
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
            color: #1e40af;
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
            color: #1e40af;
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
            color: #1e40af;
            border-bottom: 2px solid #1e40af;
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
        
        .parent-section {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        
        .parent-section:first-child {
            margin-right: 3%;
        }
        
        .parent-photo {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            padding: 2px;
            margin-bottom: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-admitted {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
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
        
        .page-break {
            page-break-after: always;
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

    <div class="document-title">Student Registration Details</div>

    {{-- Student Photo --}}
    @if($studentRegistration->student_photo)
    <div class="student-photo-section">
        <img src="{{ public_path('storage/' . $studentRegistration->student_photo) }}" 
             alt="Student Photo" 
             class="student-photo">
    </div>
    @endif

    {{-- Basic Information --}}
    <div class="section">
        <div class="section-title">Registration Information</div>
        <table class="info-table">
            <tr>
                <td>Registration Number</td>
                <td><strong>{{ $studentRegistration->registration_no }}</strong></td>
            </tr>
            <tr>
                <td>Registration Date</td>
                <td>{{ $studentRegistration->registration_date->format('d F, Y') }}</td>
            </tr>
            <tr>
                <td>Academic Year</td>
                <td>{{ $studentRegistration->academicYear->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Class</td>
                <td>{{ $studentRegistration->class->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Registration Fee</td>
                <td>₹{{ number_format($studentRegistration->registration_fee, 2) }}</td>
            </tr>
            <tr>
                <td>Admission Status</td>
                <td>
                    @php
                        $statusClass = match($studentRegistration->admission_status->value) {
                            'pending' => 'status-pending',
                            'admitted' => 'status-admitted',
                            'cancelled' => 'status-cancelled',
                            default => 'status-pending'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ $studentRegistration->admission_status->label() }}
                    </span>
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
                <td><strong>{{ $studentRegistration->full_name }}</strong></td>
            </tr>
            <tr>
                <td>Gender</td>
                <td>{{ $studentRegistration->gender_label }}</td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td>{{ $studentRegistration->dob ? $studentRegistration->dob->format('d F, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>{{ $studentRegistration->mobile_no }}</td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td>{{ $studentRegistration->email ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Father's Details --}}
    <div class="section">
        <div class="section-title">Father's Details</div>
        @if($studentRegistration->father_photo)
        <img src="{{ public_path('storage/' . $studentRegistration->father_photo) }}" 
             alt="Father Photo" 
             class="parent-photo">
        @endif
        <table class="info-table">
            <tr>
                <td>Name</td>
                <td><strong>{{ $studentRegistration->father_name_prefix }} {{ $studentRegistration->father_first_name }} {{ $studentRegistration->father_last_name }}</strong></td>
            </tr>
            <tr>
                <td>Occupation</td>
                <td>{{ $studentRegistration->father_occupation }}</td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>{{ $studentRegistration->father_mobile_no }}</td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td>{{ $studentRegistration->father_email ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Mother's Details --}}
    <div class="section">
        <div class="section-title">Mother's Details</div>
        @if($studentRegistration->mother_photo)
        <img src="{{ public_path('storage/' . $studentRegistration->mother_photo) }}" 
             alt="Mother Photo" 
             class="parent-photo">
        @endif
        <table class="info-table">
            <tr>
                <td>Name</td>
                <td><strong>{{ $studentRegistration->mother_name_prefix }} {{ $studentRegistration->mother_first_name }} {{ $studentRegistration->mother_last_name }}</strong></td>
            </tr>
            <tr>
                <td>Occupation</td>
                <td>{{ $studentRegistration->mother_occupation }}</td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>{{ $studentRegistration->mother_mobile_no }}</td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td>{{ $studentRegistration->mother_email ?? 'N/A' }}</td>
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
                <td>{{ $studentRegistration->permanent_address }}</td>
            </tr>
            <tr>
                <td>City, State</td>
                <td>{{ $studentRegistration->permanent_city }}, {{ $studentRegistration->permanent_state }}</td>
            </tr>
            <tr>
                <td>Country, PIN</td>
                <td>{{ config('countries')[$studentRegistration->permanent_country_id] ?? 'N/A' }} - {{ $studentRegistration->permanent_pin }}</td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #e5e7eb; font-weight: bold;">Correspondence Address</td>
            </tr>
            @if($studentRegistration->correspondence_address)
            <tr>
                <td>Address</td>
                <td>{{ $studentRegistration->correspondence_address }}</td>
            </tr>
            <tr>
                <td>City, State</td>
                <td>{{ $studentRegistration->correspondence_city }}, {{ $studentRegistration->correspondence_state }}</td>
            </tr>
            <tr>
                <td>Country, PIN</td>
                <td>{{ config('countries')[$studentRegistration->correspondence_country_id] ?? 'N/A' }} - {{ $studentRegistration->correspondence_pin }}</td>
            </tr>
            @else
            <tr>
                <td colspan="2" style="font-style: italic; color: #666;">Same as Permanent Address</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ now()->format('d F, Y \a\t h:i A') }} | {{ $school->name }}
    </div>
</body>
</html>
