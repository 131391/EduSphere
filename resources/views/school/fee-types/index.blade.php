@extends('layouts.school')

@section('title', 'Fee Type Management')

@section('content')
<div class="space-y-6" x-data="feeTypeManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Fee Type Management</h1>
            <p class="text-gray-600 mt-1">Manage fee types for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            Create Fee Type
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($feeTypes) {
                    static $index = 0;
                    return $feeTypes->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'FEE TYPE',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-type'))))";
                },
                'data-type' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.fee-types.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Fee Type',
                    'message' => 'Are you sure you want to delete this fee type?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$feeTypes"
        :actions="$tableActions"
        empty-message="No fee types found"
        empty-icon="fas fa-credit-card"
    >
        Fee Types List
    </x-data-table>

    <!-- Add/Edit Fee Type Modal -->
    <x-modal name="fee-type-modal" alpineTitle="editMode ? 'Edit Fee Type' : 'Add Fee Type'" maxWidth="md">
        <form :action="editMode ? `/school/fee-types/${typeId}` : '{{ route('school.fee-types.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="type_id" x-model="typeId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Fee Type Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="Enter fee type name"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                    class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold shadow-md"
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
    Alpine.data('feeTypeManagement', () => ({
        editMode: false,
        typeId: null,
        formData: {
            name: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.typeId = '{{ old('type_id') }}';
                this.formData = {
                    name: '{{ old('name') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'fee-type-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.typeId = null;
            this.formData = { name: '' };
            this.$dispatch('open-modal', 'fee-type-modal');
        },
        
        openEditModal(type) {
            this.editMode = true;
            this.typeId = type.id;
            this.formData = {
                name: type.name
            };
            this.$dispatch('open-modal', 'fee-type-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'fee-type-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(type) {
    const component = Alpine.$data(document.querySelector('[x-data*="feeTypeManagement"]'));
    if (component) {
        component.openEditModal(type);
    }
}
</script>
@endpush
@endsection
