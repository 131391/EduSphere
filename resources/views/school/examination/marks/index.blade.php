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
            <!-- Exam Selection -->
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

            <!-- Summary Box -->
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

            <!-- Subject Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-emerald-50 dark:border-gray-700 p-6 hover:border-emerald-200 dark:hover:border-emerald-500 transition-all group relative overflow-hidden lg:col-span-2">
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

            <!-- Bulk Operations -->
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl shadow-lg p-6 text-white relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-125"></div>
                <div class="relative z-10 space-y-4">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-cloud-upload-alt text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-widest italic">Bulk Operations</h4>
                        <p class="text-[10px] text-white/70 font-medium mt-1 leading-relaxed">Import marks using a pre-filled CSV template for faster data entry.</p>
                    </div>
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="button" @click="downloadTemplate()" :disabled="!selectedExamId || !selectedExamSubjectId"
                            class="w-full py-2 bg-white/20 hover:bg-white text-white hover:text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all disabled:opacity-50">
                            Download Template
                        </button>
                        <button type="button" @click="$dispatch('open-modal', 'import-modal')" :disabled="!selectedExamId || !selectedExamSubjectId"
                            class="w-full py-2 bg-indigo-900/40 hover:bg-indigo-900/60 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all disabled:opacity-50">
                            Import CSV
                        </button>
                    </div>
                </div>
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

    <!-- Import Modal -->
    <x-modal name="import-modal" title="Import Marks via CSV" maxWidth="lg">
        <form @submit.prevent="importMarks()" class="p-1">
            <div class="space-y-6 mb-8">
                <div class="flex items-start gap-4 bg-amber-50 border border-amber-100 p-4 rounded-2xl">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-1"></i>
                    <p class="text-[11px] text-amber-700 font-bold uppercase leading-relaxed">
                        Ensure you are using the template downloaded for the specific exam and subject selected. The Student IDs must match the current class snapshot.
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="modal-label-premium">Upload CSV File</label>
                    <div class="relative group">
                        <input type="file" @change="file = $event.target.files[0]" accept=".csv"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-black file:uppercase file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer">
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'import-modal')" class="btn-premium-cancel">Discard</button>
                <button type="submit" :disabled="!file || submitting" class="btn-premium-primary min-w-[140px]">
                    <span x-show="!submitting">Confirm Import</span>
                    <i x-show="submitting" class="fas fa-spinner fa-spin"></i>
                </button>
            </x-slot>
        </form>
    </x-modal>
</div>
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
            file: null,
            submitting: false,

            handleExamChange() {
                this.selectedExam = this.exams.find(exam => String(exam.id) === this.selectedExamId) || null;
                this.availableSubjects = this.selectedExam ? this.selectedExam.subjects : [];
                this.selectedExamSubjectId = '';
            },

            downloadTemplate() {
                if (!this.selectedExamId || !this.selectedExamSubjectId) return;
                const url = `{{ route('school.examination.marks.template') }}?exam_id=${this.selectedExamId}&exam_subject_id=${this.selectedExamSubjectId}`;
                window.location.href = url;
            },

            async importMarks() {
                if (!this.file || this.submitting) return;
                this.submitting = true;

                const formData = new FormData();
                formData.append('file', this.file);
                formData.append('exam_id', this.selectedExamId);
                formData.append('exam_subject_id', this.selectedExamSubjectId);

                try {
                    const response = await fetch(`{{ route('school.examination.marks.import') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();
                    if (response.ok) {
                        if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                        this.$dispatch('close-modal', 'import-modal');
                        this.file = null;
                    } else {
                        if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Import failed' });
                    }
                } catch (e) {
                    if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Connection error' });
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
</script>
@endpush
@endsection
