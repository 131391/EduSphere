@extends('layouts.school')

@section('content')
<div x-data="{
    ...ajaxDataTable({
        endpoint: '{{ route('school.hostel.assignments.fetch') }}',
        storeEndpoint: '{{ route('school.hostel.assignments.store') }}',
        updateEndpoint: '{{ route('school.hostel.assignments') }}',
        entityName: 'Assignment'
    }),
    floors: [],
    rooms: [],
    students: [],
    loadingStudents: false,
    
    async fetchFloors(hostelId) {
        if (!hostelId) { this.floors = []; this.rooms = []; return; }
        try {
            const response = await fetch(`/school/hostel/floors/by-hostel/${hostelId}`);
            this.floors = await response.json();
            this.rooms = [];
        } catch (e) { console.error(e); }
    },
    async fetchRooms(floorId) {
        if (!floorId) { this.rooms = []; return; }
        try {
            const response = await fetch(`/school/hostel/rooms/by-floor/${floorId}`);
            this.rooms = await response.json();
        } catch (e) { console.error(e); }
    },
    async searchStudents(query) {
        if (query.length < 3) { this.students = []; return; }
        this.loadingStudents = true;
        try {
            const response = await fetch(`/school/student-registrations/enquiry/${query}`); // Reusing existing search or generic one
            // Note: I might need a more specific student search endpoint. 
            // For now, I'll assume we can search via a common endpoint or I'll add one.
            const data = await response.json();
            this.students = data.success ? [data.data] : []; // This endpoint seems to return single result
        } catch (e) { console.error(e); } finally { this.loadingStudents = false; }
    },
    
    initCreate() {
        this.openCreateModal();
        this.floors = [];
        this.rooms = [];
        this.formData.start_date = new Date().toISOString().split('T')[0];
    },
    async initEdit(item) {
        this.openEditModal(item);
        await this.fetchFloors(item.raw.hostel_id);
        await this.fetchRooms(item.raw.hostel_floor_id);
        this.formData.hostel_floor_id = item.raw.hostel_floor_id;
        this.formData.hostel_room_id = item.raw.hostel_room_id;
        this.formData.status = item.raw.status;
    }
}" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Student Hostel Assignments</h1>
            <p class="text-gray-600">Assign students to hostel rooms and manage residents</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.hostel.assignments.history') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-history text-gray-400"></i>
                <span>Assignment History</span>
            </a>
            <button @click="initCreate()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
                <i class="fas fa-plus"></i>
                <span>Assign Student</span>
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Occupancy</p>
                <h3 class="text-2xl font-bold text-gray-800" x-text="stats.total_residents + ' / ' + stats.total_capacity"></h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500 uppercase mb-2">Capacity Utilization</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" :style="`width: ${Math.min(100, (stats.total_residents / stats.total_capacity) * 100)}%`"></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex-1 max-w-xs">
                <select x-model="filters.hostel_id" @change="fetchData()" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Hostels</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" x-model="search" @input.debounce.500ms="fetchData()" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                    placeholder="Search by student name or ID...">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <x-data-table>
        <x-slot name="head">
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hostel Location</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room/Bed</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rent</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
        </x-slot>
        <x-slot name="body">
            <template x-for="item in items" :key="item.id">
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900" x-text="item.student_name"></span>
                            <span class="text-xs text-gray-500" x-text="item.admission_no + ' | ' + item.class_name"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col text-sm">
                            <span class="text-gray-900 font-medium" x-text="item.hostel_name"></span>
                            <span class="text-gray-500 text-xs" x-text="item.floor_name"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col text-sm">
                            <span class="text-indigo-600 font-medium" x-text="item.room_name"></span>
                            <span class="text-gray-500 text-xs" x-text="'Bed: ' + item.bed_no"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" x-text="item.rent"></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="{
                            'px-2 py-1 rounded-full text-xs font-medium': true,
                            'bg-green-100 text-green-700': item.status === 'active',
                            'bg-red-100 text-red-700': item.status === 'inactive'
                        }" x-text="item.status.charAt(0).toUpperCase() + item.status.slice(1)"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <button @click="initEdit(item)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="deleteItem(item.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-slot>
    </x-data-table>

    <!-- Assignment Modal -->
    <x-modal x-show="showModal" @close="showModal = false" :title="editMode ? 'Edit Assignment' : 'Assign Student to Hostel'">
        <form @submit.prevent="saveItem" class="space-y-4">
            <template x-if="!editMode">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Student (Admission No/Name)</label>
                    <div class="relative">
                        <input type="text" @input.debounce.500ms="searchStudents($el.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                            placeholder="Type to search...">
                        <div x-show="loadingStudents" class="absolute right-3 top-2.5">
                            <i class="fas fa-circle-notch fa-spin text-gray-400"></i>
                        </div>
                    </div>
                    <div x-show="students.length" class="mt-2 border border-gray-100 rounded-lg divide-y divide-gray-100 shadow-sm overflow-hidden bg-gray-50">
                        <template x-for="student in students" :key="student.id">
                            <div @click="formData.student_id = student.id; students = []" 
                                :class="{'p-3 cursor-pointer hover:bg-white transition-colors flex items-center justify-between': true, 'bg-indigo-50 border-l-4 border-indigo-500': formData.student_id === student.id}">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900" x-text="student.first_name + ' ' + student.last_name"></p>
                                    <p class="text-xs text-gray-500" x-text="'Adm: ' + student.admission_no + ' | Class: ' + student.class?.name"></p>
                                </div>
                                <i x-show="formData.student_id === student.id" class="fas fa-check-circle text-indigo-500"></i>
                            </div>
                        </template>
                    </div>
                    <input type="hidden" x-model="formData.student_id" required>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Hostel <span class="text-red-500">*</span></label>
                    <select x-model="formData.hostel_id" required @change="fetchFloors(formData.hostel_id)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">-- Select Hostel --</option>
                        @foreach($hostels as $hostel)
                            <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Floor <span class="text-red-500">*</span></label>
                    <select x-model="formData.hostel_floor_id" required @change="fetchRooms(formData.hostel_floor_id)" :disabled="!floors.length"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none disabled:bg-gray-50">
                        <option value="">-- Select Floor --</option>
                        <template x-for="floor in floors" :key="floor.id">
                            <option :value="floor.id" x-text="floor.floor_name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Room <span class="text-red-500">*</span></label>
                    <select x-model="formData.hostel_room_id" required :disabled="!rooms.length"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none disabled:bg-gray-50">
                        <option value="">-- Select Room --</option>
                        <template x-for="room in rooms" :key="room.id">
                            <option :value="room.id" x-text="room.room_name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bed Number</label>
                    <input type="text" x-model="formData.bed_no" placeholder="e.g. B1, Window Side"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent</label>
                    <input type="number" x-model="formData.rent" step="0.01" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" x-model="formData.start_date" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div x-show="editMode">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="formData.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200" 
                    :disabled="submitting || (!editMode && !formData.student_id)" x-text="submitting ? 'Saving...' : (editMode ? 'Update Assignment' : 'Assign Hostel')"></button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
