@extends('layouts.receptionist')

@section('title', 'Assign Student Hostel Bed - Receptionist')
@section('page-title', 'Assign Student Hostel Bed')
@section('page-description', 'Assign hostel beds to students')

@section('content')
<div class="space-y-6" x-data="hostelBedAssignmentManagement" x-init="init()">
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('receptionist.hostels.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
                <h2 class="text-xl font-bold text-gray-800">Assign Student Hostel Bed</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Student
                </button>
                <a href="{{ route('receptionist.hostel-bed-assignments.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Assignments Table --}}
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'sortable' => false,
                'render' => function($row, $index, $data) {
                    return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'ADMISSION NO',
                'sortable' => true,
                'render' => function($row) {
                    return $row->student->admission_no ?? 'N/A';
                }
            ],
            [
                'key' => 'student_name',
                'label' => 'STUDENT NAME',
                'sortable' => true,
                'render' => function($row) {
                    $student = $row->student;
                    return $student ? trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) : 'N/A';
                }
            ],
            [
                'key' => 'class',
                'label' => 'CLASS',
                'sortable' => true,
                'render' => function($row) {
                    return $row->student->class->name ?? 'N/A';
                }
            ],
            [
                'key' => 'section',
                'label' => 'SECTION',
                'sortable' => true,
                'render' => function($row) {
                    return $row->student->section->name ?? 'N/A';
                }
            ],
            [
                'key' => 'hostel_name',
                'label' => 'HOSTEL NAME',
                'sortable' => true,
                'render' => function($row) {
                    return $row->hostel->hostel_name ?? 'N/A';
                }
            ],
            [
                'key' => 'floor_name',
                'label' => 'HOSTEL FLOOR',
                'sortable' => true,
                'render' => function($row) {
                    return $row->floor->floor_name ?? 'N/A';
                }
            ],
            [
                'key' => 'room_name',
                'label' => 'ROOM',
                'sortable' => true,
                'render' => function($row) {
                    return $row->room->room_name ?? 'N/A';
                }
            ],
            [
                'key' => 'bed_no',
                'label' => 'BED NO',
                'sortable' => true,
            ],
            [
                'key' => 'hostel_assign_date',
                'label' => 'HOSTEL ASSIGN DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->hostel_assign_date ? $row->hostel_assign_date->format('d/m/Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-assignment'))))";
                },
                'data-assignment' => function($row) {
                    $assignmentData = [
                        'id' => $row->id,
                        'student_id' => $row->student_id,
                        'student_name' => trim($row->student->first_name . ' ' . $row->student->middle_name . ' ' . $row->student->last_name),
                        'admission_no' => $row->student->admission_no,
                        'class_id' => $row->student->class_id,
                        'class_name' => $row->student->class->name ?? 'N/A',
                        'section_id' => $row->student->section_id,
                        'section_name' => $row->student->section->name ?? 'N/A',
                        'hostel_id' => $row->hostel_id,
                        'hostel_floor_id' => $row->hostel_floor_id,
                        'hostel_room_id' => $row->hostel_room_id,
                        'bed_no' => $row->bed_no,
                        'rent' => $row->rent,
                        'hostel_assign_date' => $row->hostel_assign_date ? $row->hostel_assign_date->format('Y-m-d') : '',
                        'starting_month' => $row->starting_month,
                    ];
                    return base64_encode(json_encode($assignmentData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => function($row) {
                    return route('receptionist.hostel-bed-assignments.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this assignment?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$assignments"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No assignments found"
        empty-icon="fas fa-bed"
    >
        Hostel Bed Assignments
    </x-data-table>

    {{-- Add/Edit Assignment Modal --}}
    <x-modal name="assignment-modal" alpineTitle="editMode ? 'Edit Hostel Bed Assignment' : 'Assign Hostel Bed Information'" maxWidth="4xl">
        <form :action="editMode ? `/receptionist/hostel-bed-assignments/${assignmentId}` : '{{ route('receptionist.hostel-bed-assignments.store') }}'" 
              method="POST" class="p-6">
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>

            <input type="hidden" name="student_id" x-model="formData.student_id">

            <div class="bg-teal-50 dark:bg-teal-900 p-4 rounded-lg mb-6">
                <h4 class="font-bold text-gray-800 dark:text-white mb-4">Assign Hostel Bed Information</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Admission No <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="admission_no_search"
                                   x-model="admissionSearch"
                                   @input="searchStudents()"
                                   @focus="showStudentDropdown = true"
                                   placeholder="Type to search..."
                                   class="w-full px-4 py-2 border {{ $errors->has('student_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                   autocomplete="off">
                            <div x-show="showStudentDropdown && studentResults.length > 0" 
                                 @click.away="showStudentDropdown = false"
                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="student in studentResults" :key="student.id">
                                    <div @click="selectStudent(student)" 
                                         class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600">
                                        <div class="font-semibold text-gray-900 dark:text-white" x-text="student.admission_no"></div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400" x-text="student.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-500" x-text="'Class: ' + student.class_name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <input type="hidden" name="student_id" x-model="formData.student_id">
                        @error('student_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Student Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="formData.student_name"
                               readonly
                               placeholder="Enter Student Name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Class
                        </label>
                        <input type="text" 
                               x-model="formData.class_name"
                               readonly
                               placeholder="Enter Class"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Name <span class="text-red-500">*</span>
                        </label>
                        <select name="hostel_id" 
                                id="hostel_id"
                                x-model="formData.hostel_id"
                                class="w-full px-4 py-2 border {{ $errors->has('hostel_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">
                                    {{ $hostel->hostel_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('hostel_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Select Floor <span class="text-red-500">*</span>
                        </label>
                        <select name="hostel_floor_id" 
                                id="hostel_floor_id"
                                x-model="formData.hostel_floor_id"
                                x-ref="floorSelect"
                                class="w-full px-4 py-2 border {{ $errors->has('hostel_floor_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Floor</option>
                        </select>
                        @error('hostel_floor_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Select Room <span class="text-red-500">*</span>
                        </label>
                        <select name="hostel_room_id" 
                                id="hostel_room_id"
                                x-model="formData.hostel_room_id"
                                x-ref="roomSelect"
                                class="w-full px-4 py-2 border {{ $errors->has('hostel_room_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Room</option>
                        </select>
                        @error('hostel_room_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Bed No
                        </label>
                        <input type="text" 
                               name="bed_no" 
                               x-model="formData.bed_no"
                               value="{{ old('bed_no') }}"
                               placeholder="Enter Bed No"
                               class="w-full px-4 py-2 border {{ $errors->has('bed_no') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('bed_no')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Rent
                        </label>
                        <input type="number" 
                               name="rent" 
                               x-model="formData.rent"
                               value="{{ old('rent') }}"
                               step="0.01"
                               min="0"
                               placeholder="Enter Rent"
                               class="w-full px-4 py-2 border {{ $errors->has('rent') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('rent')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Assign Date
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   name="hostel_assign_date" 
                                   x-model="formData.hostel_assign_date"
                                   value="{{ old('hostel_assign_date') }}"
                                   class="w-full px-4 py-2 border {{ $errors->has('hostel_assign_date') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        @error('hostel_assign_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Select Starting Month <span class="text-red-500">*</span>
                        </label>
                        <select name="starting_month" 
                                id="starting_month"
                                x-model="formData.starting_month"
                                class="w-full px-4 py-2 border {{ $errors->has('starting_month') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Starting Month</option>
                            <template x-for="month in months" :key="month.value">
                                <option :value="month.value" x-text="month.label"></option>
                            </template>
                        </select>
                        <div x-show="months.length === 0" class="text-xs text-gray-500 mt-1">
                            No starting month available
                        </div>
                        <div x-show="months.length > 0" class="text-xs text-gray-500 mt-1" x-text="months.length + ' fee names loaded'">
                        </div>
                        @error('starting_month')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-center gap-4">
                <button type="button" @click="closeModal()"
                        class="px-8 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold">
                    Close
                </button>
                <button type="submit"
                        class="px-8 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md">
                    Submit
                </button>
            </div>
        </form>
    </x-modal>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelBedAssignmentManagement', () => ({
        showModal: false,
        editMode: false,
        assignmentId: null,
        admissionSearch: '',
        showStudentDropdown: false,
        studentResults: [],
        searchTimeout: null,
        formData: {
            student_id: '',
            student_name: '',
            class_id: '',
            class_name: '',
            section_id: '',
            section_name: '',
            hostel_id: '',
            hostel_floor_id: '',
            hostel_room_id: '',
            bed_no: '',
            rent: '',
            hostel_assign_date: '',
            starting_month: '',
        },
        floors: [],
        rooms: [],
        months: [],
        
        async init() {
            // Watch for changes in months array
            this.$watch('months', (value) => {
                // If in edit mode and starting_month is set, select it after months load
                if (this.editMode && this.formData.starting_month && value && value.length > 0) {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (typeof $ !== 'undefined') {
                                const $monthSelect = $('#starting_month');
                                if ($monthSelect.length) {
                                    $monthSelect.val(this.formData.starting_month).trigger('change.select2');
                                }
                            }
                        }, 200);
                    });
                }
            });
            
            // Load months
            await this.loadMonths();
            
            // Listen for modal close events to clear all form fields including Select2
            window.addEventListener('close-modal', (e) => {
                if (e.detail === 'assignment-modal') {
                    this.resetForm();
                    this.editMode = false;
                    this.assignmentId = null;
                }
            });
            
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.assignmentId = '{{ old('assignment_id') }}';
                const oldStudentId = '{{ old('student_id') }}';
                const oldHostelId = '{{ old('hostel_id') }}';
                const oldFloorId = '{{ old('hostel_floor_id') }}';
                const oldRoomId = '{{ old('hostel_room_id') }}';
                
                this.formData = {
                    student_id: oldStudentId,
                    student_name: '{{ old('student_name') }}',
                    class_id: '{{ old('class_id') }}',
                    class_name: '{{ old('class_name') }}',
                    section_id: '{{ old('section_id') }}',
                    section_name: '{{ old('section_name') }}',
                    hostel_id: oldHostelId,
                    hostel_floor_id: oldFloorId,
                    hostel_room_id: oldRoomId,
                    bed_no: '{{ old('bed_no') }}',
                    rent: '{{ old('rent') }}',
                    hostel_assign_date: '{{ old('hostel_assign_date') }}',
                    starting_month: '{{ old('starting_month') }}',
                };
                
                // Load floors and rooms if hostel/floor is selected
                if (oldHostelId) {
                    await this.loadFloors(true, oldFloorId);
                }
                if (oldFloorId) {
                    await this.loadRooms(true, oldRoomId);
                }
                
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'assignment-modal');
                });
            @endif
        },
        
        async loadMonths() {
            try {
                const url = '{{ route('receptionist.hostel-bed-assignments.get-months') }}';
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && Array.isArray(data.months)) {
                    this.months = data.months;
                } else {
                    this.months = [];
                }
            } catch (error) {
                this.months = [];
            }
        },
        
        async searchStudents() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            if (this.admissionSearch.length < 2) {
                this.studentResults = [];
                this.showStudentDropdown = false;
                return;
            }
            
            this.searchTimeout = setTimeout(async () => {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const response = await fetch('{{ route('receptionist.hostel-bed-assignments.search-students') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            search: this.admissionSearch,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Failed to search students');
                    }

                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.students)) {
                        this.studentResults = data.students;
                        this.showStudentDropdown = true;
                    } else {
                        this.studentResults = [];
                    }
                } catch (error) {
                    this.studentResults = [];
                }
            }, 300);
        },
        
        selectStudent(student) {
            this.formData.student_id = student.id;
            this.formData.student_name = student.name;
            this.formData.class_id = student.class_id;
            this.formData.class_name = student.class_name;
            this.formData.section_id = student.section_id;
            this.formData.section_name = student.section_name;
            this.admissionSearch = student.admission_no;
            this.showStudentDropdown = false;
            this.studentResults = [];
        },
        
        openAddModal() {
            this.editMode = false;
            this.assignmentId = null;
            this.resetForm();
            this.$dispatch('open-modal', 'assignment-modal');
        },
        
        async openEditModal(assignment) {
            this.editMode = true;
            this.assignmentId = assignment.id;
            
            this.formData = {
                student_id: String(assignment.student_id || ''),
                student_name: assignment.student_name || '',
                class_id: assignment.class_id || '',
                class_name: assignment.class_name || '',
                section_id: assignment.section_id || '',
                section_name: assignment.section_name || '',
                hostel_id: String(assignment.hostel_id || ''),
                hostel_floor_id: String(assignment.hostel_floor_id || ''),
                hostel_room_id: String(assignment.hostel_room_id || ''),
                bed_no: assignment.bed_no || '',
                rent: assignment.rent || '',
                hostel_assign_date: assignment.hostel_assign_date || '',
                starting_month: String(assignment.starting_month || ''),
            };
            
            this.admissionSearch = assignment.admission_no || '';
            
            // Load floors and rooms
            if (this.formData.hostel_id) {
                await this.loadFloors(true, this.formData.hostel_floor_id);
            }
            if (this.formData.hostel_floor_id) {
                await this.loadRooms(true, this.formData.hostel_room_id);
            }
            
            this.$dispatch('open-modal', 'assignment-modal');
            
            // Set select values after modal opens (with Select2 support)
            this.$nextTick(() => {
                setTimeout(() => {
                    // Set hostel select (Select2)
                    if (this.formData.hostel_id && typeof $ !== 'undefined') {
                        const $hostelSelect = $('#hostel_id');
                        if ($hostelSelect.length) {
                            // Check if Select2 is initialized
                            if ($hostelSelect.hasClass('select2-hidden-accessible')) {
                                $hostelSelect.val(this.formData.hostel_id).trigger('change.select2');
                            } else {
                                // If Select2 not initialized, set native value and trigger change
                                $hostelSelect.val(this.formData.hostel_id).trigger('change');
                            }
                        }
                    }
                    
                    // Set floor select (Select2)
                    if (this.formData.hostel_floor_id && typeof $ !== 'undefined') {
                        const $floorSelect = $('#hostel_floor_id');
                        if ($floorSelect.length) {
                            $floorSelect.val(this.formData.hostel_floor_id).trigger('change.select2');
                        }
                    }
                    
                    // Set room select (Select2)
                    if (this.formData.hostel_room_id && typeof $ !== 'undefined') {
                        const $roomSelect = $('#hostel_room_id');
                        if ($roomSelect.length) {
                            $roomSelect.val(this.formData.hostel_room_id).trigger('change.select2');
                        }
                    }
                    
                    // Set starting month select (Select2) - wait for months to be loaded
                    if (this.formData.starting_month && typeof $ !== 'undefined') {
                        const checkAndSetMonth = () => {
                            const $monthSelect = $('#starting_month');
                            if ($monthSelect.length && this.months.length > 0) {
                                // Check if the value exists in months array
                                const monthExists = this.months.some(m => String(m.value) === String(this.formData.starting_month));
                                if (monthExists) {
                                    // Check if Select2 is initialized
                                    if ($monthSelect.hasClass('select2-hidden-accessible')) {
                                        $monthSelect.val(this.formData.starting_month).trigger('change.select2');
                                    } else {
                                        // If Select2 not initialized, set native value and trigger change
                                        $monthSelect.val(this.formData.starting_month).trigger('change');
                                    }
                                }
                            } else if (this.months.length === 0) {
                                // Retry if months haven't loaded yet (max 10 retries = 2 seconds)
                                const retryCount = checkAndSetMonth.retryCount || 0;
                                if (retryCount < 10) {
                                    checkAndSetMonth.retryCount = retryCount + 1;
                                    setTimeout(checkAndSetMonth, 200);
                                }
                            }
                        };
                        setTimeout(checkAndSetMonth, 500);
                    }
                }, 500);
            });
        },
        
        async loadFloors(preserveValue = false, valueToPreserve = null) {
            const hostelId = this.formData.hostel_id;
            
            if (!hostelId) {
                this.floors = [];
                if (!preserveValue) {
                    this.formData.hostel_floor_id = '';
                }
                this.updateFloorOptions(preserveValue, valueToPreserve);
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-floors') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        hostel_id: hostelId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to load floors: ' + response.status);
                }

                const data = await response.json();
                
                if (data.success && Array.isArray(data.floors)) {
                    this.floors = data.floors;
                    this.updateFloorOptions(preserveValue, valueToPreserve || this.formData.hostel_floor_id);
                    if (!preserveValue && !this.editMode) {
                        this.formData.hostel_floor_id = '';
                        this.formData.hostel_room_id = '';
                        this.rooms = [];
                        this.updateRoomOptions(false);
                    }
                } else {
                    this.floors = [];
                    this.updateFloorOptions(preserveValue, valueToPreserve);
                }
            } catch (error) {
                this.floors = [];
                this.updateFloorOptions(preserveValue, valueToPreserve);
            }
        },

        updateFloorOptions(preserveValue = false, valueToPreserve = null) {
            this.$nextTick(() => {
                const select = this.$refs.floorSelect || document.getElementById('hostel_floor_id');
                if (!select) {
                    return;
                }

                while (select.options.length > 1) {
                    select.remove(1);
                }

                if (Array.isArray(this.floors) && this.floors.length > 0) {
                    this.floors.forEach((floor) => {
                        const option = document.createElement('option');
                        option.value = floor.id;
                        option.textContent = floor.floor_name;
                        select.appendChild(option);
                    });
                }

                if (preserveValue && valueToPreserve) {
                    const valueExists = Array.from(select.options).some(opt => opt.value == valueToPreserve);
                    if (valueExists) {
                        select.value = valueToPreserve;
                        this.formData.hostel_floor_id = valueToPreserve;
                    } else {
                        select.value = '';
                        this.formData.hostel_floor_id = '';
                    }
                } else if (this.editMode && this.formData.hostel_floor_id) {
                    const valueExists = Array.from(select.options).some(opt => opt.value == this.formData.hostel_floor_id);
                    if (valueExists) {
                        select.value = this.formData.hostel_floor_id;
                    } else {
                        select.value = '';
                        this.formData.hostel_floor_id = '';
                    }
                } else if (!preserveValue) {
                    select.value = '';
                    this.formData.hostel_floor_id = '';
                }
            });
        },
        
        async loadRooms(preserveValue = false, valueToPreserve = null) {
            const floorId = this.formData.hostel_floor_id;
            
            if (!floorId) {
                this.rooms = [];
                if (!preserveValue) {
                    this.formData.hostel_room_id = '';
                }
                this.updateRoomOptions(preserveValue, valueToPreserve);
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-rooms') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        hostel_floor_id: floorId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to load rooms: ' + response.status);
                }

                const data = await response.json();
                
                if (data.success && Array.isArray(data.rooms)) {
                    this.rooms = data.rooms;
                    this.updateRoomOptions(preserveValue, valueToPreserve || this.formData.hostel_room_id);
                    if (!preserveValue && !this.editMode) {
                        this.formData.hostel_room_id = '';
                    }
                } else {
                    this.rooms = [];
                    this.updateRoomOptions(preserveValue, valueToPreserve);
                }
            } catch (error) {
                this.rooms = [];
                this.updateRoomOptions(preserveValue, valueToPreserve);
            }
        },

        updateRoomOptions(preserveValue = false, valueToPreserve = null) {
            this.$nextTick(() => {
                const select = this.$refs.roomSelect || document.getElementById('hostel_room_id');
                if (!select) {
                    return;
                }

                while (select.options.length > 1) {
                    select.remove(1);
                }

                if (Array.isArray(this.rooms) && this.rooms.length > 0) {
                    this.rooms.forEach((room) => {
                        const option = document.createElement('option');
                        option.value = room.id;
                        option.textContent = room.room_name;
                        select.appendChild(option);
                    });
                }

                if (preserveValue && valueToPreserve) {
                    const valueExists = Array.from(select.options).some(opt => opt.value == valueToPreserve);
                    if (valueExists) {
                        select.value = valueToPreserve;
                        this.formData.hostel_room_id = valueToPreserve;
                    } else {
                        select.value = '';
                        this.formData.hostel_room_id = '';
                    }
                } else if (this.editMode && this.formData.hostel_room_id) {
                    const valueExists = Array.from(select.options).some(opt => opt.value == this.formData.hostel_room_id);
                    if (valueExists) {
                        select.value = this.formData.hostel_room_id;
                    } else {
                        select.value = '';
                        this.formData.hostel_room_id = '';
                    }
                } else if (!preserveValue) {
                    select.value = '';
                    this.formData.hostel_room_id = '';
                }
            });
        },
        
        resetForm() {
            this.formData = {
                student_id: '',
                student_name: '',
                class_id: '',
                class_name: '',
                section_id: '',
                section_name: '',
                hostel_id: '',
                hostel_floor_id: '',
                hostel_room_id: '',
                bed_no: '',
                rent: '',
                hostel_assign_date: '',
                starting_month: '',
            };
            this.admissionSearch = '';
            this.studentResults = [];
            this.showStudentDropdown = false;
            this.floors = [];
            this.rooms = [];
            
            // Clear all Select2 dropdowns and form fields
            this.$nextTick(() => {
                setTimeout(() => {
                    // Clear admission input field
                    const admissionInput = document.getElementById('admission_no_search');
                    if (admissionInput) {
                        admissionInput.value = '';
                    }
                    
                    // Clear Select2 dropdowns
                    if (typeof $ !== 'undefined') {
                        // Helper function to clear Select2 dropdown
                        const clearSelect2 = (selector) => {
                            const $select = $(selector);
                            if ($select.length) {
                                // Check if Select2 is initialized
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    $select.val('').trigger('change.select2');
                                } else {
                                    // If not initialized, just clear the native select
                                    $select.val('');
                                }
                            }
                        };
                        
                        // Clear all Select2 dropdowns
                        clearSelect2('#hostel_id');
                        clearSelect2('#hostel_floor_id');
                        clearSelect2('#hostel_room_id');
                        clearSelect2('#starting_month');
                        
                        // Also clear native select values (fallback)
                        const hostelSelect = document.getElementById('hostel_id');
                        if (hostelSelect) hostelSelect.value = '';
                        
                        const floorSelect = document.getElementById('hostel_floor_id');
                        if (floorSelect) floorSelect.value = '';
                        
                        const roomSelect = document.getElementById('hostel_room_id');
                        if (roomSelect) roomSelect.value = '';
                        
                        const monthSelect = document.getElementById('starting_month');
                        if (monthSelect) monthSelect.value = '';
                    } else {
                        // Fallback if jQuery is not available
                        const hostelSelect = document.getElementById('hostel_id');
                        if (hostelSelect) hostelSelect.value = '';
                        
                        const floorSelect = document.getElementById('hostel_floor_id');
                        if (floorSelect) floorSelect.value = '';
                        
                        const roomSelect = document.getElementById('hostel_room_id');
                        if (roomSelect) roomSelect.value = '';
                        
                        const monthSelect = document.getElementById('starting_month');
                        if (monthSelect) monthSelect.value = '';
                    }
                }, 100);
            });
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'assignment-modal');
            this.resetForm();
            this.editMode = false;
            this.assignmentId = null;
        }
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(assignment) {
    const component = Alpine.$data(document.querySelector('[x-data*="hostelBedAssignmentManagement"]'));
    if (component) {
        component.openEditModal(assignment);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    const clearFieldError = function(field) {
        field.classList.remove('border-red-500');
        let errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('text-red-500')) {
            errorElement.remove();
        }
        const parentDiv = field.closest('div');
        if (parentDiv) {
            const errorInParent = parentDiv.querySelector('p.text-red-500');
            if (errorInParent) {
                errorInParent.remove();
            }
        }
    };
    
    const modal = document.querySelector('[x-data*="hostelBedAssignmentManagement"]');
    if (modal) {
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                clearFieldError(e.target);
            }
        });
        
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                clearFieldError(e.target);
            }
        });
    }
    
    $(document).on('change', 'select[name="hostel_id"], select[name="hostel_floor_id"], select[name="hostel_room_id"], select[name="starting_month"]', function() {
        clearFieldError(this);
    });
    
    // Handle hostel select change to load floors
    $(document).on('change', '#hostel_id', function() {
        const hostelId = $(this).val();
        clearFieldError(this);
        
        // Find Alpine component
        const alpineElement = document.querySelector('[x-data*="hostelBedAssignmentManagement"]');
        if (alpineElement) {
            const component = Alpine.$data(alpineElement);
            if (component) {
                component.formData.hostel_id = hostelId;
                component.loadFloors(false);
            }
        }
    });
    
    // Handle floor select change to load rooms
    $(document).on('change', '#hostel_floor_id', function() {
        const floorId = $(this).val();
        clearFieldError(this);
        
        // Find Alpine component
        const alpineElement = document.querySelector('[x-data*="hostelBedAssignmentManagement"]');
        if (alpineElement) {
            const component = Alpine.$data(alpineElement);
            if (component) {
                component.formData.hostel_floor_id = floorId;
                component.loadRooms(false);
            }
        }
    });
});
</script>
@endpush
@endsection

