@extends('layouts.school')

@section('title', 'Subject Master')

@section('content')
<div class="space-y-6" x-data="subjectMaster">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Subject Master</h1>
            <p class="text-gray-600 mt-1">Manage all subjects available in the school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center transition-colors"
        >
            <i class="fas fa-plus mr-2"></i>
            ADD SUBJECT
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'render' => function($row) use ($subjects) {
                    static $index = 0;
                    return $subjects->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'SUBJECT NAME',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-medium text-gray-900">' . e($row->name) . '</span>'
            ],
            [
                'key' => 'code',
                'label' => 'CODE',
                'sortable' => true,
            ],
            [
                'key' => 'description',
                'label' => 'DESCRIPTION',
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-subject'))))";
                },
                'data-subject' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'code' => $row->code,
                        'description' => $row->description,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.subjects.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Subject',
                    'message' => 'Are you sure you want to delete this subject?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$subjects"
        :actions="$tableActions"
        empty-message="No subjects found"
        empty-icon="fas fa-book"
    >
        Subjects List
    </x-data-table>

    <!-- Add/Edit Subject Modal -->
    <x-modal name="subject-modal" alpineTitle="editMode ? 'Edit Subject' : 'Add Subject'" maxWidth="md">
        <form :action="editMode ? `/school/subjects/${subjectId}` : '{{ route('school.subjects.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="subject_id" x-model="subjectId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Subject Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="e.g., Mathematics"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Subject Code</label>
                    <input 
                        type="text" 
                        name="code" 
                        x-model="formData.code"
                        placeholder="e.g., MATH101"
                        class="w-full px-4 py-2 border @error('code') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea 
                        name="description" 
                        x-model="formData.description"
                        placeholder="Enter subject description"
                        rows="3"
                        class="w-full px-4 py-2 border @error('description') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    ></textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-center gap-4 mt-8">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-8 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold"
                >
                    Close
                </button>
                <button 
                    type="submit"
                    class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold shadow-md"
                >
                    Submit
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
    Alpine.data('subjectMaster', () => ({
        editMode: false,
        subjectId: null,
        formData: {
            name: '',
            code: '',
            description: ''
        },
        
        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.subjectId = {{ old('subject_id', 'null') }};
                this.formData = {
                    name: '{{ old('name') }}',
                    code: '{{ old('code') }}',
                    description: '{{ old('description') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'subject-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.subjectId = null;
            this.formData = { name: '', code: '', description: '' };
            this.$dispatch('open-modal', 'subject-modal');
        },
        
        openEditModal(subject) {
            this.editMode = true;
            this.subjectId = subject.id;
            this.formData = {
                name: subject.name,
                code: subject.code || '',
                description: subject.description || ''
            };
            this.$dispatch('open-modal', 'subject-modal');
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'subject-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(subject) {
    const component = Alpine.$data(document.querySelector('[x-data*="subjectMaster"]'));
    if (component) {
        component.openEditModal(subject);
    }
}
</script>
@endpush
@endsection
