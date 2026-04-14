@extends('layouts.receptionist')

@section('title', 'Residential Mapping - Receptionist')
@section('page-title', 'Residential Mapping')
@section('page-description', 'Manage student-to-residential mapping and room assignments')

@section('content')
    <div class="space-y-6" x-data="hostelBedAssignmentManagement" x-init="init()">
        {{-- Statistics Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div
                class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Active Mappings</p>
                    <p class="text-3xl font-black text-gray-800">{{ $assignments->total() }}</p>
                </div>
                <div
                    class="bg-indigo-100 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-link text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Hostel Blocks</p>
                    <p class="text-3xl font-black text-gray-800">{{ $hostels->count() }}</p>
                </div>
                <div
                    class="bg-emerald-100 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-building text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Capacity</p>
                    <p class="text-3xl font-black text-gray-800">
                        {{ \App\Models\HostelRoom::where('school_id', auth()->user()->school_id)->count() * 4 }}</p> {{--
                    Conceptual capacity --}}
                </div>
                <div
                    class="bg-amber-100 p-4 rounded-2xl text-amber-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-bed text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Revenue Stream</p>
                    <p class="text-2xl font-black text-gray-800">₹{{ number_format($assignments->sum('rent'), 0) }}</p>
                </div>
                <div
                    class="bg-purple-100 p-4 rounded-2xl text-purple-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('receptionist.hostels.index') }}"
                        class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Residential Mapping</h2>
                        <p class="text-sm text-gray-500 font-medium">Synchronize student profiles with available residential
                            units</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button @click="$dispatch('open-add-hostel-assignment')"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-indigo-100 group">
                        <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                        Initialize Mapping
                    </button>
                    <a href="{{ route('receptionist.hostel-bed-assignments.export') }}"
                        class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fas fa-file-excel mr-2 text-emerald-500"></i>
                        Export Records
                    </a>
                </div>
            </div>
        </div>

        {{-- Assignments Table --} @php
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
        'key' => 'student_identity',
        'label' => 'STUDENT IDENTITY',
        'sortable' => false,
        'render' => function($row) {
        $student = $row->student;
        $name = $student ? trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) : 'N/A';
        $admission = $student->admission_no ?? 'N/A';
        return '<div class="flex flex-col">
            <span class="font-black text-gray-800">' . $name . '</span>
            <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter">' . $admission . ' • ' .
                ($student->class->name ?? 'N/A') . '</span>
        </div>';
        }
        ],
        [
        'key' => 'block_map',
        'label' => 'BLOCK MAPPING',
        'sortable' => false,
        'render' => function($row) {
        return '<div class="flex flex-col">
            <span class="font-bold text-gray-700 text-xs">' . ($row->hostel->hostel_name ?? 'N/A') . '</span>
            <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter">' . ($row->floor->floor_name ??
                'N/A') . ' • Room ' . ($row->room->room_name ?? 'N/A') . '</span>
        </div>';
        }
        ],
        [
        'key' => 'bed_no',
        'label' => 'UNIT ID',
        'sortable' => true,
        'render' => function($row) {
        return '<span
            class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-[10px] font-black uppercase tracking-widest">' .
            ($row->bed_no ?: 'GENERIC') . '</span>';
        }
        ],
        [
        'key' => 'rent',
        'label' => 'MONTHLY RENT',
        'sortable' => true,
        'render' => function($row) {
        return '<span class="font-black text-gray-800">₹' . number_format($row->rent, 2) . '</span>';
        }
        ],
        [
        'key' => 'timestamp',
        'label' => 'ASSIGNED ON',
        'sortable' => true,
        'render' => function($row) {
        return '<div class="text-gray-500 text-xs font-bold">' .
            ($row->hostel_assign_date ? $row->hostel_assign_date->format('d M, Y') : 'N/A') .
            '</div>';
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
        'student_name' => trim($row->student->first_name . ' ' . $row->student->middle_name . ' ' .
        $row->student->last_name),
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
        'class' => 'text-indigo-600 hover:text-indigo-900',
        'title' => 'Edit Mapping',
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
        'title' => 'Dismantle Mapping',
        ],
        ];
        @endphp

        <div
            class="bg-white/80 backdrop-blur-md rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden">
            <x-data-table :columns="$tableColumns" :data="$assignments" :searchable="true" :actions="$tableActions"
                empty-message="No residential mappings initialized" empty-icon="fas fa-link-slash" />
        </div>

        {{-- Add/Edit Mapping Modal --}}
        <x-modal name="assignment-modal"
            alpineTitle="editMode ? 'Synchronize Residential Revision' : 'Initialize Residential Mapping'" maxWidth="3xl">
            <form @submit.prevent="save" method="POST" class="p-0 relative">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-8 space-y-8">
                    {{-- Student Selection --}}
                    <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100 space-y-6">
                        <h4
                            class="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-user-graduate"></i>
                            Student Identification
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative">
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Admission
                                    No / Search <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <input type="text" x-model="admissionSearch"
                                        @input="searchStudents(); clearError('student_id')"
                                        @focus="showStudentDropdown = true" placeholder="Type student name or ID..."
                                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 shadow-sm"
                                        :class="errors.student_id ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''"
                                        autocomplete="off">
                                    <div
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                        <i class="fas fa-search"></i>
                                    </div>
                                </div>

                                {{-- Student Dropdown Results --}}
                                <div x-show="showStudentDropdown && studentResults.length > 0"
                                    @click.outside="showStudentDropdown = false"
                                    class="absolute z-[60] w-full mt-2 bg-white rounded-2xl shadow-2xl shadow-indigo-100/50 border border-gray-100 max-h-72 overflow-y-auto overflow-x-hidden transition-all py-2">
                                    <template x-for="student in studentResults" :key="student.id">
                                        <div @click="selectStudent(student)"
                                            class="px-5 py-3 hover:bg-indigo-50/50 cursor-pointer border-b border-gray-50 last:border-0 transition-colors flex items-center gap-4">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-black text-xs"
                                                x-text="student.admission_no.slice(-2)"></div>
                                            <div class="flex-1">
                                                <div class="font-black text-gray-800 text-sm" x-text="student.name"></div>
                                                <div class="text-[10px] text-gray-400 uppercase font-black tracking-tighter"
                                                    x-text="student.admission_no + ' • ' + student.class_name"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="errors.student_id">
                                    <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                        x-text="errors.student_id[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Authenticated
                                    Profile</label>
                                <div
                                    class="px-5 py-3.5 bg-indigo-50/30 border border-indigo-100 rounded-2xl flex items-center justify-between">
                                    <div>
                                        <span class="block text-sm font-black text-gray-800"
                                            x-text="formData.student_name || 'No selection'"></span>
                                        <span class="block text-[10px] text-indigo-500 font-black uppercase"
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
                        <h4
                            class="text-[10px] font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2 px-1">
                            <i class="fas fa-map-location-dot"></i>
                            Residential Hierarchy
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Hostel
                                    Block <span class="text-red-500">*</span></label>
                                <select x-model="formData.hostel_id" @change="loadFloors(); clearError('hostel_id')"
                                    class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500"
                                    :class="errors.hostel_id ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Block</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="errors.hostel_id">
                                    <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                        x-text="errors.hostel_id[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Floor
                                    Level <span class="text-red-500">*</span></label>
                                <select x-model="formData.hostel_floor_id"
                                    @change="loadRooms(); clearError('hostel_floor_id')" :disabled="!formData.hostel_id"
                                    class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 disabled:opacity-50"
                                    :class="errors.hostel_floor_id ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Floor</option>
                                    <template x-for="floor in floors" :key="floor.id">
                                        <option :value="floor.id" x-text="floor.floor_name"></option>
                                    </template>
                                </select>
                                <template x-if="errors.hostel_floor_id">
                                    <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                        x-text="errors.hostel_floor_id[0]"></p>
                                </template>
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Residential
                                    Unit <span class="text-red-500">*</span></label>
                                <select x-model="formData.hostel_room_id" @change="clearError('hostel_room_id')"
                                    :disabled="!formData.hostel_floor_id"
                                    class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 disabled:opacity-50"
                                    :class="errors.hostel_room_id ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                    <option value="">Select Room</option>
                                    <template x-for="room in rooms" :key="room.id">
                                        <option :value="room.id" x-text="room.room_name"></option>
                                    </template>
                                </select>
                                <template x-if="errors.hostel_room_id">
                                    <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                        x-text="errors.hostel_room_id[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Financial & Scheduling --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Mapping
                                Specifications</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Unit
                                        Identifier</label>
                                    <input type="text" x-model="formData.bed_no" @input="clearError('bed_no')"
                                        placeholder="e.g., Bed A"
                                        class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500"
                                        :class="errors.bed_no ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                    <template x-if="errors.bed_no">
                                        <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                            x-text="errors.bed_no[0]"></p>
                                    </template>
                                </div>
                                <div>
                                    <label
                                        class="modal-label-premium mb-2 italic">Fee
                                        Structure</label>
                                    <div class="relative group">
                                        <input type="number" step="0.01" x-model="formData.rent" @input="clearError('rent')"
                                            placeholder="0.00"
                                            class="modal-input-premium pr-10"
                                            :class="errors.rent ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                            <i class="fas fa-rupee-sign text-[10px]"></i>
                                        </div>
                                    </div>
                                    <template x-if="errors.rent">
                                        <p class="modal-error-message"
                                            x-text="errors.rent[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Mapping
                                Schedule</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Effective
                                        Date</label>
                                    <input type="date" x-model="formData.hostel_assign_date"
                                        @input="clearError('hostel_assign_date')"
                                        class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500"
                                        :class="errors.hostel_assign_date ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                    <template x-if="errors.hostel_assign_date">
                                        <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                            x-text="errors.hostel_assign_date[0]"></p>
                                    </template>
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Billing
                                        Cycle <span class="text-red-500">*</span></label>
                                    <select x-model="formData.starting_month" @change="clearError('starting_month')"
                                        class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500"
                                        :class="errors.starting_month ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                                        <option value="">Choose Cycle</option>
                                        <template x-for="month in months" :key="month.value">
                                            <option :value="month.value" x-text="month.label"></option>
                                        </template>
                                    </select>
                                    <template x-if="errors.starting_month">
                                        <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight"
                                            x-text="errors.starting_month[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div
                    class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-3xl">
                    <button type="button" @click="closeModal()" :disabled="submitting"
                        class="px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm disabled:opacity-50">
                        Discard
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all font-black text-sm shadow-xl shadow-indigo-100 flex items-center gap-2">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <span
                            x-text="submitting ? 'Propagating...' : (editMode ? 'Synch Revision' : 'Confirm Mapping')"></span>
                    </button>
                </div>
            </form>
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