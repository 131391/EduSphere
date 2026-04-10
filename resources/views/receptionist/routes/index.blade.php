@extends('layouts.receptionist')

@section('title', 'Route Management - Receptionist')
@section('page-title', 'Route Management')
@section('page-description', 'Manage transportation routes')

@section('content')
    <div class="space-y-6" x-data="routeManagement" x-init="init()">
        {{-- Success/Error Messages --}}


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
                    <button
                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
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
                        return "openEditModal(".json_encode($routeData).")";
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
        <x-modal name="route-modal" alpineTitle="editMode ? 'Edit Transport Route' : 'Establish New Route'" maxWidth="md">
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

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Route Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="route_name" x-model="formData.route_name" placeholder="Enter Route Name"
                            @input="delete errors.route_name"
                            class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                            :class="errors.route_name ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                        <template x-if="errors.route_name">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight"
                                x-text="errors.route_name[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Select Assigned Vehicle
                            <span class="text-red-500">*</span></label>
                        <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                            @change="delete errors.vehicle_id"
                            class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                            :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <option value="">Select Primary Vehicle</option>
                            <template x-for="vehicle in vehicles" :key="vehicle.id">
                                <option :value="vehicle.id" x-text="`${vehicle.vehicle_no} (${vehicle.registration_no})`">
                                </option>
                            </template>
                        </select>
                        <template x-if="errors.vehicle_id">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight"
                                x-text="errors.vehicle_id[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Route Create Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="route_create_date" x-model="formData.route_create_date"
                            @input="delete errors.route_create_date"
                            class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                            :class="errors.route_create_date ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                        <template x-if="errors.route_create_date">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight"
                                x-text="errors.route_create_date[0]"></p>
                        </template>
                    </div>

                    <template x-if="editMode">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select name="status" x-model="formData.status" id="route_status"
                                @change="delete errors.status"
                                class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                                :class="errors.status ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <template x-if="errors.status">
                                <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight"
                                    x-text="errors.status[0]"></p>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-xl">
                    <button type="button" @click="closeModal()" :disabled="submitting"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm">
                        Discard
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="px-8 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white rounded-xl hover:from-teal-600 hover:to-emerald-700 transition-all font-black text-sm shadow-lg shadow-teal-100 flex items-center gap-2">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <span x-text="submitting ? 'Processing...' : (editMode ? 'Update Route' : 'Confirm Route')"></span>
                    </button>
                </div>
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

                                // Sync Select2 if present
                                this.$nextTick(() => {
                                    if (typeof $ !== 'undefined') {
                                        $(document).on('change', '#vehicle_id', (e) => {
                                            this.formData.vehicle_id = e.target.value;
                                            if (this.errors.vehicle_id) delete this.errors.vehicle_id;
                                        });
                                        $(document).on('change', '#route_status', (e) => {
                                            this.formData.status = e.target.value;
                                            if (this.errors.status) delete this.errors.status;
                                        });
                                    }
                                });
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
                                this.formData = {
                                    route_name: route.route_name || '',
                                    vehicle_id: String(route.vehicle_id) || '',
                                    route_create_date: route.route_create_date || '',
                                    status: route.status || 1,
                                };
                                this.errors = {};
                                this.$dispatch('open-modal', 'route-modal');

                                this.$nextTick(() => {
                                    if (typeof $ !== 'undefined') {
                                        $('#vehicle_id').val(route.vehicle_id).trigger('change.select2');
                                        $('#route_status').val(route.status).trigger('change.select2');
                                    }
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