@extends('layouts.school')

@section('title', 'Registration Codes')

@section('content')
<div class="space-y-6" x-data="registrationCodeManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Registration Code</h1>
            <p class="text-gray-600 mt-1">Manage registration codes</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center shadow-md transition-all active:scale-95"
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
                'render' => function($row) use ($codes) {
                    static $index = 0;
                    return $codes->firstItem() + $index++;
                }
            ],
            [
                'key' => 'code',
                'label' => 'REGISTRATION CODE',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->code) . '</span>';
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
                'url' => fn($row) => route('school.registration-codes.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-400 hover:text-red-600',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Registration Code',
                    'message' => 'Are you sure you want to delete this registration code?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$codes"
        :actions="$tableActions"
        empty-message="No registration codes found"
        empty-icon="fas fa-code"
    >
        Registration Codes List
    </x-data-table>

    <!-- Add Code Modal -->
    <x-modal name="add-registration-code-modal" title="Registration Code">
        <form action="{{ route('school.registration-codes.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">Registration Code</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-key text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        name="code" 
                        id="code"
                        value="{{ old('code') }}"
                        placeholder="e.g. REG-2024-001"
                        class="block w-full pl-10 pr-3 py-3 border @error('code') border-red-500 @else border-gray-300 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                    >
                </div>
                @error('code')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    @click="show = false"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-8 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 font-bold shadow-lg shadow-blue-200 active:scale-95"
                >
                    Save Code
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
    Alpine.data('registrationCodeManagement', () => ({
        init() {
            @if($errors->any())
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'add-registration-code-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.$dispatch('open-modal', 'add-registration-code-modal');
        }
    }));
});
</script>
@endpush
@endsection
