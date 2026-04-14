@extends('layouts.receptionist')

@section('title', 'Student Attendance - Transport')

@section('content')
    <div class="space-y-6" x-data="transportAttendanceManagement()" x-init="init()">
        {{-- Page Header --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-3 rounded-2xl shadow-lg shadow-indigo-100">
                        <i class="fas fa-clipboard-check text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Boarding Verification</h2>
                        <p class="text-sm text-gray-500 font-medium">Verify student boarding for active transit corridors</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('receptionist.transport-assignments.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fas fa-arrow-left mr-2 text-indigo-500"></i>
                        Return to Registry
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance Configuration Form -->
        <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8 transition-all hover:shadow-md">
            <form @submit.prevent="save" method="POST" class="p-0 relative" novalidate>
                @csrf
                <div class="p-8 grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Vehicle Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Operational Fleet Asset <span class="text-red-500 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="vehicle_id" id="vehicle_id" x-model="formData.vehicle_id"
                                @change="clearError('vehicle_id'); loadRoutes()" x-ref="vehicleSelect"
                                class="modal-input-premium pl-10"
                                :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : ''">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">
                                        {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-bus text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.vehicle_id">
                            <p class="modal-error-message" x-text="errors.vehicle_id[0]"></p>
                        </template>
                    </div>

                    <!-- Route Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Assigned Transit Route <span class="text-red-500 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="route_id" id="route_id" x-model="formData.route_id"
                                @change="delete errors.route_id; loadStudents()" x-ref="routeSelect"
                                class="modal-input-premium pl-10"
                                :class="errors.route_id ? 'border-red-500 ring-red-500/10' : ''">
                                <option value="">Select Route</option>
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-blue-500 transition-colors">
                                <i class="fas fa-route text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.route_id">
                            <p class="modal-error-message" x-text="errors.route_id[0]"></p>
                        </template>
                    </div>

                    <!-- Attendance Type Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Operational Stage <span class="text-red-500 font-bold">*</span></label>
                        <select name="attendance_type" id="attendance_type" x-model="formData.attendance_type"
                            @change="delete errors.attendance_type"
                            class="modal-input-premium"
                            :class="errors.attendance_type ? 'border-red-500 ring-red-500/10' : ''">
                            <option value="">Select Stage</option>
                            @foreach($attendanceTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.attendance_type">
                            <p class="modal-error-message" x-text="errors.attendance_type[0]"></p>
                        </template>
                    </div>

                    <!-- Attendance Date -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Verification Session Date <span class="text-red-500 font-bold">*</span></label>
                        <input type="date" name="attendance_date" id="attendance_date" x-model="formData.attendance_date"
                            @input="delete errors.attendance_date" max="{{ date('Y-m-d') }}"
                            class="modal-input-premium"
                            :class="errors.attendance_date ? 'border-red-500 ring-red-500/10' : ''">
                        <template x-if="errors.attendance_date">
                            <p class="modal-error-message" x-text="errors.attendance_date[0]"></p>
                        </template>
                    </div>
                </div>

                {{-- Verification Footer --}}
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-emerald-500 shadow-sm">
                            <i class="fas fa-shield-alt text-[10px]"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Operational Integrity Verified</p>
                    </div>
                    <button type="submit" :disabled="!canSubmit || submitting"
                        class="min-w-[240px] px-10 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-black rounded-2xl transition-all shadow-lg shadow-emerald-100 disabled:opacity-50 disabled:shadow-none uppercase tracking-widest flex items-center justify-center gap-3 group">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <i class="fas fa-save group-hover:scale-110 transition-transform" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Propagating...' : 'Finalize Boarding Manifest'"></span>
                    </button>
                </div>

                <!-- Students List -->
                <div x-show="students.length > 0" class="p-8 border-t border-slate-100 bg-white" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Boarding Manifest</h3>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Capture real-time transit status for <span class="text-indigo-600 font-bold" x-text="students.length"></span> assigned boarders</p>
                        </div>
                        <div class="flex items-center gap-3 bg-slate-50 p-1.5 rounded-2xl border border-slate-100 shadow-inner">
                            <button type="button" @click="checkAll()"
                                class="px-5 py-2.5 bg-white text-indigo-600 hover:text-indigo-700 text-[10px] font-black rounded-xl transition-all uppercase tracking-widest shadow-sm border border-slate-100 flex items-center gap-2">
                                <i class="fas fa-check-double text-[8px]"></i>
                                Board All
                            </button>
                            <button type="button" @click="uncheckAll()"
                                class="px-5 py-2.5 text-slate-500 hover:text-slate-700 text-[10px] font-black rounded-xl transition-all uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-times text-[8px]"></i>
                                Clear All
                            </button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-3xl border border-slate-100 shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-5 text-left">
                                        <div class="flex items-center">
                                            <input type="checkbox" @change="toggleAll($event.target.checked)"
                                                :checked="allChecked"
                                                class="rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500/20 h-6 w-6 transition-all cursor-pointer shadow-sm">
                                        </div>
                                    </th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Boarder Profile</th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Academic Context</th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Network Node</th>
                                    <th class="px-6 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Boarding Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-50">
                                <template x-for="(student, index) in students" :key="student.id">
                                    <tr class="hover:bg-indigo-50/30 transition-all group">
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <input type="checkbox" :id="`student_${student.id}`"
                                                :value="student.id.toString()" x-model="checkedStudents"
                                                class="rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500/20 h-6 w-6 transition-all cursor-pointer shadow-sm group-hover:scale-110">
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-slate-800" x-text="student.name"></span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5" x-text="student.admission_no"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-xl border border-slate-100">
                                                <i class="fas fa-graduation-cap text-[10px] text-slate-400"></i>
                                                <span class="text-[11px] font-black text-slate-600 uppercase tracking-tight" x-text="`${student.class} - ${student.section}`"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap cursor-help group/node">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 border border-blue-100 shadow-sm group-hover/node:scale-110 transition-transform">
                                                    <i class="fas fa-map-marker-alt text-[10px]"></i>
                                                </div>
                                                <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors" x-text="student.bus_stop_name"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-right">
                                            <label :for="`student_${student.id}`" class="cursor-pointer">
                                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm border"
                                                    :class="checkedStudents.includes(student.id.toString()) 
                                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-100/20' 
                                                        : 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-100/20'">
                                                    <i class="fas" :class="checkedStudents.includes(student.id.toString()) ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                                    <span x-text="checkedStudents.includes(student.id.toString()) ? 'Boarded' : 'Absent'"></span>
                                                </span>
                                            </label>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden Context Transmission -->
                    <div class="hidden">
                        <template x-for="student in students" :key="'h1_'+student.id">
                            <input type="hidden" name="students[]" :value="student.id">
                        </template>
                        <template x-for="studentId in checkedStudents" :key="'h2_'+studentId">
                            <input type="hidden" name="checked_students[]" :value="studentId">
                        </template>
                    </div>
                </div>

                <!-- Empty Registry State -->
                <div x-show="students.length === 0 && formData.route_id" 
                    x-transition
                    class="p-20 text-center bg-slate-50/50 rounded-b-3xl border-t border-slate-100">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-white rounded-3xl shadow-sm flex items-center justify-center border border-slate-100">
                            <i class="fas fa-users-slash text-slate-200 text-4xl"></i>
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-rose-500 rounded-2xl flex items-center justify-center text-white shadow-lg border-4 border-white">
                            <i class="fas fa-exclamation text-xs"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight mb-2">Null Manifest Encountered</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest max-w-sm mx-auto leading-relaxed">
                        No active student profiles are currently mapped to the selected transit corridor for this session.
                    </p>
                </div>
            </form>
        </div>
    </div>

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

                                // Attendance Type
                                $('#attendance_type').on('change', (e) => {
                                    this.formData.attendance_type = e.target.value;
                                    this.clearError('attendance_type');
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
                            } else if (data.errors) {
                                this.errors = data.errors;
                            }
                        } catch (error) {
                            console.error('Route load failed', error);
                        }
                    },

                    updateRouteOptions() {
                        if (typeof $ === 'undefined') return;
                        const $select = $('#route_id');

                        // Clear and add placeholder
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
                                // Default to checked
                                this.checkedStudents = this.students.map(s => s.id.toString());
                            } else if (data.errors) {
                                this.errors = data.errors;
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
                            } else {
                                if (response.status === 422) {
                                    this.errors = result.errors || {};
                                    if (window.Toast) {
                                        window.Toast.fire({ icon: 'error', title: 'Data integrity failure' });
                                    }
                                } else {
                                    throw new Error(result.message || 'Transmission error');
                                }
                            }
                        } catch (error) {
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: error.message });
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

                    toggleAll(checked) {
                        if (checked) this.checkAll(); else this.uncheckAll();
                    },

                    get allChecked() {
                        return this.students.length > 0 && this.checkedStudents.length === this.students.length;
                    },
                };
            }
        </script>
    @endpush
@endsection