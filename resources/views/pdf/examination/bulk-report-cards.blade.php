<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Report Cards - {{ $exam->display_name }}</title>
    <style>
        .page-break { page-break-after: always; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .container { padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .school-name { font-size: 24px; font-weight: bold; color: #4f46e5; text-transform: uppercase; margin: 0; }
        .school-info { font-size: 12px; color: #666; margin-top: 5px; }
        .report-title { font-size: 18px; font-weight: bold; margin-top: 20px; text-decoration: underline; }
        
        .student-box { width: 100%; margin-bottom: 30px; }
        .student-info { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 5px 0; font-size: 13px; }
        .label { font-weight: bold; color: #666; width: 120px; }
        
        .marks-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .marks-table th { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b; }
        .marks-table td { border: 1px solid #e2e8f0; padding: 10px; font-size: 13px; }
        .subject-name { font-weight: bold; }
        
        .summary-box { width: 100%; margin-top: 20px; }
        .summary-table { width: 300px; margin-left: auto; border-collapse: collapse; }
        .summary-table td { padding: 8px; border: 1px solid #e2e8f0; font-size: 14px; }
        .summary-label { font-weight: bold; background-color: #f8fafc; width: 150px; }
        .summary-value { text-align: center; font-weight: bold; color: #4f46e5; }
        
        .footer { margin-top: 80px; width: 100%; }
        .signature-row { width: 100%; }
        .signature-box { text-align: center; width: 33%; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #333; width: 80%; margin: 0 auto 5px auto; }
        .signature-label { font-size: 12px; font-weight: bold; color: #666; }
        
        .watermark { position: absolute; top: 40%; left: 15%; font-size: 80px; color: rgba(226, 232, 240, 0.4); transform: rotate(-45deg); z-index: -1; pointer-events: none; }
    </style>
</head>
<body>
    @foreach($tabulation as $studentId => $data)
        @php $student = $data['student']; @endphp
        <div class="container {{ !$loop->last ? 'page-break' : '' }}">
            @if($exam->status->value === 5)
                <div class="watermark">OFFICIAL COPY</div>
            @else
                <div class="watermark" style="color: rgba(254, 226, 226, 0.5)">PROVISIONAL</div>
            @endif

            <div class="header">
                <h1 class="school-name">{{ $school->name }}</h1>
                <div class="school-info">
                    {{ $school->address }}<br>
                    Contact: {{ $school->phone ?? 'N/A' }} | Email: {{ $school->email ?? 'N/A' }}
                </div>
                <div class="report-title">ACADEMIC PROGRESS REPORT</div>
            </div>

            <div class="student-box">
                <table class="student-info">
                    <tr>
                        <td class="label">Student Name:</td>
                        <td style="font-weight: bold; font-size: 15px;">{{ $student->full_name }}</td>
                        <td class="label">Roll No:</td>
                        <td>{{ $student->roll_no ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Class:</td>
                        <td>{{ $exam->class->name }}</td>
                        <td class="label">Exam Title:</td>
                        <td>{{ $exam->display_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Academic Year:</td>
                        <td>{{ $exam->academicYear->name }}</td>
                        <td class="label">Report Date:</td>
                        <td>{{ date('d M, Y') }}</td>
                    </tr>
                </table>
            </div>

            <table class="marks-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>Subject</th>
                        <th style="text-align: center; width: 100px;">Full Marks</th>
                        <th style="text-align: center; width: 100px;">Obtained</th>
                        <th style="text-align: center; width: 80px;">Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($examSubjects as $index => $es)
                        @php $result = $data['subjects'][$es->id] ?? null; @endphp
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td class="subject-name">{{ $es->resolved_name }}</td>
                            <td style="text-align: center;">{{ $es->full_marks }}</td>
                            <td style="text-align: center; font-weight: bold;">
                                @if($result)
                                    {{ $result->is_absent ? 'ABS' : $result->marks_obtained }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #4f46e5;">
                                {{ $result && !$result->is_absent ? $result->grade : '-' }}
                            </td>
                            <td style="font-size: 11px; color: #666;">
                                {{ $result ? $result->remarks : '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary-box">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Total Obtained:</td>
                        <td class="summary-value">{{ $data['total_obtained'] }} / {{ $data['total_max'] }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Percentage:</td>
                        <td class="summary-value">{{ $data['percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Overall Grade:</td>
                        <td class="summary-value" style="font-size: 18px;">{{ $data['grade'] ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                <table class="signature-row">
                    <tr>
                        <td class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Class Teacher</div>
                        </td>
                        <td class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Exam Coordinator</div>
                        </td>
                        <td class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Principal / Headmaster</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>
</html>
