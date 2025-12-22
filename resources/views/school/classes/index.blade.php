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
            [
                'key' => 'actions',
                'label' => 'ACTION',
                'render' => function($row) {
                    $route = route('school.classes.toggle-availability', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('PATCH');
                    
                    return '<div class="flex items-center space-x-3">
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                class="text-gray-600 hover:text-gray-900 px-3 py-1 rounded border border-gray-300 hover:bg-gray-50 text-xs"
                            >
                                Is available <i class="fas fa-chevron-down ml-1 text-[10px]"></i>
                            </button>
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-32 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                            >
                                <form action="' . $route . '" method="POST">
                                    ' . $csrf . '
                                    ' . $method . '
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Yes
                                    </button>
                                </form>
                                <form action="' . $route . '" method="POST">
                                    ' . $csrf . '
                                    ' . $method . '
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        No
                                    </button>
                                </form>
                            </div>
                        </div>
                        <form 
                            action="' . route('school.classes.destroy', $row->id) . '" 
                            method="POST" 
                            class="inline"
                            @submit.prevent="$dispatch(\'open-confirm-modal\', { 
                                form: $el, 
                                title: \'Delete Class\', 
                                message: \'Are you sure you want to delete this class? This will also delete all associated sections and data.\' 
                            })"
                        >
                            ' . $csrf . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="text-red-400 hover:text-red-600" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>';
                }
            ]
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$classes"
        empty-message="No classes found"
        empty-icon="fas fa-chalkboard"
    >
        Classes List
    </x-data-table>

    <!-- Add Class Modal -->
    <div 
        x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        @click.self="closeAddModal()"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Class</h3>
                <button @click="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('school.classes.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="class_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Class Name
                    </label>
                    <input 
                        type="text" 
                        id="class_name" 
                        name="name" 
                        placeholder="Enter Class Name"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="closeAddModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                    >
                        Close
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('classManagement', () => ({
        showAddModal: false,
        
        openAddModal() {
            this.showAddModal = true;
        },
        
        closeAddModal() {
            this.showAddModal = false;
        }
    }));
});
</script>
@endpush
@endsection
