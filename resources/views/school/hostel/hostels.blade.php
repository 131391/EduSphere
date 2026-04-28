@extends('layouts.school')

@section('content')
<div x-data="ajaxDataTable({
    endpoint: '{{ route('school.hostel.hostels.fetch') }}',
    storeEndpoint: '{{ route('school.hostel.hostels.store') }}',
    updateEndpoint: '{{ route('school.hostel.hostels') }}',
    exportEndpoint: '{{ route('school.hostel.hostels.export') }}',
    entityName: 'Hostel'
})" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Hostel Management</h1>
            <p class="text-gray-600">Manage school hostels and their capacities</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="exportData()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-file-export text-gray-400"></i>
                <span>Export CSV</span>
            </button>
            <button @click="openCreateModal()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
                <i class="fas fa-plus"></i>
                <span>Add Hostel</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Total Hostels" :value="$stats['total_hostels']" icon="fas fa-hotel" color="blue" />
        <x-stat-card title="Total Rooms" :value="$stats['total_rooms']" icon="fas fa-door-open" color="indigo" />
        <x-stat-card title="Bed Capacity" :value="$stats['total_beds']" icon="fas fa-bed" color="purple" />
        <x-stat-card title="Active Residents" :value="$stats['total_residents']" icon="fas fa-users" color="green" />
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" x-model="search" @input.debounce.500ms="fetchData()" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                    placeholder="Search by name or warden...">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <x-data-table>
        <x-slot name="head">
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hostel Name</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Warden/Incharge</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Capacity</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created Date</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
        </x-slot>
        <x-slot name="body">
            <template x-for="item in items" :key="item.id">
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3">
                                <i class="fas fa-hotel text-xs"></i>
                            </div>
                            <span class="font-medium text-gray-900" x-text="item.hostel_name"></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="item.hostel_incharge || 'Not Assigned'"></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700" x-text="item.capability_label"></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="item.hostel_create_date"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <button @click="openEditModal(item)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
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
    <x-modal x-show="showModal" @close="showModal = false" :title="editMode ? 'Edit Hostel' : 'Add New Hostel'">
        <form @submit.prevent="saveItem" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hostel Name <span class="text-red-500">*</span></label>
                <input type="text" x-model="formData.hostel_name" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Warden/Incharge</label>
                <input type="text" x-model="formData.hostel_incharge" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Bed Capacity</label>
                    <input type="number" x-model="formData.capability" min="1" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Established Date</label>
                    <input type="date" x-model="formData.hostel_create_date" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200" 
                    :disabled="submitting" x-text="submitting ? 'Saving...' : 'Save Hostel'"></button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
