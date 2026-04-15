@extends('layouts.receptionist')

@section('title', 'Transport Boarding Registry')

@section('content')
<div class="space-y-6" x-data="transportAttendanceManagement()" x-init="init()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-shuttle-van text-xs"></i>
                    </div>
                    Boarding Verification Registry
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time boarding verification and manifest synchronization for fleet operations.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('receptionist.transport-attendance.month-wise-report') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <i class="fas fa-file-invoice mr-2 opacity-50"></i>
                    Monthly Audit
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-id-badge text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Global Boarders</p>
                <p class="text-xl font-black text-gray-800">{{ number_format($stats['total_students']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-bus-alt text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Boarded Today</p>
                <p class="text-xl font-black text-emerald-600">{{ number_format($stats['boarded_today']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-user-minus text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Absent Today</p>
                <p class="text-xl font-black text-rose-600">{{ number_format($stats['absent_today']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-route text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Fleet Assigned</p>
                <p class="text-xl font-black text-amber-600">{{ $vehicles->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden shadow-teal-100/20 shadow-xl">
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Vehicle Selection -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Fleet Asset</label>
                    <div class="relative group">
                        <select id="vehicle_id" x-model="formData.vehicle_id" @change="loadRoutes()"
                                class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500">
                            <i class="fas fa-bus text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Route Selection -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Transit Route</label>
                    <div class="relative group">
                        <select id="route_id" x-model="formData.route_id" @change="loadStudents()"
                                class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">Select Route</option>
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500">
                            <i class="fas fa-route text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Stage Selection -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Operational Stage</label>
                    <div class="relative group">
                        <select x-model="formData.attendance_type" @change="loadStudents()"
                                class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">Select Stage</option>
                            @foreach($attendanceTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500">
                            <i class="fas fa-toggle-on text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Session Date -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Verification Date</label>
                    <div class="relative group">
                        <input type="date" x-model="formData.attendance_date" max="{{ date('Y-m-d') }}"
                               class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none shadow-sm">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500">
                            <i class="fas fa-calendar-alt text-[10px]"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boarding Manifest Section (Students List) -->
        <div x-show="students.length > 0" x-collapse>
            <div class="px-8 py-5 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white dark:bg-gray-800 border border-teal-100 flex items-center justify-center text-teal-500 shadow-sm">
                        <i class="fas fa-id-card text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">Boarding Manifest</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider"><span x-text="students.length"></span> Students Identified</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 p-1 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-inner">
                    <button type="button" @click="checkAll()"
                        class="px-5 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white text-[10px] font-black rounded-xl transition-all uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-check-double text-[8px]"></i>
                        Verify All
                    </button>
                    <button type="button" @click="uncheckAll()"
                        class="px-5 py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white text-[10px] font-black rounded-xl transition-all uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-times text-[8px]"></i>
                        Reset Clear
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto text-sm">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/30 dark:bg-gray-700/10">
                            <th class="text-left px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Boarder Identity</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Academic Node</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Station Stop</th>
                            <th class="text-right px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Boarding Auth</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="(student, index) in students" :key="student.id">
                            <tr class="hover:bg-teal-50/30 dark:hover:bg-teal-900/10 transition-colors group">
                                <td class="px-8 py-4">
                                    <span class="text-xs font-black text-gray-300 group-hover:text-teal-400 transition-colors">#<span x-text="String(index + 1).padStart(2, '0')"></span></span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 font-bold border border-gray-200 dark:border-gray-600 uppercase text-[10px]" x-text="student.name.split(' ').map(n => n[0]).join('')"></div>
                                        <div>
                                            <div class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight" x-text="student.name"></div>
                                            <div class="text-[10px] font-bold text-gray-400" x-text="`Adm: ${student.admission_no}`"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-3 py-1.5 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-tight" x-text="`${student.class} - ${student.section}`"></span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-pin text-gray-300 text-[10px]"></i>
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase" x-text="student.bus_stop_name"></span>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <label class="inline-flex items-center cursor-pointer group/toggle">
                                        <input type="checkbox" :value="student.id.toString()" x-model="checkedStudents" class="sr-only peer">
                                        <div class="w-24 h-9 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 relative transition-all duration-300 peer-checked:bg-emerald-500 peer-checked:border-emerald-600">
                                            <div class="absolute inset-y-1 left-1 w-7 h-7 bg-white dark:bg-gray-700 rounded-lg shadow-sm transition-all duration-300 peer-checked:left-16 flex items-center justify-center text-[10px]">
                                                <i class="fas fa-bus-alt text-teal-500 peer-checked:text-emerald-500"></i>
                                            </div>
                                            <div class="flex justify-between items-center h-full px-2 text-[8px] font-black uppercase tracking-widest">
                                                <span class="text-emerald-500">Mark</span>
                                                <span class="text-gray-400">Off</span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Sync Control Footer -->
            <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-teal-100 dark:border-gray-700 flex items-center justify-center text-teal-500 shadow-sm">
                        <i class="fas fa-shield-alt text-[10px]"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Manifest Integrity Secured</p>
                </div>
                
                <button type="button" @click="save" :disabled="!canSubmit || submitting"
                        class="min-w-[280px] h-14 px-8 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all duration-300 shadow-xl disabled:opacity-50 disabled:grayscale flex items-center justify-center gap-4 ring-4 ring-gray-100 dark:ring-gray-800/50 group">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <i class="fas fa-cloud-upload-alt text-sm group-hover:scale-110 transition-transform" x-show="!submitting"></i>
                    <span x-text="submitting ? 'Propagating Manifest...' : 'Synchronize Registry State'"></span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="students.length === 0" 
            class="p-24 text-center bg-gray-50/20 rounded-b-3xl border-t border-gray-100 dark:border-gray-700">
            <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-3xl shadow-sm flex items-center justify-center mx-auto mb-6 border border-gray-100 dark:border-gray-700">
                <i class="fas fa-shuttle-van text-4xl text-gray-200"></i>
            </div>
            <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Fleet Manifest Empty</h3>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest max-w-sm mx-auto mt-2 leading-relaxed">
                Select a specific fleet asset and transit corridor from the verification parameters above to initiate boarding protocols.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function transportAttendanceManagement() {
        return {
            formData: {
                vehicle_id: '',
                route_id: '',
                attendance_type: '',
                attendance_date: '{{ date('Y-m-d') }}',
            },
            routes: [],
            students: [],
            checkedStudents: [],
            submitting: false,
            errors: {},

            get canSubmit() {
                return !!(this.formData.vehicle_id && this.formData.route_id && this.formData.attendance_type && this.formData.attendance_date && this.students.length > 0);
            },

            clearError(field) {
                if (this.errors[field]) {
                    delete this.errors[field];
                }
            },

            async init() {
                this.$nextTick(() => {
                    if (typeof $ !== 'undefined') {
                        // Vehicle Select
                        $('#vehicle_id').on('change', (e) => {
                            this.formData.vehicle_id = e.target.value;
                            this.clearError('vehicle_id');
                            this.loadRoutes();
                        });

                        // Route Select
                        $('#route_id').on('change', (e) => {
                            this.formData.route_id = e.target.value;
                            this.clearError('route_id');
                            this.loadStudents();
                        });
                    }
                });
            },

            async loadRoutes() {
                if (!this.formData.vehicle_id) {
                    this.routes = [];
                    this.formData.route_id = '';
                    this.updateRouteOptions();
                    return;
                }

                try {
                    const response = await fetch('{{ route('receptionist.transport-attendance.get-routes') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ vehicle_id: this.formData.vehicle_id })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.routes = data.routes;
                        this.updateRouteOptions();
                    }
                } catch (error) {
                    console.error('Route load failed', error);
                }
            },

            updateRouteOptions() {
                if (typeof $ === 'undefined') return;
                const $select = $('#route_id');
                $select.empty().append('<option value="">Select Route</option>');
                this.routes.forEach(route => {
                    $select.append(`<option value="${route.id}">${route.route_name}</option>`);
                });
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change');
                }
            },

            async loadStudents() {
                if (!this.formData.vehicle_id || !this.formData.route_id) {
                    this.students = [];
                    this.checkedStudents = [];
                    return;
                }

                try {
                    const response = await fetch('{{ route('receptionist.transport-attendance.get-students') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            vehicle_id: this.formData.vehicle_id,
                            route_id: this.formData.route_id,
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.students = data.students;
                        this.checkedStudents = this.students.map(s => s.id.toString());
                    }
                } catch (error) {
                    console.error('Student load failed', error);
                }
            },

            async save() {
                if (!this.canSubmit || this.submitting) return;

                this.submitting = true;
                this.errors = {};

                try {
                    const response = await fetch('{{ route('receptionist.transport-attendance.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ...this.formData,
                            students: this.students.map(s => s.id),
                            checked_students: this.checkedStudents
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: 'success',
                                title: result.message || 'Manifest Synchronized'
                            });
                        }
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } catch (error) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'error', title: 'Transmission failure encountered' });
                    }
                } finally {
                    this.submitting = false;
                }
            },

            checkAll() {
                this.checkedStudents = this.students.map(s => s.id.toString());
            },

            uncheckAll() {
                this.checkedStudents = [];
            },
        };
    }
</script>
@endpush