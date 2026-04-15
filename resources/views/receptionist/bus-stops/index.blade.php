@extends('layouts.receptionist')

@section('title', 'Bus Stop Management - Receptionist')
@section('page-title', 'Bus Stop Management')
@section('page-description', 'Manage bus stops and transportation points')

@section('content')
    <div class="space-y-6" x-data="busStopManagement" x-init="init()">
        {{-- Bus Stop Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Stops</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_stops'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Coverage Areas</p>
                        <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ $stats['distinct_areas'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-globe-asia text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Distance</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['average_distance'] }} <span class="text-sm text-gray-400 font-medium">KM</span></p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-road text-amber-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Mapped Fleet</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_mapped'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bus text-purple-600 text-xl"></i>
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
                            <i class="fas fa-network-wired text-xs"></i>
                        </div>
                        Bus Stop Network
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure and monitor transportation pickup nodes.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-plus mr-2"></i>
                        Commission Node
                    </button>
                    <a href="{{ route('receptionist.bus-stops.export') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-file-excel mr-2 text-xs"></i>
                        Stop Export
                    </a>
                </div>
            </div>
        </div>

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
                    'key' => 'route_name',
                    'label' => 'ROUTE NAME',
                    'sortable' => true,
                    'render' => function ($row) {
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
                    'key' => 'distance_from_institute',
                    'label' => 'DISTANCE (KM)',
                    'sortable' => false,
                ],
                [
                    'key' => 'charge_per_month',
                    'label' => 'CHARGE/MONTH',
                    'sortable' => false,
                    'render' => function ($row) {
                        return '₹' . number_format($row->charge_per_month, 2);
                    }
                ],
                [
                    'key' => 'area_pin_code',
                    'label' => 'PIN CODE',
                    'sortable' => false,
                ],
                [
                    'key' => 'vehicle_no',
                    'label' => 'VEHICLE',
                    'sortable' => false,
                    'render' => function ($row) {
                        return $row->vehicle ? $row->vehicle->vehicle_no : 'N/A';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        $stopData = [
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
                        return "window.dispatchEvent(new CustomEvent('open-edit-bus-stop', { detail: ".json_encode($stopData)." }))";
                    },
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        $deleteData = [
                            'url' => url('receptionist/bus-stops/' . $row->id),
                            'name' => $row->bus_stop_name
                        ];
                        return "window.dispatchEvent(new CustomEvent('open-delete-bus-stop', { detail: ".json_encode($deleteData)." }))";
                    },
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$busStops" :searchable="true" :actions="$tableActions"
            empty-message="No bus stops found" empty-icon="fas fa-map-marker-alt">
            Bus Stop List
        </x-data-table>

        {{-- Add/Edit Bus Stop Modal --}}
        <x-modal name="bus-stop-modal" alpineTitle="editMode ? 'Edit Bus Stop' : 'Create Bus Stop'"
            maxWidth="3xl">
            <form @submit.prevent="save" id="busStopForm" method="POST" class="space-y-6" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                    {{-- Logistics Mapping --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <label class="modal-label-premium">Assigned Route Designation <span class="text-red-600 font-bold">*</span></label>
                            <select name="route_id" x-model="formData.route_id" id="route_id"
                                @change="clearError('route_id')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.route_id}">
                                <option value="">Select Primary Route</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                                @endforeach
                            </select>
                            <template x-if="errors.route_id">
                                <p class="modal-error-message" x-text="errors.route_id[0]"></p>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Node Identifier (Stop No) <span class="text-red-600 font-bold">*</span></label>
                            <input type="text" name="bus_stop_no" x-model="formData.bus_stop_no"
                                placeholder="e.g. ST-001" @input="clearError('bus_stop_no')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.bus_stop_no}">
                            <template x-if="errors.bus_stop_no">
                                <p class="modal-error-message" x-text="errors.bus_stop_no[0]"></p>
                            </template>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="modal-label-premium">Geographic Landmark Name <span class="text-red-600 font-bold">*</span></label>
                            <input type="text" name="bus_stop_name" x-model="formData.bus_stop_name"
                                placeholder="e.g. Central Square Park Entrance" @input="clearError('bus_stop_name')"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.bus_stop_name}">
                            <template x-if="errors.bus_stop_name">
                                <p class="modal-error-message" x-text="errors.bus_stop_name[0]"></p>
                            </template>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- GPS & Tariff Configuration --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <label class="modal-label-premium">Precision Latitude</label>
                            <input type="number" step="0.00000001" name="latitude" x-model="formData.latitude"
                                placeholder="0.00000000" class="modal-input-premium text-xs">
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">Precision Longitude</label>
                            <input type="number" step="0.00000001" name="longitude" x-model="formData.longitude"
                                placeholder="0.00000000" class="modal-input-premium text-xs">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Distance (KM)</label>
                            <input type="number" step="0.01" name="distance_from_institute"
                                x-model="formData.distance_from_institute" class="modal-input-premium" placeholder="0.00">
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">Monthly Tariff</label>
                            <div class="relative group">
                                <input type="number" step="0.01" name="charge_per_month" x-model="formData.charge_per_month"
                                    class="modal-input-premium" placeholder="0.00">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-teal-600 font-bold text-sm">₹</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">Postal Code (PIN)</label>
                            <input type="text" name="area_pin_code" x-model="formData.area_pin_code"
                                class="modal-input-premium" placeholder="e.g. 110001">
                        </div>

                        <div class="space-y-2">
                            <label class="modal-label-premium">Operational Fleet Mapping</label>
                            <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                                class="modal-input-premium">
                                <option value="">Select Primary Vehicle Asset</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Administrative Notice --}}
                    <div class="bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-900 leading-tight">Infrastructure Notice</span>
                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                                Decommissioning or re-routing this stop will impact <span class="text-indigo-600 font-bold underline decoration-indigo-200">billing cycles</span> and student notifications for the current month.
                            </p>
                        </div>
                    </div>

            </form>
            {{-- Modal Footer --}}
            <x-slot name="footer">
                <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="busStopForm" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Changes' : 'Create Stop')"></span>
                </button>
            </x-slot>
        </x-modal>

        <x-confirm-modal title="Permanently Remove Node?"
            message="This action will decommission the bus stop from the network. This cannot be undone."
            confirm-text="Decommission" confirm-color="red" />
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('busStopManagement', () => ({
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
                    errors: {},
                    submitting: false,

                    async init() {
                        window.addEventListener('open-add-bus-stop', () => this.openAddModal());
                        window.addEventListener('open-edit-bus-stop', (e) => this.openEditModal(e.detail));
                        window.addEventListener('open-delete-bus-stop', (e) => this.confirmDelete(e.detail));

                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[name="route_id"], select[name="vehicle_id"]').on('change', (e) => {
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

                    openAddModal() {
                        this.editMode = false;
                        this.busStopId = null;
                        this.errors = {};
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
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[name="route_id"], select[name="vehicle_id"]').val('').trigger('change');
                            }
                        });
                    },

                    openEditModal(busStop) {
                        this.editMode = true;
                        this.busStopId = busStop.id;
                        this.errors = {};
                        this.formData = {
                            route_id: busStop.route_id ? String(busStop.route_id) : '',
                            vehicle_id: busStop.vehicle_id ? String(busStop.vehicle_id) : '',
                            bus_stop_no: busStop.bus_stop_no || '',
                            bus_stop_name: busStop.bus_stop_name || '',
                            latitude: busStop.latitude || '',
                            longitude: busStop.longitude || '',
                            distance_from_institute: busStop.distance_from_institute || '',
                            charge_per_month: busStop.charge_per_month || '',
                            area_pin_code: busStop.area_pin_code || '',
                        };
                        this.$dispatch('open-modal', 'bus-stop-modal');

                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (typeof $ !== 'undefined') {
                                    $('select[name="route_id"]').val(this.formData.route_id).trigger('change');
                                    $('select[name="vehicle_id"]').val(this.formData.vehicle_id).trigger('change');
                                }
                            }, 150);
                        });
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `{{ url('receptionist/bus-stops') }}/${this.busStopId}`
                            : `{{ route('receptionist.bus-stops.store') }}`;

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
                                        title: result.message || 'Node configuration propagated'
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
                                window.Toast.fire({ icon: 'error', title: error.message });
                            }
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(detail) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                message: `Are you sure you want to decommission the bus stop node "${detail.name}"?`,
                                onConfirm: () => this.deleteBusStop(detail.url)
                            }
                        }));
                    },

                    async deleteBusStop(url) {
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
                                throw new Error(result.message || 'Decommissioning failed');
                            }
                        } catch (error) {
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: error.message });
                            }
                        }
                    },

                    closeModal() {
                        this.$dispatch('close-modal', 'bus-stop-modal');
                        this.errors = {};
                    },
                }));
            });
        </script>
    @endpush
@endsection