@extends('layouts.school')

@section('title', 'Qualifications')

@section('content')
<div class="space-y-6" x-data="qualificationManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Qualification</h1>
            <p class="text-gray-600 mt-1">Manage qualifications</p>
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
                'render' => function($row) use ($qualifications) {
                    static $index = 0;
                    return $qualifications->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'QUALIFICATION',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->created_at->format('F j, Y, g:i a');
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'form',
                'url' => fn($row) => route('school.qualifications.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-400 hover:text-red-600',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Qualification',
                    'message' => 'Are you sure you want to delete this qualification?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$qualifications"
        :actions="$tableActions"
        empty-message="No qualifications found"
        empty-icon="fas fa-graduation-cap"
    >
        Qualifications List
    </x-data-table>

    <!-- Add Qualification Modal -->
    <div 
        x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        @click.self="closeAddModal()"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4 bg-blue-600 -mx-5 -mt-5 p-4 rounded-t-md">
                <h3 class="text-lg font-semibold text-white">Qualification</h3>
                <button @click="closeAddModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('school.qualifications.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center space-x-4">
                    <label class="w-1/3 text-sm font-medium text-gray-700">Qualification</label>
                    <input 
                        type="text" 
                        name="name" 
                        placeholder="Enter Qualification"
                        required
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
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
    Alpine.data('qualificationManagement', () => ({
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
