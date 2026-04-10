@extends('layouts.school')

@section('title', 'Mark Entry Gateway - Examination')

@section('content')
<div x-data="marksEntryGateway()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-edit text-xs"></i>
                    </div>
                    Marks & Assessment Gateway
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select examination parameters to start recording student performance</p>
            </div>
        </div>
    </div>

    <!-- Selection Matrix -->
    <form action="{{ route('school.examination.marks.enter') }}" method="GET" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Exam Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:border-indigo-200 transition-colors group">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-signature"></i>
                </div>
                <label class="block text-sm font-black text-gray-800 uppercase tracking-wider mb-2">1. Select Examination</label>
                <select name="exam_id" required class="w-full bg-gray-50 border-transparent rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-bold text-gray-700 py-3">
                    <option value="">-- Targeted Exam --</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">
                            {{ $exam->examType->name }} ({{ $exam->month }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Class Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:border-indigo-200 transition-colors group">
                <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-users"></i>
                </div>
                <label class="block text-sm font-black text-gray-800 uppercase tracking-wider mb-2">2. Select Class</label>
                <select name="class_id" required class="w-full bg-gray-50 border-transparent rounded-xl focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 focus:bg-white transition-all font-bold text-gray-700 py-3">
                    <option value="">-- Target Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:border-indigo-200 transition-colors group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-book-open"></i>
                </div>
                <label class="block text-sm font-black text-gray-800 uppercase tracking-wider mb-2">3. Select Subject</label>
                <select name="subject_id" required class="w-full bg-gray-50 border-transparent rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all font-bold text-gray-700 py-3">
                    <option value="">-- Targeted Subject --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-center pt-8">
            <button type="submit" class="px-12 py-4 bg-gradient-to-r from-indigo-600 to-violet-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:from-indigo-700 hover:to-violet-800 transition-all active:scale-95 flex items-center gap-3 group">
                <i class="fas fa-th-large group-hover:rotate-12 transition-transform"></i>
                Open Marks Entry Grid
            </button>
        </div>
    </form>
</div>
@endsection
