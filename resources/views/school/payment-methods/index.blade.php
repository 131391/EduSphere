@extends('layouts.school')

@section('title', 'Payment Methods')

@section('content')
<div class="space-y-6" x-data="paymentMethodManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Payment Methods</h1>
            <p class="text-gray-600 mt-1">Manage payment methods for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            Add Payment Method
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($methods) {
                    static $index = 0;
                    return $methods->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'PAYMENT METHOD',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
                }
            ],
            [
                'key' => 'code',
                'label' => 'CODE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->code ?? '-';
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-method'))))";
                },
                'data-method' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'code' => $row->code,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.payment-methods.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Payment Method',
                    'message' => 'Are you sure you want to delete this payment method?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$methods"
        :actions="$tableActions"
        empty-message="No payment methods found"
        empty-icon="fas fa-credit-card"
    >
        Payment Methods List
    </x-data-table>

    <!-- Add/Edit Payment Method Modal -->
    <x-modal name="payment-method-modal" alpineTitle="editMode ? 'Edit Payment Method' : 'Add Payment Method'" maxWidth="md">
        <form :action="editMode ? `/school/payment-methods/${methodId}` : '{{ route('school.payment-methods.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="method_id" x-model="methodId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Payment Method Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="Enter payment method name"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Code</label>
                    <input 
                        type="text" 
                        name="code" 
                        x-model="formData.code"
                        placeholder="Optional code"
                        class="w-full px-4 py-2 border @error('code') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('code')
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
    Alpine.data('paymentMethodManagement', () => ({
        editMode: false,
        methodId: null,
        formData: {
            name: '',
            code: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.methodId = '{{ old('method_id') }}';
                this.formData = {
                    name: '{{ old('name') }}',
                    code: '{{ old('code') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'payment-method-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.methodId = null;
            this.formData = { name: '', code: '' };
            this.$dispatch('open-modal', 'payment-method-modal');
        },
        
        openEditModal(method) {
            this.editMode = true;
            this.methodId = method.id;
            this.formData = {
                name: method.name,
                code: method.code || ''
            };
            this.$dispatch('open-modal', 'payment-method-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'payment-method-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(method) {
    const component = Alpine.$data(document.querySelector('[x-data*="paymentMethodManagement"]'));
    if (component) {
        component.openEditModal(method);
    }
}
});
</script>
@endpush
@endsection
