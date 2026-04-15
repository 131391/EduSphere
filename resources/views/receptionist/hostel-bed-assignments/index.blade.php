@extends('layouts.receptionist')

@section('title', 'Residential Mapping - Receptionist')
@section('page-title', 'Residential Mapping')
@section('page-description', 'Manage student-to-residential mapping and room assignments')

@section('content')
    <div class="space-y-6" x-data="hostelBedAssignmentManagement" x-init="init()">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Mappings</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $assignments->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-link text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Hostel Blocks</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $hostels->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Capacity</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        {{ \App\Models\HostelRoom::where('school_id', auth()->user()->school_id)->count() * 4 }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-bed text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue Stream</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">₹{{ number_format($assignments->sum('rent'), 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-wallet text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-link text-xs"></i>
                    </div>
                    Mapping Registry
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Synchronize student profiles with available residential units.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="$dispatch('open-add-hostel-assignment')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Initialize Mapping
                </button>
                <a href="{{ route('receptionist.hostel-bed-assignments.export') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-file-excel mr-2 text-xs"></i>
                    Export Records
                </a>
            </div>
        </div>
    </div>

    {{-- Mapping Table --}}
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
                'key' => 'student_name',
                'label' => 'STUDENT',
                'sortable' => false,
                'render' => function($row) {
                    $student = $row->student;
                    return '<span class="font-bold text-gray-800">' . trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) . '</span>';
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'ADMISSION',
                'sortable' => false,
                'render' => function($row) {
                    return $row->student->admission_no ?? 'N/A';
                }
            ],
            [
                'key' => 'class',
                'label' => 'CLASS',
                'sortable' => false,
                'render' => function($row) {
                    return $row->student->class->name ?? 'N/A';
                }
            ],
            [
                'key' => 'hostel',
                'label' => 'HOSTEL',
                'sortable' => false,
                'render' => function($row) {
                    return $row->hostel->hostel_name ?? 'N/A';
                }
            ],
            [
                'key' => 'floor',
                'label' => 'FLOOR',
                'sortable' => false,
                'render' => function($row) {
                    return $row->floor->floor_name ?? 'N/A';
                }
            ],
            [
                'key' => 'room',
                'label' => 'ROOM',
                'sortable' => false,
                'render' => function($row) {
                    return $row->room->room_name ?? 'N/A';
                }
            ],
            [
                'key' => 'rent',
                'label' => 'RENT (₹)',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-bold text-gray-900">' . number_format($row->rent, 2) . '</span>';
                }
            ],
            [
                'key' => 'date',
                'label' => 'ASSIGNED',
                'sortable' => true,
                'render' => function($row) {
                    return $row->hostel_assign_date ? $row->hostel_assign_date->format('d M, Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    $assignmentData = [
                        'id' => $row->id,
                        'student_id' => $row->student_id,
                        'student_name' => trim($row->student->first_name . ' ' . $row->student->middle_name . ' ' . $row->student->last_name),
                        'admission_no' => $row->student->admission_no,
                        'class_name' => $row->student->class->name ?? 'N/A',
                        'hostel_id' => $row->hostel_id,
                        'hostel_floor_id' => $row->hostel_floor_id,
                        'hostel_room_id' => $row->hostel_room_id,
                        'bed_no' => $row->bed_no,
                        'rent' => $row->rent,
                        'hostel_assign_date' => $row->hostel_assign_date ? $row->hostel_assign_date->format('Y-m-d') : '',
                        'starting_month' => $row->starting_month,
                    ];
                    return "window.dispatchEvent(new CustomEvent('open-edit-hostel-assignment', { detail: ".json_encode($assignmentData)." }))";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    $student = $row->student;
                    $name = $student ? trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) : 'N/A';
                    $deleteData = [
                        'url' => route('receptionist.hostel-bed-assignments.destroy', $row->id),
                        'name' => $name
                    ];
                    return "window.dispatchEvent(new CustomEvent('open-delete-hostel-assignment', { detail: ".json_encode($deleteData)." }))";
                },
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table :columns="$tableColumns" :data="$assignments" :searchable="true" :actions="$tableActions"
        empty-message="No residential mappings initialized" empty-icon="fas fa-link-slash">
        Mapping List
    </x-data-table>

        {{-- Add/Edit Mapping Modal --}}
        <x-modal name="assignment-modal"
            alpineTitle="editMode ? 'Synchronize Residential Revision' : 'Initialize Residential Mapping'" maxWidth="3xl">
            <form @submit.prevent="save" id="assignmentForm" method="POST" class="space-y-6" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-8">
                    {{-- Student Selection --}}
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-6">
                        <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-user-graduate"></i>
                            Student Identification
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 relative">
                                <label class="modal-label-premium">Admission No / Search <span class="text-red-500">*</span></label>
                                <input type="text" x-model="admissionSearch"
                                    @input="searchStudents(); clearError('student_id')"
                                    @focus="showStudentDropdown = true" placeholder="Type student name or ID..."
                                    class="modal-input-premium"
                                    :class="errors.student_id ? 'border-red-500 ring-red-500/10' : ''"
                                    autocomplete="off">

                                {{-- Student Dropdown Results --}}
                                <div x-show="showStudentDropdown && studentResults.length > 0"
                                    @click.outside="showStudentDropdown = false"
                                    class="absolute z-[60] w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 max-h-60 overflow-y-auto">
                                    <template x-for="student in studentResults" :key="student.id">
                                        <div @click="selectStudent(student)"
                                            class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 flex items-center gap-3">
                                            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs"
                                                x-text="student.admission_no.slice(-2)"></div>
                                            <div>
                                                <div class="font-bold text-sm text-gray-800 dark:text-gray-200" x-text="student.name"></div>
                                                <div class="text-xs text-gray-500"
                                                    x-text="student.admission_no + ' • ' + student.class_name"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="errors.student_id">
                                    <p class="modal-error-message" x-text="errors.student_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Authenticated Profile</label>
                                <div class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-lg flex items-center justify-between min-h-[42px]">
                                    <div>
                                        <span class="block text-sm font-bold text-gray-800 dark:text-gray-200"
                                            x-text="formData.student_name || 'No selection'"></span>
                                        <span class="block text-xs text-indigo-600 dark:text-indigo-400"
                                            x-text="formData.class_name"></span>
                                    </div>
                                    <template x-if="formData.student_id">
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Residential Mapping --}}
                    <div class="space-y-6">
                        <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                            <i class="fas fa-map-location-dot"></i>
                            Residential Hierarchy
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Hostel Block <span class="text-red-500">*</span></label>
                                <select x-model="formData.hostel_id" @change="loadFloors(); clearError('hostel_id')"
                                    class="modal-input-premium"
                                    :class="errors.hostel_id ? 'border-red-500 ring-red-500/10' : ''">
                                    <option value="">Select Block</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="errors.hostel_id">
                                    <p class="modal-error-message" x-text="errors.hostel_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Floor Level <span class="text-red-500">*</span></label>
                                <select x-model="formData.hostel_floor_id"
                                    @change="loadRooms(); clearError('hostel_floor_id')" :disabled="!formData.hostel_id"
                                    class="modal-input-premium disabled:opacity-50"
                                    :class="errors.hostel_floor_id ? 'border-red-500 ring-red-500/10' : ''">
                                    <option value="">Select Floor</option>
                                    <template x-for="floor in floors" :key="floor.id">
                                        <option :value="floor.id" x-text="floor.floor_name"></option>
                                    </template>
                                </select>
                                <template x-if="errors.hostel_floor_id">
                                    <p class="modal-error-message" x-text="errors.hostel_floor_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Residential Unit <span class="text-red-500">*</span></label>
                                <select x-model="formData.hostel_room_id" @change="clearError('hostel_room_id')"
                                    :disabled="!formData.hostel_floor_id"
                                    class="modal-input-premium disabled:opacity-50"
                                    :class="errors.hostel_room_id ? 'border-red-500 ring-red-500/10' : ''">
                                    <option value="">Select Room</option>
                                    <template x-for="room in rooms" :key="room.id">
                                        <option :value="room.id" x-text="room.room_name"></option>
                                    </template>
                                </select>
                                <template x-if="errors.hostel_room_id">
                                    <p class="modal-error-message" x-text="errors.hostel_room_id[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Financial & Scheduling --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="space-y-6">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Mapping Specifications</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Unit Identifier</label>
                                    <input type="text" x-model="formData.bed_no" @input="clearError('bed_no')"
                                        placeholder="e.g., Bed A"
                                        class="modal-input-premium"
                                        :class="errors.bed_no ? 'border-red-500 ring-red-500/10' : ''">
                                    <template x-if="errors.bed_no">
                                        <p class="modal-error-message" x-text="errors.bed_no[0]"></p>
                                    </template>
                                </div>
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Fee Structure</label>
                                    <input type="number" step="0.01" x-model="formData.rent" @input="clearError('rent')"
                                        placeholder="0.00"
                                        class="modal-input-premium"
                                        :class="errors.rent ? 'border-red-500 ring-red-500/10' : ''">
                                    <template x-if="errors.rent">
                                        <p class="modal-error-message" x-text="errors.rent[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Mapping Schedule</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Effective Date</label>
                                    <input type="date" x-model="formData.hostel_assign_date"
                                        @input="clearError('hostel_assign_date')"
                                        class="modal-input-premium"
                                        :class="errors.hostel_assign_date ? 'border-red-500 ring-red-500/10' : ''">
                                    <template x-if="errors.hostel_assign_date">
                                        <p class="modal-error-message" x-text="errors.hostel_assign_date[0]"></p>
                                    </template>
                                </div>
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Billing Cycle <span class="text-red-500">*</span></label>
                                    <select x-model="formData.starting_month" @change="clearError('starting_month')"
                                        class="modal-input-premium"
                                        :class="errors.starting_month ? 'border-red-500 ring-red-500/10' : ''">
                                        <option value="">Choose Cycle</option>
                                        <template x-for="month in months" :key="month.value">
                                            <option :value="month.value" x-text="month.label"></option>
                                        </template>
                                    </select>
                                    <template x-if="errors.starting_month">
                                        <p class="modal-error-message" x-text="errors.starting_month[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <button type="button" @click="closeModal()" :disabled="submitting"
                    class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="assignmentForm" :disabled="submitting"
                    class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Changes' : 'Confirm Mapping')"></span>
                </button>
            </x-slot>
        </x-modal>

        {{-- Custom Confirm Modal --}}
        <x-confirm-modal title="Dismantle Residential Mapping?"
            message="This operation will terminate the student's residential assignment. This action will be audited."
            confirm-text="Confirm Decommission" confirm-color="red" />
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('hostelBedAssignmentManagement', () => ({
                    editMode: false,
                    assignmentId: null,
                    submitting: false,
                    admissionSearch: '',
                    showStudentDropdown: false,
                    studentResults: [],
                    searchTimeout: null,
                    errors: {},
                    formData: {
                        student_id: '',
                        student_name: '',
                        class_name: '',
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

                    clearError(field) {
                        if (this.errors[field]) {
                            delete this.errors[field];
                        }
                    },

                    async init() {
                        window.addEventListener('open-add-hostel-assignment', () => this.openAddModal());
                        window.addEventListener('open-edit-hostel-assignment', (e) => this.openEditModal(e.detail));
                        window.addEventListener('open-delete-hostel-assignment', (e) => this.confirmDelete(e.detail));

                        await this.loadMonths();

                        // Sync Select2 with Alpine state
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[x-model^="formData."]').on('change', (e) => {
                                    const field = e.target.getAttribute('x-model').replace('formData.', '');
                                    if (field && this.formData.hasOwnProperty(field)) {
                                        this.formData[field] = e.target.value;
                                        if (field === 'hostel_id') this.loadFloors();
                                        if (field === 'hostel_floor_id') this.loadRooms();
                                        this.clearError(field);
                                    }
                                });
                            }
                        });
                    },

                    async loadMonths() {
                        try {
                            const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-months') }}');
                            const data = await response.json();
                            if (data.success) {
                                this.months = data.months;
                            }
                        } catch (error) {
                            console.error('Cycle loading failure:', error);
                        }
                    },

                    searchStudents() {
                        if (this.admissionSearch.length < 2) {
                            this.studentResults = [];
                            return;
                        }

                        if (this.searchTimeout) clearTimeout(this.searchTimeout);

                        this.searchTimeout = setTimeout(async () => {
                            try {
                                const response = await fetch('{{ route('receptionist.hostel-bed-assignments.search-students') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ search: this.admissionSearch })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    this.studentResults = data.students;
                                }
                            } catch (error) {
                                console.error('Registry search failure:', error);
                            }
                        }, 300);
                    },

                    selectStudent(student) {
                        this.formData.student_id = student.id;
                        this.formData.student_name = student.name;
                        this.formData.class_name = student.class_name;
                        this.admissionSearch = student.admission_no;
                        this.showStudentDropdown = false;
                        this.clearError('student_id');
                    },

                    async loadFloors(floorId = null) {
                        if (!this.formData.hostel_id) {
                            this.floors = [];
                            this.formData.hostel_floor_id = '';
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-floors') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ hostel_id: this.formData.hostel_id })
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.floors = data.floors;
                                if (floorId) {
                                    this.formData.hostel_floor_id = String(floorId);
                                } else {
                                    this.formData.hostel_floor_id = '';
                                }
                            }
                        } catch (error) {
                            console.error('Structural retrieval failure:', error);
                        }
                    },

                    async loadRooms(roomId = null) {
                        if (!this.formData.hostel_floor_id) {
                            this.rooms = [];
                            this.formData.hostel_room_id = '';
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-rooms') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ hostel_floor_id: this.formData.hostel_floor_id })
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.rooms = data.rooms;
                                if (roomId) {
                                    this.formData.hostel_room_id = String(roomId);
                                } else {
                                    this.formData.hostel_room_id = '';
                                }
                            }
                        } catch (error) {
                            console.error('Inventory retrieval failure:', error);
                        }
                    },

                    async save() {
                        if (this.submitting) return;

                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode 
                            ? `{{ route('receptionist.hostel-bed-assignments.update', '___ID___') }}`.replace('___ID___', this.assignmentId)
                            : '{{ route('receptionist.hostel-bed-assignments.store') }}';

                        const method = this.editMode ? 'PUT' : 'POST';

                        try {
                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(this.formData)
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) {
                                    await window.Toast.fire({
                                        icon: 'success',
                                        title: result.message || 'Residential mapping synchronized'
                                    });
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Mapping propagation failure');
                            }
                        } catch (error) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(detail) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                message: `Strike residential mapping for student: "${detail.name}"?`,
                                onConfirm: () => this.deleteAssignment(detail.url)
                            }
                        }));
                    },

                    async deleteAssignment(url) {
                        try {
                            const response = await fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                throw new Error(result.message || 'Strike failure');
                            }
                        } catch (error) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
                        }
                    },

                    openAddModal() {
                        this.editMode = false;
                        this.assignmentId = null;
                        this.errors = {};
                        this.resetForm();
                        this.$dispatch('open-modal', 'assignment-modal');
                    },

                    async openEditModal(assignment) {
                        this.editMode = true;
                        this.assignmentId = assignment.id;
                        this.errors = {};
                        this.formData = {
                            student_id: assignment.student_id,
                            student_name: assignment.student_name,
                            class_name: assignment.class_name,
                            hostel_id: String(assignment.hostel_id),
                            hostel_floor_id: String(assignment.hostel_floor_id),
                            hostel_room_id: String(assignment.hostel_room_id),
                            bed_no: assignment.bed_no || '',
                            rent: assignment.rent || '',
                            hostel_assign_date: assignment.hostel_assign_date || '',
                            starting_month: String(assignment.starting_month),
                        };
                        this.admissionSearch = assignment.admission_no;

                        // Re-chain dropdowns
                        await this.loadFloors(assignment.hostel_floor_id);
                        await this.loadRooms(assignment.hostel_room_id);

                        this.$dispatch('open-modal', 'assignment-modal');

                        // Sync other Select2 fields
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (typeof $ !== 'undefined') {
                                    const fields = ['hostel_id', 'starting_month'];
                                    fields.forEach(field => {
                                        $(`select[x-model="formData.${field}"]`).val(this.formData[field]).trigger('change');
                                    });
                                }
                            }, 150);
                        });
                    },

                    resetForm() {
                        this.formData = {
                            student_id: '',
                            student_name: '',
                            class_name: '',
                            hostel_id: '',
                            hostel_floor_id: '',
                            hostel_room_id: '',
                            bed_no: '',
                            rent: '',
                            hostel_assign_date: '{{ date('Y-m-d') }}',
                            starting_month: '',
                        };
                        this.admissionSearch = '';
                        this.floors = [];
                        this.rooms = [];
                        this.errors = {};
                    },

                    closeModal() {
                        this.$dispatch('close-modal', 'assignment-modal');
                        this.errors = {};
                    }
                }));
            });
        </script>
    @endpush
@endsection