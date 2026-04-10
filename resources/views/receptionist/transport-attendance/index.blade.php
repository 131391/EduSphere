@extends('layouts.receptionist')

@section('title', 'Student Attendance - Transport')

@section('content')
<div class="space-y-6" x-data="transportAttendanceManagement()" x-init="init()">
    <!-- Success Message -->

    {{-- Page Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Student Attendance</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('receptionist.transport-assignments.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form @submit.prevent="save" method="POST">
            @csrf
            
            {{-- Global Error Announcement --}}
            <template x-if="Object.keys(errors).length > 0">
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl mx-8 mt-8">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span class="text-xs font-black text-red-700 uppercase tracking-widest">Validation Exceptions</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="(messages, field) in errors" :key="field">
                            <template x-for="message in messages" :key="message">
                                <li class="text-[10px] text-red-600 font-bold uppercase" x-text="message"></li>
                            </template>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="p-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Vehicle Selection -->
                <div>
                    <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                        Primary Vehicle <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="vehicle_id" 
                        id="vehicle_id"
                        x-model="formData.vehicle_id"
                        @change="delete errors.vehicle_id; loadRoutes()"
                        x-ref="vehicleSelect"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                        :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'"
                    >
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.vehicle_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.vehicle_id[0]"></p>
                    </template>
                </div>

                <!-- Route Selection -->
                <div>
                    <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                        Assigned Route <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="route_id" 
                        id="route_id"
                        x-model="formData.route_id"
                        @change="delete errors.route_id; loadStudents()"
                        x-ref="routeSelect"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                        :class="errors.route_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'"
                    >
                        <option value="">Select Route</option>
                    </select>
                    <template x-if="errors.route_id">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.route_id[0]"></p>
                    </template>
                </div>

                <!-- Attendance Type Selection -->
                <div>
                    <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                        Operational Stage <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="attendance_type" 
                        id="attendance_type"
                        x-model="formData.attendance_type"
                        @change="delete errors.attendance_type"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                        :class="errors.attendance_type ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'"
                    >
                        <option value="">Select Stage</option>
                        @foreach($attendanceTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.attendance_type">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.attendance_type[0]"></p>
                    </template>
                </div>

                <!-- Attendance Date -->
                <div>
                    <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                        Session Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="attendance_date" 
                        id="attendance_date"
                        x-model="formData.attendance_date"
                        @input="delete errors.attendance_date"
                        max="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-700 dark:text-white"
                        :class="errors.attendance_date ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'"
                    >
                    <template x-if="errors.attendance_date">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.attendance_date[0]"></p>
                    </template>
                </div>
            </div>

            <!-- Submit Button Overlay -->
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest" x-show="students.length > 0">
                        Operational Integrity Verified
                    </p>
                </div>
                <button 
                    type="submit"
                    :disabled="!canSubmit || submitting"
                    class="px-10 py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-black rounded-xl transition-all shadow-lg shadow-teal-100 disabled:from-gray-300 disabled:to-gray-400 disabled:shadow-none uppercase text-xs tracking-widest flex items-center gap-2"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : 'Finalize Attendance'"></span>
                </button>
            </div>

            <!-- Students List -->
            <div x-show="students.length > 0" class="p-8 border-t border-gray-100 bg-white" x-transition>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">Boarding Manifest</h3>
                        <p class="text-xs text-gray-500">Verify present boarders for the selected transit session</p>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            type="button"
                            @click="checkAll()"
                            class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 text-[10px] font-black rounded-lg transition-all uppercase tracking-widest"
                        >
                            <i class="fas fa-check-square mr-2"></i>
                            Check All
                        </button>
                        <button 
                            type="button"
                            @click="uncheckAll()"
                            class="px-4 py-2 bg-gray-50 text-gray-600 hover:bg-gray-100 text-[10px] font-black rounded-lg transition-all uppercase tracking-widest"
                        >
                            <i class="fas fa-square mr-2"></i>
                            Uncheck All
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100">
                    <table class="min-w-full bg-white dark:bg-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">
                                    <input 
                                        type="checkbox" 
                                        @change="toggleAll($event.target.checked)"
                                        :checked="allChecked"
                                        class="rounded-lg border-gray-200 text-teal-600 focus:ring-teal-500 h-5 w-5"
                                    >
                                </th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">SR NO</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">ADMISSION NO</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">STUDENT NAME</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">BUS STOP NAME</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-gray-300 uppercase tracking-widest">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="(student, index) in students" :key="student.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input 
                                            type="checkbox" 
                                            :id="`student_${student.id}`"
                                            :value="student.id.toString()"
                                            x-model="checkedStudents"
                                            class="rounded-lg border-gray-200 text-teal-600 focus:ring-teal-500 h-5 w-5"
                                        >
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500" x-text="index + 1"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-gray-900 dark:text-white" x-text="student.admission_no"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700 dark:text-white" x-text="student.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500" x-text="student.bus_stop_name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="inline-block px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full"
                                            :class="checkedStudents.includes(student.id.toString()) ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'"
                                            x-text="checkedStudents.includes(student.id.toString()) ? 'Present' : 'Absent'"
                                        ></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Hidden inputs for all students and checked students -->
                <template x-for="student in students" :key="student.id">
                    <input type="hidden" :name="`students[]`" :value="student.id">
                </template>
                <template x-for="studentId in checkedStudents" :key="studentId">
                    <input type="hidden" :name="`checked_students[]`" :value="studentId">
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="students.length === 0 && formData.route_id" class="text-center py-20 bg-gray-50/50">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-sm mb-6 border border-gray-100">
                    <i class="fas fa-users text-gray-300 text-3xl"></i>
                </div>
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-tight">Empty Manifest</h3>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-widest">No active boarders linked to this route</p>
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

        async init() {
            // Setup Select2 change handlers
            this.$nextTick(() => {
                setTimeout(() => this.setupSelectHandlers(), 500);
            });
        },

        setupSelectHandlers() {
            if (typeof $ === 'undefined') return;

            // Vehicle Select
            const $vehicleSelect = $('#vehicle_id');
            $vehicleSelect.off('change').on('change', (e) => {
                const val = e.target.value;
                if (this.formData.vehicle_id !== val) {
                    this.formData.vehicle_id = val;
                    if (this.errors.vehicle_id) delete this.errors.vehicle_id;
                    this.loadRoutes();
                }
            });

            // Route Select
            const $routeSelect = $('#route_id');
            $routeSelect.off('change').on('change', (e) => {
                const val = e.target.value;
                if (this.formData.route_id !== val) {
                    this.formData.route_id = val;
                    if (this.errors.route_id) delete this.errors.route_id;
                    this.loadStudents();
                }
            });

            // Attendance Type
            const $typeSelect = $('#attendance_type');
            $typeSelect.off('change').on('change', (e) => {
                this.formData.attendance_type = e.target.value;
                if (this.errors.attendance_type) delete this.errors.attendance_type;
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

