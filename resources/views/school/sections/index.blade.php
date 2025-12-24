@extends('layouts.school')

@section('title', 'Section Management')

@section('content')
<div class="space-y-6" x-data="sectionManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Section Management</h1>
            <p class="text-gray-600 mt-1">Manage all sections in your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            ADD
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Sections</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_sections'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Capacity</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['total_capacity'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chair text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Students</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['total_students'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-graduate text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($sections) {
                    static $index = 0;
                    return $sections->firstItem() + $index++;
                }
            ],
            [
                'key' => 'class_name',
                'label' => 'CLASS',
                'sortable' => true,
                'render' => function($row) {
                    return $row->class->name ?? 'N/A';
                }
            ],
            [
                'key' => 'name',
                'label' => 'SECTION',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
                }
            ],
            [
                'key' => 'capacity',
                'label' => 'LENGTH',
                'sortable' => true,
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-section'))))";
                },
                'data-section' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'class_id' => (string) $row->class_id, // Convert to string to match option values
                        'name' => $row->name,
                        'capacity' => $row->capacity,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.sections.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Section',
                    'message' => 'Are you sure you want to delete this section?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$sections"
        :actions="$tableActions"
        empty-message="No sections found"
        empty-icon="fas fa-users"
    >
        Sections List
    </x-data-table>

    <!-- Add/Edit Section Modal -->
    <x-modal name="section-modal" alpineTitle="editMode ? 'Edit Section' : 'Add Section'" maxWidth="md">
        <form :action="editMode ? `/school/sections/${sectionId}` : '{{ route('school.sections.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="section_id" x-model="sectionId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Class <span class="text-red-500">*</span></label>
                    <select 
                        name="class_id" 
                        x-model="formData.class_id"
                        class="w-full px-4 py-2 border @error('class_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Section Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="e.g. A, B, C"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Student Max Length <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="capacity" 
                        x-model="formData.capacity"
                        placeholder="Enter max capacity"
                        class="w-full px-4 py-2 border @error('capacity') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('capacity')
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
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sectionManagement', () => ({
        editMode: false,
        sectionId: null,
        formData: {
            class_id: '',
            name: '',
            capacity: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.sectionId = '{{ old('section_id') }}';
                this.formData = {
                    class_id: '{{ old('class_id') }}',
                    name: '{{ old('name') }}',
                    capacity: '{{ old('capacity') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'section-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.sectionId = null;
            this.formData = { class_id: '', name: '', capacity: '' };
            this.$dispatch('open-modal', 'section-modal');
        },
        
        openEditModal(section) {
            console.log('Opening edit modal for section:', section);
            this.editMode = true;
            this.sectionId = section.id;
            
            // Ensure class_id is converted to string to match option values
            const classId = String(section.class_id || '');
            
            // Set form data first
            this.formData = {
                class_id: classId,
                name: section.name || '',
                capacity: String(section.capacity || '')
            };
            
            // Open modal
            this.$dispatch('open-modal', 'section-modal');
            
            // Directly set select value after modal is rendered (same approach as user management)
            setTimeout(() => {
                const select = document.querySelector('[name="class_id"]');
                if (select && classId) {
                    console.log('Setting select value to:', classId);
                    select.value = classId;
                    // Trigger change event to ensure Alpine.js syncs
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    // Also ensure formData is synced
                    this.formData.class_id = classId;
                }
            }, 100);
        },

        closeModal() {
            this.$dispatch('close-modal', 'section-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(section) {
    const component = Alpine.$data(document.querySelector('[x-data*="sectionManagement"]'));
    if (component) {
        component.openEditModal(section);
    }
}
</script>
@endpush
@endsection
