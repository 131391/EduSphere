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
    <x-modal name="exam-subject-modal" title="Add Subject" maxWidth="lg">
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
                            class="w-full px-4 py-2 border @error('class_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Subject Name -->
                <div class="grid grid-cols-3 gap-4 items-center">
                    <label class="text-sm font-bold text-gray-700">Subject Name</label>
                    <div class="col-span-2">
                        <select 
                            name="subject_id" 
                            required
                            class="w-full px-4 py-2 border @error('subject_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
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
                            value="{{ old('full_marks', 100) }}"
                            class="w-full px-4 py-2 border @error('full_marks') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                        @error('full_marks')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
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
    </x-modal>

    <!-- Confirmation Modal -->
    <x-confirm-modal />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subjectManagement', () => ({
        init() {
            @if($errors->any())
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'exam-subject-modal');
                });
            @endif
        },

        openAddModal() {
            this.$dispatch('open-modal', 'exam-subject-modal');
        },
        
        closeAddModal() {
            this.$dispatch('close-modal', 'exam-subject-modal');
        }
    }));
});
</script>
@endpush
@endsection
