@extends('layouts.receptionist')

@section('title', 'Route Management - Receptionist')
@section('page-title', 'Route Management')
@section('page-description', 'Manage transportation routes')

@section('content')
<div class="space-y-6" x-data="routeManagement" x-init="init()">
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
            <h2 class="text-xl font-bold text-gray-800">Route List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Route
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Routes Table --}}
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'sortable' => false,
                'render' => function($row) {
                    static $index = 0;
                    return ++$index;
                }
            ],
            [
                'key' => 'route_name',
                'label' => 'ROUTE NAME',
                'sortable' => true,
            ],
            [
                'key' => 'vehicle_no',
                'label' => 'VEHICLE NO',
                'sortable' => false,
                'render' => function($row) {
                    return $row->vehicle ? $row->vehicle->vehicle_no : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-route'))))";
                },
                'data-route' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'route_name' => $row->route_name,
                        'vehicle_id' => $row->vehicle_id,
                        'route_create_date' => $row->route_create_date ? $row->route_create_date->format('Y-m-d') : null,
                        'status' => $row->status,
                    ]));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'action' => function($row) {
                    return route('receptionist.routes.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this route?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => 'confirm-delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$routes"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No routes found"
        empty-icon="fas fa-route"
    >
        Route List
    </x-data-table>

    {{-- Add/Edit Route Modal --}}
    <div x-show="showModal" x-cloak 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto w-full max-w-md shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            {{-- Modal Header --}}
            <div class="bg-teal-500 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit Route' : 'Add New Route'"></h3>
                <button @click="closeModal()" class="text-white hover:text-teal-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form :action="editMode ? `/receptionist/routes/${routeId}` : '{{ route('receptionist.routes.store') }}'" 
                  method="POST" class="p-6">
                @csrf
                <template x-if="editMode">
                    @method('PUT')
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Route Name *</label>
                        <input type="text" name="route_name" x-model="formData.route_name" value="{{ old('route_name') }}"
                               placeholder="Enter Route Name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('route_name') border-red-500 @enderror">
                        @error('route_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Select Vehicle *</label>
                        <select name="vehicle_id" x-model="formData.vehicle_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('vehicle_id') border-red-500 @enderror">
                            <option value="">Select Vehicle</option>
                            <template x-for="vehicle in vehicles" :key="vehicle.id">
                                <option :value="vehicle.id" x-text="`${vehicle.vehicle_no} (${vehicle.registration_no})`" :selected="vehicle.id == {{ old('vehicle_id', '') }}"></option>
                            </template>
                        </select>
                        @error('vehicle_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Route Create Date *</label>
                        <input type="date" name="route_create_date" x-model="formData.route_create_date" value="{{ old('route_create_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('route_create_date') border-red-500 @enderror">
                        @error('route_create_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <template x-if="editMode">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select name="status" x-model="formData.status"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('status') border-red-500 @enderror">
                                <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="mt-6 flex items-center justify-center gap-4">
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
        </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('routeManagement', () => ({
        showModal: false,
        editMode: false,
        routeId: null,
        vehicles: [],
        formData: {
            route_name: '',
            vehicle_id: '',
            route_create_date: '',
            status: 1,
        },
        
        async init() {
            // Fetch available vehicles
            await this.fetchVehicles();
            
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.formData = {
                    route_name: '{{ old('route_name') }}',
                    vehicle_id: '{{ old('vehicle_id') }}',
                    route_create_date: '{{ old('route_create_date') }}',
                    status: {{ old('status', 1) }},
                };
                this.showModal = true;
            @endif
        },
        
        async fetchVehicles() {
            try {
                const response = await fetch('/receptionist/routes/vehicles');
                if (response.ok) {
                    this.vehicles = await response.json();
                }
            } catch (error) {
                console.error('Error fetching vehicles:', error);
            }
        },
        
        openAddModal() {
            this.editMode = false;
            this.routeId = null;
            this.formData = {
                route_name: '',
                vehicle_id: '',
                route_create_date: '',
                status: 1,
            };
            this.showModal = true;
        },
        
        openEditModal(route) {
            this.editMode = true;
            this.routeId = route.id;
            this.formData = {
                route_name: route.route_name || '',
                vehicle_id: route.vehicle_id || '',
                route_create_date: route.route_create_date || '',
                status: route.status || 1,
            };
            this.showModal = true;
            
            // Update Select2 dropdown after modal is shown
            this.$nextTick(() => {
                setTimeout(() => {
                    $('select[name="vehicle_id"]').val(route.vehicle_id).trigger('change.select2');
                    $('select[name="status"]').val(route.status).trigger('change.select2');
                }, 100);
            });
        },
        
        closeModal() {
            this.showModal = false;
        },
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(route) {
    const component = Alpine.$data(document.querySelector('[x-data*="routeManagement"]'));
    if (component) {
        component.openEditModal(route);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all inputs and selects in the modal
    const modal = document.querySelector('[x-data*="routeManagement"]');
    if (modal) {
        // Handle regular inputs
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Handle native selects and Select2 selects
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Handle Select2 change events specifically
        $(modal).on('select2:select select2:clear', 'select', function(e) {
            const select = e.target;
            // Find the error message (it might be after the Select2 container)
            let errorElement = select.nextElementSibling;
            
            // If next sibling is Select2 container, look for error after it
            if (errorElement && errorElement.classList.contains('select2')) {
                errorElement = errorElement.nextElementSibling;
            }
            
            if (errorElement && errorElement.classList.contains('text-red-500')) {
                errorElement.classList.add('hidden');
            }
            
            // Remove red border from original select
            select.classList.remove('border-red-500');
            
            // Also remove red border from Select2 container
            const select2Container = $(select).next('.select2-container').find('.select2-selection');
            select2Container.removeClass('border-red-500');
        });
    }
});
</script>
@endpush
@endsection
