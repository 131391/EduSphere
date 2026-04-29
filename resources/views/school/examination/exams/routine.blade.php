@extends('layouts.school')

@section('title', 'Exam Routine - ' . $exam->display_name)

@section('content')
    <div x-data="routineManager({
        examId: {{ $exam->id }},
        initialRoutine: @js($examSubjects->map(fn($s) => [
            'id' => $s->id,
            'subject_name' => $s->resolved_name,
            'exam_date' => $s->exam_date?->format('Y-m-d') ?? '',
            'start_time' => $s->start_time?->format('H:i') ?? '',
            'end_time' => $s->end_time?->format('H:i') ?? '',
            'room_no' => $s->room_no ?? ''
        ]))
    })" class="space-y-6">

        <!-- Header Section -->
        <x-page-header :title="'Exam Timetable: ' . $exam->display_name" 
            :description="'Schedule dates, timings, and rooms for ' . $exam->class->name . ' (' . $exam->examType->name . ')'" 
            icon="fas fa-calendar-alt">
            <div class="flex items-center gap-3">
                <a href="{{ route('school.examination.exams.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-chevron-left mr-2 text-xs opacity-50"></i>
                    Back to Exams
                </a>
                <button @click="saveRoutine()" :disabled="submitting"
                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="submitting">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                    </template>
                    <i x-show="!submitting" class="fas fa-save mr-2"></i>
                    <span x-text="submitting ? 'Saving...' : 'Save Routine'"></span>
                </button>
            </div>
        </x-page-header>

        <!-- Routine Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Start Time</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">End Time</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Room No.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="(item, index) in routine" :key="item.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                                            <span x-text="index + 1"></span>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="item.subject_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="date" x-model="item.exam_date" 
                                        class="w-full bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 transition-all dark:text-gray-200 min-w-[150px]">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="time" x-model="item.start_time" 
                                        class="w-full bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 transition-all dark:text-gray-200 min-w-[120px]">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="time" x-model="item.end_time" 
                                        class="w-full bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 transition-all dark:text-gray-200 min-w-[120px]">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" x-model="item.room_no" placeholder="Room/Hall"
                                        class="w-full bg-gray-50 dark:bg-gray-900 border-0 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 transition-all dark:text-gray-200 min-w-[100px]">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest italic">
                    <i class="fas fa-info-circle mr-1"></i> Timings are used for student hall tickets and routine posters.
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function routineManager(config) {
            return {
                examId: config.examId,
                routine: config.initialRoutine,
                submitting: false,

                async saveRoutine() {
                    this.submitting = true;
                    try {
                        const response = await fetch(`{{ route('school.examination.exams.update-routine', $exam->id) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ routine: this.routine })
                        });

                        const result = await response.json();
                        if (response.ok) {
                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                        } else {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: window.resolveApiMessage(result, '') });
                        }
                    } catch (e) {
                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Connection error' });
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
