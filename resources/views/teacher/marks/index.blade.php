@extends('layouts.teacher')

@section('title', 'My Mark Entry Assignments')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-indigo-100/50">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                <i class="fas fa-edit text-xs"></i>
            </div>
            Mark Entry Assignments
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Subjects assigned to you across active exams. Pick one to record marks.</p>
    </div>

    @if($assignments->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center text-gray-500">
            <i class="fas fa-clipboard-list text-3xl text-gray-300 mb-3"></i>
            <p class="font-bold">No mark-entry assignments yet.</p>
            <p class="text-xs mt-2">Ask your school admin to assign you to an exam subject from the Examination Schedule.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($assignments as $assignment)
                <a href="{{ route('teacher.marks.entry', ['exam_id' => $assignment->exam_id, 'exam_subject_id' => $assignment->id]) }}"
                   class="block bg-white rounded-2xl shadow-sm border border-indigo-50 p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                    <div class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-2">
                        {{ $assignment->exam->examType?->name ?? 'Exam' }}
                    </div>
                    <div class="text-lg font-black text-gray-800">{{ $assignment->exam->display_name }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ $assignment->exam->class?->name ?? 'N/A' }}</div>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700">{{ $assignment->resolved_name }}</span>
                        <span class="text-[10px] font-black bg-indigo-50 text-indigo-700 px-2 py-1 rounded-lg uppercase tracking-wider">
                            / {{ $assignment->full_marks }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
