@extends('layouts.receptionist')

@section('title', 'Vehicle Registry - Receptionist')
@section('page-title', 'Vehicle Management')

@section('content')
    <div class="space-y-6" x-data="ajaxDataTable({
        endpoint: '{{ route('receptionist.vehicles.fetch') }}',
        initialData: @js($initialData),
        initialFilters: { search: '' }
    })">
        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card title="Total Fleet" value="{{ $stats['total'] }}" icon="fas fa-bus" color="blue" />
            <x-stat-card title="Diesel Units" value="{{ $stats['diesel'] }}" icon="fas fa-gas-pump" color="slate" />
            <x-stat-card title="Petrol Units" value="{{ $stats['petrol'] }}" icon="fas fa-gas-pump" color="indigo" />
            <x-stat-card title="CNG Core" value="{{ $stats['cng'] }}" icon="fas fa-charging-station" color="purple" />
            <x-stat-card title="Electric EV" value="{{ $stats['electric'] }}" icon="fas fa-bolt" color="teal" />
        </div>

        {{-- Action Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/50 p-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600">
                        <i class="fas fa-truck-pickup text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Institutional Fleet Registry</h2>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Coordinate and monitor logistics assets</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <input type="text" x-model.debounce.300ms="filters.search" placeholder="Search registration, engine..." 
                            class="w-full md:w-72 bg-slate-50 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-teal-500/20 transition-all font-medium">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                    
                    <button @click="$dispatch('open-vehicle-modal')" 
                        class="bg-slate-900 hover:bg-black text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-sm active:scale-95">
                        <i class="fas fa-plus text-[10px]"></i>
                        Register Vehicle
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden min-h-[400px] relative">
            <div x-show="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] z-10 flex items-center justify-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-teal-500/20 border-t-teal-500 rounded-full animate-spin"></div>
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Synchronizing...</span>
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-gray-900/50 border-b border-slate-100 dark:border-gray-700">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Vehicle Identity</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Propulsion Specs</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Configuration</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Operational Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-gray-700">
                    <template x-for="vehicle in items" :key="vehicle.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-900/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 transition-colors group-hover:bg-teal-100 group-hover:text-teal-600">
                                        <i class="fas fa-bus-alt"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight" x-text="vehicle.registration_no"></span>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight mt-0.5" x-text="'Ref: ' + (vehicle.vehicle_no || 'N/A')"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" :class="'bg-' + vehicle.fuel_color + '-500'"></span>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="vehicle.fuel_label"></span>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase" x-text="'Capacity: ' + vehicle.capacity"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="vehicle.model_no"></span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase mt-0.5" x-text="'Purchased: ' + vehicle.purchase_date"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="$dispatch('open-vehicle-modal', vehicle)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="$dispatch('open-delete-modal', {
                                        url: '{{ route('receptionist.vehicles.index') }}/' + vehicle.id,
                                        name: vehicle.registration_no
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
                        <i class="fas fa-bus-alt text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Fleet Registry Empty</h3>
                    <p class="text-sm text-slate-500">No vehicles found matching your criteria</p>
                </div>
            </template>

            {{-- Load More --}}
            <div class="p-6 border-t border-slate-50 flex justify-center" x-show="hasMore">
                <button @click="loadMore()" :disabled="loading" 
                    class="px-8 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-widest rounded-xl transition-all disabled:opacity-50">
                    Discover More Assets
                </button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Vehicle Modal --}}
    <x-modal name="vehicle-modal" x-data="vehicleForm()" @open-vehicle-modal.window="open($event.detail)" alpineTitle="editMode ? 'Edit Vehicle Specification' : 'Register New Vehicle'" maxWidth="3xl">
        <form @submit.prevent="save" id="vehicleForm" class="space-y-6">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Column 1 --}}
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Registration Number <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.registration_no" placeholder="e.g., DL 1C AB 1234"
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.registration_no ? 'ring-2 ring-red-500/20' : ''">
                        <p x-show="errors.registration_no" x-text="errors.registration_no[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Propulsion / Fuel Type <span class="text-red-500">*</span></label>
                        <select x-model="formData.fuel_type" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <option value="">Select Propulsion</option>
                            @foreach(\App\Enums\FuelType::cases() as $fuel)
                                <option value="{{ $fuel->value }}">{{ $fuel->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.fuel_type" x-text="errors.fuel_type[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Seating Capacity</label>
                        <input type="number" x-model="formData.capacity" placeholder="0" 
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Engine Serial</label>
                        <input type="text" x-model="formData.engine_no" placeholder="SN-XXXX" 
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>
                </div>

                {{-- Column 2 --}}
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Internal Reference No</label>
                        <input type="text" x-model="formData.vehicle_no" placeholder="e.g. BUS-01" 
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Vehicle Variant</label>
                        <select x-model="formData.vehicle_type" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <option value="">Select Configuration</option>
                            <option value="bus">School Bus</option>
                            <option value="van">Transport Van</option>
                            <option value="car">Staff Car</option>
                            <option value="truck">Utility Truck</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Model / Year</label>
                        <input type="text" x-model="formData.model_no" placeholder="e.g. 2024 Turbo" 
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Purchase Date</label>
                        <input type="date" x-model="formData.date_of_purchase" 
                            class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-start gap-3">
                <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-900 leading-tight">Asset Synchronization Notice</span>
                    <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wider opacity-80 leading-relaxed">
                        Changes to vehicle specifications will automatically propagate to all <span class="text-indigo-600 font-bold underline">Route Manifests</span> and <span class="text-indigo-600 font-bold underline">Historical Transit Logs</span>.
                    </p>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button @click="$dispatch('close-modal', 'vehicle-modal')" class="px-6 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-700 transition-colors">
                Cancel
            </button>
            <button type="submit" form="vehicleForm" :disabled="submitting" 
                class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                <template x-if="submitting">
                    <i class="fas fa-spinner animate-spin mr-2"></i>
                </template>
                <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Metadata' : 'Create Record')"></span>
            </button>
        </x-slot>
    </x-modal>

    <x-confirm-modal title="Strike Vehicle from Registry?" 
        message="This action will permanently decommissioning the vehicle asset. This operation is irreversible." 
        confirm-text="Strike Record" confirm-color="red" />

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
                            window.Toast.fire({ icon: 'success', title: result.message });
                            this.$dispatch('close-modal', 'vehicle-modal');
                            this.$dispatch('refresh-table');
                        } else {
                            this.errors = result.errors || {};
                        }
                    } catch (e) {
                        window.Toast.fire({ icon: 'error', title: 'System Propagation Failure' });
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection