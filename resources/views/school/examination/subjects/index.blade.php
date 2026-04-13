@extends('layouts.school')

@section('title', 'Add Subject - Examination')

@section('content')
<div class="space-y-6" x-data="subjectManagement">


    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-book-reader text-xs"></i>
                    </div>
                    Subject Assignment
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign subjects to classes and define assessment parameters</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Assign New Subject
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'render' => function($row) use ($classSubjects) {
                    static $index = 0;
                    $srNo = $classSubjects->firstItem() + $index++;
                    return '<div class="flex items-center gap-3">' .
                           '<div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-[10px]">' . $srNo . '</div>' .
                           '</div>';
                }
            ],
            [
                'key' => 'class_name',
                'label' => 'TARGET CLASS',
                'sortable' => true,
                'render' => fn($row) => '<div class="flex items-center gap-3"><div class="w-7 h-7 rounded-md bg-slate-50 flex items-center justify-center text-slate-400"><i class="fas fa-chalkboard text-[10px]"></i></div><span class="font-bold text-gray-700">' . e($row->class_name) . '</span></div>'
            ],
            [
                'key' => 'subject_name',
                'label' => 'ASSIGNED SUBJECT',
                'sortable' => true,
                'render' => fn($row) => '<div class="flex items-center gap-3"><div class="w-7 h-7 rounded-md bg-indigo-50 flex items-center justify-center text-indigo-400"><i class="fas fa-book text-[10px]"></i></div><span class="font-bold text-indigo-600">' . e($row->subject_name) . '</span></div>'
            ],
            [
                'key' => 'full_marks',
                'label' => 'FULL MARKS',
                'sortable' => true,
                'render' => fn($row) => '<span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-black border border-emerald-100 shadow-sm">' . e($row->full_marks) . '</span>'
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-trash-alt',
                'class' => 'text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('delete-subject', { detail: " . $row->id . " }))";
                },
                'title' => 'Remove Assignment',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$classSubjects"
        :actions="$tableActions"
        empty-message="No subjects assigned to classes yet"
        empty-icon="fas fa-book-open"
    >
        Assigned Subjects List
    </x-data-table>

    <!-- Add Subject Modal -->
    <x-modal name="exam-subject-modal" alpineTitle="'Assign New Subject Configuration'" maxWidth="2xl">
        <form id="subject-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <!-- Form Body - Using the exact structure from Academic Years -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Target Class -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="formData.class_id" @change="clearError('class_id')" 
                            class="modal-input-premium appearance-none pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                            <option value="">Choose Class</option>
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

                <!-- Subject Name -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Subject Name <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="formData.subject_id" @change="clearError('subject_id')" 
                            class="modal-input-premium appearance-none pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.subject_id}">
                            @if(count($subjects) > 0)
                                <option value="">Choose Subject</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            @else
                                <option value="">No subjects defined in Master</option>
                            @endif
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    @if(count($subjects) == 0)
                        <div class="flex items-center gap-1.5 mt-2 px-0.5">
                            <i class="fas fa-exclamation-triangle text-[9px] text-amber-500"></i>
                            <span class="text-[9px] font-bold text-amber-600 uppercase tracking-widest opacity-80">Action Required: Define subjects first.</span>
                        </div>
                    @endif
                    <template x-if="errors.subject_id">
                        <p class="modal-error-message" x-text="errors.subject_id[0]"></p>
                    </template>
                </div>
            </div>

            <!-- Full Marks -->
            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Assessment Full Marks <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input type="number" x-model="formData.full_marks" @input="clearError('full_marks')" placeholder="e.g., 100"
                        class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.full_marks}">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-indigo-500 pointer-events-none">
                        <i class="fas fa-star text-sm"></i>
                    </div>
                </div>
                <!-- Infographic Block sitting exactly like Academic Year toggle -->
                <div class="mt-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-lightbulb text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 leading-tight">Configuration Scope Notification</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            This setting applies to the <span class="text-indigo-600 italic">current active session</span>. Parameters can be adjusted in the assessments portal later.
                        </p>
                    </div>
                </div>
                <template x-if="errors.full_marks">
                    <p class="modal-error-message" x-text="errors.full_marks[0]"></p>
                </template>
            </div>

            <!-- Modal Footer - Exact Match -->
            <x-slot name="footer">
                <button type="button" @click="closeAddModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="subject-form" 
                    :disabled="submitting || {{ count($subjects) == 0 ? 'true' : 'false' }}" 
                    class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Allocating...' : 'Assign Subject'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>

    <!-- Confirmation Modal -->
    <x-confirm-modal />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subjectManagement', () => ({
        submitting: false,
        errors: {},
        formData: {
            class_id: '',
            subject_id: '',
            full_marks: 100
        },

        init() {
            window.addEventListener('delete-subject', (e) => {
                this.confirmDelete(e.detail);
            });
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            try {
                const response = await fetch('{{ route('school.examination.subjects.store') }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify(this.formData)
                });
                const result = await response.json();
                
                if (response.status === 422) {
                    this.errors = result.errors || {};
                } else if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else { 
                    throw new Error(result.message || 'Failed to assign subject'); 
                }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { 
                this.submitting = false; 
            }
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        async confirmDelete(id) {
            try {
                const response = await fetch(`/school/examination/subjects/${id}`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });
                const result = await response.json();
                if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    throw new Error(result.message || 'Deletion failed');
                }
            } catch (e) { 
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            }
        },

        openAddModal() {
            this.errors = {};
            this.formData = { class_id: '', subject_id: '', full_marks: 100 };
            this.$dispatch('open-modal', 'exam-subject-modal');
        },
        
        closeAddModal() {
            this.$dispatch('close-modal', 'exam-subject-modal');
        }
    }));
});
</script>
@endpush
@endsection
