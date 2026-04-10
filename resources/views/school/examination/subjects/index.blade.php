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
                'label' => 'Sr No',
                'render' => function($row) use ($classSubjects) {
                    static $index = 0;
                    return $classSubjects->firstItem() + $index++;
                }
            ],
            [
                'key' => 'class_name',
                'label' => 'Class',
                'sortable' => true,
            ],
            [
                'key' => 'subject_name',
                'label' => 'Subject',
                'sortable' => true,
            ],
            [
                'key' => 'full_marks',
                'label' => 'Full Marks',
                'sortable' => true,
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('delete-subject', { detail: " . $row->id . " }))";
                },
                'title' => 'Delete',
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
    <x-modal name="exam-subject-modal" title="Assign Subject" maxWidth="md">
        <form @submit.prevent="submitForm()" method="POST" class="p-0">
            @csrf
            <div class="px-8 py-8 space-y-6">
                <!-- Select Class -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Select Class <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-chalkboard text-sm"></i>
                        </div>
                        <select 
                            name="class_id" 
                            x-model="formData.class_id"
                            required
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 font-medium"
                        >
                            <option value="">Choose Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Subject Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Subject Name <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-book text-sm"></i>
                        </div>
                        <select 
                            name="subject_id" 
                            x-model="formData.subject_id"
                            required
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 font-medium"
                        >
                            <option value="">Choose Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Full Marks -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Full Marks <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-star text-sm"></i>
                        </div>
                        <input 
                            type="number" 
                            name="full_marks" 
                            x-model="formData.full_marks"
                            placeholder="e.g., 100"
                            required
                            min="1"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 font-medium placeholder:text-gray-400"
                        >
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button 
                    type="button" 
                    @click="closeAddModal()"
                    class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 rounded-xl transition-all"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    :disabled="submitting"
                    class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all shadow-lg shadow-indigo-200 flex items-center gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Processing...' : 'Assign Subject'"></span>
                </button>
            </div>
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
            this.clearErrors();
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
                    this.displayErrors(result.errors);
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

        displayErrors(errors) {
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('!border-red-500');
                    input.classList.add('!ring-red-500/10');
                    
                    let errorMsg = input.closest('div').querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('p');
                        errorMsg.className = 'error-message text-red-500 text-[10px] mt-1 font-bold italic';
                        input.closest('div').appendChild(errorMsg);
                    }
                    errorMsg.innerText = errors[field][0];
                }
            });
        },

        clearErrors() {
            document.querySelectorAll('.\\!border-red-500').forEach(el => {
                el.classList.remove('!border-red-500');
                el.classList.remove('!ring-red-500/10');
            });
            document.querySelectorAll('.error-message').forEach(el => el.remove());
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
            this.clearErrors();
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
