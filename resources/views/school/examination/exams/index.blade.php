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
    <x-modal name="exam-modal" maxWidth="2xl">
        <x-slot name="title">
            <div class="flex items-center gap-3 py-1">
                <i class="fas fa-calendar-check text-white/80 text-sm"></i>
                <span class="text-white font-bold tracking-tight">Schedule New Examination</span>
            </div>
        </x-slot>

        <form id="exam-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <!-- Form Body - Academic Year Standard -->
            <div class="space-y-6">
                <!-- Name Block -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Type Name <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="text" x-model="formData.name" @input="clearError('name')" placeholder="e.g., Mid-Term Assessment"
                            class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.name}">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:scale-110 transition-transform">
                            <i class="fas fa-tag text-[10px]"></i>
                        </div>
                    </div>
                    <template x-if="errors.name">
                        <p class="modal-error-message" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <!-- Guidance Notification Card -->
                <div class="mb-8 flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-layer-group text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 leading-tight">Classification Notice</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Categories help group <span class="text-indigo-600 italic">assessment subjects</span> into logical sessions like Mid-Term or Final Exams.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Exam Category -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Exam Category <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="formData.exam_type_id" @change="clearError('exam_type_id')" 
                            class="modal-input-premium appearance-none pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.exam_type_id}">
                            <option value="">-- Select Type --</option>
                            @foreach($examTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <template x-if="errors.exam_type_id">
                        <p class="modal-error-message" x-text="errors.exam_type_id[0]"></p>
                    </template>
                </div>

                <!-- Target Class -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="formData.class_id" @change="clearError('class_id')" 
                            class="modal-input-premium appearance-none pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <template x-if="errors.class_id">
                        <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                    </template>
                </div>
            </div>

            <!-- Month Selection -->
            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Assessment Window (Month) <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <select x-model="formData.month" @change="clearError('month')" 
                        class="modal-input-premium appearance-none pr-10 shadow-sm"
                        :class="{'border-red-500 ring-red-500/10': errors.month}">
                        <option value="">-- Select Month --</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}">{{ $month }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                </div>

                <!-- Timeline Notification Card -->
                <div class="mt-6 flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-clock text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 leading-tight">Timeline Locking Notification</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Scheduling create a <span class="text-indigo-600 italic underline decoration-indigo-100">centralized session</span>. Individual subject dates can be finalised in the assessment grid.
                        </p>
                    </div>
                </div>
                <template x-if="errors.month">
                    <p class="modal-error-message" x-text="errors.month[0]"></p>
                </template>
            </div>
            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'exam-modal')" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="exam-form" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Allocating...' : 'Lock Schedule'"></span>
                </button>
            </x-slot>
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
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        }
    }));
});
</script>
@endpush
@endsection
