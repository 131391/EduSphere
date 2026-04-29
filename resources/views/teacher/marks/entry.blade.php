@extends('layouts.teacher')

@section('title', 'Record Marks - ' . $exam->display_name)

@section('content')
<div x-data="teacherMarksGrid()">
    <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
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
    </div>

    <form @submit.prevent="saveMarks" method="POST" class="bg-white rounded-2xl shadow-sm border border-indigo-50 overflow-hidden">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">#</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Student</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-32">Absent</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-40">Marks</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $index => $student)
                        @php $result = $results->get($student->id); @endphp
                        <tr>
                            <td class="px-6 py-4 text-xs font-bold text-gray-300">#{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800">{{ $student->full_name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $student->admission_no }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="scores['{{ $student->id }}'].is_absent" class="rounded">
                            </td>
                            <td class="px-6 py-4 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $fullMarks }}"
                                    x-model="scores['{{ $student->id }}'].marks_obtained"
                                    :disabled="scores['{{ $student->id }}'].is_absent"
                                    class="w-32 px-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-center font-black text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:bg-gray-100 disabled:text-gray-400">
                            </td>
                            <td class="px-6 py-4">
                                <input type="text" x-model="scores['{{ $student->id }}'].remarks" placeholder="Remarks..."
                                    class="w-full px-3 py-2 bg-transparent border border-transparent focus:bg-white focus:border-slate-200 rounded-xl text-sm focus:outline-none">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-100 flex justify-end">
            <button type="submit" :disabled="submitting" class="px-10 py-3 bg-indigo-600 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 disabled:opacity-40 transition-all">
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
        scores: {
            @foreach($students as $student)
                @php $result = $results->get($student->id); @endphp
                '{{ $student->id }}': {
                    student_id: '{{ $student->id }}',
                    marks_obtained: '{{ $result?->marks_obtained ?? '' }}',
                    is_absent: @json($result?->is_absent ?? false),
                    remarks: @json($result?->remarks ?? '')
                },
            @endforeach
        },
        async saveMarks() {
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
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.href = '{{ route('teacher.marks.index') }}', 800);
                } else {
                    window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, 'Save failed') });
                }
            } catch (e) {
                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(e.response?.data || { message: e.message }, e.message || 'Save failed') });
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
