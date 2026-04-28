@extends('layouts.school')

@section('content')
<div x-data="{
    hostels: @js($hostels),
    academicYears: @js($academicYears),
    floors: [],
    rooms: [],
    students: [],
    loading: false,
    marking: false,
    filters: {
        hostel_id: '',
        hostel_floor_id: '',
        hostel_room_id: '',
        attendance_date: new Date().toISOString().split('T')[0],
        academic_year_id: '{{ $academicYears->firstWhere('is_current', true)?->id ?? '' }}'
    },
    
    async fetchFloors() {
        if (!this.filters.hostel_id) { this.floors = []; this.rooms = []; return; }
        const response = await fetch(`/school/hostel/floors/by-hostel/${this.filters.hostel_id}`);
        this.floors = await response.json();
        this.rooms = [];
        this.students = [];
    },
    async fetchRooms() {
        if (!this.filters.hostel_floor_id) { this.rooms = []; return; }
        const response = await fetch(`/school/hostel/rooms/by-floor/${this.filters.hostel_floor_id}`);
        this.rooms = await response.json();
        this.students = [];
    },
    async loadStudents() {
        if (!this.filters.hostel_room_id || !this.filters.academic_year_id) return;
        this.loading = true;
        try {
            const response = await fetch(`/school/hostel/attendance/residents?hostel_room_id=${this.filters.hostel_room_id}`);
            const data = await response.json();
            this.students = data.map(s => ({
                student_id: s.student_id,
                name: s.student_name,
                admission_no: s.admission_no,
                is_present: true,
                remarks: ''
            }));
        } catch (e) {
            console.error(e);
            Toast.error('Failed to load residents');
        } finally {
            this.loading = false;
        }
    },
    async saveAttendance() {
        this.marking = true;
        try {
            const response = await fetch('{{ route('school.hostel.attendance.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ...this.filters,
                    attendance_data: this.students
                })
            });
            const data = await response.json();
            if (data.success) {
                Toast.success(data.message);
            } else {
                Toast.error(data.message);
            }
        } catch (e) {
            console.error(e);
            Toast.error('An error occurred while saving attendance');
        } finally {
            this.marking = false;
        }
    },
    toggleAll(present) {
        this.students.forEach(s => s.is_present = present);
    }
}" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Hostel Attendance</h1>
            <p class="text-gray-600">Mark daily attendance for hostel residents</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.hostel.attendance.month_wise_report') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-calendar-alt text-gray-400"></i>
                <span>Attendance Report</span>
            </a>
        </div>
    </div>

    <!-- Selection Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hostel</label>
                <select x-model="filters.hostel_id" @change="fetchFloors()" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Hostel</option>
                    <template x-for="h in hostels" :key="h.id">
                        <option :value="h.id" x-text="h.hostel_name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Floor</label>
                <select x-model="filters.hostel_floor_id" @change="fetchRooms()" :disabled="!floors.length" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select Floor</option>
                    <template x-for="f in floors" :key="f.id">
                        <option :value="f.id" x-text="f.floor_name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Room</label>
                <select x-model="filters.hostel_room_id" @change="loadStudents()" :disabled="!rooms.length" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select Room</option>
                    <template x-for="r in rooms" :key="r.id">
                        <option :value="r.id" x-text="r.room_name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Date</label>
                <input type="date" x-model="filters.attendance_date" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="flex items-end pb-0.5">
                <button @click="loadStudents()" :disabled="!filters.hostel_room_id" class="w-full py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md disabled:bg-gray-300 disabled:shadow-none">
                    <i class="fas fa-sync-alt mr-2" :class="loading ? 'fa-spin' : ''"></i>
                    <span>Fetch List</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Attendance List -->
    <div x-show="students.length > 0" x-transition class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800">Resident List</h2>
            <div class="flex gap-2">
                <button @click="toggleAll(true)" class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-200 transition-colors">Mark All Present</button>
                <button @click="toggleAll(false)" class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-200 transition-colors">Mark All Absent</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adm No</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attendance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(student, index) in students" :key="student.student_id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="student.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="student.admission_no"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-4">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" :name="'att_' + student.student_id" :value="true" x-model="student.is_present" 
                                            class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Present</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" :name="'att_' + student.student_id" :value="false" x-model="student.is_present"
                                            class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-700">Absent</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <input type="text" x-model="student.remarks" placeholder="Add optional remarks..." 
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-xs">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button @click="saveAttendance()" :disabled="marking" 
                class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 disabled:bg-gray-400 disabled:shadow-none">
                <i class="fas fa-save mr-2" x-show="!marking"></i>
                <i class="fas fa-circle-notch fa-spin mr-2" x-show="marking"></i>
                <span x-text="marking ? 'Saving Attendance...' : 'Save Attendance'"></span>
            </button>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="students.length === 0 && !loading" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-user-check text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-1">Select a Room</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Please select a hostel, floor, and room above to load the resident list for attendance marking.</p>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="p-12 text-center">
        <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-500"></i>
        <p class="mt-4 text-gray-500 font-medium tracking-wide animate-pulse">Loading resident list...</p>
    </div>
</div>
@endsection
