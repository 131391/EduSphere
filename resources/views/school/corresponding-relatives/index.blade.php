@extends('layouts.school')

@section('title', 'Corresponding Relatives')

@section('content')
<div class="space-y-6" x-data="relativeManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Corresponding Relatives</h1>
            <p class="text-gray-600 mt-1">Manage corresponding relatives</p>
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
                'render' => function($row) use ($relatives) {
                    static $index = 0;
                    return $relatives->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'CORRESPONDING RELATIVES',
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
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-relative'))))";
                },
                'data-relative' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.corresponding-relatives.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Relative',
                    'message' => 'Are you sure you want to delete this relative?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$relatives"
        :actions="$tableActions"
        empty-message="No relatives found"
        empty-icon="fas fa-users"
    >
        Relatives List
    </x-data-table>

    <!-- Add/Edit Relative Modal -->
    <x-modal name="relative-modal" alpineTitle="editMode ? 'Edit Corresponding Relative' : 'Add Corresponding Relative'" maxWidth="md">
        <form :action="editMode ? `/school/corresponding-relatives/${relativeId}` : '{{ route('school.corresponding-relatives.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="relative_id" x-model="relativeId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Relative Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="Enter Relative Name"
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
    Alpine.data('relativeManagement', () => ({
        editMode: false,
        relativeId: null,
        formData: {
            name: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.relativeId = '{{ old('relative_id') }}';
                this.formData = {
                    name: '{{ old('name') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'relative-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.relativeId = null;
            this.formData = { name: '' };
            this.$dispatch('open-modal', 'relative-modal');
        },
        
        openEditModal(relative) {
            this.editMode = true;
            this.relativeId = relative.id;
            this.formData = {
                name: relative.name
            };
            this.$dispatch('open-modal', 'relative-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'relative-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(relative) {
    const component = Alpine.$data(document.querySelector('[x-data*="relativeManagement"]'));
    if (component) {
        component.openEditModal(relative);
    }
}
</script>
@endpush
@endsection
