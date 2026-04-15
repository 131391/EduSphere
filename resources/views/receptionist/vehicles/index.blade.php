@extends('layouts.receptionist')

@section('title', 'Vehicle Management - Receptionist')
@section('page-title', 'Vehicle Management')
@section('page-description', 'Manage school vehicles and transportation')

@section('content')
    <div class="space-y-6" x-data="vehicleManagement" x-init="init()">
        {{-- Vehicle Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Fleet</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bus text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Diesel Units</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['diesel'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-gas-pump text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Petrol Units</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['petrol'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-gas-pump text-amber-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">CNG Core</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['cng'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-charging-station text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Electric EV</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['electric'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bolt text-teal-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-bus-alt text-xs"></i>
                        </div>
                        Vehicle Management
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Coordinate and monitor institutional fleet logistics.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-plus mr-2"></i>
                        Register New Vehicle
                    </button>
                    <a href="{{ route('receptionist.vehicles.export') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-file-excel mr-2 text-xs"></i>
                        Fleet Export
                    </a>
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
                    'render' => function ($row, $index, $data) {
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
                    'render' => function ($row) {
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
                    'render' => function ($row) {
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
                    'render' => function ($row) {
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
                    'render' => function ($row) {
                        return $row->vehicle_create_date ? $row->vehicle_create_date->format('Y-m-d') : 'N/A';
                    }
                ],
            ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function ($row) {
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
                    return "window.dispatchEvent(new CustomEvent('open-edit-vehicle', { detail: ".json_encode($vehicleData)." }))";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function ($row) {
                    $deleteData = [
                        'url' => route('receptionist.vehicles.destroy', $row->id),
                        'name' => $row->registration_no
                    ];
                    return "window.dispatchEvent(new CustomEvent('open-delete-vehicle', { detail: ".json_encode($deleteData)." }))";
                },
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$vehicles" :searchable="true" :actions="$tableActions"
            empty-message="No vehicles found" empty-icon="fas fa-bus">
            Vehicle List
        </x-data-table>

        {{-- Add/Edit Vehicle Modal --}}
        <x-modal name="vehicle-modal" alpineTitle="editMode ? 'Edit Vehicle Specification' : 'Register New Vehicle'"
            maxWidth="3xl">
            <form @submit.prevent="save" id="vehicleForm" method="POST" class="space-y-6" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                    {{-- Core Specifications --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <label class="modal-label-premium">Registration No <span class="text-red-600 font-bold">*</span></label>
                            <input type="text" name="registration_no" x-model="formData.registration_no"
                                placeholder="e.g., DL-1C-AB-1234" @input="clearError('registration_no')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.registration_no}">
                            <template x-if="errors.registration_no">
                                <p class="modal-error-message" x-text="errors.registration_no[0]"></p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Internal Vehicle No</label>
                            <input type="text" name="vehicle_no" x-model="formData.vehicle_no"
                                placeholder="Internal ID (Optional)" @input="clearError('vehicle_no')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.vehicle_no}">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Propulsion / Fuel Type <span class="text-red-600 font-bold">*</span></label>
                            <select name="fuel_type" x-model="formData.fuel_type" id="fuel_type"
                                @change="clearError('fuel_type')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.fuel_type}">
                                <option value="">Select Propulsion</option>
                                @foreach(\App\Enums\FuelType::cases() as $fuel)
                                    <option value="{{ $fuel->value }}">{{ $fuel->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="errors.fuel_type">
                                <p class="modal-error-message" x-text="errors.fuel_type[0]"></p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Seating Capacity</label>
                            <input type="number" name="capacity" x-model="formData.capacity"
                                placeholder="0" @input="clearError('capacity')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.capacity}">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Initial Reading (KM)</label>
                            <input type="number" name="initial_reading" x-model="formData.initial_reading"
                                placeholder="0" @input="clearError('initial_reading')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.initial_reading}">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Engine Serial</label>
                            <input type="text" name="engine_no" x-model="formData.engine_no"
                                placeholder="SN-XXXX" @input="clearError('engine_no')"
                                class="modal-input-premium">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Chassis Serial</label>
                            <input type="text" name="chassis_no" x-model="formData.chassis_no"
                                placeholder="CS-XXXX" @input="clearError('chassis_no')"
                                class="modal-input-premium">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Vehicle Configuration</label>
                            <select name="vehicle_type" x-model="formData.vehicle_type" id="vehicle_type"
                                class="modal-input-premium">
                                <option value="">Select Variant</option>
                                <option value="bus">School Bus</option>
                                <option value="van">Transport Van</option>
                                <option value="car">Staff Car</option>
                                <option value="truck">Utility Truck</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Model / Year</label>
                            <input type="text" name="model_no" x-model="formData.model_no"
                                placeholder="e.g. 2024 Turbo" class="modal-input-premium">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Purchase Date</label>
                            <input type="date" name="date_of_purchase" x-model="formData.date_of_purchase"
                                class="modal-input-premium">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">GPS IMEI Metadata</label>
                            <input type="text" name="imei_gps_device" x-model="formData.imei_gps_device"
                                placeholder="Device Identifier" class="modal-input-premium">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Tracking URL</label>
                            <input type="url" name="tracking_url" x-model="formData.tracking_url"
                                placeholder="https://" class="modal-input-premium">
                        </div>
                    </div>

                    {{-- Administrative Notice --}}
                    <div class="bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-900 leading-tight">Administrative Notice</span>
                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                                Updating vehicle specifications will synchronize across all <span class="text-indigo-600 font-bold underline decoration-indigo-200">active route manifests</span> and transit logs.
                            </p>
                        </div>
                    </div>

            </form>
            {{-- Modal Footer --}}
            <x-slot name="footer">
                <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="vehicleForm" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Changes' : 'Create Vehicle')"></span>
                </button>
            </x-slot>
        </x-modal>

        <x-confirm-modal title="Permanently Remove Vehicle?"
            message="This action will strike the vehicle from the registry. This cannot be undone."
            confirm-text="Strike Record" confirm-color="red" />

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
                                window.addEventListener('open-add-vehicle', () => this.openAddModal());
                                window.addEventListener('open-edit-vehicle', (e) => this.openEditModal(e.detail));
                                window.addEventListener('open-delete-vehicle', (e) => this.confirmDelete(e.detail));

                                // Sync Select2 with Alpine state
                                this.$nextTick(() => {
                                    if (typeof $ !== 'undefined') {
                                        $('select[name="fuel_type"], select[name="vehicle_type"]').on('change', (e) => {
                                            const field = e.target.getAttribute('name');
                                            if (field && this.formData.hasOwnProperty(field)) {
                                                this.formData[field] = e.target.value;
                                                this.clearError(field);
                                            }
                                        });
                                    }
                                });
                            },

                            clearError(field) {
                                if (this.errors[field]) {
                                    delete this.errors[field];
                                }
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

                            confirmDelete(detail) {
                                window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                                    detail: {
                                        message: `Are you sure you want to strike the vehicle record for "${detail.name}"?`,
                                        onConfirm: () => this.deleteVehicle(detail.url)
                                    }
                                }));
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
                                        $('select[name="fuel_type"], select[name="vehicle_type"]').val('').trigger('change');
                                    }
                                });
                            },

                            openEditModal(vehicle) {
                                this.editMode = true;
                                this.vehicleId = vehicle.id;
                                this.errors = {};
                                this.formData = {
                                    registration_no: vehicle.registration_no || '',
                                    vehicle_no: vehicle.vehicle_no || '',
                                    fuel_type: vehicle.fuel_type ? String(vehicle.fuel_type) : '',
                                    capacity: vehicle.capacity || '',
                                    initial_reading: vehicle.initial_reading || '',
                                    engine_no: vehicle.engine_no || '',
                                    chassis_no: vehicle.chassis_no || '',
                                    vehicle_type: vehicle.vehicle_type ? String(vehicle.vehicle_type) : '',
                                    model_no: vehicle.model_no || '',
                                    date_of_purchase: vehicle.date_of_purchase || '',
                                    vehicle_group: vehicle.vehicle_group || '',
                                    imei_gps_device: vehicle.imei_gps_device || '',
                                    tracking_url: vehicle.tracking_url || '',
                                    manufacturing_year: vehicle.manufacturing_year || '',
                                    vehicle_create_date: vehicle.vehicle_create_date || '',
                                };
                                this.$dispatch('open-modal', 'vehicle-modal');

                                this.$nextTick(() => {
                                    setTimeout(() => {
                                        if (typeof $ !== 'undefined') {
                                            $('select[name="fuel_type"]').val(this.formData.fuel_type).trigger('change');
                                            $('select[name="vehicle_type"]').val(this.formData.vehicle_type).trigger('change');
                                        }
                                    }, 150);
                                });
                            },

                            closeModal() {
                                this.$dispatch('close-modal', 'vehicle-modal');
                                this.errors = {};
                            },
                        }));
                    });
                </script>
            </div>
        @endpush
@endsection