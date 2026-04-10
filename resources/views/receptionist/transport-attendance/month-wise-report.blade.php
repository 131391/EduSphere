@extends('layouts.receptionist')

@section('title', 'Transport Attendance Month Wise Report')

@section('content')
<div class="space-y-6" x-data="transportAttendanceReport()" x-init="init()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Transport Attendance Month Wise</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View monthly attendance reports for transport routes</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('receptionist.transport-attendance.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8 transition-all hover:shadow-md">
        <div class="p-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">Audit Parameters</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Select vehicle, route, and temporal period to generate the report</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Vehicle Select -->
                <div>
                    <label for="vehicle_id" class="block text-[10px] font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest leading-none">
                        Institutional Fleet <span class="text-red-500">*</span>
                    </label>
                    <select id="vehicle_id" 
                            x-model="formData.vehicle_id"
                            @change="delete errors.vehicle_id; loadRoutes(true)"
                            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                            :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $selectedVehicle?->id) == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.vehicle_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.vehicle_id[0]"></p>
                    </template>
                </div>

                <!-- Route Select -->
                <div>
                    <label for="route_id" class="block text-[10px] font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest leading-none">
                        Active Route <span class="text-red-500">*</span>
                    </label>
                    <select id="route_id" 
                            x-model="formData.route_id"
                            @change="delete errors.route_id"
                            x-ref="routeSelect"
                            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                            :class="errors.route_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                        <option value="">Select Route</option>
                        <template x-for="route in routes" :key="route.id">
                            <option :value="route.id" x-text="route.route_name"></option>
                        </template>
                    </select>
                    <template x-if="errors.route_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.route_id[0]"></p>
                    </template>
                </div>

                <!-- Month Select -->
                <div>
                    <label for="month" class="block text-[10px] font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest leading-none">
                        Audit Month <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <input type="month" 
                               id="month"
                               x-model="formData.month"
                               @input="delete errors.month"
                               value="{{ old('month', $selectedMonth) }}"
                               class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                               :class="errors.month ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-300 group-hover:text-teal-500 transition-colors"></i>
                        </div>
                    </div>
                    <template x-if="errors.month">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.month[0]"></p>
                    </template>
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button @click="searchReport()" 
                            class="w-full px-8 py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-black rounded-xl transition-all shadow-lg shadow-teal-100/50 hover:shadow-teal-200/50 flex items-center justify-center gap-2 uppercase text-xs tracking-widest">
                        <i class="fas fa-chart-bar"></i>
                        Generate Audit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Display -->
    @if($selectedVehicle && $selectedRoute)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Vehicle Info Card -->
            <div class="relative group overflow-hidden bg-blue-50/50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-100/50">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <i class="fas fa-bus text-5xl text-blue-600"></i>
                </div>
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-300 uppercase tracking-widest mb-1">Fleet Identity</p>
                <p class="text-xl font-black text-gray-800 dark:text-blue-100">{{ $selectedVehicle->vehicle_no }}</p>
                <p class="text-[10px] text-blue-500 font-bold mt-1 uppercase tracking-tighter">{{ $selectedVehicle->registration_no }}</p>
            </div>

            <!-- Route Info Card -->
            <div class="relative group overflow-hidden bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl p-6 border border-emerald-100/50">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <i class="fas fa-route text-5xl text-emerald-600"></i>
                </div>
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-300 uppercase tracking-widest mb-1">Transit Channel</p>
                <p class="text-xl font-black text-gray-800 dark:text-emerald-100">{{ $selectedRoute->route_name }}</p>
                <p class="text-[10px] text-emerald-500 font-bold mt-1 uppercase tracking-tighter">Active Manifest</p>
            </div>

            <!-- Month Info Card -->
            <div class="relative group overflow-hidden bg-purple-50/50 dark:bg-purple-900/20 rounded-2xl p-6 border border-purple-100/50">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-check text-5xl text-purple-600"></i>
                </div>
                <p class="text-[10px] font-black text-purple-600 dark:text-purple-300 uppercase tracking-widest mb-1">Temporal Context</p>
                <p class="text-xl font-black text-gray-800 dark:text-purple-100">
                    {{ $selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('M Y') : '' }}
                </p>
                <p class="text-[10px] text-purple-500 font-bold mt-1 uppercase tracking-tighter">Billing Period</p>
            </div>

            <!-- Legend Card -->
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest mb-4">Code Legend</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-5 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-md font-black text-[9px]">PBS</span>
                        <span class="text-[9px] text-gray-500 font-bold uppercase">Pickup</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-5 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-md font-black text-[9px]">DSC</span>
                        <span class="text-[9px] text-gray-500 font-bold uppercase">Drop (S)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-5 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-md font-black text-[9px]">PSC</span>
                        <span class="text-[9px] text-gray-500 font-bold uppercase">Pickup (S)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-5 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-md font-black text-[9px]">DBS</span>
                        <span class="text-[9px] text-gray-500 font-bold uppercase">Drop</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Attendance Table -->
    @if($selectedVehicle && $selectedRoute && count($students) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-white sticky left-0">
            <div>
                <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">Consolidated Audit Trail</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manifest tracking for <span class="font-bold text-teal-600">{{ count($students) }}</span> pupils</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Cycle Duration</span>
                <span class="px-4 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-200 rounded-full text-xs font-black uppercase tracking-widest border border-teal-100/50">
                    @php
                        $year = (int)substr($selectedMonth, 0, 4);
                        $monthNum = (int)substr($selectedMonth, 5, 2);
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
                    @endphp
                    {{ $daysInMonth }} Days
                </span>
            </div>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50/50 dark:bg-gray-700">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest sticky left-0 bg-gray-50 z-20 border-r border-gray-100">
                            Admission Registry
                        </th>
                        @php
                            if (!isset($daysInMonth)) {
                                $year = (int)substr($selectedMonth, 0, 4);
                                $monthNum = (int)substr($selectedMonth, 5, 2);
                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
                            }
                        @endphp
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <th class="px-3 py-5 text-center text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest min-w-[65px] border-l border-gray-50">
                                <span class="block text-gray-400 opacity-50 mb-0.5">D-{{ $day }}</span>
                                <span class="block text-gray-800 text-[11px] font-black">
                                    {{ \Carbon\Carbon::createFromDate($year, $monthNum, $day)->format('D') }}
                                </span>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($students as $student)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-8 py-5 sticky left-0 bg-white group-hover:bg-gray-50 transition-colors z-10 border-r border-gray-100">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-900 rounded-xl flex items-center justify-center border border-teal-100/50 shadow-sm">
                                        <span class="text-teal-600 dark:text-teal-200 font-black text-xs">{{ substr($student['admission_no'], -2) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-gray-800 dark:text-white">{{ $student['admission_no'] }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold truncate max-w-[120px]">{{ $student['name'] }}</p>
                                    </div>
                                </div>
                            </td>
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                <td class="px-3 py-5 text-center align-middle border-l border-gray-50 group-hover:bg-gray-50/50 transition-colors">
                                    @if(isset($student['days'][$day]) && count($student['days'][$day]) > 0)
                                        <div class="flex flex-col gap-1.5 items-center justify-center">
                                            @foreach($student['days'][$day] as $code)
                                                <span class="inline-flex items-center justify-center w-9 h-6 bg-teal-500/10 text-teal-600 dark:bg-teal-900/50 dark:text-teal-200 rounded-lg font-black text-[9px] uppercase tracking-tighter border border-teal-500/20 shadow-sm">
                                                    {{ $code }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-200 dark:text-gray-600 font-black">-</span>
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
