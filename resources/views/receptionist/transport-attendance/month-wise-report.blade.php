@extends('layouts.receptionist')

@section('title', 'Transport Attendance Month Wise Report')

@section('content')
<div class="space-y-6" x-data="transportAttendanceReport()" x-init="init()">
    {{-- Page Header --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-3 rounded-2xl shadow-lg shadow-purple-100/50">
                    <i class="fas fa-file-invoice text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight text-shadow-sm">Transit Analytics</h2>
                    <p class="text-sm text-gray-500 font-medium">Consolidated boarding audit & manifest telemetry</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('receptionist.transport-attendance.index') }}"
                    class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-arrow-left mr-2 text-purple-500"></i>
                    Back to Verification
                </a>
            </div>
        </div>
    </div>

    <!-- Audit Parameters Section -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8 transition-all hover:shadow-md">
        <div class="p-8">
            <div class="mb-8 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 border border-purple-100 shadow-sm">
                    <i class="fas fa-sliders-h text-sm"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Audit Parameters</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Define temporal & operational scope</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Vehicle Select -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Institutional Fleet <span class="text-red-500 font-bold">*</span></label>
                    <select id="vehicle_id" 
                            x-model="formData.vehicle_id"
                            @change="delete errors.vehicle_id; loadRoutes(true)"
                            class="modal-input-premium"
                            :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : ''">
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $selectedVehicle?->id) == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.vehicle_id">
                        <p class="modal-error-message" x-text="errors.vehicle_id[0]"></p>
                    </template>
                </div>

                <!-- Route Select -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Active Transit Channel <span class="text-red-500 font-bold">*</span></label>
                    <select id="route_id" 
                            x-model="formData.route_id"
                            @change="delete errors.route_id"
                            x-ref="routeSelect"
                            class="modal-input-premium"
                            :class="errors.route_id ? 'border-red-500 ring-red-500/10' : ''">
                        <option value="">Select Route</option>
                        <template x-for="route in routes" :key="route.id">
                            <option :value="route.id" x-text="route.route_name"></option>
                        </template>
                    </select>
                    <template x-if="errors.route_id">
                        <p class="modal-error-message" x-text="errors.route_id[0]"></p>
                    </template>
                </div>

                <!-- Month Select -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Audit / Billing Month <span class="text-red-500 font-bold">*</span></label>
                    <input type="month" id="month" x-model="formData.month"
                            @input="delete errors.month"
                            value="{{ old('month', $selectedMonth) }}"
                            class="modal-input-premium"
                            :class="errors.month ? 'border-red-500 ring-red-500/10' : ''">
                    <template x-if="errors.month">
                        <p class="modal-error-message" x-text="errors.month[0]"></p>
                    </template>
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button @click="searchReport()" 
                            class="w-full h-[54px] bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white font-black rounded-2xl transition-all shadow-lg shadow-purple-100/50 flex items-center justify-center gap-3 uppercase text-xs tracking-widest group">
                        <i class="fas fa-analytics group-hover:scale-110 transition-transform"></i>
                        Execute Audit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Context & Legend -->
    @if($selectedVehicle && $selectedRoute)
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 transition-all hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                <i class="fas fa-bus text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fleet Asset</p>
                <p class="text-sm font-black text-slate-800">{{ $selectedVehicle->vehicle_no }}</p>
                <p class="text-[10px] text-blue-500 font-bold uppercase tracking-tighter mt-0.5">{{ $selectedVehicle->registration_no }}</p>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 transition-all hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                <i class="fas fa-route text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Transit Channel</p>
                <p class="text-sm font-black text-slate-800">{{ $selectedRoute->route_name }}</p>
                <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-tighter mt-0.5">Active Network</p>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 transition-all hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 border border-purple-100 shadow-sm">
                <i class="fas fa-calendar-check text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Audit Period</p>
                <p class="text-sm font-black text-slate-800">{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</p>
                <p class="text-[10px] text-purple-500 font-bold uppercase tracking-tighter mt-0.5">Billing Cycle</p>
            </div>
        </div>

        <div class="bg-slate-900 rounded-2xl p-5 shadow-lg border border-slate-800 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-key text-white text-6xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 relative z-10">Telemetry Legend</p>
            <div class="grid grid-cols-2 gap-x-3 gap-y-2 relative z-10">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-4 bg-teal-500 rounded flex items-center justify-center text-[8px] font-black text-white">PBS</span>
                    <span class="text-[9px] text-slate-300 font-bold uppercase tracking-tighter">Pickup (B)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-5 h-4 bg-teal-500 rounded flex items-center justify-center text-[8px] font-black text-white">DSC</span>
                    <span class="text-[9px] text-slate-300 font-bold uppercase tracking-tighter">Drop (S)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-5 h-4 bg-indigo-500 rounded flex items-center justify-center text-[8px] font-black text-white">PSC</span>
                    <span class="text-[9px] text-slate-300 font-bold uppercase tracking-tighter">Pickup (S)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-5 h-4 bg-indigo-500 rounded flex items-center justify-center text-[8px] font-black text-white">DBS</span>
                    <span class="text-[9px] text-slate-300 font-bold uppercase tracking-tighter">Drop (B)</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Consolidated Audit Trail --}}
    @if($selectedVehicle && $selectedRoute && count($students) > 0)
    <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-12">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/50 sticky left-0 z-30">
            <div>
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Consolidated Audit Trail</h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Manifest tracking for <span class="font-bold text-indigo-600">{{ count($students) }}</span> pupil profiles</p>
            </div>
            <div class="px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl flex items-center gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cycle Duration</span>
                <span class="px-3 py-1 bg-white border border-slate-200 text-indigo-600 rounded-lg text-xs font-black tracking-widest">
                    @php
                        $year = (int)substr($selectedMonth, 0, 4);
                        $monthNum = (int)substr($selectedMonth, 5, 2);
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
                    @endphp
                    {{ $daysInMonth }} Days
                </span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar relative">
            <table class="min-w-full divide-y divide-slate-100 border-collapse">
                <thead class="bg-slate-50/80 sticky top-0 z-20">
                    <tr>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest sticky left-0 bg-slate-50 z-30 border-r border-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Boarder Registry
                        </th>
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <th class="px-4 py-6 text-center min-w-[70px] border-l border-slate-100/50">
                                <span class="block text-[9px] font-black text-slate-300 uppercase tracking-tighter mb-1">D-{{ sprintf('%02d', $day) }}</span>
                                <span class="block text-[11px] font-black text-slate-700 uppercase leading-none">
                                    {{ \Carbon\Carbon::createFromDate($year, $monthNum, $day)->format('D') }}
                                </span>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-50">
                    @foreach($students as $student)
                        <tr class="hover:bg-indigo-50/20 transition-all group">
                            <td class="px-8 py-5 sticky left-0 bg-white group-hover:bg-indigo-50/40 transition-all z-20 border-r border-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center border border-slate-100 shadow-inner group-hover:scale-110 transition-transform">
                                        <i class="fas fa-user text-[10px] text-slate-300"></i>
                                    </div>
                                    <div class="flex flex-col min-w-[140px]">
                                        <span class="text-xs font-black text-slate-800 leading-tight">{{ $student['name'] }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $student['admission_no'] }}</span>
                                    </div>
                                </div>
                            </td>
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $isWeekend = in_array(\Carbon\Carbon::createFromDate($year, $monthNum, $day)->format('l'), ['Saturday', 'Sunday']);
                                @endphp
                                <td class="px-3 py-5 text-center align-middle border-l border-slate-50 group-hover:bg-indigo-50/10 transition-all {{ $isWeekend ? 'bg-slate-50/30' : '' }}">
                                    @if(isset($student['days'][$day]) && count($student['days'][$day]) > 0)
                                        <div class="flex flex-col gap-1.5 items-center justify-center">
                                            @foreach($student['days'][$day] as $code)
                                                <span class="inline-flex items-center justify-center w-10 h-6 rounded-lg font-black text-[9px] uppercase tracking-tighter border shadow-sm transition-all hover:scale-110"
                                                    class="{{ in_array($code, ['PBS', 'DSC']) ? 'bg-teal-500 text-white border-teal-600' : 'bg-indigo-500 text-white border-indigo-600' }}"
                                                    :style="`background-color: ${'{{ in_array($code, ['PBS', 'DSC']) ? '#10b981' : '#6366f1' }}'}; color: white; border-color: ${'{{ in_array($code, ['PBS', 'DSC']) ? '#059669' : '#4f46e5' }}'}`">
                                                    {{ $code }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-200 font-black">-</span>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($selectedVehicle && $selectedRoute && count($students) == 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
        <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-8 border border-gray-100">
                <i class="fas fa-users text-gray-300 text-4xl"></i>
            </div>
            <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">No Temporal Data</h3>
            <p class="text-xs text-gray-400 mt-2 uppercase tracking-widest leading-relaxed">No boarding records synchronized for the chosen route and audit period.</p>
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 p-20 text-center">
        <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-teal-50 dark:bg-teal-900 rounded-full flex items-center justify-center mx-auto mb-8 border border-teal-100">
                <i class="fas fa-chart-line text-teal-600 dark:text-teal-300 text-4xl"></i>
            </div>
            <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">Awaiting Audit Selection</h3>
            <p class="text-xs text-gray-400 mt-2 uppercase tracking-widest leading-relaxed">Select institutional fleet, transit channel, and billing period to generate the manifest audit trail.</p>
        </div>
    </div>
    @endif
</div>

<script>
function transportAttendanceReport() {
    return {
        formData: {
            vehicle_id: '{{ old('vehicle_id', $selectedVehicle?->id ?? '') }}',
            route_id: '{{ old('route_id', $selectedRoute?->id ?? '') }}',
            month: '{{ old('month', $selectedMonth) }}',
        },
        routes: [],
        errors: {},

        init() {
            // Initialize Select2 for vehicle and route
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        const $vehicleSelect = $('#vehicle_id');
                        if ($vehicleSelect.length && !$vehicleSelect.hasClass('select2-hidden-accessible')) {
                            $vehicleSelect.select2({
                                placeholder: 'Select Vehicle',
                                allowClear: false,
                                width: '100%'
                            });
                        }

                        const $routeSelect = $('#route_id');
                        if ($routeSelect.length && !$routeSelect.hasClass('select2-hidden-accessible')) {
                            $routeSelect.select2({
                                placeholder: 'Select Route',
                                allowClear: false,
                                width: '100%'
                            });
                        }

                        // Handle vehicle change
                        $('#vehicle_id').on('select2:select select2:change', (e) => {
                            this.formData.vehicle_id = e.target.value || $('#vehicle_id').val();
                            if (this.errors.vehicle_id) delete this.errors.vehicle_id;
                            // Clear route when vehicle changes
                            this.loadRoutes(true);
                        });

                        // Handle route change
                        $('#route_id').on('select2:select select2:change', (e) => {
                            this.formData.route_id = e.target.value || $('#route_id').val();
                            if (this.errors.route_id) delete this.errors.route_id;
                        });
                    }
                    
                    // Enhance month input styling and behavior
                    const monthInput = document.getElementById('month');
                    if (monthInput) {
                        // Sync with Alpine.js on change
                        monthInput.addEventListener('change', (e) => {
                            this.formData.month = e.target.value;
                            if (this.errors.month) delete this.errors.month;
                        });
                        
                        // Sync Alpine.js value to input if needed
                        if (this.formData.month && !monthInput.value) {
                            monthInput.value = this.formData.month;
                        }
                    }
                    
                    // If vehicle is already selected, load routes (preserve route_id if set)
                    if (this.formData.vehicle_id) {
                        this.loadRoutes(false);
                    }
                }, 300);
            });
        },

        async loadRoutes(clearRoute = false) {
            if (!this.formData.vehicle_id) {
                this.routes = [];
                this.formData.route_id = '';
                this.updateRouteOptions();
                return;
            }

            // Preserve current route_id if we're not clearing it
            const preserveRouteId = this.formData.route_id && !clearRoute;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('{{ route('receptionist.transport-attendance.get-routes-for-report') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        vehicle_id: this.formData.vehicle_id,
                    }),
                });

                const data = await response.json();
                
                if (response.ok && data.success) {
                    this.routes = data.routes;
                    this.updateRouteOptions();
                    // Only clear route_id if explicitly requested (e.g., when vehicle changes)
                    if (clearRoute || !preserveRouteId) {
                        this.formData.route_id = '';
                    }
                } else {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                    }
                    this.routes = [];
                    this.updateRouteOptions();
                    if (clearRoute) {
                        this.formData.route_id = '';
                    }
                }
            } catch (error) {
                console.error('Error loading routes:', error);
                this.routes = [];
                this.updateRouteOptions();
                if (clearRoute) {
                    this.formData.route_id = '';
                }
            }
        },

        updateRouteOptions() {
            setTimeout(() => {
                const select = this.$refs.routeSelect || document.getElementById('route_id');
                if (!select) return;

                if (typeof $ === 'undefined') {
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    if (Array.isArray(this.routes) && this.routes.length > 0) {
                        this.routes.forEach(route => {
                            const option = document.createElement('option');
                            option.value = route.id;
                            option.textContent = route.route_name;
                            select.appendChild(option);
                        });
                    }
                    return;
                }

                const $select = $(select);
                const isSelect2 = $select.hasClass('select2-hidden-accessible');
                const currentValue = this.formData.route_id;

                if (isSelect2) {
                    try {
                        $select.select2('destroy');
                    } catch (e) {
                        // Silently handle error
                    }
                }

                while (select.options.length > 1) {
                    select.remove(1);
                }

                if (Array.isArray(this.routes) && this.routes.length > 0) {
                    this.routes.forEach((route) => {
                        const option = document.createElement('option');
                        option.value = route.id;
                        option.textContent = route.route_name;
                        select.appendChild(option);
                    });
                }

                try {
                    $select.select2({
                        placeholder: 'Select Route',
                        allowClear: false,
                        width: '100%'
                    });
                    
                    $select.off('select2:select select2:change').on('select2:select select2:change', (e) => {
                        this.formData.route_id = e.target.value || $select.val();
                    });
                    
                    if (currentValue) {
                        const optionExists = $select.find(`option[value="${currentValue}"]`).length > 0;
                        if (optionExists) {
                            $select.val(currentValue).trigger('change');
                            this.formData.route_id = currentValue;
                        } else {
                            $select.val('').trigger('change');
                            this.formData.route_id = '';
                        }
                    } else {
                        $select.val('').trigger('change');
                    }
                } catch (e) {
                    // Silently handle error
                }
            }, 150);
        },

        searchReport() {
            this.errors = {};
            let localErrors = {};
            if (!this.formData.vehicle_id) localErrors.vehicle_id = ['Vehicle selection required'];
            if (!this.formData.route_id) localErrors.route_id = ['Route selection required'];
            if (!this.formData.month) localErrors.month = ['Month selection required'];

            if (Object.keys(localErrors).length > 0) {
                this.errors = localErrors;
                return;
            }

            // Build query string
            const params = new URLSearchParams({
                vehicle_id: this.formData.vehicle_id,
                route_id: this.formData.route_id,
                month: this.formData.month,
            });

            window.location.href = '{{ route('receptionist.transport-attendance.month-wise-report') }}?' + params.toString();
        },
    };
}
</script>
@endsection
