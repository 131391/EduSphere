@extends('layouts.school')

@section('title', 'Result Tabulation - ' . $exam->examType->name)

@section('content')
<div class="mb-6 flex items-center justify-between no-print">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Result Tabulation</h1>
        <p class="text-gray-600">
            Exam: <span class="font-bold">{{ $exam->examType->name }}</span> | 
            Class: <span class="font-bold">{{ $exam->class->name }}</span> | 
            Year: <span class="font-bold">{{ $exam->academicYear->name }}</span>
        </p>
    </div>
    <div class="flex space-x-3">
        <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-black">
            <i class="fas fa-print mr-2"></i> Print Sheet
        </button>
        <a href="{{ route('school.examination.exams.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
                <tr>
                    <th rowspan="2" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r">SR</th>
                    <th rowspan="2" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r">Admission No</th>
                    <th rowspan="2" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r">Student Name</th>
                    <th colspan="{{ $subjects->count() }}" class="px-6 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b">Subjects</th>
                    <th rowspan="2" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-l">Total</th>
                    <th rowspan="2" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-l">Per%</th>
                    <th rowspan="2" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-l border-r">Grade</th>
                    <th rowspan="2" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider no-print">Action</th>
                </tr>
                <tr class="bg-gray-50">
                    @foreach($subjects as $subject)
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase border-r">{{ $subject->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($students as $index => $student)
                @php 
                    $studentResults = $results->get($student->id) ?? collect();
                    $totalObtained = $studentResults->sum('marks_obtained');
                    $totalMax = $studentResults->sum('total_marks');
                    $avgPercentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 border-r">{{ $student->admission_no }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r">{{ $student->full_name }}</td>
                    
                    @foreach($subjects as $subject)
                    @php $res = $studentResults->where('subject_id', $subject->id)->first(); @endphp
                    <td class="px-4 py-4 text-center text-sm border-r {{ $res && $res->marks_obtained < ($res->total_marks * 0.33) ? 'text-red-600 font-bold' : '' }}">
                        {{ $res ? number_format($res->marks_obtained, 1) : '-' }}
                    </td>
                    @endforeach

                    <td class="px-6 py-4 text-center text-sm font-bold text-gray-900 border-l">{{ number_format($totalObtained, 1) }}</td>
                    <td class="px-6 py-4 text-center text-sm font-bold {{ $avgPercentage < 33 ? 'text-red-600' : 'text-blue-600' }} border-l">{{ number_format($avgPercentage, 1) }}%</td>
                    <td class="px-6 py-4 text-center text-sm font-black border-l border-r text-gray-800">
                        @php
                            $finalGrade = app(\App\Services\School\Examination\ResultService::class)->calculateGrade($exam->school, $avgPercentage);
                        @endphp
                        {{ $finalGrade ?? 'F' }}
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium no-print">
                        <button class="text-blue-600 hover:text-blue-900 mr-2"><i class="fas fa-file-pdf"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { font-size: 10px; }
    table { width: 100% !important; border-collapse: collapse; }
    th, td { border: 1px solid #e2e8f0 !important; }
}
</style>
@endsection
