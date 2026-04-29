@extends('layouts.receptionist')

@section('title', 'Bus Stop Network - Receptionist')
@section('page-title', 'Bus Stop Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('receptionist.bus-stops.fetch') }}',
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
            <x-stat-card label="Total Stops" :value="$stats['total_stops']" icon="fas fa-map-marker-alt" color="blue"
                alpine-text="stats.total_stops" />
            <x-stat-card label="Coverage Areas" :value="$stats['distinct_areas']" icon="fas fa-globe-asia" color="teal"
                alpine-text="stats.distinct_areas" />
            <x-stat-card label="Avg. Distance" value="{{ $stats['average_distance'] }} KM" icon="fas fa-road" color="amber"
                alpine-text="stats.average_distance + ' KM'" />
            <x-stat-card label="Mapped Fleet" :value="$stats['total_mapped']" icon="fas fa-bus-alt" color="purple"
                alpine-text="stats.total_mapped" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Bus Stops" description="Manage pickup points and locations" icon="fas fa-network-wired">
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
        </x-page-header>

        <!-- AJAX Data Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Stop Registry</h2>
                        <x-table.search placeholder="Search stops, routes..." />
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

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-map-marked text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg text-gray-500">No bus stops found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
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
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 dark:text-gray-500 mt-0.5">Stop No:
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
                                                class="text-[10px] font-medium text-slate-500 dark:text-gray-400">{{ $row['vehicle_label'] }}</span>
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
                                        <div class="flex items-center gap-1.5 opacity-60">
                                            <i class="fas fa-crosshairs text-[8px] text-slate-400 dark:text-gray-500"></i>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400">{{ $row['coords'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="$dispatch('open-stop-modal', {{ json_encode($row) }})" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="quickAction(`/receptionist/bus-stops/{{ $row['id'] }}`, 'Delete Bus Stop', 'DELETE')"
                                            title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body (Successive Hydration) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                        :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
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
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 dark:text-gray-500 mt-0.5"
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
                                                x-text="row.vehicle_label"></span>
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
                                        <div class="flex items-center gap-1.5 opacity-60">
                                            <i class="fas fa-crosshairs text-[8px] text-slate-400 dark:text-gray-500"></i>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400" x-text="row.coords"></span>
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
                                            @click="quickAction(`/receptionist/bus-stops/${row.id}`, 'Delete Bus Stop', 'DELETE')"
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


            <x-table.pagination :initial="$initialData['pagination']" />
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

                {{-- Logistics Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Route Designation
                            <span class="text-red-500">*</span></label>
                        <select x-model="formData.route_id" @change="clearError('route_id')"
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                            :class="errors.route_id ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                            <option value="">Select Primary Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.route_id">
                            <template x-if="errors.route_id[0]"><p class="modal-error-message" x-text="errors.route_id[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Node Identifier (Stop
                            No) <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.bus_stop_no" placeholder="e.g. ST-001"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                            :class="errors.bus_stop_no ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('bus_stop_no')">
                        <template x-if="errors.bus_stop_no">
                            <template x-if="errors.bus_stop_no[0]"><p class="modal-error-message" x-text="errors.bus_stop_no[0]"></p></template>
                        </template>
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Geographic Landmark
                            Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.bus_stop_name" placeholder="e.g. Central Square Park Entrance"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                            :class="errors.bus_stop_name ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('bus_stop_name')">
                        <template x-if="errors.bus_stop_name">
                            <template x-if="errors.bus_stop_name[0]"><p class="modal-error-message" x-text="errors.bus_stop_name[0]"></p></template>
                        </template>
                    </div>
                </div>

                <hr class="border-slate-100/50 dark:border-gray-700">

                {{-- GPS & Tariff Section --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Latitude</label>
                        <input type="number" step="0.00000001" x-model="formData.latitude" placeholder="0.00000000"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-2 px-3 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.latitude ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @input="clearError('latitude')">
                        <template x-if="errors.latitude">
                            <template x-if="errors.latitude[0]"><p class="modal-error-message" x-text="errors.latitude[0]"></p></template>
                        </template>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Longitude</label>
                        <input type="number" step="0.00000001" x-model="formData.longitude" placeholder="0.00000000"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-2 px-3 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.longitude ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @input="clearError('longitude')">
                        <template x-if="errors.longitude">
                            <template x-if="errors.longitude[0]"><p class="modal-error-message" x-text="errors.longitude[0]"></p></template>
                        </template>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">PIN Code</label>
                        <input type="text" x-model="formData.area_pin_code" placeholder="110001"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-2 px-3 text-xs font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.area_pin_code ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @input="clearError('area_pin_code')">
                        <template x-if="errors.area_pin_code">
                            <template x-if="errors.area_pin_code[0]"><p class="modal-error-message" x-text="errors.area_pin_code[0]"></p></template>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Distance & Tariff
                            Mapping</label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1 group">
                                <input type="number" step="0.01" x-model="formData.distance_from_institute"
                                    placeholder="Distance (KM)"
                                    class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                    :class="errors.distance_from_institute ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                                    @input="clearError('distance_from_institute')">
                                <template x-if="errors.distance_from_institute">
                                    <template x-if="errors.distance_from_institute[0]"><p class="modal-error-message" x-text="errors.distance_from_institute[0]"></p></template>
                                </template>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 dark:text-gray-500">KM</span>
                            </div>
                            <div class="relative flex-1 group">
                                <input type="number" step="0.01" x-model="formData.charge_per_month" placeholder="Tariff (MT)"
                                    class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                                    :class="errors.charge_per_month ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                                    @input="clearError('charge_per_month')">
                                <template x-if="errors.charge_per_month">
                                    <template x-if="errors.charge_per_month[0]"><p class="modal-error-message" x-text="errors.charge_per_month[0]"></p></template>
                                </template>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-teal-600">₹</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Fleet Assignment
                            Override</label>
                        <select x-model="formData.vehicle_id"
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                            :class="errors.vehicle_id ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @change="clearError('vehicle_id')">
                            <option value="">Allocated via Route</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                                </option>
                            @endforeach
                        </select>
                        <template x-if="errors.vehicle_id">
                            <template x-if="errors.vehicle_id[0]"><p class="modal-error-message" x-text="errors.vehicle_id[0]"></p></template>
                        </template>
                    </div>
                </div>
                
                {{-- Instructional Notice --}}
                <div class="mt-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-satellite-dish text-indigo-600 mt-0.5"></i>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-900 dark:text-gray-100 dark:text-gray-100 leading-tight">Note</span>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                            Adding GPS coordinates enables <span class="text-indigo-600 font-bold">live tracking</span> and
                            student proximity alerts.
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
                    formData: {
                        route_id: '',
                        vehicle_id: '',
                        bus_stop_no: '',
                        bus_stop_name: '',
                        latitude: '',
                        longitude: '',
                        distance_from_institute: '',
                        charge_per_month: '',
                        area_pin_code: ''
                    },
                    errors: {},

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors }; delete e[field]; this.errors = e;
                        }
                    },

                    init() {
                        // Using no-select2 on select elements to ensure standard Alpine binding
                    },

                    resetForm() {
                        this.editMode = false;
                        this.formData = {
                            route_id: '',
                            vehicle_id: '',
                            bus_stop_no: '',
                            bus_stop_name: '',
                            latitude: '',
                            longitude: '',
                            distance_from_institute: '',
                            charge_per_month: '',
                            area_pin_code: ''
                        };
                        this.errors = {};
                    },

                    async open(stop = null) {
                        this.errors = {};
                        if (stop) {
                            this.editMode = true;
                            this.stopId = stop.id;
                            this.formData = { ...stop.raw };
                        } else {
                            this.editMode = false;
                            this.formData = {
                                route_id: '',
                                vehicle_id: '',
                                bus_stop_no: '',
                                bus_stop_name: '',
                                latitude: '',
                                longitude: '',
                                distance_from_institute: '',
                                charge_per_month: '',
                                area_pin_code: ''
                            };
                        }
                        this.$dispatch('open-modal', 'bus-stop-modal');
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `{{ route('receptionist.bus-stops.index') }}/${this.stopId}`
                            : `{{ route('receptionist.bus-stops.store') }}`;

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
                    }
                }
            }
        </script>
    @endpush
@endsection