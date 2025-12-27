@extends('layouts.school')

@section('title', 'Fee Names')

@section('content')
<div class="space-y-6" x-data="feeNameManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Fee Names</h1>
            <p class="text-gray-600 mt-1">Manage fee names for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            Add Fee Name
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($feeNames) {
                    static $index = 0;
                    return $feeNames->firstItem() + $index++;
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
                'key' => 'description',
                'label' => 'DESCRIPTION',
                'sortable' => true,
                'render' => function($row) {
                    return $row->description ?? '-';
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-feename'))))";
                },
                'data-feename' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'description' => $row->description,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.fee-names.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Fee Name',
                    'message' => 'Are you sure you want to delete this fee name?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$feeNames"
        :actions="$tableActions"
        empty-message="No fee names found"
        empty-icon="fas fa-list"
    >
        Fee Names List
    </x-data-table>

    <!-- Add/Edit Fee Name Modal -->
    <x-modal name="fee-name-modal" alpineTitle="editMode ? 'Edit Fee Name' : 'Add Fee Name'" maxWidth="md">
        <form :action="editMode ? `/school/fee-names/${feeNameId}` : '{{ route('school.fee-names.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="fee_name_id" x-model="feeNameId">

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
    Alpine.data('feeNameManagement', () => ({
        editMode: false,
        feeNameId: null,
        formData: {
            name: '',
            description: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.feeNameId = '{{ old('fee_name_id') }}';
                this.formData = {
                    name: '{{ old('name') }}',
                    description: '{{ old('description') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'fee-name-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.feeNameId = null;
            this.formData = { name: '', description: '' };
            this.$dispatch('open-modal', 'fee-name-modal');
        },
        
        openEditModal(feeName) {
            this.editMode = true;
            this.feeNameId = feeName.id;
            this.formData = {
                name: feeName.name,
                description: feeName.description || ''
            };
            this.$dispatch('open-modal', 'fee-name-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'fee-name-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(feeName) {
    const component = Alpine.$data(document.querySelector('[x-data*="feeNameManagement"]'));
    if (component) {
        component.openEditModal(feeName);
    }
}

</script>
@endpush
@endsection
