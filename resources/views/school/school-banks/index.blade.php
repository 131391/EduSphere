@extends('layouts.school')

@section('title', 'School Banks')

@section('content')
<div class="space-y-6" x-data="schoolBankManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">School Banks</h1>
            <p class="text-gray-600 mt-1">Manage bank accounts for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            Add Bank Name
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($banks) {
                    static $index = 0;
                    return $banks->firstItem() + $index++;
                }
            ],
            [
                'key' => 'bank_name',
                'label' => 'BANK NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->bank_name) . '</span>';
                }
            ],
            [
                'key' => 'account_number',
                'label' => 'ACCOUNT NUMBER',
                'sortable' => true,
            ],
            [
                'key' => 'branch_name',
                'label' => 'BRANCH NAME',
                'sortable' => true,
                'render' => function($row) {
                    return $row->branch_name ?? '-';
                }
            ],
            [
                'key' => 'ifsc_code',
                'label' => 'IFSC CODE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->ifsc_code ?? '-';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'form',
                'url' => fn($row) => route('school.school-banks.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-400 hover:text-red-600',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete School Bank',
                    'message' => 'Are you sure you want to delete this bank account?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$banks"
        :actions="$tableActions"
        empty-message="No bank accounts found"
        empty-icon="fas fa-university"
    >
        School Banks List
    </x-data-table>

    <!-- Add Bank Modal -->
    <div 
        x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        @click.self="closeAddModal()"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4 bg-green-500 -mx-5 -mt-5 p-4 rounded-t-md">
                <h3 class="text-lg font-semibold text-white">Add Bank Name</h3>
                <button @click="closeAddModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('school.school-banks.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <input 
                        type="text" 
                        name="bank_name" 
                        placeholder="Enter Bank Name"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <div>
                    <input 
                        type="text" 
                        name="account_number" 
                        placeholder="Enter Account Number"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <div>
                    <input 
                        type="text" 
                        name="branch_name" 
                        placeholder="Enter Branch Name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <div>
                    <input 
                        type="text" 
                        name="ifsc_code" 
                        placeholder="Enter IFSC Code"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
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
                        Save and Add
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
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
    Alpine.data('schoolBankManagement', () => ({
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
