<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Form — {{ $studentRegistration->registration_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            background: #fff;
        }

        .page { padding: 28px 32px 60px; }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .header-logo {
            display: table-cell;
            width: 72px;
            vertical-align: middle;
        }
        .header-logo img {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }
        .header-logo-placeholder {
            width: 64px;
            height: 64px;
            background: #dbeafe;
            border-radius: 8px;
            text-align: center;
            line-height: 64px;
            font-size: 22pt;
            color: #1d4ed8;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }
        .school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: 0.3px;
        }
        .school-meta {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.5;
        }
        .header-doc {
            display: table-cell;
            width: 160px;
            vertical-align: middle;
            text-align: right;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-no   { font-size: 9pt;  color: #64748b; margin-top: 4px; }
        .doc-date { font-size: 8pt;  color: #94a3b8; margin-top: 2px; }

        /* ── Hero Row ── */
        .hero {
            display: table;
            width: 100%;
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            margin-bottom: 18px;
            padding: 14px 16px;
        }
        .hero-photo {
            display: table-cell;
            width: 90px;
            vertical-align: top;
        }
        .hero-photo img {
            width: 80px;
            height: 96px;
            object-fit: cover;
            border: 2px solid #93c5fd;
            border-radius: 4px;
        }
        .hero-photo-placeholder {
            width: 80px;
            height: 96px;
            background: #dbeafe;
            border: 2px solid #93c5fd;
            border-radius: 4px;
            text-align: center;
            line-height: 96px;
            font-size: 28pt;
            color: #93c5fd;
        }
        .hero-info {
            display: table-cell;
            vertical-align: top;
            padding-left: 16px;
        }
        .hero-name {
            font-size: 15pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .hero-badges { margin-bottom: 10px; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: bold;
            margin-right: 6px;
        }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-indigo { background: #e0e7ff; color: #4338ca; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .hero-quick { display: table; width: 100%; }
        .hero-quick-cell {
            display: table-cell;
            width: 25%;
            padding-right: 10px;
        }
        .quick-label {
            font-size: 7.5pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }
        .quick-value {
            font-size: 9pt;
            font-weight: bold;
            color: #334155;
            margin-top: 1px;
        }

        /* ── Section ── */
        .section { margin-bottom: 16px; }
        .section-header {
            background: #1d4ed8;
            color: #fff;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 10px;
            border-radius: 3px 3px 0 0;
        }
        .section-body {
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 3px 3px;
        }

        /* ── Two-column grid ── */
        .info-grid { display: table; width: 100%; }
        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px 12px;
        }
        .info-col + .info-col { border-left: 1px solid #e2e8f0; }
        .info-row { margin-bottom: 8px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label {
            font-size: 7.5pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }
        .info-value {
            font-size: 9.5pt;
            color: #1e293b;
            font-weight: 600;
            margin-top: 1px;
        }
        .info-value.muted { color: #64748b; font-weight: normal; }

        /* ── Sub-section header ── */
        .sub-header {
            background: #f1f5f9;
            font-size: 8.5pt;
            font-weight: bold;
            color: #475569;
            padding: 5px 12px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── Photos row ── */
        .photos-row { display: table; width: 100%; padding: 12px; }
        .photo-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 8px;
        }
        .photo-cell img {
            width: 80px;
            height: 90px;
            object-fit: cover;
            border: 1.5px solid #cbd5e1;
            border-radius: 4px;
        }
        .photo-placeholder {
            width: 80px;
            height: 90px;
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            border-radius: 4px;
            display: inline-block;
            line-height: 90px;
            font-size: 22pt;
            color: #cbd5e1;
        }
        .photo-label {
            font-size: 7.5pt;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* ── Signature row ── */
        .sig-row { display: table; width: 100%; padding: 10px 12px; }
        .sig-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 8px;
        }
        .sig-cell img {
            height: 40px;
            max-width: 120px;
            object-fit: contain;
            border-bottom: 1.5px solid #94a3b8;
            padding-bottom: 4px;
        }
        .sig-line {
            width: 120px;
            height: 40px;
            border-bottom: 1.5px solid #cbd5e1;
            display: inline-block;
        }
        .sig-label {
            font-size: 7.5pt;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* ── Declaration ── */
        .declaration {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 8pt;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 16px;
            background: #fafafa;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 7px 32px;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }
        .footer-inner { display: table; width: 100%; }
        .footer-left {
            display: table-cell;
            font-size: 7.5pt;
            color: #94a3b8;
            vertical-align: middle;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            font-size: 7.5pt;
            color: #94a3b8;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-logo">
            @if($school->logo)
                <img src="{{ public_path('storage/' . $school->logo) }}" alt="{{ $school->name }}">
            @else
                <div class="header-logo-placeholder">&#127979;</div>
            @endif
        </div>
        <div class="header-info">
            <div class="school-name">{{ $school->name }}</div>
            <div class="school-meta">
                {{ $school->address }}{{ $school->city ? ', ' . $school->city->name : '' }}{{ $school->state ? ', ' . $school->state->name : '' }}
                @if($school->pincode) — {{ $school->pincode }}@endif
                <br>
                @if($school->phone) Tel: {{ $school->phone }} @endif
                @if($school->email) &nbsp;|&nbsp; {{ $school->email }} @endif
            </div>
        </div>
        <div class="header-doc">
            <div class="doc-title">Registration Form</div>
            <div class="doc-no">No. {{ $studentRegistration->registration_no }}</div>
            <div class="doc-date">Issued: {{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- ── Hero Row ── --}}
    <div class="hero">
        <div class="hero-photo">
            @if($studentRegistration->student_photo)
                <img src="{{ public_path('storage/' . $studentRegistration->student_photo) }}" alt="Photo">
            @else
                <div class="hero-photo-placeholder">&#128100;</div>
            @endif
        </div>
        <div class="hero-info">
            <div class="hero-name">{{ $studentRegistration->full_name }}</div>
            <div class="hero-badges">
                <span class="badge badge-blue">{{ $studentRegistration->registration_no }}</span>
                <span class="badge badge-indigo">{{ $studentRegistration->class->name ?? 'N/A' }}</span>
                @php
                    $badgeClass = match($studentRegistration->admission_status->value) {
                        'admitted'  => 'badge-green',
                        'cancelled' => 'badge-red',
                        default     => 'badge-amber',
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $studentRegistration->admission_status->label() }}</span>
            </div>
            <div class="hero-quick">
                <div class="hero-quick-cell">
                    <div class="quick-label">Academic Year</div>
                    <div class="quick-value">{{ $studentRegistration->academicYear->name ?? 'N/A' }}</div>
                </div>
                <div class="hero-quick-cell">
                    <div class="quick-label">Registration Date</div>
                    <div class="quick-value">{{ $studentRegistration->registration_date?->format('d M Y') ?? 'N/A' }}</div>
                </div>
                <div class="hero-quick-cell">
                    <div class="quick-label">Date of Birth</div>
                    <div class="quick-value">{{ $studentRegistration->dob?->format('d M Y') ?? 'N/A' }}</div>
                </div>
                <div class="hero-quick-cell">
                    <div class="quick-label">Registration Fee</div>
                    <div class="quick-value">&#8377;{{ number_format($studentRegistration->registration_fee, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Student & Registration Info ── --}}
    <div class="section">
        <div class="section-header">Student Information</div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $studentRegistration->full_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">{{ $studentRegistration->dob?->format('d F Y') ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Gender</div>
                        <div class="info-value">{{ $studentRegistration->gender_label }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Blood Group</div>
                        <div class="info-value muted">{{ $studentRegistration->blood_group ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Religion</div>
                        <div class="info-value muted">{{ $studentRegistration->religion ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Category</div>
                        <div class="info-value muted">{{ $studentRegistration->category ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Mobile</div>
                        <div class="info-value">{{ $studentRegistration->mobile_no ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value muted">{{ $studentRegistration->email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nationality</div>
                        <div class="info-value muted">{{ $studentRegistration->nationality ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Aadhaar No</div>
                        <div class="info-value muted">{{ $studentRegistration->aadhar_no ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Applied Class</div>
                        <div class="info-value">{{ $studentRegistration->class->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Academic Year</div>
                        <div class="info-value">{{ $studentRegistration->academicYear->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Parent Information ── --}}
    <div class="section">
        <div class="section-header">Parent Information</div>
        <div class="section-body">
            <div class="info-grid">
                {{-- Father --}}
                <div class="info-col">
                    <div class="sub-header" style="margin: -10px -12px 10px; padding: 5px 12px;">Father</div>
                    <div class="info-row">
                        <div class="info-label">Name</div>
                        <div class="info-value">{{ trim($studentRegistration->father_name_prefix . ' ' . $studentRegistration->father_first_name . ' ' . $studentRegistration->father_last_name) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Mobile</div>
                        <div class="info-value">{{ $studentRegistration->father_mobile_no ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value muted">{{ $studentRegistration->father_email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Occupation</div>
                        <div class="info-value muted">{{ $studentRegistration->father_occupation ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Qualification</div>
                        <div class="info-value muted">{{ $studentRegistration->father_qualification ?? 'N/A' }}</div>
                    </div>
                </div>
                {{-- Mother --}}
                <div class="info-col">
                    <div class="sub-header" style="margin: -10px -12px 10px; padding: 5px 12px;">Mother</div>
                    <div class="info-row">
                        <div class="info-label">Name</div>
                        <div class="info-value">{{ trim($studentRegistration->mother_name_prefix . ' ' . $studentRegistration->mother_first_name . ' ' . $studentRegistration->mother_last_name) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Mobile</div>
                        <div class="info-value">{{ $studentRegistration->mother_mobile_no ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value muted">{{ $studentRegistration->mother_email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Occupation</div>
                        <div class="info-value muted">{{ $studentRegistration->mother_occupation ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Qualification</div>
                        <div class="info-value muted">{{ $studentRegistration->mother_qualification ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Address ── --}}
    <div class="section">
        <div class="section-header">Address</div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-col">
                    <div class="sub-header" style="margin: -10px -12px 10px; padding: 5px 12px;">Permanent Address</div>
                    <div class="info-row">
                        <div class="info-value">{{ $studentRegistration->permanent_address ?? '—' }}</div>
                    </div>
                    @if($studentRegistration->permanent_city || $studentRegistration->permanent_state)
                    <div class="info-row">
                        <div class="info-value muted">
                            {{ $studentRegistration->permanent_city }}{{ $studentRegistration->permanent_state ? ', ' . $studentRegistration->permanent_state : '' }}
                        </div>
                    </div>
                    @endif
                    @if($studentRegistration->permanent_pin)
                    <div class="info-row">
                        <div class="info-label">PIN</div>
                        <div class="info-value muted">{{ $studentRegistration->permanent_pin }}</div>
                    </div>
                    @endif
                </div>
                <div class="info-col">
                    <div class="sub-header" style="margin: -10px -12px 10px; padding: 5px 12px;">Correspondence Address</div>
                    @if($studentRegistration->correspondence_address)
                    <div class="info-row">
                        <div class="info-value">{{ $studentRegistration->correspondence_address }}</div>
                    </div>
                    @if($studentRegistration->correspondence_city || $studentRegistration->correspondence_state)
                    <div class="info-row">
                        <div class="info-value muted">
                            {{ $studentRegistration->correspondence_city }}{{ $studentRegistration->correspondence_state ? ', ' . $studentRegistration->correspondence_state : '' }}
                        </div>
                    </div>
                    @endif
                    @if($studentRegistration->correspondence_pin)
                    <div class="info-row">
                        <div class="info-label">PIN</div>
                        <div class="info-value muted">{{ $studentRegistration->correspondence_pin }}</div>
                    </div>
                    @endif
                    @else
                    <div class="info-row">
                        <div class="info-value muted" style="font-style: italic;">Same as permanent address</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Photographs ── --}}
    @if($studentRegistration->student_photo || $studentRegistration->father_photo || $studentRegistration->mother_photo)
    <div class="section">
        <div class="section-header">Photographs</div>
        <div class="section-body">
            <div class="photos-row">
                <div class="photo-cell">
                    @if($studentRegistration->student_photo)
                        <img src="{{ public_path('storage/' . $studentRegistration->student_photo) }}" alt="Student">
                    @else
                        <div class="photo-placeholder">&#128100;</div>
                    @endif
                    <div class="photo-label">Student</div>
                </div>
                <div class="photo-cell">
                    @if($studentRegistration->father_photo)
                        <img src="{{ public_path('storage/' . $studentRegistration->father_photo) }}" alt="Father">
                    @else
                        <div class="photo-placeholder">&#128100;</div>
                    @endif
                    <div class="photo-label">Father</div>
                </div>
                <div class="photo-cell">
                    @if($studentRegistration->mother_photo)
                        <img src="{{ public_path('storage/' . $studentRegistration->mother_photo) }}" alt="Mother">
                    @else
                        <div class="photo-placeholder">&#128100;</div>
                    @endif
                    <div class="photo-label">Mother</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Declaration & Signatures ── --}}
    <div class="declaration">
        I hereby declare that all the information provided in this registration form is true and correct to the best of my knowledge.
        I understand that any false information may result in cancellation of the registration.
    </div>

    <div class="section">
        <div class="section-header">Signatures</div>
        <div class="section-body">
            <div class="sig-row">
                <div class="sig-cell">
                    @if($studentRegistration->student_signature)
                        <img src="{{ public_path('storage/' . $studentRegistration->student_signature) }}" alt="Student Signature">
                    @else
                        <div class="sig-line"></div>
                    @endif
                    <div class="sig-label">Student</div>
                </div>
                <div class="sig-cell">
                    @if($studentRegistration->father_signature)
                        <img src="{{ public_path('storage/' . $studentRegistration->father_signature) }}" alt="Father Signature">
                    @else
                        <div class="sig-line"></div>
                    @endif
                    <div class="sig-label">Father</div>
                </div>
                <div class="sig-cell">
                    @if($studentRegistration->mother_signature)
                        <img src="{{ public_path('storage/' . $studentRegistration->mother_signature) }}" alt="Mother Signature">
                    @else
                        <div class="sig-line"></div>
                    @endif
                    <div class="sig-label">Mother</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Fixed Footer ── --}}
<div class="footer">
    <div class="footer-inner">
        <div class="footer-left">{{ $school->name }} &nbsp;|&nbsp; Registration No: {{ $studentRegistration->registration_no }}</div>
        <div class="footer-right">Generated: {{ now()->format('d M Y, h:i A') }}</div>
    </div>
</div>

</body>
</html>
