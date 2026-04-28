@extends('layouts.school')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Attendance Report</h1>
            <p class="text-gray-600">Monthly attendance summary for hostel rooms</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.hostel.attendance.index') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md">
                <i class="fas fa-plus"></i>
                <span>Mark Attendance</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6" x-data="{
        hostels: @js($hostels),
        floors: [],
        rooms: [],
        filters: {
            hostel_id: '{{ request('hostel_id') }}',
            hostel_floor_id: '{{ request('hostel_floor_id') }}',
            hostel_room_id: '{{ request('hostel_room_id') }}'
        },
        async fetchFloors() {
            if (!this.filters.hostel_id) { this.floors = []; this.rooms = []; return; }
            const response = await fetch(`/school/hostel/floors/by-hostel/${this.filters.hostel_id}`);
            this.floors = await response.json();
            this.rooms = [];
        },
        async fetchRooms() {
            if (!this.filters.hostel_floor_id) { this.rooms = []; return; }
            const response = await fetch(`/school/hostel/rooms/by-floor/${this.filters.hostel_floor_id}`);
            this.rooms = await response.json();
        },
        init() {
            if (this.filters.hostel_id) this.fetchFloors().then(() => {
                if (this.filters.hostel_floor_id) this.fetchRooms();
            });
        }
    }">
        <form action="{{ route('school.hostel.attendance.month_wise_report') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hostel</label>
                <select name="hostel_id" x-model="filters.hostel_id" @change="fetchFloors()" required class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select Hostel</option>
                    <template x-for="h in hostels" :key="h.id">
                        <option :value="h.id" x-text="h.hostel_name" :selected="h.id == filters.hostel_id"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Floor</label>
                <select name="hostel_floor_id" x-model="filters.hostel_floor_id" @change="fetchRooms()" required :disabled="!floors.length" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select Floor</option>
                    <template x-for="f in floors" :key="f.id">
                        <option :value="f.id" x-text="f.floor_name" :selected="f.id == filters.hostel_floor_id"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Room</label>
                <select name="hostel_room_id" x-model="filters.hostel_room_id" required :disabled="!rooms.length" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select Room</option>
                    <template x-for="r in rooms" :key="r.id">
                        <option :value="r.id" x-text="r.room_name" :selected="r.id == filters.hostel_room_id"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Month</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" required class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md">
                    <i class="fas fa-filter mr-2"></i>
                    <span>Generate Report</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Report Table -->
    @if(!empty($reportData['students']))
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800">
                Attendance for {{ $reportData['month_name'] }} - 
                <span class="text-indigo-600">{{ $selectedHostel->hostel_name }}</span> | 
                <span class="text-indigo-600">{{ $selectedFloor->floor_name }}</span> | 
                <span class="text-indigo-600">{{ $selectedRoom->room_name }}</span>
            </h2>
            <div class="flex gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-green-500 rounded-sm"></div>
                    <span>Present</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-red-500 rounded-sm"></div>
                    <span>Absent</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="sticky left-0 z-10 bg-gray-50 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase border-r border-gray-200 min-w-[200px]">Student Details</th>
                        @for($i = 1; $i <= $reportData['days_in_month']; $i++)
                            <th class="px-1 py-3 text-center text-[10px] font-bold text-gray-500 uppercase min-w-[30px] border-r border-gray-100">{{ $i }}</th>
                        @endfor
                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase min-w-[60px] bg-indigo-50">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reportData['students'] as $student)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="sticky left-0 z-10 bg-white px-4 py-3 whitespace-nowrap border-r border-gray-200 group-hover:bg-gray-50">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900">{{ $student['name'] }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase tracking-tighter">{{ $student['admission_no'] }}</span>
                                </div>
                            </td>
                            @php $presentCount = 0; @endphp
                            @for($i = 1; $i <= $reportData['days_in_month']; $i++)
                                <td class="px-1 py-3 text-center border-r border-gray-100">
                                    @if($student['days'][$i])
                                        <div class="w-4 h-4 bg-green-500 rounded-sm mx-auto shadow-sm"></div>
                                        @php $presentCount++; @endphp
                                    @else
                                        <div class="w-4 h-4 bg-red-100 border border-red-200 rounded-sm mx-auto"></div>
                                    @endif
                                </td>
                            @endfor
                            <td class="px-3 py-3 text-center font-bold text-indigo-700 bg-indigo-50/50">
                                {{ $presentCount }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
        <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-calendar-alt text-4xl text-indigo-200"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">No Report Data</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Select a hostel room and month to view the attendance summary report.</p>
    </div>
    @endif
</div>
@endsection
