@extends('layouts.school')

@section('title', 'Class Management')

@section('content')
<div class="space-y-6" x-data="classManagement">
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Class Management</h1>
            <p class="text-gray-600 mt-1">Manage all classes in your school</p>
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Classes</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_classes'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chalkboard text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Available Classes</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['available_classes'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Unavailable</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['unavailable_classes'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
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
                    <i class="fas fa-users text-purple-600"></i>
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
                'render' => function($row) use ($classes) {
                    static $index = 0;
                    return $classes->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'CLASS',
                'sortable' => true,
                'render' => function($row) {
                    return '<div>
                        <div class="text-sm font-medium text-gray-900">' . e($row->name) . '</div>
                        <div class="text-xs text-gray-500">' . $row->sections_count . ' sections • ' . $row->students_count . ' students</div>
                    </div>';
                }
            ],
            [
                'key' => 'is_available',
                'label' => 'IS AVAILABLE',
                'sortable' => true,
                'render' => function($row) {
                    $color = $row->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    $text = $row->is_available ? 'Y' : 'N';
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' . $text . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-class'))))";
                },
                'data-class' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.classes.toggle-availability', $row->id),
                'method' => 'PATCH',
                'icon' => 'fas fa-toggle-on',
                'class' => 'text-teal-600 hover:text-teal-900',
                'title' => 'Toggle Availability',
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.classes.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Class',
                    'message' => 'Are you sure you want to delete this class? This will also delete all associated sections and data.'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$classes"
        :actions="$tableActions"
        empty-message="No classes found"
        empty-icon="fas fa-chalkboard"
    >
        Classes List
    </x-data-table>

    <!-- Add/Edit Class Modal -->
    <x-modal name="class-modal" alpineTitle="editMode ? 'Edit Class' : 'Add Class'" maxWidth="md">
        <form :action="editMode ? `/school/classes/${classId}` : '{{ route('school.classes.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="class_id" x-model="classId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Class Name *
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="Enter Class Name"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('name')
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
    Alpine.data('classManagement', () => ({
        editMode: false,
        classId: null,
        formData: {
            name: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.classId = '{{ old('class_id') }}';
                this.formData = {
                    name: '{{ old('name') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'class-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.classId = null;
            this.formData = { name: '' };
            this.$dispatch('open-modal', 'class-modal');
        },
        
        openEditModal(classData) {
            this.editMode = true;
            this.classId = classData.id;
            this.formData = {
                name: classData.name
            };
            this.$dispatch('open-modal', 'class-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'class-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(classData) {
    const component = Alpine.$data(document.querySelector('[x-data*="classManagement"]'));
    if (component) {
        component.openEditModal(classData);
    }
}
</script>
@endpush
@endsection
