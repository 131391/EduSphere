@extends('layouts.receptionist')

@section('title', 'Transport Attendance Report - Receptionist')
@section('page-title', 'Monthly Attendance Report')
@section('page-description', 'View transport attendance by vehicle, route and month')

@section('content')
<div class="space-y-6" x-data="transportAttendanceReport()" x-init="init()">

    {{-- Page Header --}}
    <x-page-header title="Monthly Attendance Report" description="Select a vehicle, route and month to view the attendance calendar" icon="fas fa-calendar-alt">
        <a href="{{ route('receptionist.transport-attendance.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-xs"></i>
            Back to Attendance
        </a>
    </x-page-header>

    {{-- ── Filter Bar ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

            {{-- Vehicle --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-bus mr-1 text-teal-500"></i> Vehicle <span class="text-red-500">*</span>
                </label>
                <select id="vehicle_id" x-model="formData.vehicle_id"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none"
                    :class="errors.vehicle_id ? 'border-red-400' : ''">
                    <option value="">— Select Vehicle —</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $selectedVehicle?->id) == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                        </option>
                    @endforeach
                </select>
                <p x-show="errors.vehicle_id" x-text="errors.vehicle_id?.[0]" class="text-xs text-red-500 font-medium" x-cloak></p>
            </div>

            {{-- Route --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-route mr-1 text-teal-500"></i> Route <span class="text-red-500">*</span>
                </label>
                <select id="route_id" x-model="formData.route_id"
                    :disabled="!formData.vehicle_id"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none disabled:opacity-50"
                    :class="errors.route_id ? 'border-red-400' : ''">
                    <option value="">— Select Route —</option>
                    <template x-for="route in routes" :key="route.id">
                        <option :value="route.id" x-text="route.route_name"></option>
                    </template>
                </select>
                <p x-show="errors.route_id" x-text="errors.route_id?.[0]" class="text-xs text-red-500 font-medium" x-cloak></p>
            </div>

            {{-- Month --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calendar mr-1 text-teal-500"></i> Month <span class="text-red-500">*</span>
                </label>
                <input type="month" x-model="formData.month" @input="delete errors.month"
                    value="{{ old('month', $selectedMonth) }}"
                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none"
                    :class="errors.month ? 'border-red-400' : ''">
                <p x-show="errors.month" x-text="errors.month?.[0]" class="text-xs text-red-500 font-medium" x-cloak></p>
            </div>

            {{-- Search --}}
            <div>
                <button @click="searchReport()"
                    class="w-full h-11 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-search text-xs"></i>
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards (shown when results exist) ── --}}
    @if($selectedVehicle && $selectedRoute)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shrink-0">
                <i class="fas fa-bus text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 mb-0.5">Vehicle</p>
                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $selectedVehicle->vehicle_no }}</p>
                <p class="text-[11px] text-blue-500 font-medium">{{ $selectedVehicle->registration_no }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shrink-0">
                <i class="fas fa-route text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 mb-0.5">Route</p>
                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $selectedRoute->route_name }}</p>
                <p class="text-[11px] text-emerald-500 font-medium">Active</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 border border-purple-100 shrink-0">
                <i class="fas fa-calendar-check text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 mb-0.5">Period</p>
                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</p>
                <p class="text-[11px] text-purple-500 font-medium">{{ count($students) }} students</p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Attendance Calendar Table ── --}}
    @if($selectedVehicle && $selectedRoute && count($students) > 0)
    @php
        $year       = (int) substr($selectedMonth, 0, 4);
        $monthNum   = (int) substr($selectedMonth, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

        {{-- Table header --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-800/60">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Attendance Calendar</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ count($students) }} students &nbsp;·&nbsp; {{ $daysInMonth }} days
                </p>
            </div>
            {{-- Legend --}}
            <div class="hidden sm:flex items-center gap-4 text-xs font-semibold text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded bg-teal-500 flex items-center justify-center text-white text-[9px] font-bold">P</span>
                    Pickup (Before School)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded bg-indigo-500 flex items-center justify-center text-white text-[9px] font-bold">D</span>
                    Drop (After School)
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-xs">
                <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0 z-10">
                    <tr>
                        {{-- Sticky student column --}}
                        <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-700/50 px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 min-w-[180px]">
                            Student
                        </th>
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date      = \Carbon\Carbon::createFromDate($year, $monthNum, $day);
                                $isWeekend = $date->isWeekend();
                            @endphp
                            <th class="px-2 py-3 text-center min-w-[52px] border-l border-gray-100 dark:border-gray-700 {{ $isWeekend ? 'bg-gray-100/60 dark:bg-gray-700/80' : '' }}">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase">{{ $date->format('D') }}</span>
                                <span class="block text-[11px] font-bold {{ $isWeekend ? 'text-gray-400' : 'text-gray-700 dark:text-gray-200' }}">{{ $day }}</span>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($students as $student)
                    <tr class="hover:bg-teal-50/20 dark:hover:bg-teal-900/10 transition-colors group">
                        {{-- Sticky student cell --}}
                        <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 group-hover:bg-teal-50/20 dark:group-hover:bg-teal-900/10 px-5 py-3 border-r border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-500 dark:text-gray-400 shrink-0">
                                    {{ strtoupper(substr($student['name'], 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 dark:text-gray-100 leading-tight">{{ $student['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ $student['admission_no'] }}</div>
                                </div>
                            </div>
                        </td>

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $isWeekend = \Carbon\Carbon::createFromDate($year, $monthNum, $day)->isWeekend();
                                $dayCodes  = $student['days'][$day] ?? [];
                            @endphp
                            <td class="px-1.5 py-3 text-center align-middle border-l border-gray-50 dark:border-gray-700/50 {{ $isWeekend ? 'bg-gray-50/60 dark:bg-gray-700/30' : '' }}">
                                @if(count($dayCodes) > 0)
                                    <div class="flex flex-col gap-1 items-center">
                                        @foreach($dayCodes as $code)
                                            @php
                                                $isTeal = in_array($code, ['PBS', 'DSC']);
                                            @endphp
                                            <span class="inline-flex items-center justify-center w-9 h-5 rounded text-[8px] font-bold uppercase tracking-tight {{ $isTeal ? 'bg-teal-500 text-white' : 'bg-indigo-500 text-white' }}">
                                                {{ $code }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-200 dark:text-gray-600 font-bold">—</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend (mobile) --}}
        <div class="sm:hidden px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60 flex flex-wrap gap-3 text-xs font-semibold text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="w-5 h-5 rounded bg-teal-500 flex items-center justify-center text-white text-[9px] font-bold">PBS</span>
                Pickup Before School
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-5 h-5 rounded bg-teal-500 flex items-center justify-center text-white text-[9px] font-bold">DSC</span>
                Drop After School
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-5 h-5 rounded bg-indigo-500 flex items-center justify-center text-white text-[9px] font-bold">PSC</span>
                Pickup After School
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-5 h-5 rounded bg-indigo-500 flex items-center justify-center text-white text-[9px] font-bold">DBS</span>
                Drop Before School
            </span>
        </div>
    </div>

    {{-- No records for selected filters --}}
    @elseif($selectedVehicle && $selectedRoute && count($students) == 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 py-20 text-center">
        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100 dark:border-gray-600">
            <i class="fas fa-calendar-times text-2xl text-gray-300"></i>
        </div>
        <h3 class="text-base font-bold text-gray-700 dark:text-white mb-1">No Records Found</h3>
        <p class="text-sm text-gray-400 max-w-xs mx-auto">
            No attendance records found for this route and month. Try a different month or route.
        </p>
    </div>

    {{-- Initial state --}}
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 py-20 text-center">
        <div class="w-16 h-16 bg-teal-50 dark:bg-teal-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-teal-100 dark:border-teal-800/40">
            <i class="fas fa-calendar-alt text-2xl text-teal-300"></i>
        </div>
        <h3 class="text-base font-bold text-gray-700 dark:text-white mb-1">Select Filters Above</h3>
        <p class="text-sm text-gray-400 max-w-xs mx-auto">
            Choose a vehicle, route and month, then click Generate Report.
        </p>
    </div>
    @endif

</div>

@push('scripts')
<script>
function transportAttendanceReport() {
    return {
        formData: {
            vehicle_id: '{{ old('vehicle_id', $selectedVehicle?->id ?? '') }}',
            route_id:   '{{ old('route_id',   $selectedRoute?->id   ?? '') }}',
            month:      '{{ old('month',       $selectedMonth) }}',
        },
        routes: [],
        errors: {},

        init() {
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#vehicle_id').on('change', (e) => {
                        this.formData.vehicle_id = e.target.value;
                        delete this.errors.vehicle_id;
                        this.loadRoutes(true);
                    });
                    $('#route_id').on('change', (e) => {
                        this.formData.route_id = e.target.value;
                        delete this.errors.route_id;
                    });
                }
                if (this.formData.vehicle_id) this.loadRoutes(false);
            });
        },

        async loadRoutes(clearRoute = false) {
            this.routes = [];
            if (clearRoute) this.formData.route_id = '';
            this.updateRouteOptions();
            if (!this.formData.vehicle_id) return;

            try {
                const res = await fetch('{{ route('receptionist.transport-attendance.get-routes-for-report') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ vehicle_id: this.formData.vehicle_id }),
                });
                const data = await res.json();
                if (res.ok && data.success) { this.routes = data.routes; this.updateRouteOptions(); }
            } catch (e) { console.error('Route load failed', e); }
        },

        updateRouteOptions() {
            this.$nextTick(() => {
                const sel = document.getElementById('route_id');
                if (!sel) return;
                while (sel.options.length > 1) sel.remove(1);
                this.routes.forEach(r => {
                    const o = document.createElement('option');
                    o.value = r.id; o.textContent = r.route_name;
                    sel.appendChild(o);
                });
                if (typeof $ !== 'undefined') $(sel).val(this.formData.route_id).trigger('change');
            });
        },

        searchReport() {
            this.errors = {};
            if (!this.formData.vehicle_id) this.errors.vehicle_id = ['Please select a vehicle.'];
            if (!this.formData.route_id)   this.errors.route_id   = ['Please select a route.'];
            if (!this.formData.month)      this.errors.month      = ['Please select a month.'];
            if (Object.keys(this.errors).length) return;

            const params = new URLSearchParams({
                vehicle_id: this.formData.vehicle_id,
                route_id:   this.formData.route_id,
                month:      this.formData.month,
            });
            window.location.href = '{{ route('receptionist.transport-attendance.month-wise-report') }}?' + params.toString();
        },
    };
}
</script>
@endpush
@endsection
