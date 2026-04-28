@extends('layouts.school')

@section('title', 'Transport Attendance - School Admin')
@section('page-title', 'Transport Attendance')
@section('page-description', 'Mark daily boarding attendance for transport students')

@section('content')
<div class="space-y-6" x-data="transportAttendanceManagement()" x-init="init()">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Students"  :value="$stats['total_students']"  icon="fas fa-users"      color="blue"   />
        <x-stat-card label="Boarded Today"   :value="$stats['boarded_today']"   icon="fas fa-bus"        color="emerald"/>
        <x-stat-card label="Absent Today"    :value="$stats['absent_today']"    icon="fas fa-user-minus" color="rose"   />
        <x-stat-card label="Active Vehicles" :value="$vehicles->count()"        icon="fas fa-route"      color="amber"  />
    </div>

    {{-- Page Header --}}
    <x-page-header title="Transport Attendance" description="Select a vehicle, route and type, then mark each student present or absent" icon="fas fa-bus">
        <a href="{{ route('school.transport_attendance.month_wise_report') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-calendar-alt mr-2 text-xs"></i>
            Monthly Report
        </a>
    </x-page-header>

    {{-- ── Filter Bar ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Vehicle --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-bus mr-1 text-teal-500"></i> Vehicle
                </label>
                <select id="vehicle_id" x-model="formData.vehicle_id" @change="loadRoutes()"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none">
                    <option value="">— Select Vehicle —</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Route --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-route mr-1 text-teal-500"></i> Route
                </label>
                <select id="route_id" x-model="formData.route_id" @change="loadStudents()"
                    :disabled="!formData.vehicle_id"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none disabled:opacity-50">
                    <option value="">— Select Route —</option>
                </select>
            </div>

            {{-- Attendance Type --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-exchange-alt mr-1 text-teal-500"></i> Type
                </label>
                <select x-model="formData.attendance_type" @change="loadStudents()"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none">
                    <option value="">— Select Type —</option>
                    @foreach($attendanceTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calendar-alt mr-1 text-teal-500"></i> Date
                </label>
                <input type="date" x-model="formData.attendance_date" max="{{ date('Y-m-d') }}"
                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none">
            </div>
        </div>
    </div>

    {{-- ── Student Attendance Panel ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden relative"
         :class="students.length > 0 ? '' : 'min-h-[360px]'">

        {{-- Loading overlay --}}
        <div x-show="loading" x-cloak
            class="absolute inset-0 bg-white/70 dark:bg-gray-800/70 z-10 flex items-center justify-center">
            <div class="flex flex-col items-center gap-2">
                <div class="w-8 h-8 border-4 border-teal-200 border-t-teal-600 rounded-full animate-spin"></div>
                <p class="text-sm text-gray-500 font-medium">Loading students...</p>
            </div>
        </div>

        {{-- ── Toolbar ── --}}
        <div x-show="students.length > 0" x-cloak
            class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

            {{-- Counts + progress --}}
            <div class="flex items-center gap-5">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Present: <span class="text-emerald-600 font-bold" x-text="presentCount"></span>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Absent: <span class="text-rose-500 font-bold" x-text="absentCount"></span>
                    </span>
                </div>
                <div class="hidden sm:flex items-center gap-2">
                    <div class="w-32 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                            :style="`width: ${students.length ? Math.round((presentCount / students.length) * 100) : 0}%`"></div>
                    </div>
                    <span class="text-xs font-bold text-gray-400"
                        x-text="`${students.length ? Math.round((presentCount / students.length) * 100) : 0}%`"></span>
                </div>
            </div>

            {{-- Bulk actions --}}
            <div class="flex items-center gap-2">
                <button type="button" @click="checkAll()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-semibold rounded-lg transition-all border border-emerald-100 hover:border-emerald-600">
                    <i class="fas fa-check-double text-[10px]"></i> All Present
                </button>
                <button type="button" @click="uncheckAll()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white text-xs font-semibold rounded-lg transition-all border border-rose-100 hover:border-rose-600">
                    <i class="fas fa-times text-[10px]"></i> All Absent
                </button>
            </div>
        </div>

        {{-- ── Student Cards Grid ── --}}
        <div x-show="students.length > 0" x-cloak class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="(student, index) in students" :key="student.id">
                    <div class="rounded-xl border transition-all duration-200 overflow-hidden"
                        :class="checkedStudents.includes(student.id.toString())
                            ? 'border-emerald-200 bg-emerald-50/50 dark:bg-emerald-900/10 dark:border-emerald-800/50'
                            : 'border-rose-200 bg-rose-50/40 dark:bg-rose-900/10 dark:border-rose-800/50'">

                        {{-- Card Header --}}
                        <div class="flex items-center justify-between px-4 pt-4 pb-3">
                            <div class="flex items-center gap-3 min-w-0">
                                {{-- Avatar --}}
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0 shadow-sm"
                                    :class="checkedStudents.includes(student.id.toString()) ? 'bg-emerald-500 text-white' : 'bg-rose-400 text-white'"
                                    x-text="student.name ? student.name.trim().split(/\s+/).filter(n => n.length).map(n => n[0].toUpperCase()).slice(0,2).join('') : '?'">
                                </div>
                                {{-- Name + Admission --}}
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="student.name"></div>
                                    <div class="text-[11px] text-gray-400 font-medium" x-text="student.admission_no"></div>
                                </div>
                            </div>

                            {{-- Toggle --}}
                            <label class="relative cursor-pointer shrink-0 ml-2">
                                <input type="checkbox"
                                    :value="student.id.toString()"
                                    x-model="checkedStudents"
                                    class="sr-only peer">
                                <div class="w-12 h-6 rounded-full transition-colors duration-200 peer-checked:bg-emerald-500 bg-rose-400 relative">
                                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                                        :class="checkedStudents.includes(student.id.toString()) ? 'translate-x-6' : 'translate-x-0'"></div>
                                </div>
                            </label>
                        </div>

                        {{-- Class + Bus Stop --}}
                        <div class="px-4 pb-3 flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-chalkboard text-[9px] text-gray-400"></i>
                                <span x-text="student.class + ' - ' + student.section"></span>
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-teal-600">
                                <i class="fas fa-map-pin text-[9px]"></i>
                                <span x-text="student.bus_stop_name"></span>
                            </span>
                        </div>

                        {{-- Status Badge --}}
                        <div class="px-4 pb-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                :class="checkedStudents.includes(student.id.toString())
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'">
                                <i class="fas" :class="checkedStudents.includes(student.id.toString()) ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span x-text="checkedStudents.includes(student.id.toString()) ? 'Present' : 'Absent'"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── Empty State ── --}}
        <div x-show="students.length === 0 && !loading" x-cloak
            class="flex flex-col items-center justify-center py-24 px-6 text-center">
            <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/20 rounded-2xl flex items-center justify-center mb-5 border border-teal-100 dark:border-teal-800/40">
                <i class="fas fa-bus text-3xl text-teal-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 dark:text-white mb-1">No Students Loaded</h3>
            <p class="text-sm text-gray-400 max-w-xs">
                Select a vehicle and route above to load the student list.
            </p>
        </div>

        {{-- ── Footer Save Bar ── --}}
        <div x-show="students.length > 0" x-cloak
            class="px-6 py-4 bg-gray-50/60 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">

            <p class="text-xs text-gray-400 font-medium">
                <i class="fas fa-info-circle mr-1 text-teal-400"></i>
                <span class="font-bold text-gray-600 dark:text-gray-300" x-text="formData.attendance_date"></span>
                &nbsp;—&nbsp;
                <span class="text-emerald-600 font-bold" x-text="presentCount"></span> present,
                <span class="text-rose-500 font-bold" x-text="absentCount"></span> absent
            </p>

            <button type="button" @click="save()" :disabled="!canSubmit || submitting"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md disabled:opacity-40 disabled:cursor-not-allowed">
                <template x-if="submitting">
                    <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                </template>
                <i class="fas fa-save" x-show="!submitting"></i>
                <span x-text="submitting ? 'Saving...' : 'Save Attendance'"></span>
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function transportAttendanceManagement() {
    return {
        loading: false,
        submitting: false,
        formData: {
            vehicle_id: '',
            route_id: '',
            attendance_type: '',
            attendance_date: '{{ date('Y-m-d') }}',
        },
        routes: [],
        students: [],
        checkedStudents: [],
        errors: {},

        get presentCount() { return this.checkedStudents.length; },
        get absentCount()  { return this.students.length - this.checkedStudents.length; },
        get canSubmit() {
            return !!(this.formData.vehicle_id && this.formData.route_id &&
                      this.formData.attendance_type && this.formData.attendance_date &&
                      this.students.length > 0);
        },

        async init() {
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#vehicle_id').on('change', (e) => {
                        this.formData.vehicle_id = e.target.value;
                        this.loadRoutes();
                    });
                    $('#route_id').on('change', (e) => {
                        this.formData.route_id = e.target.value;
                        this.loadStudents();
                    });
                }
            });
        },

        async loadRoutes() {
            this.routes = [];
            this.formData.route_id = '';
            this.students = [];
            this.checkedStudents = [];
            this.updateRouteOptions();

            if (!this.formData.vehicle_id) return;

            try {
                const res = await fetch('{{ route('school.transport_attendance.get_routes') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ vehicle_id: this.formData.vehicle_id })
                });
                const data = await res.json();
                if (data.success) { this.routes = data.routes; this.updateRouteOptions(); }
            } catch (e) { console.error('Route load failed', e); }
        },

        updateRouteOptions() {
            if (typeof $ === 'undefined') return;
            const $s = $('#route_id');
            $s.empty().append('<option value="">— Select Route —</option>');
            this.routes.forEach(r => $s.append(`<option value="${r.id}">${r.route_name}</option>`));
            if ($s.hasClass('select2-hidden-accessible')) $s.trigger('change');
        },

        async loadStudents() {
            this.students = [];
            this.checkedStudents = [];
            if (!this.formData.vehicle_id || !this.formData.route_id) return;

            this.loading = true;
            try {
                const res = await fetch('{{ route('school.transport_attendance.get_students') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ vehicle_id: this.formData.vehicle_id, route_id: this.formData.route_id })
                });
                const data = await res.json();
                if (data.success) {
                    this.students = data.students;
                    this.checkedStudents = this.students.map(s => s.id.toString());
                }
            } catch (e) { console.error('Student load failed', e); }
            finally { this.loading = false; }
        },

        checkAll()   { this.checkedStudents = this.students.map(s => s.id.toString()); },
        uncheckAll() { this.checkedStudents = []; },

        async save() {
            if (!this.canSubmit || this.submitting) return;
            this.submitting = true;

            try {
                const res = await fetch('{{ route('school.transport_attendance.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        ...this.formData,
                        students: this.students.map(s => s.id),
                        checked_students: this.checkedStudents
                    })
                });

                const result = await res.json().catch(() => ({ message: 'Invalid server response' }));

                if (res.ok && result.success !== false) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message || 'Attendance saved.' })
                            .then(() => { window.location.href = window.location.href; });
                    } else {
                        window.location.href = window.location.href;
                    }
                } else {
                    throw new Error(result.message || 'Failed to save attendance.');
                }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                else alert(e.message);
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
