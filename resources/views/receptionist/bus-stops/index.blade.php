@extends('layouts.receptionist')

@section('title', 'Bus Stop Network - Receptionist')
@section('page-title', 'Bus Stop Management')

@section('content')
    <div class="space-y-6" x-data="ajaxDataTable({
            endpoint: '{{ route('receptionist.bus-stops.fetch') }}',
            initialData: @js($initialData),
            initialFilters: { search: '' }
        })">
        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card title="Total Stops" value="{{ $stats['total_stops'] }}" icon="fas fa-map-marker-alt"
                color="blue" />
            <x-stat-card title="Coverage Areas" value="{{ $stats['distinct_areas'] }}" icon="fas fa-globe-asia"
                color="teal" />
            <x-stat-card title="Avg. Distance" value="{{ $stats['average_distance'] }} KM" icon="fas fa-road"
                color="amber" />
            <x-stat-card title="Mapped Fleet" value="{{ $stats['total_mapped'] }}" icon="fas fa-bus-alt" color="purple" />
        </div>

        {{-- Action Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/50 p-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600">
                        <i class="fas fa-network-wired text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Bus Stops</h2>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Manage pickup points and locations</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <input type="text" x-model.debounce.300ms="filters.search"
                            placeholder="Search stops, routes..."
                            class="w-full md:w-72 bg-slate-50 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-teal-500/20 transition-all font-medium">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>

                    <button @click="$dispatch('open-stop-modal')"
                        class="bg-slate-900 hover:bg-black text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-sm active:scale-95">
                        <i class="fas fa-plus text-[10px]"></i>
                        Add Bus Stop
                    </button>

                    <button @click="exportData()"
                        class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 active:scale-95">
                        <i class="fas fa-file-csv text-teal-600"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>

        {{-- Registry Table --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden min-h-[400px] relative">
            <div x-show="loading"
                class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] z-10 flex items-center justify-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-teal-500/20 border-t-teal-500 rounded-full animate-spin"></div>
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Loading...</span>
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-gray-900/50 border-b border-slate-100 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Stop Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Route &amp; Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Distance &amp; Charges</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                    <template x-for="stop in items" :key="stop.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-900/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 transition-colors group-hover:bg-teal-100 group-hover:text-teal-600">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight"
                                            x-text="stop.bus_stop_name"></span>
                                        <span class="text-[10px] font-medium text-slate-400 mt-0.5"
                                            x-text="'Stop No: ' + stop.bus_stop_no"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-route text-[10px] text-slate-400"></i>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                            x-text="stop.route_name"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-bus-alt text-[10px] text-slate-400"></i>
                                        <span class="text-[10px] font-medium text-slate-500"
                                            x-text="stop.vehicle_label"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-teal-600" x-text="stop.charge"></span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                        <span class="text-[10px] font-medium text-slate-500"
                                            x-text="stop.distance"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 opacity-60">
                                        <i class="fas fa-crosshairs text-[8px] text-slate-400"></i>
                                        <span class="text-[10px] font-medium text-slate-500"
                                            x-text="stop.coords"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-right">
                                    <button @click="$dispatch('open-stop-modal', stop)" title="Edit"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="$dispatch('open-delete-modal', {
                                            url: '{{ route('receptionist.bus-stops.index') }}/' + stop.id,
                                            name: stop.bus_stop_name
                                        })" title="Delete"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- Empty State --}}
            <template x-if="!loading && items.length === 0">
                <div class="py-20 flex flex-col items-center">
                    <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4">
                        <i class="fas fa-map-marked text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No Bus Stops Found</h3>
                    <p class="text-sm text-slate-500">Get started by adding your first bus stop.</p>
                </div>
            </template>

            {{-- Load More --}}
            <div class="p-6 border-t border-slate-50 flex justify-center" x-show="hasMore">
                <button @click="loadMore()" :disabled="loading"
                    class="px-8 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-widest rounded-xl transition-all disabled:opacity-50">
                    Load More
                </button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Stop Modal --}}
    <x-modal name="bus-stop-modal" x-data="stopForm()" @open-stop-modal.window="open($event.detail)"
        alpineTitle="editMode ? 'Edit Bus Stop' : 'Add New Bus Stop'" maxWidth="3xl">
        <form @submit.prevent="save" id="busStopForm" class="space-y-6">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Logistics Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Route Designation
                        <span class="text-red-500">*</span></label>
                    <select x-model="formData.route_id"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        <option value="">Select Primary Route</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                        @endforeach
                    </select>
                    <p x-show="errors.route_id" x-text="errors.route_id[0]" class="text-[10px] font-bold text-red-500 ml-1">
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Node Identifier (Stop
                        No) <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.bus_stop_no" placeholder="e.g. ST-001"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all font-premium">
                    <p x-show="errors.bus_stop_no" x-text="errors.bus_stop_no[0]"
                        class="text-[10px] font-bold text-red-500 ml-1"></p>
                </div>

                <div class="md:col-span-2 space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Geographic Landmark
                        Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.bus_stop_name" placeholder="e.g. Central Square Park Entrance"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all font-premium">
                    <p x-show="errors.bus_stop_name" x-text="errors.bus_stop_name[0]"
                        class="text-[10px] font-bold text-red-500 ml-1"></p>
                </div>
            </div>

            <hr class="border-slate-100/50">

            {{-- GPS & Tariff Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Latitude</label>
                    <input type="number" step="0.00000001" x-model="formData.latitude" placeholder="0.00000000"
                        class="w-full bg-slate-100/50 border-none rounded-xl py-2 px-3 text-xs font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Longitude</label>
                    <input type="number" step="0.00000001" x-model="formData.longitude" placeholder="0.00000000"
                        class="w-full bg-slate-100/50 border-none rounded-xl py-2 px-3 text-xs font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">PIN Code</label>
                    <input type="text" x-model="formData.area_pin_code" placeholder="110001"
                        class="w-full bg-slate-100/50 border-none rounded-xl py-2 px-3 text-xs font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Distance & Tariff
                        Mapping</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 group">
                            <input type="number" step="0.01" x-model="formData.distance_from_institute"
                                placeholder="Distance (KM)"
                                class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">KM</span>
                        </div>
                        <div class="relative flex-1 group">
                            <input type="number" step="0.01" x-model="formData.charge_per_month" placeholder="Tariff (MT)"
                                class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-teal-600">₹</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Fleet Assignment
                        Override</label>
                    <select x-model="formData.vehicle_id"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        <option value="">Allocated via Route</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Instructional Notice --}}
            <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-start gap-3">
                <i class="fas fa-satellite-dish text-indigo-600 mt-0.5"></i>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-900 leading-tight">Note</span>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Adding GPS coordinates enables <span class="text-indigo-600 font-bold">live tracking</span> and student proximity alerts.
                    </p>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button @click="$dispatch('close-modal', 'bus-stop-modal')"
                class="px-6 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-700 transition-colors">
                Cancel
            </button>
            <button type="submit" form="busStopForm" :disabled="submitting"
                class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                <template x-if="submitting">
                    <i class="fas fa-spinner animate-spin mr-2"></i>
                </template>
                <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Stop' : 'Save Stop')"></span>
            </button>
        </x-slot>
    </x-modal>

    <x-confirm-modal title="Delete Bus Stop?"
        message="This will remove the stop from the route. Associated billing for this stop will end."
        confirm-text="Delete" confirm-color="red" />

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
                                window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'bus-stop-modal');
                                this.$dispatch('refresh-table');
                            } else {
                                this.errors = result.errors || {};
                            }
                        } catch (e) {
                            window.Toast.fire({ icon: 'error', title: 'Failed to save bus stop' });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection