@extends('layouts.receptionist')

@section('title', 'Vehicle Management - Receptionist')
@section('page-title', 'Vehicle Management')
@section('page-description', 'Manage school vehicles and transportation')

@section('content')
<div class="space-y-6" x-data="vehicleManagement" x-init="init()">
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

    {{-- Vehicle Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Vehicles</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-bus text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Diesel</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['diesel'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-gas-pump text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Petrol</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['petrol'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-gas-pump text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">CNG</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['cng'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-charging-station text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-teal-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Electric</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['electric'] }}</p>
                </div>
                <div class="bg-teal-100 p-3 rounded-full">
                    <i class="fas fa-bolt text-teal-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800">Vehicle List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Vehicle
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Vehicles Table --}}
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
                'key' => 'registration_no',
                'label' => 'REGISTRATION NO',
                'sortable' => true,
            ],
            [
                'key' => 'vehicle_no',
                'label' => 'VEHICLE NO',
                'sortable' => true,
            ],
            [
                'key' => 'fuel_type',
                'label' => 'FUEL TYPE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->getFuelTypeLabel();
                }
            ],
            [
                'key' => 'capacity',
                'label' => 'CAPACITY',
                'sortable' => true,
            ],
            [
                'key' => 'initial_reading',
                'label' => 'INITIAL READING',
                'sortable' => false,
            ],
            [
                'key' => 'engine_no',
                'label' => 'ENGINE NO',
                'sortable' => false,
            ],
            [
                'key' => 'chassis_no',
                'label' => 'CHASSIS NO',
                'sortable' => false,
            ],
            [
                'key' => 'vehicle_type',
                'label' => 'VEHICLE TYPE',
                'sortable' => false,
            ],
            [
                'key' => 'model_no',
                'label' => 'MODEL NO',
                'sortable' => false,
            ],
            [
                'key' => 'date_of_purchase',
                'label' => 'DATE OF PURCHASE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->date_of_purchase ? $row->date_of_purchase->format('Y-m-d') : 'N/A';
                }
            ],
            [
                'key' => 'vehicle_group',
                'label' => 'VEHICLE GROUP',
                'sortable' => false,
            ],
            [
                'key' => 'imei_gps_device',
                'label' => 'IMEI NO OF GPS DEVICE',
                'sortable' => false,
            ],
            [
                'key' => 'tracking_url',
                'label' => 'TRACKING URL',
                'sortable' => false,
                'render' => function($row) {
                    return $row->tracking_url ? '<a href="' . $row->tracking_url . '" target="_blank" class="text-blue-600 hover:underline">View</a>' : 'N/A';
                }
            ],
            [
                'key' => 'manufacturing_year',
                'label' => 'MANUFACTURING YEAR',
                'sortable' => true,
            ],
            [
                'key' => 'vehicle_create_date',
                'label' => 'VEHICLE CREATE DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->vehicle_create_date ? $row->vehicle_create_date->format('Y-m-d') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-vehicle'))))";
                },
                'data-vehicle' => function($row) {
                    $vehicleData = [
                        'id' => $row->id,
                        'registration_no' => $row->registration_no,
                        'vehicle_no' => $row->vehicle_no,
                        'fuel_type' => $row->fuel_type,
                        'capacity' => $row->capacity,
                        'initial_reading' => $row->initial_reading,
                        'engine_no' => $row->engine_no,
                        'chassis_no' => $row->chassis_no,
                        'vehicle_type' => $row->vehicle_type,
                        'model_no' => $row->model_no,
                        'date_of_purchase' => $row->date_of_purchase ? $row->date_of_purchase->format('Y-m-d') : '',
                        'vehicle_group' => $row->vehicle_group,
                        'imei_gps_device' => $row->imei_gps_device,
                        'tracking_url' => $row->tracking_url,
                        'manufacturing_year' => $row->manufacturing_year,
                        'vehicle_create_date' => $row->vehicle_create_date ? $row->vehicle_create_date->format('Y-m-d') : '',
                    ];
                    return base64_encode(json_encode($vehicleData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'action' => function($row) {
                    return route('receptionist.vehicles.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this vehicle?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => 'confirm-delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$vehicles"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No vehicles found"
        empty-icon="fas fa-bus"
    >
        Vehicle List
    </x-data-table>

    {{-- Add/Edit Vehicle Modal --}}
    <div x-show="showModal" x-cloak 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto w-full max-w-5xl shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            {{-- Modal Header --}}
            <div class="bg-teal-500 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit Vehicle' : 'Add New Vehicle'"></h3>
                <button @click="closeModal()" class="text-white hover:text-teal-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form :action="editMode ? `/receptionist/vehicles/${vehicleId}` : '{{ route('receptionist.vehicles.store') }}'" 
                  method="POST" class="p-6">
                @csrf
                <template x-if="editMode">
                    @method('PUT')
                </template>

                <div class="bg-teal-50 dark:bg-teal-900 p-4 rounded-lg mb-6">
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4">Vehicle Information</h4>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Registration No *</label>
                            <input type="text" name="registration_no" x-model="formData.registration_no" value="{{ old('registration_no') }}"
                                   placeholder="Enter Vehicle Registration no"
                                   @input="$el.nextElementSibling?.classList.add('hidden')"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('registration_no') border-red-500 @enderror">
                            @error('registration_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Vehicle No</label>
                            <input type="text" name="vehicle_no" x-model="formData.vehicle_no" value="{{ old('vehicle_no') }}"
                                   placeholder="Enter Vehicle No"
                                   @input="$el.nextElementSibling?.classList.add('hidden')"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('vehicle_no') border-red-500 @enderror">
                            @error('vehicle_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Fuel Type *</label>
                            <select name="fuel_type" x-model="formData.fuel_type"
                                    @change="$el.nextElementSibling?.classList.add('hidden')"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('fuel_type') border-red-500 @enderror">
                                <option value="">Select Fuel Type</option>
                                <option value="1" {{ old('fuel_type') == 1 ? 'selected' : '' }}>Diesel</option>
                                <option value="2" {{ old('fuel_type') == 2 ? 'selected' : '' }}>Petrol</option>
                                <option value="3" {{ old('fuel_type') == 3 ? 'selected' : '' }}>CNG</option>
                                <option value="4" {{ old('fuel_type') == 4 ? 'selected' : '' }}>Electric</option>
                            </select>
                            @error('fuel_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Capacity</label>
                            <input type="number" name="capacity" x-model="formData.capacity" value="{{ old('capacity') }}"
                                   placeholder="Enter Capacity"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('capacity') border-red-500 @enderror">
                            @error('capacity')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Initial Reading</label>
                            <input type="number" name="initial_reading" x-model="formData.initial_reading" value="{{ old('initial_reading') }}"
                                   placeholder="Enter Initial Reading"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('initial_reading') border-red-500 @enderror">
                            @error('initial_reading')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Engine No</label>
                            <input type="text" name="engine_no" x-model="formData.engine_no" value="{{ old('engine_no') }}"
                                   placeholder="Enter Engine No"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('engine_no') border-red-500 @enderror">
                            @error('engine_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Chassis No</label>
                            <input type="text" name="chassis_no" x-model="formData.chassis_no" value="{{ old('chassis_no') }}"
                                   placeholder="Enter Chassis No"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('chassis_no') border-red-500 @enderror">
                            @error('chassis_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Vehicle type</label>
                            <select name="vehicle_type" x-model="formData.vehicle_type"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('vehicle_type') border-red-500 @enderror">
                                <option value="">Select Vehicle type</option>
                                <option value="bus" {{ old('vehicle_type') == 'bus' ? 'selected' : '' }}>Bus</option>
                                <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                                <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                                <option value="truck" {{ old('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                            </select>
                            @error('vehicle_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Model No</label>
                            <input type="text" name="model_no" x-model="formData.model_no" value="{{ old('model_no') }}"
                                   placeholder="Enter model No"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('model_no') border-red-500 @enderror">
                            @error('model_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Date of purchase</label>
                            <input type="date" name="date_of_purchase" x-model="formData.date_of_purchase" value="{{ old('date_of_purchase') }}"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('date_of_purchase') border-red-500 @enderror">
                            @error('date_of_purchase')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Vehicle Group</label>
                            <input type="text" name="vehicle_group" x-model="formData.vehicle_group" value="{{ old('vehicle_group') }}"
                                   placeholder="Vehicle Group"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('vehicle_group') border-red-500 @enderror">
                            @error('vehicle_group')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">IMEI NO of GPS Device</label>
                            <input type="text" name="imei_gps_device" x-model="formData.imei_gps_device" value="{{ old('imei_gps_device') }}"
                                   placeholder="IMEI NO of GPS Device"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('imei_gps_device') border-red-500 @enderror">
                            @error('imei_gps_device')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tracking Url</label>
                            <input type="url" name="tracking_url" x-model="formData.tracking_url" value="{{ old('tracking_url') }}"
                                   placeholder="Tracking Url"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('tracking_url') border-red-500 @enderror">
                            @error('tracking_url')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Manufacturing Year</label>
                            <input type="number" name="manufacturing_year" x-model="formData.manufacturing_year" value="{{ old('manufacturing_year') }}"
                                   placeholder="dd/mm/yyyy"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('manufacturing_year') border-red-500 @enderror">
                            @error('manufacturing_year')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Vehicle Create Date</label>
                            <input type="date" name="vehicle_create_date" x-model="formData.vehicle_create_date" value="{{ old('vehicle_create_date') }}"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('vehicle_create_date') border-red-500 @enderror">
                            @error('vehicle_create_date')
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
        </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('vehicleManagement', () => ({
        showModal: false,
        editMode: false,
        vehicleId: null,
        formData: {
            registration_no: '',
            vehicle_no: '',
            fuel_type: '',
            capacity: '',
            initial_reading: '',
            engine_no: '',
            chassis_no: '',
            vehicle_type: '',
            model_no: '',
            date_of_purchase: '',
            vehicle_group: '',
            imei_gps_device: '',
            tracking_url: '',
            manufacturing_year: '',
            vehicle_create_date: '',
        },
        
        init() {
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.formData = {
                    registration_no: '{{ old('registration_no') }}',
                    vehicle_no: '{{ old('vehicle_no') }}',
                    fuel_type: '{{ old('fuel_type') }}',
                    capacity: '{{ old('capacity') }}',
                    initial_reading: '{{ old('initial_reading') }}',
                    engine_no: '{{ old('engine_no') }}',
                    chassis_no: '{{ old('chassis_no') }}',
                    vehicle_type: '{{ old('vehicle_type') }}',
                    model_no: '{{ old('model_no') }}',
                    date_of_purchase: '{{ old('date_of_purchase') }}',
                    vehicle_group: '{{ old('vehicle_group') }}',
                    imei_gps_device: '{{ old('imei_gps_device') }}',
                    tracking_url: '{{ old('tracking_url') }}',
                    manufacturing_year: '{{ old('manufacturing_year') }}',
                    vehicle_create_date: '{{ old('vehicle_create_date') }}',
                };
                this.showModal = true;
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.vehicleId = null;
            this.formData = {
                registration_no: '',
                vehicle_no: '',
                fuel_type: '',
                capacity: '',
                initial_reading: '',
                engine_no: '',
                chassis_no: '',
                vehicle_type: '',
                model_no: '',
                date_of_purchase: '',
                vehicle_group: '',
                imei_gps_device: '',
                tracking_url: '',
                manufacturing_year: '',
                vehicle_create_date: '',
            };
            this.showModal = true;
        },
        
        openEditModal(vehicle) {
            this.editMode = true;
            this.vehicleId = vehicle.id;
            this.formData = {
                registration_no: vehicle.registration_no || '',
                vehicle_no: vehicle.vehicle_no || '',
                fuel_type: vehicle.fuel_type || '',
                capacity: vehicle.capacity || '',
                initial_reading: vehicle.initial_reading || '',
                engine_no: vehicle.engine_no || '',
                chassis_no: vehicle.chassis_no || '',
                vehicle_type: vehicle.vehicle_type || '',
                model_no: vehicle.model_no || '',
                date_of_purchase: vehicle.date_of_purchase || '',
                vehicle_group: vehicle.vehicle_group || '',
                imei_gps_device: vehicle.imei_gps_device || '',
                tracking_url: vehicle.tracking_url || '',
                manufacturing_year: vehicle.manufacturing_year || '',
                vehicle_create_date: vehicle.vehicle_create_date || '',
            };
            this.showModal = true;
            
            // Update Select2 dropdowns after modal is shown
            this.$nextTick(() => {
                setTimeout(() => {
                    $('select[name="fuel_type"]').val(vehicle.fuel_type).trigger('change.select2');
                    $('select[name="vehicle_type"]').val(vehicle.vehicle_type).trigger('change.select2');
                }, 100);
            });
        },
        
        closeModal() {
            this.showModal = false;
        },
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(vehicle) {
    const component = Alpine.$data(document.querySelector('[x-data*="vehicleManagement"]'));
    if (component) {
        component.openEditModal(vehicle);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all inputs and selects in the modal
    const modal = document.querySelector('[x-data*="vehicleManagement"]');
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
