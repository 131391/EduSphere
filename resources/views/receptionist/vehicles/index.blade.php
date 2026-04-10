@extends('layouts.receptionist')

@section('title', 'Vehicle Management - Receptionist')
@section('page-title', 'Vehicle Management')
@section('page-description', 'Manage school vehicles and transportation')

@section('content')
<div class="space-y-6" x-data="vehicleManagement" x-init="init()">
    {{-- Success/Error Messages --}}


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
                'render' => function($row, $index, $data) {
                    return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
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
                    return "openEditModal(".json_encode($vehicleData).")";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "confirmDelete('".route('receptionist.vehicles.destroy', $row->id)."', '{$row->registration_no}')";
                },
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
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
    <x-modal name="vehicle-modal" alpineTitle="editMode ? 'Edit Vehicle Registry' : 'Register New Vehicle'" maxWidth="5xl">
        <form @submit.prevent="save" method="POST" class="p-0 relative">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Global Error Announcement --}}
            <template x-if="Object.keys(errors).length > 0">
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl mx-4 mt-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span class="text-xs font-black text-red-700 uppercase tracking-widest">Validation Exceptions</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="(messages, field) in errors" :key="field">
                            <template x-for="message in messages" :key="message">
                                <li class="text-[10px] text-red-600 font-bold uppercase" x-text="message"></li>
                            </template>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="p-6">
                <div class="bg-teal-50 dark:bg-teal-900/20 p-6 rounded-2xl mb-6 border border-teal-100 dark:border-teal-800">
                    <h4 class="font-bold text-teal-800 dark:text-teal-400 mb-6 flex items-center gap-2 uppercase tracking-tight text-sm">
                        <i class="fas fa-info-circle"></i>
                        Vehicle Core Specifications
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Registration No <span class="text-red-500">*</span></label>
                            <input type="text" name="registration_no" x-model="formData.registration_no"
                                   placeholder="Enter Vehicle Registration no"
                                   @input="delete errors.registration_no"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.registration_no ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.registration_no">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.registration_no[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Vehicle No</label>
                            <input type="text" name="vehicle_no" x-model="formData.vehicle_no"
                                   placeholder="Auto-generated if empty"
                                   @input="delete errors.vehicle_no"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.vehicle_no ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.vehicle_no">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.vehicle_no[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Fuel Type <span class="text-red-500">*</span></label>
                            <select name="fuel_type" x-model="formData.fuel_type" id="fuel_type"
                                    @change="delete errors.fuel_type"
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                    :class="errors.fuel_type ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                                <option value="">Select Fuel Type</option>
                                <option value="1">Diesel</option>
                                <option value="2">Petrol</option>
                                <option value="3">CNG</option>
                                <option value="4">Electric</option>
                            </select>
                            <template x-if="errors.fuel_type">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.fuel_type[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Capacity</label>
                            <input type="number" name="capacity" x-model="formData.capacity"
                                   placeholder="Passenger Count"
                                   @input="delete errors.capacity"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.capacity ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.capacity">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.capacity[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Initial Reading (KM)</label>
                            <input type="number" name="initial_reading" x-model="formData.initial_reading"
                                   placeholder="Odometer at Registration"
                                   @input="delete errors.initial_reading"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.initial_reading ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.initial_reading">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.initial_reading[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Engine No</label>
                            <input type="text" name="engine_no" x-model="formData.engine_no"
                                   placeholder="Enter Engine Serial"
                                   @input="delete errors.engine_no"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.engine_no ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.engine_no">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.engine_no[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Chassis No</label>
                            <input type="text" name="chassis_no" x-model="formData.chassis_no"
                                   placeholder="Enter Chassis Serial"
                                   @input="delete errors.chassis_no"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.chassis_no ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.chassis_no">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.chassis_no[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Vehicle Type</label>
                            <select name="vehicle_type" x-model="formData.vehicle_type" id="vehicle_type"
                                    @change="delete errors.vehicle_type"
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                    :class="errors.vehicle_type ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                                <option value="">Select type</option>
                                <option value="bus">Bus</option>
                                <option value="van">Van</option>
                                <option value="car">Car</option>
                                <option value="truck">Truck</option>
                            </select>
                            <template x-if="errors.vehicle_type">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.vehicle_type[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Model No</label>
                            <input type="text" name="model_no" x-model="formData.model_no"
                                   placeholder="Vehicle Model/Year"
                                   @input="delete errors.model_no"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.model_no ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.model_no">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.model_no[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-2xl border border-blue-100 dark:border-blue-800">
                    <h4 class="font-bold text-blue-800 dark:text-blue-400 mb-6 flex items-center gap-2 uppercase tracking-tight text-sm">
                        <i class="fas fa-microchip"></i>
                        Auxiliary & Tracking Metadata
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Date of Purchase</label>
                            <input type="date" name="date_of_purchase" x-model="formData.date_of_purchase"
                                   @input="delete errors.date_of_purchase"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.date_of_purchase ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.date_of_purchase">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.date_of_purchase[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Fleet Group</label>
                            <input type="text" name="vehicle_group" x-model="formData.vehicle_group"
                                   placeholder="Primary/Standby"
                                   @input="delete errors.vehicle_group"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.vehicle_group ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.vehicle_group">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.vehicle_group[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">GPS IMEI No</label>
                            <input type="text" name="imei_gps_device" x-model="formData.imei_gps_device"
                                   placeholder="15-digit IMEI"
                                   @input="delete errors.imei_gps_device"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.imei_gps_device ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.imei_gps_device">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.imei_gps_device[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Live Tracking URL</label>
                            <input type="url" name="tracking_url" x-model="formData.tracking_url"
                                   placeholder="https://maps.google.com/..."
                                   @input="delete errors.tracking_url"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.tracking_url ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.tracking_url">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.tracking_url[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Mfg Year</label>
                            <input type="number" name="manufacturing_year" x-model="formData.manufacturing_year"
                                   placeholder="YYYY"
                                   @input="delete errors.manufacturing_year"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                   :class="errors.manufacturing_year ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <template x-if="errors.manufacturing_year">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.manufacturing_year[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-3xl">
                <button type="button" @click="closeModal()" :disabled="submitting"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm">
                    Discard
                </button>
                <button type="submit" :disabled="submitting"
                        class="px-8 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white rounded-xl hover:from-teal-600 hover:to-emerald-700 transition-all font-black text-sm shadow-lg shadow-teal-100 flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Committing...' : (editMode ? 'Update Registry' : 'Propagate Record')"></span>
                </button>
            </div>
        </form>
    </x-modal>

    <x-confirm-modal 
        title="Permanently Remove Vehicle?" 
        message="This action will strike the vehicle from the registry. This cannot be undone."
        confirm-text="Strike Record"
        confirm-color="red"
    />

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
        errors: {},
        submitting: false,
        
        async init() {
            // Sync Select2 if present
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $(document).on('change', '#fuel_type', (e) => {
                        this.formData.fuel_type = e.target.value;
                        if (this.errors.fuel_type) delete this.errors.fuel_type;
                    });
                    $(document).on('change', '#vehicle_type', (e) => {
                        this.formData.vehicle_type = e.target.value;
                        if (this.errors.vehicle_type) delete this.errors.vehicle_type;
                    });
                }
            });
        },

        async save() {
            this.submitting = true;
            this.errors = {};

            const url = this.editMode 
                ? `/receptionist/vehicles/${this.vehicleId}` 
                : '{{ route('receptionist.vehicles.store') }}';
            
            const method = this.editMode ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        _method: method
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message || 'Vehicle registry updated'
                        });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    if (response.status === 422) {
                        this.errors = result.errors || {};
                        console.error('Validation Errors Map:', this.errors);
                    } else {
                        throw new Error(result.message || 'Transmission failed');
                    }
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message
                    });
                }
            } finally {
                this.submitting = false;
            }
        },

        async deleteVehicle(url) {
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
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(result.message || 'Deletion failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            }
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
                vehicle_create_date: '{{ date('Y-m-d') }}',
            };
            this.errors = {};
            this.$dispatch('open-modal', 'vehicle-modal');

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#fuel_type, #vehicle_type').val('').trigger('change.select2');
                }
            });
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
            this.errors = {};
            this.$dispatch('open-modal', 'vehicle-modal');
            
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#fuel_type').val(vehicle.fuel_type).trigger('change.select2');
                    $('#vehicle_type').val(vehicle.vehicle_type).trigger('change.select2');
                }
            });
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'vehicle-modal');
            this.errors = {};
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

function confirmDelete(url, regNo) {
    window.dispatchEvent(new CustomEvent('open-confirm-modal', {
        detail: {
            message: `Are you sure you want to strike the vehicle record for "${regNo}"?`,
            onConfirm: () => {
                const component = Alpine.$data(document.querySelector('[x-data*="vehicleManagement"]'));
                if (component) {
                    component.deleteVehicle(url);
                }
            }
        }
    }));
}
</script>
</div>
@endpush
@endsection
