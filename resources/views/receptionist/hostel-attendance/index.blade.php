@extends('layouts.receptionist')

@section('title', 'Hostel Census Registry')

@section('content')
<div class="space-y-6" x-data="hostelAttendanceManagement()" x-init="init()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-hotel text-xs"></i>
                    </div>
                    Hostel Residential Census
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Capture and synchronize real-time residential presence across institutional blocks.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('receptionist.hostel-attendance.report') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <i class="fas fa-file-invoice mr-2 opacity-50"></i>
                    Attendance Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Total Residents</p>
                <p class="text-xl font-black text-gray-800">{{ number_format($stats['total_residents']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Present Today</p>
                <p class="text-xl font-black text-emerald-600">{{ number_format($stats['present_today']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-user-clock text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Absent Today</p>
                <p class="text-xl font-black text-rose-600">{{ number_format($stats['absent_today']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-building text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Active Blocks</p>
                <p class="text-xl font-black text-amber-600">{{ $hostels->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Configuration Selector -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden shadow-teal-100/20 shadow-xl">
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <!-- Hostel Selection -->
                <div class="flex-1 w-full space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Residential Block</label>
                    <div class="relative group">
                        <select id="hostel_id" x-model="formData.hostel_id" @change="loadStudents()"
                                class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">Select Hostel Block</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500 opacity-50 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-building text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Session Date -->
                <div class="w-full md:w-64 space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Census Date</label>
                    <div class="relative group">
                        <input type="date" x-model="formData.attendance_date" @change="loadStudents()" max="{{ date('Y-m-d') }}"
                               class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none shadow-sm cursor-pointer">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-500 opacity-50 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-calendar-check text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Sync Control -->
                <div class="w-full md:w-auto pt-6 md:pt-6">
                    <button @click="save()" :disabled="!canSubmit || submitting"
                            class="w-full h-12 px-8 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all duration-300 shadow-xl disabled:opacity-50 disabled:grayscale flex items-center justify-center gap-3">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <i class="fas fa-cloud-upload-alt text-sm" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Propagating...' : 'Sync Census'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Census Manifest Section -->
        <div x-show="students.length > 0" x-collapse>
            <div class="px-8 py-5 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white dark:bg-gray-800 border border-teal-100 flex items-center justify-center text-teal-500 shadow-sm">
                        <i class="fas fa-users-viewfinder text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest leading-tight">Occupancy Manifest</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1"><span x-text="students.length"></span> Registered Inmates</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 p-1 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-inner">
                    <button type="button" @click="checkAll()"
                        class="px-5 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white text-[10px] font-black rounded-xl transition-all uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-check-double text-[8px]"></i>
                        Mark All Present
                    </button>
                    <button type="button" @click="uncheckAll()"
                        class="px-5 py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white text-[10px] font-black rounded-xl transition-all uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-times text-[8px]"></i>
                        Mark All Absent
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto text-sm">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/30 dark:bg-gray-700/10">
                            <th class="text-left px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Resident Identity</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cluster Context</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Presence State</th>
                            <th class="text-right px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Observational Log</th>
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
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-tight" x-text="'Floor ' + student.floor_name"></span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase" x-text="'Room ' + student.room_name + ' • Bed ' + student.bed_no"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <label class="inline-flex items-center cursor-pointer group/toggle">
                                        <input type="checkbox" :checked="checkedStudents.includes(String(student.id))" @change="updateStatus(student.id, $event.target.checked)" class="sr-only peer">
                                        <div class="w-24 h-9 p-1 bg-rose-50 dark:bg-rose-900/20 rounded-xl border border-rose-100 dark:border-rose-900/30 relative transition-all duration-300 peer-checked:bg-emerald-500 peer-checked:border-emerald-600">
                                            <div class="absolute inset-y-1 left-1 w-7 h-7 bg-white dark:bg-gray-700 rounded-lg shadow-sm transition-all duration-300 peer-checked:left-16 flex items-center justify-center text-[10px]">
                                                <i class="fas" :class="checkedStudents.includes(String(student.id)) ? 'fa-check text-emerald-500' : 'fa-times text-rose-500'"></i>
                                            </div>
                                            <div class="flex justify-between items-center h-full px-2 text-[8px] font-black uppercase tracking-widest">
                                                <span class="text-emerald-500 opacity-0 peer-checked:opacity-100 transition-opacity">IN</span>
                                                <span class="text-rose-500 peer-checked:opacity-0 transition-opacity">OUT</span>
                                            </div>
                                        </div>
                                    </label>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <input type="text" x-model="student.remarks"
                                           placeholder="Presence observations..."
                                           class="w-full max-w-[200px] h-9 px-4 bg-gray-50/50 dark:bg-gray-700/50 border-transparent focus:bg-white dark:focus:bg-gray-800 focus:border-teal-500/30 rounded-xl transition-all text-[10px] font-bold placeholder:text-gray-300">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Verification Footer -->
            <div class="px-8 py-6 bg-gray-50/30 dark:bg-gray-700/10 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900/30 flex items-center justify-center text-emerald-500 shadow-sm">
                        <i class="fas fa-shield-check text-[10px]"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Institutional Security Protocol Verified</p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="students.length === 0" 
            class="p-24 text-center bg-gray-50/20 rounded-b-3xl border-t border-gray-100 dark:border-gray-700">
            <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-3xl shadow-sm flex items-center justify-center mx-auto mb-6 border border-gray-100 dark:border-gray-700">
                <i class="fas fa-users-viewfinder text-4xl text-gray-100"></i>
            </div>
            <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Census Matrix Uninitialized</h3>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest max-w-sm mx-auto mt-2 leading-relaxed">
                Select a residential block from the parameters above to initiate the student occupancy verified census protocol.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelAttendanceManagement', () => ({
        loading: false,
        submitting: false,
        errors: {},
        formData: {
            hostel_id: '',
            attendance_date: '{{ date('Y-m-d') }}',
        },
        students: [],
        checkedStudents: [],

        init() {
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#hostel_id').on('change', (e) => {
                        this.formData.hostel_id = e.target.value;
                        this.clearError('hostel_id');
                        this.loadStudents();
                    });
                }
            });
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        get canSubmit() {
            return this.formData.hostel_id && this.formData.attendance_date && this.students.length > 0;
        },

        async loadStudents() {
            this.errors = {};
            if (!this.formData.hostel_id) {
                this.students = [];
                this.checkedStudents = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch('{{ route('receptionist.hostel-attendance.get-students') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ hostel_id: this.formData.hostel_id })
                });

                const data = await response.json();
                if (data.success) {
                    this.students = data.students.map(s => ({ ...s, remarks: '' }));
                    this.checkedStudents = this.students.map(s => String(s.id));
                } else {
                    this.students = [];
                    this.checkedStudents = [];
                }
            } catch (error) {
                console.error('Matrix Query failure:', error);
            } finally {
                this.loading = false;
            }
        },

        checkAll() {
            this.checkedStudents = this.students.map(s => String(s.id));
        },

        uncheckAll() {
            this.checkedStudents = [];
        },

        updateStatus(studentId, isChecked) {
            if (isChecked) {
                if (!this.checkedStudents.includes(String(studentId))) {
                    this.checkedStudents.push(String(studentId));
                }
            } else {
                this.checkedStudents = this.checkedStudents.filter(id => id !== String(studentId));
            }
        },

        async save() {
            if (this.submitting) return;

            this.submitting = true;
            this.errors = {};
            
            try {
                const payload = {
                    hostel_id: this.formData.hostel_id,
                    attendance_date: this.formData.attendance_date,
                    students: this.students.map(s => ({
                        student_id: s.id,
                        is_present: this.checkedStudents.includes(String(s.id)),
                        remarks: s.remarks
                    }))
                };

                const response = await fetch('{{ route('receptionist.hostel-attendance.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message || 'Census Synchronized'
                        });
                    }
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(result.message || 'Registry synchronization failure');
                }
            } catch (error) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>
@endpush

