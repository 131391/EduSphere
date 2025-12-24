@extends('layouts.school')

@section('title', 'Miscellaneous Fees')

@section('content')
<div class="space-y-6" x-data="miscFeeManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Miscellaneous Fees</h1>
            <p class="text-gray-600 mt-1">Manage miscellaneous fees for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            Add Fee
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($fees) {
                    static $index = 0;
                    return $fees->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'FEE NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
                }
            ],
            [
                'key' => 'amount',
                'label' => 'AMOUNT',
                'sortable' => true,
                'render' => function($row) {
                    return number_format($row->amount, 2);
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-fee'))))";
                },
                'data-fee' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'amount' => $row->amount,
                        'description' => $row->description,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.miscellaneous-fees.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Fee',
                    'message' => 'Are you sure you want to delete this fee?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$fees"
        :actions="$tableActions"
        empty-message="No fees found"
        empty-icon="fas fa-coins"
    >
        Miscellaneous Fees List
    </x-data-table>

    <!-- Add/Edit Fee Modal -->
    <x-modal name="misc-fee-modal" alpineTitle="editMode ? 'Edit Miscellaneous Fee' : 'Add Miscellaneous Fee'" maxWidth="md">
        <form :action="editMode ? `/school/miscellaneous-fees/${feeId}` : '{{ route('school.miscellaneous-fees.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="fee_id" x-model="feeId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Fee Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="Enter fee name"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        step="0.01"
                        name="amount" 
                        x-model="formData.amount"
                        placeholder="0.00"
                        class="w-full px-4 py-2 border @error('amount') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea 
                        name="description" 
                        x-model="formData.description"
                        rows="3"
                        placeholder="Optional description"
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
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('miscFeeManagement', () => ({
        editMode: false,
        feeId: null,
        formData: {
            name: '',
            amount: '',
            description: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.feeId = '{{ old('fee_id') }}';
                this.formData = {
                    name: '{{ old('name') }}',
                    amount: '{{ old('amount') }}',
                    description: '{{ old('description') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'misc-fee-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.feeId = null;
            this.formData = { name: '', amount: '', description: '' };
            this.$dispatch('open-modal', 'misc-fee-modal');
        },
        
        openEditModal(fee) {
            this.editMode = true;
            this.feeId = fee.id;
            this.formData = {
                name: fee.name,
                amount: fee.amount,
                description: fee.description || ''
            };
            this.$dispatch('open-modal', 'misc-fee-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'misc-fee-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(fee) {
    const component = Alpine.$data(document.querySelector('[x-data*="miscFeeManagement"]'));
    if (component) {
        component.openEditModal(fee);
    }
}
</script>
@endpush
@endsection
