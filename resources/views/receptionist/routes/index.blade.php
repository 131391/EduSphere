@extends('layouts.receptionist')

@section('title', 'Route Manifest - Receptionist')
@section('page-title', 'Route Management')

@section('content')
    <div class="space-y-6" x-data="ajaxDataTable({
        endpoint: '{{ route('receptionist.routes.index') }}',
        initialData: @js($initialData),
        initialFilters: { search: '' }
    })">
        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card title="Total Corridors" value="{{ $stats['total_routes'] }}" icon="fas fa-route" color="blue" />
            <x-stat-card title="Active Channels" value="{{ $stats['active_routes'] }}" icon="fas fa-check-double" color="teal" />
            <x-stat-card title="Mapped Fleet" value="{{ $stats['mapped_vehicles'] }}" icon="fas fa-bus-alt" color="indigo" />
            <x-stat-card title="Peak Capacity" value="{{ $stats['total_capacity'] }}" icon="fas fa-users" color="purple" />
        </div>

        {{-- Action Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/50 p-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-network-wired text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Institutional Transit Corridors</h2>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Define and monitor logistics pathways</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <input type="text" x-model.debounce.300ms="filters.search" placeholder="Search corridors, vehicles..." 
                            class="w-full md:w-72 bg-slate-50 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-teal-500/20 transition-all font-medium">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                    
                    <button @click="$dispatch('open-route-modal')" 
                        class="bg-slate-900 hover:bg-black text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-sm active:scale-95">
                        <i class="fas fa-plus text-[10px]"></i>
                        Establish Route
                    </button>

                    <button @click="exportData()" 
                        class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 active:scale-95">
                        <i class="fas fa-file-csv text-teal-600"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>

        {{-- Manifest Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden min-h-[400px] relative">
            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] z-10 flex items-center justify-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-teal-500/20 border-t-teal-500 rounded-full animate-spin"></div>
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Synchronizing Manifest...</span>
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-gray-900/50 border-b border-slate-100 dark:border-gray-700">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Route Designation</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Fleet Assignment</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Operational Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                    <template x-for="route in items" :key="route.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-900/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 transition-colors group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight" x-text="route.route_name"></span>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mt-0.5" x-text="'Established: ' + route.created_at"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-bus-alt text-[10px] text-slate-400"></i>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="route.vehicle_label"></span>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase" x-text="'Route Capacity: ' + route.vehicle_capacity"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" 
                                    :class="'bg-' + route.status_color + '-50 text-' + route.status_color + '-600 ring-1 ring-' + route.status_color + '-200'"
                                    x-text="route.status_label"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-right">
                                    <button @click="$dispatch('open-route-modal', route)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="$dispatch('open-delete-modal', {
                                        url: '{{ route('receptionist.routes.index') }}/' + route.id,
                                        name: route.route_name
                                    })" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
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
                        <i class="fas fa-route text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No Corridors Defined</h3>
                    <p class="text-sm text-slate-500">Kickstart logistics by establishing your first transit route</p>
                </div>
            </template>

            {{-- Load More --}}
            <div class="p-6 border-t border-slate-50 flex justify-center" x-show="hasMore">
                <button @click="loadMore()" :disabled="loading" 
                    class="px-8 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-widest rounded-xl transition-all disabled:opacity-50">
                    Discover More Channels
                </button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Route Modal --}}
    <x-modal name="route-modal" x-data="routeForm()" @open-route-modal.window="open($event.detail)" alpineTitle="editMode ? 'Modify Route Configuration' : 'Establish Transit Channel'" maxWidth="2xl">
        <form @submit.prevent="save" id="routeForm" class="space-y-6">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Route Designation / Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.route_name" placeholder="e.g. North Sector Express"
                        class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                        :class="errors.route_name ? 'ring-2 ring-red-500/20' : ''">
                    <p x-show="errors.route_name" x-text="errors.route_name[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Assigned Fleet Asset <span class="text-red-500">*</span></label>
                    <select x-model="formData.vehicle_id" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        <option value="">Select Primary Vehicle</option>
                        <template x-for="vehicle in vehicles" :key="vehicle.id">
                            <option :value="vehicle.id" x-text="`${vehicle.registration_no} (${vehicle.vehicle_no || 'N/A'})`"></option>
                        </template>
                    </select>
                    <p x-show="errors.vehicle_id" x-text="errors.vehicle_id[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Activation Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="formData.route_create_date" 
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        <p x-show="errors.route_create_date" x-text="errors.route_create_date[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Operational Status</label>
                        <select x-model="formData.status" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <option value="1">Active / Operational</option>
                            <option value="0">Inactive / Suspended</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-teal-50 border border-teal-100 p-4 rounded-2xl flex items-start gap-3">
                <i class="fas fa-network-wired text-teal-600 mt-0.5"></i>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-900 leading-tight">Infrastructure Correlation</span>
                    <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wider opacity-80 leading-relaxed">
                        Mapping changes will automatically update all <span class="text-teal-600 font-bold underline">Institutional Stop Records</span> and student transit assignments.
                    </p>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button @click="$dispatch('close-modal', 'route-modal')" class="px-6 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-700 transition-colors">
                Cancel
            </button>
            <button type="submit" form="routeForm" :disabled="submitting" 
                class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                <template x-if="submitting">
                    <i class="fas fa-spinner animate-spin mr-2"></i>
                </template>
                <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Channel' : 'Establish Route')"></span>
            </button>
        </x-slot>
    </x-modal>

    <x-confirm-modal title="Decommission Transit Corridor?" 
        message="This operation will strike the route from active manifest. Associated student assignments will be orphaned." 
        confirm-text="Decommission" confirm-color="red" />

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
                            this.$dispatch('refresh-table');
                        } else {
                            this.errors = result.errors || {};
                        }
                    } catch (e) {
                        window.Toast.fire({ icon: 'error', title: 'Route Propagation Failure' });
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection