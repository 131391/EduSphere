@extends('layouts.receptionist')

@section('title', 'Bus Stop Management - Receptionist')
@section('page-title', 'Bus Stop Management')
@section('page-description', 'Manage bus stops and transportation points')

@section('content')
    <div class="space-y-6" x-data="busStopManagement" x-init="init()">
        {{-- Bus Stop Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Stops</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['total_stops'] }}</p>
                    </div>
                    <div class="bg-teal-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-map-marker-alt text-teal-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Coverage Areas</p>
                        <p class="text-2xl font-black text-blue-600">{{ $stats['distinct_areas'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-globe-asia text-blue-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Avg. Distance</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['average_distance'] }} <span class="text-xs text-gray-400">KM</span></p>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-road text-amber-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Mapped Fleet</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['total_mapped'] }}</p>
                    </div>
                    <div class="bg-indigo-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bus text-indigo-500 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-2xl shadow-lg shadow-blue-100">
                        <i class="fas fa-network-wired text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Bus Stop Network</h2>
                        <p class="text-sm text-gray-500 font-medium">Configure and monitor transportation pickup nodes</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-blue-100 group">
                        <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                        Commission Node
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
                        return "openEditModal(" . json_encode($stopData) . ")";
                    },
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        return "confirmDelete('" . url('receptionist/bus-stops/' . $row->id) . "', '{$row->bus_stop_name}')";
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
        <x-modal name="bus-stop-modal" alpineTitle="editMode ? 'Modify Network Node' : 'Commission New Bus Stop'"
            maxWidth="3xl">
            <form @submit.prevent="save" class="p-0 relative" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-8 space-y-8">
                    {{-- Logistics Mapping --}}
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                                <i class="fas fa-map-marked-alt text-sm"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Logistics Mapping</h4>
                        </div>

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
                        </div>

                        <div class="mt-6 space-y-2">
                            <label class="modal-label-premium">Geographic Landmark / Node Name <span class="text-red-600 font-bold">*</span></label>
                            <div class="relative group">
                                <input type="text" name="bus_stop_name" x-model="formData.bus_stop_name"
                                    placeholder="e.g. Central Square Park Entrance" @input="clearError('bus_stop_name')"
                                    class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.bus_stop_name}">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-blue-500 transition-colors">
                                    <i class="fas fa-map-pin text-[10px]"></i>
                                </div>
                            </div>
                            <template x-if="errors.bus_stop_name">
                                <p class="modal-error-message" x-text="errors.bus_stop_name[0]"></p>
                            </template>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- GPS & Tariff Configuration --}}
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100 shadow-sm">
                                <i class="fas fa-satellite text-sm"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">GPS & Tariff Configuration</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 p-6 rounded-2xl border border-slate-100 shadow-inner">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Precision Latitude</label>
                                <input type="number" step="0.00000001" name="latitude" x-model="formData.latitude"
                                    placeholder="0.00000000" class="modal-input-premium">
                            </div>
                            <div class="space-y-2">
                                <label class="modal-label-premium">Precision Longitude</label>
                                <input type="number" step="0.00000001" name="longitude" x-model="formData.longitude"
                                    placeholder="0.00000000" class="modal-input-premium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Distance (KM)</label>
                                <input type="number" step="0.01" name="distance_from_institute"
                                    x-model="formData.distance_from_institute" class="modal-input-premium" placeholder="0.00">
                            </div>
                            <div class="space-y-2">
                                <label class="modal-label-premium">Monthly Tariff</label>
                                <div class="relative group">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-blue-600 transition-colors">₹</span>
                                    <input type="number" step="0.01" name="charge_per_month" x-model="formData.charge_per_month"
                                        class="modal-input-premium pl-8" placeholder="0.00">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="modal-label-premium">Postal Code (PIN)</label>
                                <input type="text" name="area_pin_code" x-model="formData.area_pin_code"
                                    class="modal-input-premium" placeholder="e.g. 110001">
                            </div>
                        </div>
                    </div>

                    {{-- Fleet Synchronization --}}
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

                    {{-- Administrative Notice --}}
                    <div class="bg-[#f0f9ff] border border-[#e0f2fe] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[13px] font-bold text-slate-900 leading-tight">Infrastructure Notice</span>
                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                                Decommissioning or re-routing this stop will impact <span class="text-blue-600 italic underline decoration-blue-100">billing cycles</span> and student notifications for the current month.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <x-slot name="footer">
                    <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[180px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Node' : 'Initialize Stop')"></span>
                    </button>
                </x-slot>
            </form>
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
                    submitting: false,
                    errors: {},
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

                    async init() {
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

            // Global helpers
            window.openEditModal = function (busStop) {
                const el = document.querySelector('[x-data*="busStopManagement"]');
                if (el) {
                    const component = Alpine.$data(el);
                    if (component) component.openEditModal(busStop);
                }
            };

            window.confirmDelete = function (url, name) {
                window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                    detail: {
                        message: `Are you sure you want to decommission the bus stop node "${name}"?`,
                        onConfirm: () => {
                            const el = document.querySelector('[x-data*="busStopManagement"]');
                            if (el) {
                                const component = Alpine.$data(el);
                                if (component) component.deleteBusStop(url);
                            }
                        }
                    }
                }));
            };
        </script>
        </div>
    @endpush
@endsection