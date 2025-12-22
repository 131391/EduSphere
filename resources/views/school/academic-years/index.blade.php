@extends('layouts.school')

@section('title', 'Academic Years')

@section('content')
<div class="space-y-6" x-data="academicYearManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Academic Years</h1>
            <p class="text-gray-600 mt-1">Manage academic years for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            ADD
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($academicYears) {
                    static $index = 0;
                    return $academicYears->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'ACADEMIC YEAR',
                'sortable' => true,
                'render' => function($row) {
                    $html = '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
                    if ($row->is_current) {
                        $html .= '<span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Current</span>';
                    }
                    return $html;
                }
            ],
            [
                'key' => 'start_date',
                'label' => 'DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->start_date->format('M d, Y') . ' - ' . $row->end_date->format('M d, Y');
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'form',
                'url' => fn($row) => route('school.academic-years.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-400 hover:text-red-600',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Academic Year',
                    'message' => 'Are you sure you want to delete this academic year?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$academicYears"
        :actions="$tableActions"
        empty-message="No academic years found"
        empty-icon="fas fa-calendar-alt"
    >
        Academic Years List
    </x-data-table>

    <!-- Add Academic Year Modal -->
    <div 
        x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        @click.self="closeAddModal()"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Academic Year</h3>
                <button @click="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('school.academic-years.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        placeholder="e.g., 2025-2026"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="is_current" 
                            value="1"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Set as current academic year</span>
                    </label>
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
    Alpine.data('academicYearManagement', () => ({
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
