@extends('layouts.receptionist')

@section('title', 'Transport Attendance Month Wise Report')

@push('styles')
<style>
    /* Custom month input styling to match Select2 */
    input[type="month"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none;
        height: 42px;
    }
    
    /* Hide default calendar picker icon */
    input[type="month"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 1;
    }
    
    /* Hover effect */
    input[type="month"]:hover {
        border-color: #14b8a6;
    }
    
    /* Focus effect matching Select2 */
    input[type="month"]:focus {
        outline: none;
        border-color: #14b8a6;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }
    
    /* Dark mode support */
    .dark input[type="month"] {
        color-scheme: dark;
    }
    
    /* Ensure icons don't interfere with click */
    .relative input[type="month"] + div {
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="transportAttendanceReport()" x-init="init()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('receptionist.transport-attendance.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Transport Attendance Month Wise</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View monthly attendance reports for transport routes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">Filter Options</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Select vehicle, route, and month to generate the report</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Vehicle Select -->
            <div>
                <label for="vehicle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Vehicle <span class="text-red-500">*</span>
                </label>
                <select id="vehicle_id" 
                        x-model="formData.vehicle_id"
                        @change="loadRoutes()"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 shadow-sm">
                    <option value="">Select Vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $selectedVehicle?->id) == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicle_no }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Route Select -->
            <div>
                <label for="route_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Route <span class="text-red-500">*</span>
                </label>
                <select id="route_id" 
                        x-model="formData.route_id"
                        x-ref="routeSelect"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 shadow-sm">
                    <option value="">Select Route</option>
                    <template x-for="route in routes" :key="route.id">
                        <option :value="route.id" x-text="route.route_name"></option>
                    </template>
                </select>
                @error('route_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Month Select -->
            <div>
                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Month <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="month" 
                           id="month"
                           x-model="formData.month"
                           value="{{ old('month', $selectedMonth) }}"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-teal-500 focus:ring-teal-500 shadow-sm pl-10 pr-4 py-2.5 appearance-none cursor-pointer">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-alt text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs"></i>
                    </div>
                </div>
                @error('month')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button @click="searchReport()" 
                        class="w-full inline-flex items-center justify-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
            </div>
        </div>
    </div>

    <!-- Information Display -->
    @if($selectedVehicle && $selectedRoute)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Vehicle Info Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wide mb-1">Vehicle No</p>
                        <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $selectedVehicle->vehicle_no }}</p>
                    </div>
                    <div class="bg-blue-200 dark:bg-blue-700 p-3 rounded-full">
                        <i class="fas fa-bus text-blue-600 dark:text-blue-300 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Route Info Card -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-600 dark:text-green-300 uppercase tracking-wide mb-1">Route Name</p>
                        <p class="text-lg font-bold text-green-900 dark:text-green-100">{{ $selectedRoute->route_name }}</p>
                    </div>
                    <div class="bg-green-200 dark:bg-green-700 p-3 rounded-full">
                        <i class="fas fa-route text-green-600 dark:text-green-300 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Month Info Card -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-lg p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-purple-600 dark:text-purple-300 uppercase tracking-wide mb-1">Month</p>
                        <p class="text-lg font-bold text-purple-900 dark:text-purple-100">
                            {{ $selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') : '' }}
                        </p>
                    </div>
                    <div class="bg-purple-200 dark:bg-purple-700 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-300 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Legend Card -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4 border-l-4 border-gray-400">
                <div class="mb-3">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Legend</p>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-8 h-6 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded font-bold text-xs">PBS</span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Pickup (Bus Stop)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-8 h-6 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded font-bold text-xs">DSC</span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Drop (School Campus)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-8 h-6 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded font-bold text-xs">PSC</span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Pickup (School Campus)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-8 h-6 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded font-bold text-xs">DBS</span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Drop (Bus Stop)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Attendance Table -->
    @if($selectedVehicle && $selectedRoute && count($students) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Attendance Report</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ count($students) }} student(s) found</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Total Days:</span>
                    <span class="px-3 py-1 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-full text-sm font-semibold">
                        @php
                            $year = (int)substr($selectedMonth, 0, 4);
                            $monthNum = (int)substr($selectedMonth, 5, 2);
                            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
                        @endphp
                        {{ $daysInMonth }}
                    </span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-700 z-10 border-r-2 border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-id-card text-gray-400"></i>
                                <span>Admission No</span>
                            </div>
                        </th>
                        @php
                            if (!isset($daysInMonth)) {
                                $year = (int)substr($selectedMonth, 0, 4);
                                $monthNum = (int)substr($selectedMonth, 5, 2);
                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
                            }
                        @endphp
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <th class="px-2 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider min-w-[60px]">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs">D-{{ $day }}</span>
                                    <span class="text-[10px] text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::createFromDate($year, $monthNum, $day)->format('D') }}
                                    </span>
                                </div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($students as $student)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-800 z-10 border-r-2 border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center">
                                        <span class="text-teal-800 dark:text-teal-200 font-bold text-xs">{{ substr($student['admission_no'], -2) }}</span>
                                    </div>
                                    <span>{{ $student['admission_no'] }}</span>
                                </div>
                            </td>
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                <td class="px-2 py-4 text-center">
                                    @if(isset($student['days'][$day]) && count($student['days'][$day]) > 0)
                                        <div class="flex flex-col space-y-1 items-center">
                                            @foreach($student['days'][$day] as $code)
                                                <span class="inline-flex items-center justify-center px-2 py-1 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-md font-bold text-xs shadow-sm min-w-[40px]">
                                                    {{ $code }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">-</span>
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">No Students Found</h3>
            <p class="text-gray-500 dark:text-gray-400">No students are assigned to the selected route for the chosen month.</p>
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="w-20 h-20 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chart-line text-teal-600 dark:text-teal-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Select Filters to Generate Report</h3>
            <p class="text-gray-500 dark:text-gray-400">Please select a vehicle, route, and month to view the attendance report.</p>
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

        init() {
            // Initialize Select2 for vehicle and route
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        $('#vehicle_id').select2({
                            placeholder: 'Select Vehicle',
                            allowClear: true,
                            width: '100%'
                        });

                        $('#route_id').select2({
                            placeholder: 'Select Route',
                            allowClear: true,
                            width: '100%'
                        });

                        // Handle vehicle change
                        $('#vehicle_id').on('select2:select select2:change', (e) => {
                            this.formData.vehicle_id = e.target.value || $('#vehicle_id').val();
                            // Clear route when vehicle changes
                            this.loadRoutes(true);
                        });

                        // Handle route change
                        $('#route_id').on('select2:select select2:change', (e) => {
                            this.formData.route_id = e.target.value || $('#route_id').val();
                        });
                    }
                    
                    // Enhance month input styling and behavior
                    const monthInput = document.getElementById('month');
                    if (monthInput) {
                        // Ensure proper height matching Select2
                        monthInput.style.height = '42px';
                        
                        // Sync with Alpine.js on change
                        monthInput.addEventListener('change', (e) => {
                            this.formData.month = e.target.value;
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

                if (!response.ok) {
                    throw new Error('Failed to load routes');
                }

                const data = await response.json();
                
                if (data.success && Array.isArray(data.routes)) {
                    this.routes = data.routes;
                    this.updateRouteOptions();
                    // Only clear route_id if explicitly requested (e.g., when vehicle changes)
                    if (clearRoute || !preserveRouteId) {
                        this.formData.route_id = '';
                    }
                } else {
                    this.routes = [];
                    this.updateRouteOptions();
                    if (clearRoute) {
                        this.formData.route_id = '';
                    }
                }
            } catch (error) {
                alert('Error loading routes: ' + error.message);
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
                        allowClear: true,
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
            if (!this.formData.vehicle_id || !this.formData.route_id || !this.formData.month) {
                alert('Please select Vehicle, Route, and Month');
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
