@extends('layouts.school')

@section('title', 'Transport Attendance Report - School Admin')
@section('page-title', 'Monthly Attendance Report')
@section('page-description', 'View transport attendance by vehicle, route and month')

@section('content')
<div class="space-y-6" x-data="transportAttendanceReport()" x-init="init()">

    {{-- Page Header --}}
    <x-page-header title="Monthly Attendance Report" description="Select a vehicle, route and month to view the attendance calendar" icon="fas fa-calendar-alt">
        <a href="{{ route('school.transport.transport_attendance.index') }}"
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
                <select id="vehicle_id" x-model="formData.vehicle_id" @change="loadRoutes(true)"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none"
                    :class="errors.vehicle_id ? 'border-red-400' : ''">
                    <option value="">— Select Vehicle —</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $selectedVehicle?->id) == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                        </option>
                    @endforeach
                </select>
                <template x-if="errors.vehicle_id"><p class="text-[10px] text-red-500 font-bold mt-1" x-text="errors.vehicle_id[0]"></p></template>
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
                </select>
                <template x-if="errors.route_id"><p class="text-[10px] text-red-500 font-bold mt-1" x-text="errors.route_id[0]"></p></template>
            </div>

            {{-- Month --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calendar mr-1 text-teal-500"></i> Month <span class="text-red-500">*</span>
                </label>
                <input type="month" x-model="formData.month"
                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none"
                    :class="errors.month ? 'border-red-400' : ''">
                <template x-if="errors.month"><p class="text-[10px] text-red-500 font-bold mt-1" x-text="errors.month[0]"></p></template>
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
        <x-stat-card label="Vehicle" :value="$selectedVehicle->vehicle_no" icon="fas fa-bus" color="blue" :description="$selectedVehicle->registration_no" />
        <x-stat-card label="Route" :value="$selectedRoute->route_name" icon="fas fa-route" color="emerald" description="Active" />
        <x-stat-card label="Period" :value="\Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y')" icon="fas fa-calendar-check" color="purple" :description="count($students) . ' students'" />
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
                    Boarded (Morning/Afternoon)
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
                                            <span class="inline-flex items-center justify-center w-9 h-5 rounded text-[8px] font-bold uppercase tracking-tight bg-teal-500 text-white">
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

        async init() {
            if (this.formData.vehicle_id) {
                await this.loadRoutes(false);
            }
        },

        async loadRoutes(clearRoute = false) {
            if (clearRoute) this.formData.route_id = '';
            if (!this.formData.vehicle_id) {
                this.routes = [];
                this.updateRouteOptions();
                return;
            }

            try {
                const res = await fetch('{{ route('school.transport.transport_attendance.get_routes') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ vehicle_id: this.formData.vehicle_id }),
                });
                const data = await res.json();
                if (data.success) { 
                    this.routes = data.routes; 
                    this.updateRouteOptions(); 
                }
            } catch (e) { console.error('Route load failed', e); }
        },

        updateRouteOptions() {
            const sel = document.getElementById('route_id');
            if (!sel) return;
            while (sel.options.length > 1) sel.remove(1);
            this.routes.forEach(r => {
                const o = document.createElement('option');
                o.value = r.id; o.textContent = r.route_name;
                if (r.id == this.formData.route_id) o.selected = true;
                sel.appendChild(o);
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
            window.location.href = '{{ route('school.transport.transport_attendance.month_wise_report') }}?' + params.toString();
        },
    };
}
</script>
@endpush
@endsection
