@extends('layouts.school')

@section('title', 'Academic Assessment Grid')

@section('content')
<div x-data="marksGridManager()">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('school.examination.marks.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
                <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $exam->examType->name }} Assessment</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 ml-0.5">
                    {{ $subject->name }} &bull; {{ $class->name }} &bull; {{ $exam->month }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <template x-if="hasChanges">
                <div class="px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-2 animate-pulse">
                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                    <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Unsaved Changes</span>
                </div>
            </template>
            <template x-if="!hasChanges">
                <div class="px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">All Persistent</span>
                </div>
            </template>
            <div class="bg-indigo-600 px-6 py-2 rounded-2xl shadow-lg shadow-indigo-100 border border-indigo-500/20 text-center">
                <span class="block text-[8px] font-black text-indigo-200 uppercase tracking-widest">Global Max Marks</span>
                <div class="flex items-center justify-center gap-2">
                    <input type="number" x-model="totalMarks" @input="validateAll()" 
                           class="bg-transparent border-none p-0 text-white font-black text-xl w-16 text-center focus:ring-0">
                </div>
            </div>
        </div>
    </div>

    <!-- The Grid -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <form @submit.prevent="saveAllMarks" method="POST" id="marksForm" class="p-0">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-20">Rank</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Student Identity</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-48">Score Obtained</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Observational Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($students as $index => $student)
                        @php $result = $results->get($student->id); @endphp
                        <tr class="group hover:bg-indigo-50/20 transition-colors duration-150">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <span class="text-xs font-black text-gray-300 group-hover:text-indigo-300 transition-colors">#{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-xs ring-2 ring-transparent group-hover:ring-indigo-100 group-hover:bg-white group-hover:text-indigo-600 transition-all">
                                        {{ substr($student->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">{{ $student->full_name }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">{{ $student->admission_no }}</div>
                                            <template x-if="scores['{{ $student->id }}'].isModified">
                                                <span class="text-[8px] font-black bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded uppercase tracking-tighter">Modified</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="relative w-32">
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        x-model="scores['{{ $student->id }}'].marks_obtained"
                                        @input="validateRow('{{ $student->id }}')"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:outline-none focus:ring-4 transition-all font-black text-gray-700 text-center"
                                        :class="scores['{{ $student->id }}'].invalid ? 'bg-red-50 border-red-200 text-red-600 ring-red-500/10' : 'group-hover:bg-white focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white'"
                                    >
                                    <template x-if="scores['{{ $student->id }}'].invalid">
                                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white animate-pulse"></div>
                                    </template>
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <input 
                                    type="text" 
                                    x-model="scores['{{ $student->id }}'].remarks"
                                    placeholder="Absent, Disqualified, etc."
                                    class="w-full px-4 py-2.5 bg-transparent border-transparent rounded-xl focus:outline-none focus:bg-white focus:border-gray-100 transition-all font-medium text-gray-500 placeholder:text-gray-300"
                                >
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Toolbar -->
            <div class="px-10 py-8 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Validated Grid</span>
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    :disabled="submitting || hasInvalid"
                    class="px-12 py-4 bg-gradient-to-r from-indigo-600 to-violet-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:from-indigo-700 hover:to-violet-800 transition-all active:scale-95 disabled:opacity-30 flex items-center gap-3 min-w-[240px] justify-center text-sm uppercase tracking-widest"
                >
                    <template x-if="submitting">
                        <span class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <i x-show="!submitting" class="fas fa-cloud-upload-alt"></i>
                    <span x-text="submitting ? 'Transmitting Data...' : 'Finalize Marks Registry'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('marksGridManager', () => ({
        submitting: false,
        totalMarks: 100,
        hasInvalid: false,
        scores: {
            @foreach($students as $student)
            @php $result = $results->get($student->id); @endphp
            '{{ $student->id }}': {
                student_id: '{{ $student->id }}',
                marks_obtained: '{{ $result ? $result->marks_obtained : '' }}',
                remarks: '{{ $result ? $result->remarks : '' }}',
                invalid: false,
                isModified: false
            },
            @endforeach
        },
        hasChanges: false,

        validateRow(studentId) {
            const score = parseFloat(this.scores[studentId].marks_obtained);
            const max = parseFloat(this.totalMarks);
            this.scores[studentId].invalid = !isNaN(score) && score > max;
            
            // Check for modification
            this.checkModification(studentId);
            this.checkGlobalValidity();
        },

        checkModification(studentId) {
            const current = this.scores[studentId];
            const original = this.originalScores[studentId];
            current.isModified = current.marks_obtained != original.marks_obtained || current.remarks != original.remarks;
            this.hasChanges = Object.values(this.scores).some(s => s.isModified);
        },

        originalScores: {
            @foreach($students as $student)
            @php $result = $results->get($student->id); @endphp
            '{{ $student->id }}': {
                marks_obtained: '{{ $result ? $result->marks_obtained : '' }}',
                remarks: '{{ $result ? $result->remarks : '' }}'
            },
            @endforeach
        },

        validateRow(studentId) {
            const score = parseFloat(this.scores[studentId].marks_obtained);
            const max = parseFloat(this.totalMarks);
            this.scores[studentId].invalid = !isNaN(score) && score > max;
            this.checkGlobalValidity();
        },

        validateAll() {
            Object.keys(this.scores).forEach(id => this.validateRow(id));
        },

        checkGlobalValidity() {
            this.hasInvalid = Object.values(this.scores).some(s => s.invalid);
        },

        async saveAllMarks() {
            if (this.hasInvalid) return;
            this.submitting = true;

            const payload = {
                exam_id: '{{ $exam->id }}',
                subject_id: '{{ $subject->id }}',
                class_id: '{{ $class->id }}',
                academic_year_id: '{{ $exam->academic_year_id }}',
                total_marks: this.totalMarks,
                marks: Object.values(this.scores)
            };

            try {
                const response = await fetch('{{ route('school.examination.marks.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: 'Registry updated successfully!' });
                    }
                    setTimeout(() => window.location.href = '{{ route('school.examination.marks.index') }}', 1000);
                } else {
                    throw new Error(result.message || 'Transmission failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>
@endpush
@endsection
