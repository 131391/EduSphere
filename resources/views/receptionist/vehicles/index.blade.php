@extends('layouts.receptionist')

@section('title', 'Vehicle Registry - Receptionist')
@section('page-title', 'Vehicle Management')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('receptionist.vehicles.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { search: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats'])
    }), vehicleForm())" class="space-y-6" @close-modal.window="if($event.detail === 'vehicle-modal') resetForm()">
        
        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Fleet" :value="$stats['total']" icon="fas fa-bus" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Diesel Units" :value="$stats['diesel']" icon="fas fa-gas-pump" color="slate" alpine-text="stats.diesel" />
            <x-stat-card label="Petrol Units" :value="$stats['petrol']" icon="fas fa-gas-pump" color="indigo" alpine-text="stats.petrol" />
            <x-stat-card label="CNG Core" :value="$stats['cng']" icon="fas fa-charging-station" color="purple" alpine-text="stats.cng" />
            <x-stat-card label="Electric EV" :value="$stats['electric']" icon="fas fa-bolt" color="teal" alpine-text="stats.electric" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Vehicle Fleet" description="Manage school transport vehicles" icon="fas fa-truck-pickup">
            <button @click="open()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Register Vehicle
            </button>
            <button @click="exportData('csv')" :disabled="exporting"
                class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
                <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Vehicle Registry</h2>
                        <x-table.search placeholder="Search registration, engine..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                    <button @click="clearAllFilters()" class="flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs hover:bg-red-200 transition-colors">
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
                            <x-table.sort-header column="registration_no" label="Vehicle" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="fuel_type" label="Fuel & Capacity" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="model_no" label="Model & Purchase" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-bus-alt text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No vehicles found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-teal-100 dark:group-hover:bg-teal-900/40 group-hover:text-teal-600">
                                        <i class="fas fa-bus-alt"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $row['registration_no'] }}</span>
                                        <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 dark:text-gray-500 mt-0.5">Ref: {{ $row['vehicle_no'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-{{ $row['fuel_color'] }}-500"></span>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['fuel_label'] }}</span>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400">{{ $row['capacity'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['model_no'] }}</span>
                                    <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400 mt-0.5">Purchased: {{ $row['purchase_date'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="$dispatch('open-vehicle-modal', {{ json_encode($row) }})" title="Edit" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="quickAction(`/receptionist/vehicles/{{ $row['id'] }}`, 'Delete Vehicle', 'DELETE')" title="Delete" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body (Successive Hydration) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-teal-100 dark:group-hover:bg-teal-900/40 group-hover:text-teal-600">
                                            <i class="fas fa-bus-alt"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight" x-text="row.registration_no"></span>
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 dark:text-gray-500 mt-0.5" x-text="'Ref: ' + (row.vehicle_no || 'N/A')"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" :class="'bg-' + row.fuel_color + '-500'"></span>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="row.fuel_label"></span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400" x-text="row.capacity"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="row.model_no"></span>
                                        <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400 mt-0.5" x-text="'Purchased: ' + row.purchase_date"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="open(row)" title="Edit" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="quickAction(`/receptionist/vehicles/${row.id}`, 'Delete Vehicle', 'DELETE')" title="Delete" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-bus-alt" message="No vehicles found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <!-- Server-rendered pagination: visible instantly, hidden once Alpine takes over -->
            <x-table.pagination />
        </div>

        <x-confirm-modal />

        {{-- Add/Edit Vehicle Modal --}}
        <x-modal name="vehicle-modal" alpineTitle="editMode ? 'Edit Vehicle' : 'Add New Vehicle'" maxWidth="3xl">
            <form @submit.prevent="save" id="vehicleForm" class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Column 1 --}}
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Registration Number <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.registration_no" placeholder="e.g., DL 1C AB 1234"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.registration_no ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('registration_no')">
                        <template x-if="errors.registration_no">
                            <template x-if="errors.registration_no[0]"><p class="modal-error-message" x-text="errors.registration_no[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Propulsion / Fuel Type <span class="text-red-500">*</span></label>
                        <select x-model="formData.fuel_type" 
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.fuel_type ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @change="clearError('fuel_type')">
                            <option value="">Select Propulsion</option>
                            @foreach(\App\Enums\FuelType::cases() as $fuel)
                                <option value="{{ $fuel->value }}">{{ $fuel->name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.fuel_type">
                            <template x-if="errors.fuel_type[0]"><p class="modal-error-message" x-text="errors.fuel_type[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Seating Capacity</label>
                        <input type="number" x-model="formData.capacity" placeholder="0" 
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.capacity ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('capacity')">
                        <template x-if="errors.capacity">
                            <template x-if="errors.capacity[0]"><p class="modal-error-message" x-text="errors.capacity[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Engine Serial</label>
                        <input type="text" x-model="formData.engine_no" placeholder="SN-XXXX" 
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.engine_no ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('engine_no')">
                        <template x-if="errors.engine_no">
                            <template x-if="errors.engine_no[0]"><p class="modal-error-message" x-text="errors.engine_no[0]"></p></template>
                        </template>
                    </div>
                </div>

                {{-- Column 2 --}}
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Internal Reference No</label>
                        <input type="text" x-model="formData.vehicle_no" placeholder="e.g. BUS-01" 
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.vehicle_no ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('vehicle_no')">
                        <template x-if="errors.vehicle_no">
                            <template x-if="errors.vehicle_no[0]"><p class="modal-error-message" x-text="errors.vehicle_no[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Vehicle Variant</label>
                        <select x-model="formData.vehicle_type" 
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.vehicle_type ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'"
                            @change="clearError('vehicle_type')">
                            <option value="">Select Configuration</option>
                            <option value="bus">School Bus</option>
                            <option value="van">Transport Van</option>
                            <option value="car">Staff Car</option>
                            <option value="truck">Utility Truck</option>
                        </select>
                        <template x-if="errors.vehicle_type">
                            <template x-if="errors.vehicle_type[0]"><p class="modal-error-message" x-text="errors.vehicle_type[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Model / Year</label>
                        <input type="text" x-model="formData.model_no" placeholder="e.g. 2024 Turbo" 
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.model_no ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('model_no')">
                        <template x-if="errors.model_no">
                            <template x-if="errors.model_no[0]"><p class="modal-error-message" x-text="errors.model_no[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Purchase Date</label>
                        <input type="date" x-model="formData.date_of_purchase" 
                            class="w-full bg-slate-50 dark:bg-gray-700 border-none rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.date_of_purchase ? 'ring-2 ring-red-500/20' : ''" @change="clearError('date_of_purchase')">
                        <template x-if="errors.date_of_purchase">
                            <template x-if="errors.date_of_purchase[0]"><p class="modal-error-message" x-text="errors.date_of_purchase[0]"></p></template>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-2xl flex items-start gap-3">
                <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-900 dark:text-gray-100 dark:text-gray-100 leading-tight">Note</span>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                        Changes to vehicle details will automatically update all <span class="text-indigo-600 font-bold">routes</span> and <span class="text-indigo-600 font-bold">transport history</span>.
                    </p>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button @click="$dispatch('close-modal', 'vehicle-modal')" class="px-6 py-2.5 text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest hover:text-slate-700 dark:hover:text-gray-200 transition-colors">
                Cancel
            </button>
            <button type="submit" form="vehicleForm" :disabled="submitting" 
                class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                <template x-if="submitting">
                    <i class="fas fa-spinner animate-spin mr-2"></i>
                </template>
                <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Vehicle' : 'Save Vehicle')"></span>
            </button>
            </x-slot>
        </x-modal>
    </div>


    @push('scripts')
    <script>
        function vehicleForm() {
            return {
                editMode: false,
                submitting: false,
                formData: {
                    registration_no: '',
                    fuel_type: '',
                    capacity: '',
                    engine_no: '',
                    vehicle_no: '',
                    vehicle_type: '',
                    model_no: '',
                    date_of_purchase: '',
                },
                errors: {},

                clearError(field) {
                    if (this.errors && this.errors[field]) {
                        const e = { ...this.errors }; delete e[field]; this.errors = e;
                    }
                },

                init() {
                    // Standardized initialization
                },

                resetForm() {
                    this.editMode = false;
                    this.formData = {
                        registration_no: '',
                        fuel_type: '',
                        capacity: '',
                        engine_no: '',
                        vehicle_no: '',
                        vehicle_type: '',
                        model_no: '',
                        date_of_purchase: '',
                    };
                    this.errors = {};
                },

                open(vehicle = null) {
                    this.errors = {};
                    if (vehicle) {
                        this.editMode = true;
                        this.vehicleId = vehicle.id;
                        // Fetch full data for editing if needed, or use transformed data
                        this.formData = {
                            registration_no: vehicle.registration_no,
                            fuel_type: vehicle.fuel_type || '', // We need to handle this correctly if it's not in the transformer
                            capacity: parseFloat(vehicle.capacity),
                            engine_no: vehicle.engine_no === 'N/A' ? '' : vehicle.engine_no,
                            vehicle_no: vehicle.vehicle_no || '',
                            vehicle_type: vehicle.vehicle_type || '',
                            model_no: vehicle.model_no === 'N/A' ? '' : vehicle.model_no,
                            date_of_purchase: vehicle.purchase_date_raw || '', // We might need bit more data in transformer
                        };
                        // Since transformer is optimized for display, we might need a quick AJAX fetch for full edit data
                        this.fetchFullData(vehicle.id);
                    } else {
                        this.editMode = false;
                        this.formData = {
                            registration_no: '',
                            fuel_type: '',
                            capacity: '',
                            engine_no: '',
                            vehicle_no: '',
                            vehicle_type: '',
                            model_no: '',
                            date_of_purchase: '',
                        };
                    }
                    this.$dispatch('open-modal', 'vehicle-modal');
                },

                async fetchFullData(id) {
                    try {
                        const response = await fetch(`{{ route('receptionist.vehicles.index') }}/${id}/edit`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.formData = {
                                registration_no: data.registration_no,
                                fuel_type: data.fuel_type,
                                capacity: data.capacity,
                                engine_no: data.engine_no || '',
                                vehicle_no: data.vehicle_no || '',
                                vehicle_type: data.vehicle_type || '',
                                model_no: data.model_no || '',
                                date_of_purchase: data.date_of_purchase ? data.date_of_purchase.split('T')[0] : '',
                            };
                        }
                    } catch (e) {
                        console.error('Failed to fetch full vehicle data');
                    }
                },

                async save() {
                    this.submitting = true;
                    this.errors = {};
                    const url = this.editMode 
                        ? `{{ route('receptionist.vehicles.index') }}/${this.vehicleId}`
                        : `{{ route('receptionist.vehicles.store') }}`;
                    
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
                            this.$dispatch('close-modal', 'vehicle-modal');
                            this.fetchData();
                        } else {
                            this.errors = result.errors || {};
                        }
                    } catch (e) {
                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save vehicle' });
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection