@extends('layouts.school')

@section('title', 'Add Subject - Examination')

@section('content')
<div class="space-y-6" x-data="subjectManagement">
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
            <h1 class="text-2xl font-bold text-gray-800">Add Subject</h1>
            <p class="text-gray-600 mt-1">Assign subjects to classes and set full marks</p>
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
                'render' => function($row) use ($classSubjects) {
                    static $index = 0;
                    return $classSubjects->firstItem() + $index++;
                }
            ],
            [
                'key' => 'class_name',
                'label' => 'Class',
                'sortable' => true,
            ],
            [
                'key' => 'subject_name',
                'label' => 'Subject',
                'sortable' => true,
            ],
            [
                'key' => 'full_marks',
                'label' => 'Full Marks',
                'sortable' => true,
            ],
        ];

        $tableActions = [
            [
                'type' => 'form',
                'url' => fn($row) => route('school.examination.subjects.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-400 hover:text-red-600',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Remove Subject',
                    'message' => 'Are you sure you want to remove this subject from the class?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$classSubjects"
        :actions="$tableActions"
        empty-message="No subjects assigned to classes yet"
        empty-icon="fas fa-book-open"
    >
        Assigned Subjects List
    </x-data-table>

    <!-- Add Subject Modal -->
    <div 
        x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        @click.self="closeAddModal()"
    >
        <div 
            class="relative mx-auto w-full max-w-lg shadow-2xl rounded-xl bg-white overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
        >
            <!-- Modal Header -->
            <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Add Subject</h3>
                <button @click="closeAddModal()" class="text-white hover:text-blue-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('school.examination.subjects.store') }}" method="POST" class="p-6">
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

                    <!-- Subject Name -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Subject Name</label>
                        <div class="col-span-2">
                            <select 
                                name="subject_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Full Marks -->
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-bold text-gray-700">Full Marks</label>
                        <div class="col-span-2">
                            <input 
                                type="number" 
                                name="full_marks" 
                                placeholder="Enter Full Marks"
                                required
                                min="1"
                                value="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-8 flex items-center justify-center gap-4">
                    <button 
                        type="button" 
                        @click="closeAddModal()"
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
    Alpine.data('subjectManagement', () => ({
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
