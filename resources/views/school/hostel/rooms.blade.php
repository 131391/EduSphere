@extends('layouts.school')

@section('content')
<div x-data="{
    ...ajaxDataTable({
        endpoint: '{{ route('school.hostel.rooms.fetch') }}',
        storeEndpoint: '{{ route('school.hostel.rooms.store') }}',
        updateEndpoint: '{{ route('school.hostel.rooms') }}',
        entityName: 'Room'
    }),
    floors: [],
    async fetchFloors(hostelId) {
        if (!hostelId) {
            this.floors = [];
            return;
        }
        try {
            const response = await fetch(`/school/hostel/floors/by-hostel/${hostelId}`);
            this.floors = await response.json();
        } catch (e) {
            console.error('Failed to fetch floors', e);
        }
    },
    // Override openCreateModal to clear floors
    initCreate() {
        this.openCreateModal();
        this.floors = [];
        this.formData.no_of_beds = '';
    },
    // Override openEditModal to fetch floors for existing hostel
    async initEdit(item) {
        this.openEditModal(item);
        await this.fetchFloors(item.raw.hostel_id);
        this.formData.hostel_floor_id = item.raw.hostel_floor_id;
        this.formData.no_of_beds = item.raw.no_of_beds || '';
    }
}" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Hostel Rooms</h1>
            <p class="text-gray-600">Manage rooms and bed allotments</p>
        </div>
        <button @click="initCreate()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
            <i class="fas fa-plus"></i>
            <span>Add Room</span>
        </button>
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
                    placeholder="Search by room name...">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <x-data-table>
        <x-slot name="head">
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room Name</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Floor</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hostel</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Capacity</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assignments</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
        </x-slot>
        <x-slot name="body">
            <template x-for="item in items" :key="item.id">
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="item.room_name"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="item.floor_name"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="item.hostel_name"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <span x-text="item.no_of_beds + ' Beds'"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <span class="px-2 py-1 bg-green-50 text-green-600 rounded-md" 
                              :class="{'bg-red-50 text-red-600': item.occupancy_count >= item.no_of_beds}"
                              x-text="item.occupancy_count + ' / ' + item.no_of_beds + ' Occupied'"></span>
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

    <!-- Modal -->
    <x-modal x-show="showModal" @close="showModal = false" :title="editMode ? 'Edit Room' : 'Add New Room'">
        <form @submit.prevent="saveItem" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Hostel <span class="text-red-500">*</span></label>
                <select x-model="formData.hostel_id" required @change="fetchFloors(formData.hostel_id)"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <option value="">-- Select Hostel --</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Floor <span class="text-red-500">*</span></label>
                <select x-model="formData.hostel_floor_id" required :disabled="!floors.length"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all disabled:bg-gray-50 disabled:cursor-not-allowed">
                    <option value="">-- Select Floor --</option>
                    <template x-for="floor in floors" :key="floor.id">
                        <option :value="floor.id" x-text="floor.floor_name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Name/Number <span class="text-red-500">*</span></label>
                <input type="text" x-model="formData.room_name" required placeholder="e.g. Room 101, A-1" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Beds (Capacity) <span class="text-red-500">*</span></label>
                <input type="number" min="1" x-model="formData.no_of_beds" required placeholder="e.g. 2" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200" 
                    :disabled="submitting" x-text="submitting ? 'Saving...' : 'Save Room'"></button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
