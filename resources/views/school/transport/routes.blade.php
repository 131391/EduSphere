@extends('layouts.school')

@section('title', 'Route Manifest - School Admin')
@section('page-title', 'Route Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('school.transport.transport_routes.index') }}',
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
            <x-stat-card label="Total Corridors" :value="$stats['total']" icon="fas fa-route" color="blue"
                alpine-text="stats.total" />
            <x-stat-card label="Active Channels" :value="$stats['active']" icon="fas fa-check-double" color="teal"
                alpine-text="stats.active" />
            <x-stat-card label="Inactive / Suspended" :value="$stats['inactive']" icon="fas fa-ban" color="rose"
                alpine-text="stats.inactive" />
            <x-stat-card label="Unassigned Routes" :value="$stats['unassigned']" icon="fas fa-bus-alt" color="indigo"
                alpine-text="stats.unassigned" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Transport Routes" description="Manage routes and vehicle assignments"
            icon="fas fa-network-wired">
            <div class="flex items-center gap-3">
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
            </div>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Route Manifest</h2>
                        <x-table.search placeholder="Search routes, vehicles..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 group-hover:text-indigo-600">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $row['route_name'] }}</span>
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 mt-0.5">Created:
                                                {{ $row['route_create_date'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-bus-alt text-[10px] text-slate-400 dark:text-gray-500"></i>
                                        <span
                                            class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['vehicle_no'] }}</span>
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
                                        <button @click="open(@js($row))" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`{{ route('school.transport.transport_routes.index') }}/${row.id}`, 'Delete Route', 'DELETE')"
                                            title="Delete"
                                            class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                        :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 group-hover:text-indigo-600">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight"
                                                x-text="row.route_name"></span>
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 mt-0.5"
                                                x-text="'Created: ' + row.route_create_date"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-bus-alt text-[10px] text-slate-400 dark:text-gray-500"></i>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                            x-text="row.vehicle_no"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                        :class="'bg-' + row.status_color + '-50 text-' + row.status_color + '-600 ring-1 ring-' + row.status_color + '-200'"
                                        x-text="row.status_label"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="open(row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`{{ route('school.transport.transport_routes.index') }}/${row.id}`, 'Delete Route', 'DELETE')"
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
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Route Designation /
                            Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.route_name" placeholder="e.g. North Sector Express"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.route_name ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('route_name')">
                        <template x-if="errors.route_name">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.route_name[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Assigned Fleet Asset
                            <span class="text-red-500">*</span></label>
                        <select x-model="formData.vehicle_id"
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.vehicle_id ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @change="clearError('vehicle_id')">
                            <option value="">Select Primary Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->registration_no }} ({{ $vehicle->vehicle_no ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        <template x-if="errors.vehicle_id">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.vehicle_id[0]"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Activation Date
                                <span class="text-red-500">*</span></label>
                            <input type="date" x-model="formData.route_create_date"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                :class="errors.route_create_date ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                                @change="clearError('route_create_date')">
                            <template x-if="errors.route_create_date">
                                <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.route_create_date[0]"></p>
                            </template>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Operational
                                Status</label>
                            <select x-model="formData.status"
                                class="no-select2 w-full bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all">
                                <option value="active">Active / Operational</option>
                                <option value="inactive">Inactive / Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 bg-teal-50 border border-teal-100 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-network-wired text-teal-600 mt-0.5"></i>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-900 dark:text-gray-100 leading-tight">Note</span>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                            Changes to this route will automatically update all <span class="text-teal-600 font-bold">bus
                                stops</span> and student transport assignments.
                        </p>
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <button @click="$dispatch('close-modal', 'route-modal')"
                    class="px-6 py-2.5 text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest hover:text-slate-700 dark:hover:text-gray-200 transition-colors">
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
                    routeId: null,
                    formData: {
                        route_name: '',
                        vehicle_id: '',
                        route_create_date: '{{ date('Y-m-d') }}',
                        status: 'active'
                    },
                    errors: {},

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors }; delete e[field]; this.errors = e;
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.routeId = null;
                        this.formData = {
                            route_name: '',
                            vehicle_id: '',
                            route_create_date: '{{ date('Y-m-d') }}',
                            status: 'active'
                        };
                        this.errors = {};
                    },

                    open(route = null) {
                        this.errors = {};
                        if (route) {
                            this.editMode = true;
                            this.routeId = route.id;
                            this.fetchFullData(route.id);
                        } else {
                            this.editMode = false;
                            this.routeId = null;
                            this.formData = {
                                route_name: '',
                                vehicle_id: '',
                                route_create_date: '{{ date('Y-m-d') }}',
                                status: 'active'
                            };
                        }
                        this.$dispatch('open-modal', 'route-modal');
                    },

                    async fetchFullData(id) {
                        try {
                            const response = await fetch(`{{ route('school.transport.transport_routes.index') }}/${id}/edit`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await response.json();
                            if (response.ok) {
                                this.formData = {
                                    route_name: data.route_name,
                                    vehicle_id: data.vehicle_id || '',
                                    route_create_date: data.route_create_date ? data.route_create_date.split('T')[0] : '',
                                    status: data.status
                                };
                            }
                        } catch (e) {
                            console.error('Failed to fetch full route data');
                        }
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `{{ route('school.transport.transport_routes.index') }}/${this.routeId}`
                            : `{{ route('school.transport.transport_routes.store') }}`;

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
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'route-modal');
                                this.fetchData();
                            } else {
                                this.errors = result.errors || {};
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save route' });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    exportData(format) {
                        window.location.href = `{{ route('school.transport.transport_routes.export') }}?format=${format}`;
                    }
                }
            }
        </script>
    @endpush
@endsection
