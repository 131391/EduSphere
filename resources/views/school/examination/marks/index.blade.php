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
    <form action="{{ route('school.examination.marks.enter') }}" method="GET" class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Exam Selection -->
            <div class="bg-white rounded-3xl shadow-sm border border-indigo-50 p-6 hover:border-indigo-200 transition-all group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-file-signature text-lg"></i>
                </div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">1. Assessment Type</label>
                <div class="relative">
                    <select name="exam_id" required class="w-full bg-gray-50/50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-bold text-gray-700 py-3.5 appearance-none pr-10">
                        <option value="">-- Targeted Exam --</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">
                                {{ $exam->examType->name }} ({{ $exam->month }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Class Selection -->
            <div class="bg-white rounded-3xl shadow-sm border border-violet-50 p-6 hover:border-violet-200 transition-all group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-violet-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
                <div class="w-12 h-12 rounded-2xl bg-violet-100 flex items-center justify-center text-violet-600 mb-6 group-hover:bg-violet-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">2. Target Audience</label>
                <div class="relative">
                    <select name="class_id" required class="w-full bg-gray-50/50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 focus:bg-white transition-all font-bold text-gray-700 py-3.5 appearance-none pr-10">
                        <option value="">-- Target Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Subject Selection -->
            <div class="bg-white rounded-3xl shadow-sm border border-emerald-50 p-6 hover:border-emerald-200 transition-all group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-book-open text-lg"></i>
                </div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">3. Assessment Subject</label>
                <div class="relative">
                    <select name="subject_id" required class="w-full bg-gray-50/50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all font-bold text-gray-700 py-3.5 appearance-none pr-10">
                        <option value="">-- Targeted Subject --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center pt-8">
            <button type="submit" class="px-12 py-4 bg-gradient-to-r from-indigo-600 to-violet-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100/50 hover:shadow-indigo-200/80 transition-all active:scale-95 flex items-center gap-4 group">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <i class="fas fa-th-large text-sm"></i>
                </div>
                <span class="text-sm uppercase tracking-widest italic">Initialize Registry Grid</span>
            </button>
        </div>
    </form>
</div>
@endsection
