@extends('layouts.school')

@section('title', 'Student Grade - Examination')

@section('content')
<div class="space-y-6" x-data="gradeManagement" x-init="init()">
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Student Grade</h1>
            <p class="text-gray-600 mt-1">Manage grading scales based on percentage ranges</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center transition-colors shadow-md"
        >
            <i class="fas fa-plus mr-2"></i>
            Create Grade
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'Sr No',
                'render' => function($row) use ($grades) {
                    static $index = 0;
                    return $grades->firstItem() + $index++;
                }
            ],
            [
                'key' => 'range_start',
                'label' => 'Marks % Range 1',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-medium text-gray-900">' . $row->range_start . '%</span>'
            ],
            [
                'key' => 'range_end',
                'label' => 'Marks % Range 2',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-medium text-gray-900">' . $row->range_end . '%</span>'
            ],
            [
                'key' => 'grade',
                'label' => 'Grade',
                'sortable' => true,
                'render' => fn($row) => '<span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">' . e($row->grade) . '</span>'
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
                'url' => fn($row) => route('school.examination.grades.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Grade',
                    'message' => 'Are you sure you want to delete this grading scale?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$grades"
        :actions="$tableActions"
        empty-message="No grading scales found"
        empty-icon="fas fa-graduation-cap"
    >
        Grades List
    </x-data-table>

    <!-- Add/Edit Grade Modal -->
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
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit Student Grade' : 'Student Grade'"></h3>
                <button @click="closeModal()" class="text-white hover:text-blue-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form :action="editMode ? `/school/examination/grades/${gradeId}` : '{{ route('school.examination.grades.store') }}'" method="POST" class="p-6">
                @csrf
                <template x-if="editMode">
                    @method('PUT')
                </template>

                <div class="space-y-5">
                    <!-- Range Start -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Range Start</label>
                        <div class="col-span-2">
                            <input 
                                type="number" 
                                name="range_start" 
                                x-model="formData.range_start"
                                placeholder="Enter Range Start %"
                                required
                                min="0"
                                max="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                        </div>
                    </div>

                    <!-- Range End -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Range End</label>
                        <div class="col-span-2">
                            <input 
                                type="number" 
                                name="range_end" 
                                x-model="formData.range_end"
                                placeholder="Enter Range end %"
                                required
                                min="0"
                                max="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                        </div>
                    </div>

                    <!-- Grade -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Grade</label>
                        <div class="col-span-2">
                            <input 
                                type="text" 
                                name="grade" 
                                x-model="formData.grade"
                                placeholder="Enter Grade"
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
    Alpine.data('gradeManagement', () => ({
        showModal: false,
        editMode: false,
        gradeId: null,
        formData: {
            range_start: '',
            range_end: '',
            grade: ''
        },
        
        init() {
            // Initialization logic if needed
        },
        
        openAddModal() {
            this.editMode = false;
            this.gradeId = null;
            this.formData = { range_start: '', range_end: '', grade: '' };
            this.showModal = true;
        },
        
        openEditModal(grade) {
            this.editMode = true;
            this.gradeId = grade.id;
            this.formData = {
                range_start: grade.range_start,
                range_end: grade.range_end,
                grade: grade.grade
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
