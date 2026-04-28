@extends('layouts.school')

@section('title', 'Bus Stop Network - School Admin')
@section('page-title', 'Bus Stop Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('school.bus_stops.index') }}',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            defaultFilters: { search: '' },
            initialRows: @js($initialData['rows']),
            initialPagination: @js($initialData['pagination']),
            initialStats: @js($stats)
        }), stopForm())" class="space-y-6" @close-modal.window="if($event.detail === 'bus-stop-modal') resetForm()">

        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Stops" :value="$stats['total']" icon="fas fa-map-marker-alt" color="blue"
                alpine-text="stats.total" />
            <x-stat-card label="Avg. Charge" :value="$stats['avg_charge']" icon="fas fa-money-bill-wave" color="teal"
                alpine-text="stats.avg_charge" />
            <x-stat-card label="Total Routes" :value="$stats['total_routes']" icon="fas fa-route" color="amber"
                alpine-text="stats.total_routes" />
            <x-stat-card label="Mapped Fleet" :value="$stats['total_vehicles']" icon="fas fa-bus-alt" color="purple"
                alpine-text="stats.total_vehicles" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Bus Stops" description="Manage pickup points and locations" icon="fas fa-network-wired">
            <div class="flex items-center gap-3">
                <button @click="open()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i>
                    Add Bus Stop
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
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Stop Registry</h2>
                        <x-table.search placeholder="Search stops, routes..." />
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
                            <x-table.sort-header column="bus_stop_name" label="Stop Name" sort-var="sort"
                                direction-var="direction" />
                            <x-table.sort-header column="route_name" label="Route & Vehicle" sort-var="sort"
                                direction-var="direction" />
                            <x-table.sort-header column="distance_from_institute" label="Distance & Charges" sort-var="sort"
                                direction-var="direction" />
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
                                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-teal-100 dark:group-hover:bg-teal-900/40 group-hover:text-teal-600">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $row['bus_stop_name'] }}</span>
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 mt-0.5">Stop No:
                                                {{ $row['bus_stop_no'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-route text-[10px] text-slate-400 dark:text-gray-500"></i>
                                            <span
                                                class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['route_name'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-bus-alt text-[10px] text-slate-400 dark:text-gray-500"></i>
                                            <span
                                                class="text-[10px] font-medium text-slate-500 dark:text-gray-400">{{ $row['vehicle_no'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-teal-600">{{ $row['charge'] }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400">{{ $row['distance'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="open(@js($row))" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`{{ route('school.bus_stops.index') }}/${row.id}`, 'Delete Bus Stop', 'DELETE')"
                                            title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
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
                                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-teal-100 dark:group-hover:bg-teal-900/40 group-hover:text-teal-600">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight"
                                                x-text="row.bus_stop_name"></span>
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 mt-0.5"
                                                x-text="'Stop No: ' + row.bus_stop_no"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-route text-[10px] text-slate-400 dark:text-gray-500"></i>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                                x-text="row.route_name"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-bus-alt text-[10px] text-slate-400 dark:text-gray-500"></i>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400"
                                                x-text="row.vehicle_no"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-teal-600" x-text="row.charge"></span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400"
                                                x-text="row.distance"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="open(row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`{{ route('school.bus_stops.index') }}/${row.id}`, 'Delete Bus Stop', 'DELETE')"
                                            title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-map-marked"
                            message="No bus stops found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <x-confirm-modal title="Delete Bus Stop?"
            message="This will remove the stop from the route. Associated billing for this stop will end." confirm-text="Delete"
            confirm-color="red" />

        {{-- Add/Edit Stop Modal --}}
        <x-modal name="bus-stop-modal" alpineTitle="editMode ? 'Edit Bus Stop' : 'Add New Bus Stop'" maxWidth="3xl">
            <form @submit.prevent="save" id="stopForm" class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Route Designation
                            <span class="text-red-500">*</span></label>
                        <select x-model="formData.route_id" @change="clearError('route_id')"
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.route_id ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                            <option value="">Select Primary Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.route_id">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.route_id[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Node Identifier (Stop
                            No) <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.bus_stop_no" placeholder="e.g. ST-001"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.bus_stop_no ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('bus_stop_no')">
                        <template x-if="errors.bus_stop_no">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.bus_stop_no[0]"></p>
                        </template>
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Geographic Landmark
                            Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.bus_stop_name" placeholder="e.g. Central Square Park Entrance"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.bus_stop_name ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('bus_stop_name')">
                        <template x-if="errors.bus_stop_name">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.bus_stop_name[0]"></p>
                        </template>
                    </div>
                </div>

                <hr class="my-6 border-slate-100 dark:border-gray-700">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Distance from Institute</label>
                        <div class="relative group">
                            <input type="number" step="0.01" x-model="formData.distance_from_institute"
                                placeholder="Distance (KM)"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                :class="errors.distance_from_institute ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                                @input="clearError('distance_from_institute')">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 dark:text-gray-500">KM</span>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Charge Per Month</label>
                        <div class="relative group">
                            <input type="number" step="0.01" x-model="formData.charge_per_month" placeholder="Tariff (MT)"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                :class="errors.charge_per_month ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                                @input="clearError('charge_per_month')">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-teal-600">₹</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-900 dark:text-gray-100 leading-tight">Note</span>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                            Distance and charge details are used for automated transport fee generation.
                        </p>
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <button @click="$dispatch('close-modal', 'bus-stop-modal')"
                    class="px-6 py-2.5 text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest hover:text-slate-700 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="stopForm" :disabled="submitting"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                    <template x-if="submitting">
                        <i class="fas fa-spinner animate-spin mr-2"></i>
                    </template>
                    <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Stop' : 'Save Stop')"></span>
                </button>
            </x-slot>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function stopForm() {
                return {
                    editMode: false,
                    submitting: false,
                    stopId: null,
                    formData: {
                        route_id: '',
                        bus_stop_no: '',
                        bus_stop_name: '',
                        distance_from_institute: '',
                        charge_per_month: ''
                    },
                    errors: {},

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors }; delete e[field]; this.errors = e;
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.stopId = null;
                        this.formData = {
                            route_id: '',
                            bus_stop_no: '',
                            bus_stop_name: '',
                            distance_from_institute: '',
                            charge_per_month: ''
                        };
                        this.errors = {};
                    },

                    open(stop = null) {
                        this.errors = {};
                        if (stop) {
                            this.editMode = true;
                            this.stopId = stop.id;
                            this.fetchFullData(stop.id);
                        } else {
                            this.editMode = false;
                            this.stopId = null;
                            this.formData = {
                                route_id: '',
                                bus_stop_no: '',
                                bus_stop_name: '',
                                distance_from_institute: '',
                                charge_per_month: ''
                            };
                        }
                        this.$dispatch('open-modal', 'bus-stop-modal');
                    },

                    async fetchFullData(id) {
                        try {
                            const response = await fetch(`{{ route('school.bus_stops.index') }}/${id}/edit`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await response.json();
                            if (response.ok) {
                                this.formData = {
                                    route_id: data.route_id || '',
                                    bus_stop_no: data.bus_stop_no,
                                    bus_stop_name: data.bus_stop_name,
                                    distance_from_institute: data.distance_from_institute,
                                    charge_per_month: data.charge_per_month
                                };
                            }
                        } catch (e) {
                            console.error('Failed to fetch full stop data');
                        }
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `{{ route('school.bus_stops.index') }}/${this.stopId}`
                            : `{{ route('school.bus_stops.store') }}`;

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
                                this.$dispatch('close-modal', 'bus-stop-modal');
                                this.fetchData();
                            } else {
                                this.errors = result.errors || {};
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save bus stop' });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    exportData(format) {
                        window.location.href = `{{ route('school.bus_stops.export') }}?format=${format}`;
                    }
                }
            }
        </script>
    @endpush
@endsection
