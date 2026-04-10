@extends('layouts.receptionist')

@section('title', 'Bus Stop Management - Receptionist')
@section('page-title', 'Bus Stop Management')
@section('page-description', 'Manage bus stops and transportation points')

@section('content')
<div class="space-y-6" x-data="busStopManagement" x-init="init()">
    {{-- Success/Error Messages --}}


    {{-- Page Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Bus Stop Network</h2>
                <p class="text-sm text-gray-500 mt-1">Configure and monitor transportation pickup nodes</p>
            </div>
            <div class="flex gap-3">
                <button @click="openAddModal()" class="px-6 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white rounded-xl hover:shadow-lg hover:shadow-teal-100 transition-all flex items-center font-bold text-sm">
                    <i class="fas fa-plus-circle mr-2"></i>
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
                'render' => function($row, $index, $data) {
                    return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
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
                'key' => 'distance_from_institute',
                'label' => 'DISTANCE (KM)',
                'sortable' => false,
            ],
            [
                'key' => 'charge_per_month',
                'label' => 'CHARGE/MONTH',
                'sortable' => false,
                'render' => function($row) {
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
                'render' => function($row) {
                    return $row->vehicle ? $row->vehicle->vehicle_no : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
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
                    return "openEditModal(".json_encode($stopData).")";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "confirmDelete('".url('receptionist/bus-stops/'.$row->id)."', '{$row->bus_stop_name}')";
                },
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
    <x-modal name="bus-stop-modal" alpineTitle="editMode ? 'Modify Network Node' : 'Commission Bus Stop'" maxWidth="2xl">
        <form @submit.prevent="save" class="p-0 relative">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Global Error Announcement --}}
            <template x-if="Object.keys(errors).length > 0">
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl mx-6 mt-6">
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

            <div class="p-8 space-y-6">
                {{-- Row 1: Route and Bus Stop No --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Assigned Route <span class="text-red-500">*</span></label>
                        <select name="route_id" x-model="formData.route_id" id="route_id"
                                @change="delete errors.route_id"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                :class="errors.route_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <option value="">Select Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.route_id">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.route_id[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Stop Identifier (No) <span class="text-red-500">*</span></label>
                        <input type="text" name="bus_stop_no" x-model="formData.bus_stop_no"
                               placeholder="e.g. ST-001"
                               @input="delete errors.bus_stop_no"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                :class="errors.bus_stop_no ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                        <template x-if="errors.bus_stop_no">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.bus_stop_no[0]"></p>
                        </template>
                    </div>
                </div>

                {{-- Row 2: Bus Stop Name --}}
                <div>
                    <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Node Description Name <span class="text-red-500">*</span></label>
                    <input type="text" name="bus_stop_name" x-model="formData.bus_stop_name"
                           placeholder="Enter prominent landmark or area name"
                           @input="delete errors.bus_stop_name"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                           :class="errors.bus_stop_name ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                    <template x-if="errors.bus_stop_name">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.bus_stop_name[0]"></p>
                    </template>
                </div>

                {{-- Row 3: Latitude and Longitude --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-4 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">GPS Latitude</label>
                        <input type="number" step="0.00000001" name="latitude" x-model="formData.latitude"
                               placeholder="0.00000000"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white focus:ring-teal-500/10 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">GPS Longitude</label>
                        <input type="number" step="0.00000001" name="longitude" x-model="formData.longitude"
                               placeholder="0.00000000"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white focus:ring-teal-500/10 focus:border-teal-500">
                    </div>
                </div>

                {{-- Row 4: Distance and Charge --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Distance (KM)</label>
                        <input type="number" step="0.01" name="distance_from_institute" x-model="formData.distance_from_institute"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white focus:ring-teal-500/10 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Monthly Tariff</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₹</span>
                            <input type="number" step="0.01" name="charge_per_month" x-model="formData.charge_per_month"
                                   class="w-full pl-8 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white focus:ring-teal-500/10 focus:border-teal-500">
                        </div>
                    </div>
                </div>

                {{-- Row 5: Area Pin Code and Vehicle --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Postal Code (PIN)</label>
                        <input type="text" name="area_pin_code" x-model="formData.area_pin_code"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white focus:ring-teal-500/10 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Link Vehicle (Primary)</label>
                        <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                                @change="delete errors.vehicle_id"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white focus:ring-teal-500/10 focus:border-teal-500">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 p-6 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <button type="button" @click="closeModal()"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm">
                    Discard
                </button>
                <button type="submit" :disabled="submitting"
                        class="px-10 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white rounded-xl shadow-lg shadow-teal-100 hover:shadow-xl transition-all font-black text-sm uppercase tracking-widest flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Syncing...' : (editMode ? 'Update Node' : 'Initialize Stop')"></span>
                </button>
            </div>
        </form>
    </x-modal>

    <x-confirm-modal 
        title="Permanently Remove Node?" 
        message="This action will decommission the bus stop from the network. This cannot be undone."
        confirm-text="Decommission"
        confirm-color="red"
    />
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
                    $(document).on('change', '#route_id', (e) => {
                        this.formData.route_id = e.target.value;
                        if (this.errors.route_id) delete this.errors.route_id;
                    });
                    $(document).on('change', '#vehicle_id', (e) => {
                        this.formData.vehicle_id = e.target.value;
                        if (this.errors.vehicle_id) delete this.errors.vehicle_id;
                    });
                }
            });
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
                    $('#route_id, #vehicle_id').val('').trigger('change.select2');
                }
            });
        },
        
        openEditModal(busStop) {
            this.editMode = true;
            this.busStopId = busStop.id;
            this.errors = {};
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
            
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#route_id').val(busStop.route_id).trigger('change.select2');
                    $('#vehicle_id').val(busStop.vehicle_id).trigger('change.select2');
                }
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
window.openEditModal = function(busStop) {
    const el = document.querySelector('[x-data*="busStopManagement"]');
    if (el) {
        const component = Alpine.$data(el);
        if (component) component.openEditModal(busStop);
    }
};

window.confirmDelete = function(url, name) {
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
