@extends('layouts.school')

@section('title', 'Create Exams - Examination')

@section('content')
<div class="space-y-6" x-data="examManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Create Exams</h1>
            <p class="text-gray-600 mt-1">Create and manage examinations for classes</p>
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
                'render' => function($row) use ($exams) {
                    static $index = 0;
                    return $exams->firstItem() + $index++;
                }
            ],
            [
                'key' => 'academic_year',
                'label' => 'Academic Year',
                'render' => fn($row) => $row->academicYear->name
            ],
            [
                'key' => 'class',
                'label' => 'Class',
                'render' => fn($row) => $row->class->name
            ],
            [
                'key' => 'exam_type',
                'label' => 'Exam Type',
                'render' => fn($row) => $row->examType->name
            ],
            [
                'key' => 'month',
                'label' => 'Exam Month',
            ],
            [
                'key' => 'status',
                'label' => 'Exam Status',
                'render' => function($row) {
                    $colors = [
                        'scheduled' => 'bg-blue-100 text-blue-800',
                        'ongoing' => 'bg-yellow-100 text-yellow-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $color = $colors[$row->status] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 rounded-full text-xs font-semibold ' . $color . '">' . ucfirst($row->status) . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'form',
                'url' => fn($row) => route('school.examination.exams.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Exam',
                    'message' => 'Are you sure you want to delete this exam?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$exams"
        :actions="$tableActions"
        empty-message="No exams created yet"
        empty-icon="fas fa-file-alt"
    >
        Exams List
    </x-data-table>

    <!-- Create Exams Modal -->
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
                <h3 class="text-xl font-bold text-white">Create Exams</h3>
                <button @click="closeModal()" class="text-white hover:text-blue-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('school.examination.exams.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-5">
                    <!-- Select Class -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Select Class</label>
                        <div class="col-span-2">
                            <select 
                                name="class_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Exam Type -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Exam Type</label>
                        <div class="col-span-2">
                            <select 
                                name="exam_type_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                                <option value="">Select Exam Type</option>
                                @foreach($examTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Select Month -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Select Month</label>
                        <div class="col-span-2">
                            <select 
                                name="month" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                                <option value="">Select Month</option>
                                @foreach($months as $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                                @endforeach
                            </select>
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
    Alpine.data('examManagement', () => ({
        showModal: false,
        
        openAddModal() {
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
