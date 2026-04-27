@extends('layouts.school')

@section('title', 'Academic Assessment Grid')

@section('content')
<div x-data="marksGridManager()">
    <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('school.examination.marks.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
                <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $exam->display_name }}</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 ml-0.5">
                    {{ $examSubject->resolved_name }} &bull; {{ $class->name }} &bull; {{ $exam->assessment_window }}
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
                <span class="block text-[8px] font-black text-indigo-200 uppercase tracking-widest">Configured Full Marks</span>
                <div class="text-white font-black text-xl">{{ number_format($fullMarks, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-indigo-50 overflow-hidden">
        <form @submit.prevent="saveAllMarks" method="POST" id="marksForm" class="p-0">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-20">Rank</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Student Identity</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-48 text-center">Score Obtained</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Observational Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($students as $index => $student)
                        @php $result = $results->get($student->id); @endphp
                        <tr class="group hover:bg-indigo-50/30 transition-all duration-200">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <span class="text-xs font-black text-slate-300 group-hover:text-indigo-400 transition-colors">#{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs group-hover:bg-white group-hover:border-indigo-100 group-hover:text-indigo-600 group-hover:shadow-sm transition-all duration-300">
                                        {{ substr($student->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 group-hover:text-indigo-900 transition-colors">{{ $student->full_name }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tight group-hover:text-slate-500 transition-colors">{{ $student->admission_no }}</div>
                                            <template x-if="scores['{{ $student->id }}'].isModified">
                                                <span class="text-[8px] font-black bg-indigo-50 text-indigo-500 px-2 py-0.5 rounded-lg border border-indigo-100 uppercase tracking-tighter">Modified</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="relative w-36 mx-auto">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $fullMarks }}"
                                        x-model="scores['{{ $student->id }}'].marks_obtained"
                                        @input="validateRow('{{ $student->id }}')"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl focus:outline-none focus:ring-4 transition-all font-black text-gray-700 text-center text-base"
                                        :class="scores['{{ $student->id }}'].invalid ? 'bg-rose-50 border-rose-200 text-rose-600 ring-rose-500/10' : 'group-hover:bg-white group-hover:border-indigo-200 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white focus:shadow-sm'"
                                    >
                                    <template x-if="scores['{{ $student->id }}'].invalid">
                                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 rounded-full border-2 border-white animate-pulse shadow-sm shadow-rose-200"></div>
                                    </template>
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="relative group/input">
                                    <input
                                        type="text"
                                        x-model="scores['{{ $student->id }}'].remarks"
                                        @input="checkModification('{{ $student->id }}')"
                                        placeholder="Add performance remarks..."
                                        class="w-full px-4 py-2.5 bg-transparent border border-transparent rounded-xl focus:outline-none focus:bg-white focus:border-slate-100 transition-all font-medium text-gray-500 placeholder:text-slate-300 group-hover:placeholder:text-slate-400"
                                    >
                                    <div class="absolute left-0 bottom-0 w-0 h-0.5 bg-indigo-500 group-focus-within/input:w-full transition-all duration-300 rounded-full opacity-0 group-focus-within/input:opacity-100"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Integrity Validated</span>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="submitting || hasInvalid"
                    class="btn-premium-primary min-w-[280px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200/50 py-4 uppercase tracking-[0.2em] text-xs font-black disabled:opacity-40 disabled:grayscale"
                >
                    <template x-if="submitting">
                        <span class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3"></span>
                    </template>
                    <i x-show="!submitting" class="fas fa-cloud-upload-alt mr-3 text-sm"></i>
                    <span x-text="submitting ? 'Transmitting...' : 'Finalize Marks Registry'"></span>
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
        fullMarks: {{ json_encode((float) $fullMarks) }},
        hasInvalid: false,
        hasChanges: false,
        scores: {
            @foreach($students as $student)
            @php $result = $results->get($student->id); @endphp
            '{{ $student->id }}': {
                student_id: '{{ $student->id }}',
                marks_obtained: '{{ $result ? $result->marks_obtained : '' }}',
                remarks: @js($result?->remarks ?? ''),
                invalid: false,
                isModified: false
            },
            @endforeach
        },
        originalScores: {
            @foreach($students as $student)
            @php $result = $results->get($student->id); @endphp
            '{{ $student->id }}': {
                marks_obtained: '{{ $result ? $result->marks_obtained : '' }}',
                remarks: @js($result?->remarks ?? '')
            },
            @endforeach
        },

        init() {
            this.validateAll();
        },

        validateRow(studentId) {
            const score = parseFloat(this.scores[studentId].marks_obtained);
            const max = parseFloat(this.fullMarks);
            this.scores[studentId].invalid = !isNaN(score) && score > max;
            this.checkModification(studentId);
            this.checkGlobalValidity();
        },

        validateAll() {
            Object.keys(this.scores).forEach(id => this.validateRow(id));
        },

        checkModification(studentId) {
            const current = this.scores[studentId];
            const original = this.originalScores[studentId];
            current.isModified = current.marks_obtained != original.marks_obtained || current.remarks != original.remarks;
            this.hasChanges = Object.values(this.scores).some(score => score.isModified);
        },

        checkGlobalValidity() {
            this.hasInvalid = Object.values(this.scores).some(score => score.invalid);
        },

        async saveAllMarks() {
            if (this.hasInvalid) return;
            this.submitting = true;

            const payload = {
                exam_id: '{{ $exam->id }}',
                exam_subject_id: '{{ $examSubject->id }}',
                marks: Object.values(this.scores),
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
                        window.Toast.fire({ icon: 'success', title: result.message || 'Registry updated successfully!' });
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
