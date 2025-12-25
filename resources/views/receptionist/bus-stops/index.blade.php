@extends('layouts.receptionist')

@section('title', 'Bus Stop Management - Receptionist')
@section('page-title', 'Bus Stop Management')
@section('page-description', 'Manage bus stops and transportation points')

@section('content')
<div class="space-y-6" x-data="busStopManagement" x-init="init()">
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
            <div>
                <h2 class="text-xl font-bold text-gray-800">Bus Stop List</h2>
                <p class="text-sm text-gray-600 mt-1">Manage all bus stops and pickup points</p>
            </div>
            <div class="flex gap-2">
                <button @click="openAddModal()" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Add Bus Stop
                </button>
                <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </button>
            </div>
        </div>
    </div>

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
                'render' => function($row) {
                    return $row->route ? $row->route->route_name : 'N/A';
                }
            ],
            [
                'key' => 'bus_stop_no',
                'label' => 'BUS STOP NO',
                'sortable' => true,
            ],
            [
                'key' => 'bus_stop_name',
                'label' => 'BUS STOP NAME',
                'sortable' => true,
            ],
            [
                'key' => 'latitude',
                'label' => 'LATITUDE',
                'sortable' => false,
            ],
            [
                'key' => 'longitude',
                'label' => 'LONGITUDE',
                'sortable' => false,
            ],
            [
                'key' => 'distance_from_institute',
                'label' => 'DISTANCE (KM)',
                'sortable' => false,
            ],
            [
                'key' => 'charge_per_month',
                'label' => 'CHARGE/MONTH',
                'sortable' => false,
            ],
            [
                'key' => 'area_pin_code',
                'label' => 'AREA PIN CODE',
                'sortable' => false,
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-busstop'))))";
                },
                'data-busstop' => function($row) {
                    $busStopData = [
                        'id' => $row->id,
                        'route_id' => $row->route_id,
                        'vehicle_id' => $row->vehicle_id,
                        'bus_stop_no' => $row->bus_stop_no,
                        'bus_stop_name' => $row->bus_stop_name,
                        'latitude' => $row->latitude,
                        'longitude' => $row->longitude,
                        'distance_from_institute' => $row->distance_from_institute,
                        'charge_per_month' => $row->charge_per_month,
                        'area_pin_code' => $row->area_pin_code,
                    ];
                    return base64_encode(json_encode($busStopData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => function($row) {
                    return route('receptionist.bus-stops.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this bus stop?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$busStops"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No bus stops found"
        empty-icon="fas fa-map-marker-alt"
    >
        Bus Stop List
    </x-data-table>

    {{-- Add/Edit Bus Stop Modal --}}
    <x-modal name="bus-stop-modal" alpineTitle="editMode ? 'Edit Bus Stop' : 'Add New Bus Stop'" maxWidth="4xl">
        {{-- Modal Body --}}
        <form :action="editMode ? '{{ url('receptionist/bus-stops') }}/' + busStopId : '{{ route('receptionist.bus-stops.store') }}'" 
              method="POST" class="p-6">
            @csrf
            <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">

            <div class="space-y-4">
                {{-- Row 1: Route and Bus Stop No --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Route <span class="text-red-500">*</span></label>
                        <select name="route_id" x-model="formData.route_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('route_id') border-red-500 @enderror">
                            <option value="">Select Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">
                                    {{ $route->route_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('route_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Bus Stop No <span class="text-red-500">*</span></label>
                        <input type="text" name="bus_stop_no" x-model="formData.bus_stop_no"
                               placeholder="Enter Bus Stop No"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('bus_stop_no') border-red-500 @enderror">
                        @error('bus_stop_no')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row 2: Bus Stop Name --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Bus Stop Name <span class="text-red-500">*</span></label>
                    <input type="text" name="bus_stop_name" x-model="formData.bus_stop_name"
                           placeholder="Enter Bus Stop Name"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('bus_stop_name') border-red-500 @enderror">
                    @error('bus_stop_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 3: Latitude and Longitude --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Latitude</label>
                        <input type="number" step="0.00000001" name="latitude" x-model="formData.latitude"
                               placeholder="Enter Latitude"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('latitude') border-red-500 @enderror">
                        @error('latitude')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Longitude</label>
                        <input type="number" step="0.00000001" name="longitude" x-model="formData.longitude"
                               placeholder="Enter Longitude"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('longitude') border-red-500 @enderror">
                        @error('longitude')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row 4: Distance and Charge --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Distance from Institute (KM)</label>
                        <input type="number" step="0.01" name="distance_from_institute" x-model="formData.distance_from_institute"
                               placeholder="Enter Distance"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('distance_from_institute') border-red-500 @enderror">
                        @error('distance_from_institute')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Charge Per Month</label>
                        <input type="number" step="0.01" name="charge_per_month" x-model="formData.charge_per_month"
                               placeholder="Enter Charge"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('charge_per_month') border-red-500 @enderror">
                        @error('charge_per_month')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row 5: Area Pin Code and Vehicle --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Area Pin Code</label>
                        <input type="text" name="area_pin_code" x-model="formData.area_pin_code"
                               placeholder="Enter Area Pin Code"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('area_pin_code') border-red-500 @enderror">
                        @error('area_pin_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Vehicle</label>
                        <select name="vehicle_id" x-model="formData.vehicle_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('vehicle_id') border-red-500 @enderror">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-center gap-4 mt-6">
                <button type="button" @click="closeModal()"
                        class="px-8 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold">
                    Close
                </button>
                <button type="submit"
                        class="px-8 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-semibold">
                    Submit
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('busStopManagement', () => ({
        showModal: false,
        editMode: false,
        busStopId: null,
        formData: {
            route_id: '',
            vehicle_id: '',
            bus_stop_no: '',
            bus_stop_name: '',
            latitude: '',
            longitude: '',
            distance_from_institute: '',
            charge_per_month: '',
            area_pin_code: '',
        },
        
        init() {
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.busStopId = '{{ old('bus_stop_id') }}';
                this.formData = {
                    route_id: '{{ old('route_id') }}',
                    vehicle_id: '{{ old('vehicle_id') }}',
                    bus_stop_no: '{{ old('bus_stop_no') }}',
                    bus_stop_name: '{{ old('bus_stop_name') }}',
                    latitude: '{{ old('latitude') }}',
                    longitude: '{{ old('longitude') }}',
                    distance_from_institute: '{{ old('distance_from_institute') }}',
                    charge_per_month: '{{ old('charge_per_month') }}',
                    area_pin_code: '{{ old('area_pin_code') }}',
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'bus-stop-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.busStopId = null;
            this.formData = {
                route_id: '',
                vehicle_id: '',
                bus_stop_no: '',
                bus_stop_name: '',
                latitude: '',
                longitude: '',
                distance_from_institute: '',
                charge_per_month: '',
                area_pin_code: '',
            };
            this.$dispatch('open-modal', 'bus-stop-modal');
        },
        
        openEditModal(busStop) {
            this.editMode = true;
            this.busStopId = busStop.id;
            this.formData = {
                route_id: busStop.route_id || '',
                vehicle_id: busStop.vehicle_id || '',
                bus_stop_no: busStop.bus_stop_no || '',
                bus_stop_name: busStop.bus_stop_name || '',
                latitude: busStop.latitude || '',
                longitude: busStop.longitude || '',
                distance_from_institute: busStop.distance_from_institute || '',
                charge_per_month: busStop.charge_per_month || '',
                area_pin_code: busStop.area_pin_code || '',
            };
            this.$dispatch('open-modal', 'bus-stop-modal');
            
            // Update Select2 dropdowns after modal is shown
            this.$nextTick(() => {
                setTimeout(() => {
                    $('select[name="route_id"]').val(busStop.route_id).trigger('change.select2');
                    $('select[name="vehicle_id"]').val(busStop.vehicle_id).trigger('change.select2');
                }, 100);
            });
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'bus-stop-modal');
        },
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(busStop) {
    const component = Alpine.$data(document.querySelector('[x-data*="busStopManagement"]'));
    if (component) {
        component.openEditModal(busStop);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.querySelector('[x-data*="busStopManagement"]');
    if (modal) {
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                e.target.classList.remove('border-red-500');
            }
        });
        
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Handle Select2 change events specifically
        $(modal).on('select2:select select2:clear', 'select', function(e) {
            const select = e.target;
            let errorElement = select.nextElementSibling;
            
            if (errorElement && errorElement.classList.contains('select2')) {
                errorElement = errorElement.nextElementSibling;
            }
            
            if (errorElement && errorElement.classList.contains('text-red-500')) {
                errorElement.classList.add('hidden');
            }
            
            select.classList.remove('border-red-500');
            const select2Container = $(select).next('.select2-container').find('.select2-selection');
            select2Container.removeClass('border-red-500');
        });
    }
});
</script>
@endpush
@endsection
