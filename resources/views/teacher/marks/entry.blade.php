@extends('layouts.teacher')

@section('title', 'Record Marks - ' . $exam->display_name)

@section('content')
<div x-data="teacherMarksGrid()">
    <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.marks.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
                <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $exam->display_name }}</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                    {{ $examSubject->resolved_name }} &bull; {{ $class->name }} &bull; Full Marks: {{ number_format($fullMarks, 0) }}
                </p>
            </div>
        </div>
        <div x-show="lastSaved" x-cloak class="text-xs text-emerald-600 font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            Saved at <span x-text="lastSaved" class="font-mono"></span>
        </div>
    </div>

    {{-- Live progress bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-indigo-50 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center"><i class="fas fa-users"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</p>
                <p class="text-xl font-black text-gray-800">{{ $students->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center"><i class="fas fa-pen"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Entered</p>
                <p class="text-xl font-black text-emerald-600" x-text="enteredCount"></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-50 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center"><i class="fas fa-user-clock"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Absent</p>
                <p class="text-xl font-black text-amber-600" x-text="absentCount"></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-rose-50 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pending</p>
                <p class="text-xl font-black text-rose-600" x-text="pendingCount"></p>
            </div>
        </div>
    </div>

    <form @submit.prevent="saveMarks" method="POST" class="bg-white rounded-2xl shadow-sm border border-indigo-50 overflow-hidden">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">#</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Student</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-24">Absent</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-32">Marks</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-20">%</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $index => $student)
                        @php $result = $results->get($student->id); @endphp
                        <tr :class="rowClass('{{ $student->id }}')">
                            <td class="px-6 py-4 text-xs font-bold text-gray-300">#{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800">{{ $student->full_name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $student->admission_no }}{{ $student->roll_no ? ' · Roll '.$student->roll_no : '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="scores['{{ $student->id }}'].is_absent" class="rounded text-amber-600 focus:ring-amber-500">
                            </td>
                            <td class="px-6 py-4 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $fullMarks }}"
                                    x-model="scores['{{ $student->id }}'].marks_obtained"
                                    :disabled="scores['{{ $student->id }}'].is_absent"
                                    class="w-28 px-3 py-2 bg-slate-50 border rounded-xl text-center font-black text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:bg-gray-100 disabled:text-gray-400"
                                    :class="overLimit('{{ $student->id }}') ? 'border-rose-300 bg-rose-50' : 'border-slate-100'">
                                <p x-show="overLimit('{{ $student->id }}')" x-cloak class="text-[10px] text-rose-600 font-semibold mt-1">Exceeds {{ number_format($fullMarks, 0) }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold tabular-nums" :class="percentColor('{{ $student->id }}')" x-text="percentDisplay('{{ $student->id }}')"></span>
                            </td>
                            <td class="px-6 py-4">
                                <input type="text" x-model="scores['{{ $student->id }}'].remarks" placeholder="Remarks..." maxlength="500"
                                    class="w-full px-3 py-2 bg-transparent border border-transparent focus:bg-white focus:border-slate-200 rounded-xl text-sm focus:outline-none">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-4">
            <p class="text-xs text-gray-500">
                <span x-text="enteredCount + absentCount"></span> of {{ $students->count() }} students recorded.
                <span x-show="hasOverLimit" x-cloak class="text-rose-600 font-semibold ml-2"><i class="fas fa-exclamation-triangle mr-1"></i>Some marks exceed full marks.</span>
            </p>
            <button type="submit" :disabled="submitting || hasOverLimit" class="px-10 py-3 bg-indigo-600 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                <span x-text="submitting ? 'Saving...' : 'Save Marks'"></span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function teacherMarksGrid() {
    return {
        submitting: false,
        lastSaved: '',
        fullMarks: {{ (float) $fullMarks }},
        scores: {
            @foreach($students as $student)
                @php $result = $results->get($student->id); @endphp
                '{{ $student->id }}': {
                    student_id: '{{ $student->id }}',
                    marks_obtained: @json($result?->marks_obtained ?? ''),
                    is_absent: @json((bool)($result?->is_absent ?? false)),
                    remarks: @json($result?->remarks ?? '')
                },
            @endforeach
        },

        get enteredCount() {
            return Object.values(this.scores).filter(s => !s.is_absent && s.marks_obtained !== '' && s.marks_obtained !== null).length;
        },
        get absentCount() {
            return Object.values(this.scores).filter(s => s.is_absent).length;
        },
        get pendingCount() {
            return Object.values(this.scores).length - this.enteredCount - this.absentCount;
        },
        get hasOverLimit() {
            return Object.values(this.scores).some(s => !s.is_absent && parseFloat(s.marks_obtained) > this.fullMarks);
        },

        overLimit(id) {
            const s = this.scores[id];
            return !s.is_absent && s.marks_obtained !== '' && parseFloat(s.marks_obtained) > this.fullMarks;
        },
        percentDisplay(id) {
            const s = this.scores[id];
            if (s.is_absent) return 'AB';
            if (s.marks_obtained === '' || s.marks_obtained === null) return '—';
            const pct = (parseFloat(s.marks_obtained) / this.fullMarks) * 100;
            return isFinite(pct) ? pct.toFixed(0) + '%' : '—';
        },
        percentColor(id) {
            const s = this.scores[id];
            if (s.is_absent) return 'text-amber-600';
            if (s.marks_obtained === '' || s.marks_obtained === null) return 'text-gray-300';
            const pct = (parseFloat(s.marks_obtained) / this.fullMarks) * 100;
            if (pct >= 75) return 'text-emerald-600';
            if (pct >= 40) return 'text-indigo-600';
            return 'text-rose-600';
        },
        rowClass(id) {
            const s = this.scores[id];
            if (s.is_absent) return 'bg-amber-50/30';
            if (s.marks_obtained !== '' && s.marks_obtained !== null) return 'bg-emerald-50/20';
            return '';
        },

        async saveMarks() {
            if (this.hasOverLimit) {
                window.Toast?.fire({ icon: 'error', title: 'Some marks exceed the full marks. Fix them before saving.' });
                return;
            }
            this.submitting = true;
            try {
                const response = await fetch('{{ route('teacher.marks.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        exam_id: '{{ $exam->id }}',
                        exam_subject_id: '{{ $examSubject->id }}',
                        marks: Object.values(this.scores),
                    }),
                });
                const result = await response.json();
                if (response.ok) {
                    window.Toast?.fire({ icon: 'success', title: result.message || 'Marks saved.' });
                    this.lastSaved = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                } else {
                    window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, 'Save failed') });
                }
            } catch (e) {
                window.Toast?.fire({ icon: 'error', title: e.message || 'Save failed' });
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
