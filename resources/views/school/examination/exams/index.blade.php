@extends('layouts.school')

@section('title', 'Exam Scheduling - Examination')

@section('content')
<div x-data="examManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-calendar-alt text-xs"></i>
                    </div>
                    Examination Schedule
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Plan and coordinate upcoming academic assessments across classes</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Schedule Exam
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'details',
                'label' => 'EXAM PARTICULARS',
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                            <i class="fas fa-file-signature text-sm"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">' . e($row->examType->name) . '</div>
                            <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-tighter">' . e($row->month) . ' assessment</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'class',
                'label' => 'TARGET AUDIENCE',
                'render' => function($row) {
                    return '
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-700">Class: ' . e($row->class->name) . '</span>
                        <span class="text-[10px] text-gray-400 font-medium tracking-tight">AY: ' . e($row->academicYear->name) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'STATUS',
                'render' => function($row) {
                    $status = $row->status->name ?? 'Scheduled';
                    $color = match($status) {
                        'Scheduled' => 'indigo',
                        'In-Progress' => 'amber',
                        'Completed' => 'emerald',
                        'Published' => 'violet',
                        default => 'gray'
                    };
                    return '<span class="px-2.5 py-1 bg-'.$color.'-50 text-'.$color.'-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-'.$color.'-100">'.$status.'</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'icon' => 'fas fa-table',
                'class' => 'text-emerald-600 hover:text-emerald-800 bg-emerald-50 p-2 rounded-lg transition-colors mr-1',
                'url' => fn($row) => route('school.examination.exams.tabulate', $row),
                'title' => 'Tabulation Sheet',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-exam', { detail: { id: " . $row->id . ", name: '" . addslashes($row->examType->name) . " - " . addslashes($row->class->name) . "' } }))";
                },
                'title' => 'Remove Schedule',
            ],
        ];
    @endphp

    <div x-on:open-delete-exam.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$exams"
            :actions="$tableActions"
            empty-message="No exams scheduled for the current session"
            empty-icon="fas fa-calendar-times"
        >
            Active Examination Roster
        </x-data-table>
    </div>

    <!-- Schedule Exam Modal -->
    <x-modal name="exam-modal" alpineTitle="'Schedule New Examination'" maxWidth="2xl">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <div class="px-8 py-8 space-y-6">
                <!-- Group 1: Exam & Class -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Exam Category <span class="text-red-500">*</span></label>
                        <select name="exam_type_id" x-model="formData.exam_type_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all font-medium text-gray-700 appearance-none shadow-sm">
                            <option value="">-- Select Type --</option>
                            @foreach($examTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Target Class <span class="text-red-500">*</span></label>
                        <select name="class_id" x-model="formData.class_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all font-medium text-gray-700 appearance-none shadow-sm">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Group 2: Period/Month -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Assessment Window (Month) <span class="text-red-500">*</span></label>
                    <select name="month" x-model="formData.month" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all font-medium text-gray-700 appearance-none shadow-sm">
                        <option value="">-- Select Month --</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}">{{ $month }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 rounded-xl transition-all duration-200"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    :disabled="submitting"
                    class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 shadow-lg shadow-indigo-200 flex items-center justify-center min-w-[160px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Finalizing...' : 'Lock Schedule'"></span>
                </button>
            </div>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('examManagement', () => ({
        submitting: false,
        errors: {},
        formData: {
            exam_type_id: '',
            class_id: '',
            month: ''
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            try {
                const response = await fetch('{{ route('school.examination.exams.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Scheduling failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            } finally {
                this.submitting = false;
            }
        },

        openAddModal() {
            this.errors = {};
            this.formData = { exam_type_id: '', class_id: '', month: '' };
            this.$dispatch('open-modal', 'exam-modal');
        },

        async confirmDelete(exam) {
            if (window.confirm(`Are you sure you want to remove the schedule for "${exam.name}"?`)) {
                try {
                    const response = await fetch(`/school/examination/exams/${exam.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });
                    
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Operation failed');
                    }
                } catch (error) {
                    alert('An error occurred');
                }
            }
        },

        closeModal() {
            this.$dispatch('close-modal', 'exam-modal');
        }
    }));
});
</script>
@endpush
@endsection
