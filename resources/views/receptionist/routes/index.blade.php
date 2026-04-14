@extends('layouts.receptionist')

@section('title', 'Route Management - Receptionist')
@section('page-title', 'Route Management')
@section('page-description', 'Manage transportation routes')

@section('content')
    <div class="space-y-6" x-data="routeManagement" x-init="init()">
        {{-- Route Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Routes</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['total_routes'] }}</p>
                    </div>
                    <div class="bg-indigo-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-route text-indigo-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Active Channels</p>
                        <p class="text-2xl font-black text-emerald-600">{{ $stats['active_routes'] }}</p>
                    </div>
                    <div class="bg-emerald-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Mapped Fleet</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['mapped_vehicles'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bus text-blue-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Capacity</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['total_capacity'] }}</p>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-amber-500 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-3 rounded-2xl shadow-lg shadow-indigo-100">
                        <i class="fas fa-map-marked-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Route Manifest</h2>
                        <p class="text-sm text-gray-500 font-medium">Define and manage institutional transit corridors</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-indigo-100 group">
                        <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                        Establish New Route
                    </button>
                    <button
                        class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fas fa-file-excel mr-2 text-emerald-500"></i>
                        Route Export
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
                    'render' => function ($row, $index, $data) {
                        return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
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
                    'render' => function ($row) {
                        return $row->vehicle ? $row->vehicle->vehicle_no : 'N/A';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        $routeData = [
                            'id' => $row->id,
                            'route_name' => $row->route_name,
                            'vehicle_id' => $row->vehicle_id,
                            'route_create_date' => $row->route_create_date ? $row->route_create_date->format('Y-m-d') : null,
                            'status' => $row->status,
                        ];
                        return "openEditModal(" . json_encode($routeData) . ")";
                    },
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        return "confirmDelete('" . route('receptionist.routes.destroy', $row->id) . "', '{$row->route_name}')";
                    },
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$routes" :searchable="true" :actions="$tableActions"
            empty-message="No routes found" empty-icon="fas fa-route">
            Route List
        </x-data-table>

        {{-- Add/Edit Route Modal --}}
        <x-modal name="route-modal" alpineTitle="editMode ? 'Modify Route Specification' : 'Establish New Transit Corridor'" maxWidth="xl">
            <form @submit.prevent="save" method="POST" class="p-0 relative" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Route Designation / Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="route_name" x-model="formData.route_name" placeholder="e.g. North Sector Express"
                                @input="clearError('route_name')"
                                class="modal-input-premium pr-10"
                                :class="errors.route_name ? 'border-red-500 ring-red-500/10' : ''">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-route text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.route_name">
                            <p class="modal-error-message" x-text="errors.route_name[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Assigned Fleet Asset <span class="text-red-600 font-bold">*</span></label>
                        <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                            @change="clearError('vehicle_id')"
                            class="modal-input-premium"
                            :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : ''">
                            <option value="">Select Primary Vehicle</option>
                            <template x-for="vehicle in vehicles" :key="vehicle.id">
                                <option :value="vehicle.id" x-text="`${vehicle.vehicle_no} (${vehicle.registration_no})`"></option>
                            </template>
                        </select>
                        <template x-if="errors.vehicle_id">
                            <p class="modal-error-message" x-text="errors.vehicle_id[0]"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Activation Date <span class="text-red-600 font-bold">*</span></label>
                            <input type="date" name="route_create_date" x-model="formData.route_create_date"
                                @input="clearError('route_create_date')"
                                class="modal-input-premium"
                                :class="errors.route_create_date ? 'border-red-500 ring-red-500/10' : ''">
                            <template x-if="errors.route_create_date">
                                <p class="modal-error-message" x-text="errors.route_create_date[0]"></p>
                            </template>
                        </div>

                        <template x-if="editMode">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Operational Status</label>
                                <select name="status" x-model="formData.status" id="route_status" @change="clearError('status')"
                                    class="modal-input-premium">
                                    <option value="1">Active / Operational</option>
                                    <option value="0">Inactive / Suspended</option>
                                </select>
                            </div>
                        </template>
                    </div>

                    {{-- Administrative Notice --}}
                    <div class="bg-[#f5f3ff] border border-[#ddd6fe] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-network-wired text-indigo-600 text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[13px] font-bold text-slate-900 leading-tight">Infrastructure Correlation</span>
                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                                Modifying route mapping will affect <span class="text-indigo-600 italic underline decoration-indigo-100">transit schedules</span> and student stop assignments globally.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <x-slot name="footer">
                    <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="submitting ? 'Syncing...' : (editMode ? 'Update Manifest' : 'Establish Route')"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal title="Permanently Decommission Route?"
            message="This action will remove the route from the active registry. Students assigned to this route will need reassignment."
            confirm-text="Decommission" confirm-color="red" />

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

                            errors: {},
                            submitting: false,

                            async init() {
                                await this.fetchVehicles();

                                // Sync Select2 with Alpine state
                                this.$nextTick(() => {
                                    if (typeof $ !== 'undefined') {
                                        $('select[name="vehicle_id"], select[name="status"]').on('change', (e) => {
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
                                    ? `/receptionist/routes/${this.routeId}`
                                    : '{{ route('receptionist.routes.store') }}';

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
                                                title: result.message || 'Route registry updated'
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

                            async deleteRoute(url) {
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
                                    route_create_date: '{{ date('Y-m-d') }}',
                                    status: 1,
                                };
                                this.errors = {};
                                this.$dispatch('open-modal', 'route-modal');
                            },

                            openEditModal(route) {
                                this.editMode = true;
                                this.routeId = route.id;
                                this.errors = {};
                                this.formData = {
                                    route_name: route.route_name || '',
                                    vehicle_id: route.vehicle_id ? String(route.vehicle_id) : '',
                                    route_create_date: route.route_create_date || '',
                                    status: String(route.status ?? 1),
                                };
                                this.$dispatch('open-modal', 'route-modal');

                                this.$nextTick(() => {
                                    setTimeout(() => {
                                        if (typeof $ !== 'undefined') {
                                            $('select[name="vehicle_id"]').val(this.formData.vehicle_id).trigger('change');
                                            $('select[name="status"]').val(this.formData.status).trigger('change');
                                        }
                                    }, 150);
                                });
                            },

                            closeModal() {
                                this.$dispatch('close-modal', 'route-modal');
                                this.errors = {};
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

                    function confirmDelete(url, routeName) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                message: `Are you sure you want to decommission the route: "${routeName}"?`,
                                onConfirm: () => {
                                    const component = Alpine.$data(document.querySelector('[x-data*="routeManagement"]'));
                                    if (component) {
                                        component.deleteRoute(url);
                                    }
                                }
                            }
                        }));
                    }
                </script>
            </div>
        @endpush
@endsection