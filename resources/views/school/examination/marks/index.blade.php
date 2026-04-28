@extends('layouts.school')

@section('title', 'Mark Entry Gateway - Examination')

@section('content')
@php
    $examCatalog = $exams
        ->filter(fn ($exam) => $exam->isMarkEntryAllowed())
        ->map(function ($exam) {
            return [
                'id' => $exam->id,
                'label' => $exam->display_name,
                'class_name' => $exam->class?->name ?? 'N/A',
                'assessment_window' => $exam->assessment_window,
                'status' => $exam->status->label(),
                'subjects' => $exam->examSubjects->map(fn ($examSubject) => [
                    'id' => $examSubject->id,
                    'name' => $examSubject->resolved_name,
                    'full_marks' => $examSubject->full_marks,
                ])->values()->all(),
            ];
        })->values();
@endphp

<div x-data="marksEntryGateway(@js($examCatalog))">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-edit text-xs"></i>
                    </div>
                    Marks & Assessment Gateway
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose an exam schedule first, then record marks only against the subject snapshot stored for that exam.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('school.examination.marks.entry') }}" method="GET" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-indigo-50 dark:border-gray-700 p-6 hover:border-indigo-200 dark:hover:border-indigo-500 transition-all group relative overflow-hidden lg:col-span-2">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-file-signature text-lg"></i>
                </div>
                <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-3">1. Exam Schedule</label>
                <div class="relative">
                    <select name="exam_id" x-model="selectedExamId" @change="handleExamChange()" required class="no-select2 w-full bg-gray-50/50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 dark:focus:bg-gray-700 transition-all font-bold text-gray-700 py-3.5 appearance-none pr-10">
                        <option value="">-- Select Scheduled Exam --</option>
                        <template x-for="exam in exams" :key="exam.id">
                            <option :value="String(exam.id)" x-text="`${exam.label} • ${exam.class_name}`"></option>
                        </template>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-violet-50 dark:border-gray-700 p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-violet-50/30 rounded-full -mr-16 -mt-16"></div>
                <div class="w-12 h-12 rounded-2xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center text-violet-600 dark:text-violet-400 mb-6">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-3">Class Snapshot</label>
                <template x-if="selectedExam">
                    <div class="space-y-2">
                        <p class="text-lg font-black text-gray-800 dark:text-gray-100" x-text="selectedExam.class_name"></p>
                        <p class="text-xs font-bold uppercase tracking-widest text-violet-500 dark:text-violet-400" x-text="selectedExam.assessment_window"></p>
                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500" x-text="'Status: ' + selectedExam.status"></p>
                    </div>
                </template>
                <template x-if="!selectedExam">
                    <p class="text-sm text-gray-400 dark:text-gray-500 leading-relaxed">Select an exam to lock onto its class and subject snapshot.</p>
                </template>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-emerald-50 dark:border-gray-700 p-6 hover:border-emerald-200 dark:hover:border-emerald-500 transition-all group relative overflow-hidden lg:col-span-3">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/30 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <i class="fas fa-book-open text-lg"></i>
                </div>
                <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-3">2. Exam Subject</label>
                <div class="relative">
                    <select name="exam_subject_id" x-model="selectedExamSubjectId" required :disabled="availableSubjects.length === 0" class="no-select2 w-full bg-gray-50/50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 border-gray-100 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 dark:focus:bg-gray-700 transition-all font-bold text-gray-700 py-3.5 appearance-none pr-10 disabled:opacity-60 disabled:cursor-not-allowed">
                        <option value="">-- Select Exam Subject --</option>
                        <template x-for="subject in availableSubjects" :key="subject.id">
                            <option :value="String(subject.id)" x-text="`${subject.name} • ${subject.full_marks} marks`"></option>
                        </template>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <template x-if="selectedExam && availableSubjects.length === 0">
                    <p class="mt-3 text-sm text-rose-500 font-semibold">This exam has no subject snapshot yet. Reopen the schedule after assigning class subjects.</p>
                </template>
            </div>
        </div>

        <div class="flex items-center justify-center pt-8">
            <button type="submit" :disabled="!selectedExamId || !selectedExamSubjectId" class="px-12 py-4 bg-gradient-to-r from-indigo-600 to-violet-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100/50 hover:shadow-indigo-200/80 transition-all active:scale-95 flex items-center gap-4 group disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <i class="fas fa-th-large text-sm"></i>
                </div>
                <span class="text-sm uppercase tracking-widest italic">Initialize Registry Grid</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function marksEntryGateway(exams) {
        return {
            exams,
            selectedExamId: '',
            selectedExamSubjectId: '',
            availableSubjects: [],
            selectedExam: null,

            handleExamChange() {
                this.selectedExam = this.exams.find(exam => String(exam.id) === this.selectedExamId) || null;
                this.availableSubjects = this.selectedExam ? this.selectedExam.subjects : [];
                this.selectedExamSubjectId = '';
            }
        };
    }
</script>
@endpush
@endsection
