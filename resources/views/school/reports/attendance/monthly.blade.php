@extends('layouts.school')

@section('title', 'Monthly Attendance Report')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="card-title mb-4">Monthly Attendance Report</h4>
                    
                    <form action="{{ route('school.reports.attendance.monthly') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ ($classId == $class->id) ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="section_id" class="form-select" required>
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ ($sectionId == $section->id) ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Month</label>
                            <input type="month" name="month" class="form-control" value="{{ $monthYear }}" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($reportData)
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Attendance for {{ date('F Y', strtotime($monthYear)) }} 
                        ({{ $classes->find($classId)->name }} - {{ $sections->find($sectionId)->name }})
                    </h5>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0 attendance-report-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="sticky-col first-col">Student Name</th>
                                    @for($i = 1; $i <= $reportData['daysInMonth']; $i++)
                                        <th class="text-center">{{ $i }}</th>
                                    @endfor
                                    <th class="text-center bg-light">P</th>
                                    <th class="text-center bg-light">A</th>
                                    <th class="text-center bg-light">L</th>
                                    <th class="text-center bg-light">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['students'] as $student)
                                <tr>
                                    <td class="sticky-col first-col text-nowrap">{{ $student['name'] }}</td>
                                    @for($i = 1; $i <= $reportData['daysInMonth']; $i++)
                                        <td class="text-center">
                                            @php $status = $student['attendance'][$i] ?? '-'; @endphp
                                            @if($status == 'P')
                                                <span class="text-success small fw-bold">P</span>
                                            @elseif($status == 'A')
                                                <span class="text-danger small fw-bold">A</span>
                                            @elseif($status == 'L')
                                                <span class="text-warning small fw-bold">L</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="text-center bg-light fw-bold">{{ $student['present_count'] }}</td>
                                    <td class="text-center bg-light fw-bold">{{ $student['absent_count'] }}</td>
                                    <td class="text-center bg-light fw-bold">{{ $student['leave_count'] }}</td>
                                    <td class="text-center bg-light fw-bold">
                                        <span class="{{ $student['percentage'] < 75 ? 'text-danger' : 'text-success' }}">
                                            {{ round($student['percentage'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.attendance-report-table th, .attendance-report-table td {
    font-size: 0.75rem;
    padding: 0.25rem;
    min-width: 25px;
}
.sticky-col {
    position: sticky;
    background-color: white;
    z-index: 1;
}
.first-col {
    left: 0;
    min-width: 150px;
    border-right: 2px solid #dee2e6 !important;
}
@media print {
    .btn, form, label { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .table-responsive { overflow: visible !important; }
    body { background: white !important; }
    .container-fluid { padding: 0 !important; }
}
</style>
@endsection
