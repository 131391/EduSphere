@extends('layouts.school')

@section('title', 'Tabulation Matrix - ' . $exam->examType->name)

@section('content')
<div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4 no-print">
    <div class="flex items-center gap-4">
        <a href="{{ route('school.examination.exams.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
            <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Consolidated Tabulation Sheet</h1>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 ml-0.5">
                {{ $exam->examType->name }} &bull; {{ $exam->class->name }} &bull; {{ $exam->academicYear->name }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button onclick="window.print()" class="px-6 py-2.5 bg-gray-900 text-white text-xs font-black rounded-xl hover:bg-black transition-all shadow-lg shadow-gray-200 flex items-center gap-2 uppercase tracking-widest">
            <i class="fas fa-print"></i>
            Generate Report
        </button>
    </div>
</div>

<!-- Tabulation Canvas -->
<div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden print:shadow-none print:border-none print:rounded-none">
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
            <thead>
                <!-- Top Header: Info -->
                <tr class="bg-indigo-600 text-white hidden print:table-row">
                    <th colspan="{{ 3 + $subjects->count() + 3 }}" class="px-8 py-6 text-center">
                        <div class="text-2xl font-black">{{ auth()->user()->school->name }}</div>
                        <div class="text-[10px] uppercase tracking-[0.3em] font-bold mt-1 opacity-70">Official Result Tabulation Register – {{ $exam->academicYear->name }}</div>
                    </th>
                </tr>
                
                <!-- Matrix Headers -->
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th rowspan="2" class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-r border-gray-100">Roll/SR</th>
                    <th rowspan="2" class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-r border-gray-100">Student Identity</th>
                    <th colspan="{{ $subjects->count() }}" class="px-4 py-3 text-center text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] bg-indigo-50/30 border-b border-indigo-100">Subject-wise Score Analysis</th>
                    <th rowspan="2" class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest border-l border-gray-100">Agg. Total</th>
                    <th rowspan="2" class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest border-l border-gray-100">Pct %</th>
                    <th rowspan="2" class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest border-l border-gray-100">Grade</th>
                </tr>
                <tr class="bg-gray-50/30 border-b border-gray-100">
                    @foreach($subjects as $subject)
                        <th class="px-3 py-3 text-center text-[9px] font-black text-gray-500 uppercase tracking-tighter border-r border-gray-100 min-w-[100px]">{{ $subject->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($students as $index => $student)
                @php 
                    $studentResults = $results->get($student->id) ?? collect();
                    $totalObtained = $studentResults->sum('marks_obtained');
                    $totalMax = $studentResults->sum('total_marks');
                    $avgPercentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
                    $finalGrade = app(\App\Services\School\Examination\ResultService::class)->calculateGrade($exam->school, $avgPercentage);
                @endphp
                <tr class="hover:bg-indigo-50/10 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-[11px] font-black text-gray-300 border-r border-gray-50 uppercase tracking-tighter">
                        #{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap border-r border-gray-50">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 uppercase tracking-tight">{{ $student->full_name }}</span>
                            <span class="text-[9px] font-medium text-gray-400 uppercase">{{ $student->admission_no }}</span>
                        </div>
                    </td>
                    
                    @foreach($subjects as $subject)
                    @php $res = $studentResults->where('subject_id', $subject->id)->first(); @endphp
                    <td class="px-3 py-4 text-center border-r border-gray-50">
                        @if($res)
                            <div class="flex flex-col items-center">
                                <span class="text-[11px] font-black {{ $res->marks_obtained < ($res->total_marks * 0.33) ? 'text-red-500' : 'text-gray-700' }}">
                                    {{ number_format($res->marks_obtained, 1) }}
                                </span>
                                <span class="text-[8px] font-bold text-gray-300">/ {{ number_format($res->total_marks, 0) }}</span>
                            </div>
                        @else
                            <span class="text-xs font-bold text-gray-200">--</span>
                        @endif
                    </td>
                    @endforeach

                    <td class="px-6 py-4 text-center whitespace-nowrap bg-gray-50/20 border-l border-gray-50">
                        <span class="text-xs font-black text-indigo-700">{{ number_format($totalObtained, 1) }}</span>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap border-l border-gray-50">
                        <div class="flex flex-col items-center">
                            <span class="text-xs font-black {{ $avgPercentage < 33 ? 'text-red-600' : 'text-indigo-600' }}">
                                {{ number_format($avgPercentage, 1) }}%
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap border-l border-gray-50">
                        <div class="flex justify-center">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black bg-gray-900 text-white shadow-sm">
                                {{ $finalGrade ?? 'F' }}
                            </span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Footer -->
    <div class="px-10 py-6 bg-indigo-600 text-white flex items-center justify-between no-print border-t border-indigo-700 mt-4">
        <div class="flex items-center gap-6">
            <div class="flex flex-col">
                <span class="text-[10px] font-black opacity-60 uppercase tracking-widest">Enrollment</span>
                <span class="text-lg font-black">{{ $students->count() }} Students</span>
            </div>
            <div class="w-px h-8 bg-white/20"></div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black opacity-60 uppercase tracking-widest">Assessments</span>
                <span class="text-lg font-black">{{ $subjects->count() }} Subjects</span>
            </div>
        </div>
        
        <div class="text-right">
            <p class="text-[9px] font-bold uppercase tracking-widest leading-none">Confidential Registry</p>
            <p class="text-[10px] opacity-70 mt-1">Generated by AntiGravity ERP Internal Core</p>
        </div>
    </div>
</div>

<style>
@media print {
    @page { size: landscape; margin: 1cm; }
    .no-print { display: none !important; }
    body { background: white !important; font-size: 8px !important; }
    .bg-indigo-600 { background-color: #4f46e5 !important; -webkit-print-color-adjust: exact; }
    .text-white { color: white !important; -webkit-print-color-adjust: exact; }
    table { width: 100% !important; border-collapse: collapse; }
    th, td { border: 1px solid #e2e8f0 !important; }
}
</style>
@endsection
