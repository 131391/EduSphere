@extends('layouts.school')

@section('title', 'Exam Type - Examination')

@section('content')
<div class="space-y-6" x-data="examTypeManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Exam Type</h1>
            <p class="text-gray-600 mt-1">Manage different types of examinations</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center transition-colors"
        >
            <i class="fas fa-plus mr-2"></i>
            ADD
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'Sr No',
                'render' => function($row) use ($examTypes) {
                    static $index = 0;
                    return $examTypes->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'Exam Type',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-medium text-gray-900">' . e($row->name) . '</span>'
            ],
            [
                'key' => 'created_at',
                'label' => 'Date',
                'sortable' => true,
                'render' => fn($row) => $row->created_at->format('d-m-Y')
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onClick' => 'openEditModal(row)'
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.examination.exam-types.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Exam Type',
                    'message' => 'Are you sure you want to delete this exam type?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$examTypes"
        :actions="$tableActions"
        empty-message="No exam types found"
        empty-icon="fas fa-file-invoice"
    >
        Exam Types List
    </x-data-table>

    <!-- Add/Edit Exam Type Modal -->
    <div 
        x-show="showModal" 
        x-cloak
        class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        @click.self="closeModal()"
    >
        <div 
            class="relative mx-auto w-full max-w-lg shadow-2xl rounded-xl bg-white overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
        >
            <!-- Modal Header -->
            <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit Exam Type' : 'Add Exam Type'"></h3>
                <button @click="closeModal()" class="text-white hover:text-blue-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form :action="editMode ? `/school/examination/exam-types/${examTypeId}` : '{{ route('school.examination.exam-types.store') }}'" method="POST" class="p-6">
                @csrf
                <template x-if="editMode">
                    @method('PUT')
                </template>

                <div class="space-y-5">
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Exam Type</label>
                        <div class="col-span-2">
                            <input 
                                type="text" 
                                name="name" 
                                x-model="formData.name"
                                placeholder="Enter Exam Type"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-8 flex items-center justify-center gap-4">
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
        </div>
    </div>

    <!-- Confirmation Modal -->
    <x-confirm-modal />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('examTypeManagement', () => ({
        showModal: false,
        editMode: false,
        examTypeId: null,
        formData: {
            name: ''
        },
        
        openAddModal() {
            this.editMode = false;
            this.examTypeId = null;
            this.formData = { name: '' };
            this.showModal = true;
        },
        
        openEditModal(examType) {
            this.editMode = true;
            this.examTypeId = examType.id;
            this.formData = {
                name: examType.name
            };
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
        }
    }));
});
</script>
@endpush
@endsection
