@extends('layouts.receptionist')

@section('title', 'Route Manifest - Receptionist')
@section('page-title', 'Route Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('receptionist.routes.fetch') }}',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            defaultFilters: { search: '' },
            initialRows: @js($initialData['rows']),
            initialPagination: @js($initialData['pagination']),
            initialStats: @js($stats)
        }), routeForm())" class="space-y-6" @close-modal.window="if($event.detail === 'route-modal') resetForm()">

        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Corridors" :value="$stats['total_routes']" icon="fas fa-route" color="blue"
                alpine-text="stats.total_routes" />
            <x-stat-card label="Active Channels" :value="$stats['active_routes']" icon="fas fa-check-double" color="teal"
                alpine-text="stats.active_routes" />
            <x-stat-card label="Mapped Fleet" :value="$stats['mapped_vehicles']" icon="fas fa-bus-alt" color="indigo"
                alpine-text="stats.mapped_vehicles" />
            <x-stat-card label="Peak Capacity" :value="$stats['total_capacity']" icon="fas fa-users" color="purple"
                alpine-text="stats.total_capacity" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Transport Routes" description="Manage routes and vehicle assignments"
            icon="fas fa-network-wired">
            <button @click="open()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Route
            </button>
            <button @click="exportData('csv')" :disabled="exporting"
                class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                <span x-show="exporting"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"
                    x-cloak></span>
                <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Route Manifest</h2>
                        <x-table.search placeholder="Search routes, vehicles..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value"
                            class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                    <button @click="clearAllFilters()"
                        class="flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs hover:bg-red-200 transition-colors">
                        <i class="fas fa-times-circle"></i>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="route_name" label="Route Name" sort-var="sort"
                                direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Assigned Vehicle</th>
                            <x-table.sort-header column="status" label="Status" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">
                                Actions</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @if(empty($initialData['rows']))
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-route text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg text-gray-500">No routes found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 transition-colors group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $row['route_name'] }}</span>
                                            <span class="text-[10px] font-medium text-slate-400 mt-0.5">Created:
                                                {{ $row['created_at'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-bus-alt text-[10px] text-slate-400"></i>
                                            <span
                                                class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['vehicle_label'] }}</span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500">Capacity:
                                            {{ $row['vehicle_capacity'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-{{ $row['status_color'] }}-50 text-{{ $row['status_color'] }}-600 ring-1 ring-{{ $row['status_color'] }}-200">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="open(row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`/receptionist/routes/{{ $row['id'] }}`, 'Delete Route', 'DELETE')"
                                            title="Delete"
                                            class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body (Successive Hydration) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                        :class="loading ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 transition-colors group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight"
                                                x-text="row.route_name"></span>
                                            <span class="text-[10px] font-medium text-slate-400 mt-0.5"
                                                x-text="'Created: ' + row.created_at"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-bus-alt text-[10px] text-slate-400"></i>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                                x-text="row.vehicle_label"></span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500"
                                            x-text="'Capacity: ' + row.vehicle_capacity + ' seats'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                        :class="'bg-' + row.status_color + '-50 text-' + row.status_color + '-600 ring-1 ring-' + row.status_color + '-200'"
                                        x-text="row.status_label"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="$dispatch('open-route-modal', row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`/receptionist/routes/${row.id}`, 'Delete Route', 'DELETE')"
                                            title="Delete"
                                            class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-route"
                            message="No routes found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <!-- Server-rendered pagination: visible instantly, hidden once Alpine takes over -->
            @if($initialData['pagination']['total'] > 0)
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    :class="{ 'hidden': true }">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ $initialData['pagination']['from'] }} to {{ $initialData['pagination']['to'] }} of
                        {{ $initialData['pagination']['total'] }} results
                    </div>
                </div>
            @endif

            <x-table.pagination />
        </div>

        <x-confirm-modal title="Delete Route?"
            message="This will remove the route from active service. Linked student assignments will be unassigned."
            confirm-text="Delete" confirm-color="red" />

        {{-- Add/Edit Route Modal --}}
        <x-modal name="route-modal" alpineTitle="editMode ? 'Edit Route' : 'Add New Route'" maxWidth="2xl">
            <form @submit.prevent="save" id="routeForm" class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Route Designation /
                            Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.route_name" placeholder="e.g. North Sector Express"
                            class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                            :class="errors.route_name ? 'border-red-500' : 'border-slate-200'" @input="clearError('route_name')">
                        <template x-if="errors.route_name">
                            <p x-text="errors.route_name[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Assigned Fleet Asset
                            <span class="text-red-500">*</span></label>
                        <select x-model="formData.vehicle_id"
                            class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.vehicle_id ? 'border-red-500' : 'border-slate-200'"
                            @change="clearError('vehicle_id')">
                            <option value="">Select Primary Vehicle</option>
                            <template x-for="vehicle in vehicles" :key="vehicle.id">
                                <option :value="vehicle.id"
                                    x-text="`${vehicle.registration_no} (${vehicle.vehicle_no || 'N/A'})`"></option>
                            </template>
                        </select>
                        <template x-if="errors.vehicle_id">
                            <p x-text="errors.vehicle_id[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Activation Date
                                <span class="text-red-500">*</span></label>
                            <input type="date" x-model="formData.route_create_date"
                                class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all"
                                :class="errors.route_create_date ? 'border-red-500' : 'border-slate-200'"
                                @change="clearError('route_create_date')">
                            <template x-if="errors.route_create_date">
                                <p x-text="errors.route_create_date[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                            </template>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Operational
                                Status</label>
                            <select x-model="formData.status"
                                class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                                <option value="1">Active / Operational</option>
                                <option value="0">Inactive / Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 bg-teal-50 border border-teal-100 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-network-wired text-teal-600 mt-0.5"></i>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-900 leading-tight">Note</span>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Changes to this route will automatically update all <span class="text-teal-600 font-bold">bus
                                stops</span> and student transport assignments.
                        </p>
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <button @click="$dispatch('close-modal', 'route-modal')"
                    class="px-6 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="routeForm" :disabled="submitting"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                    <template x-if="submitting">
                        <i class="fas fa-spinner animate-spin mr-2"></i>
                    </template>
                    <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Route' : 'Save Route')"></span>
                </button>
            </x-slot>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function routeForm() {
                return {
                    editMode: false,
                    submitting: false,
                    vehicles: [],
                    formData: {
                        route_name: '',
                        vehicle_id: '',
                        route_create_date: '{{ date('Y-m-d') }}',
                        status: 1
                    },
                    errors: {},

                    clearError(field) {
                        if (this.errors[field]) {
                            delete this.errors[field];
                        }
                    },

                    init() {
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[x-model="formData.vehicle_id"]').on('change', (e) => {
                                    this.formData.vehicle_id = e.target.value;
                                    this.clearError('vehicle_id');
                                });
                            }
                        });
                    },

                    resetForm() {
                        this.editMode = false;
                        this.formData = {
                            route_name: '',
                            vehicle_id: '',
                            route_create_date: '{{ date('Y-m-d') }}',
                            status: 1
                        };
                        this.errors = {};
                    },

                    async open(route = null) {
                        this.errors = {};
                        await this.fetchVehicles();

                        if (route) {
                            this.editMode = true;
                            this.routeId = route.id;
                            this.formData = {
                                route_name: route.route_name,
                                vehicle_id: route.vehicle_id,
                                route_create_date: route.raw_date || '',
                                status: route.status
                            };
                        } else {
                            this.editMode = false;
                            this.formData = {
                                route_name: '',
                                vehicle_id: '',
                                route_create_date: '{{ date('Y-m-d') }}',
                                status: 1
                            };
                        }
                        this.$dispatch('open-modal', 'route-modal');
                    },

                    async fetchVehicles() {
                        if (this.vehicles.length > 0) return;
                        try {
                            const response = await fetch('{{ route('receptionist.routes.vehicles') }}');
                            if (response.ok) {
                                this.vehicles = await response.json();
                            }
                        } catch (e) {
                            console.error('Failed to synchronize fleet metadata');
                        }
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `{{ route('receptionist.routes.index') }}/${this.routeId}`
                            : `{{ route('receptionist.routes.store') }}`;

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
                                    _method: this.editMode ? 'PUT' : 'POST'
                                })
                            });

                            const result = await response.json();
                            if (response.ok) {
                                window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'route-modal');
                                this.fetchData();
                            } else {
                                this.errors = result.errors || {};
                            }
                        } catch (e) {
                            window.Toast.fire({ icon: 'error', title: 'Failed to save route' });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection