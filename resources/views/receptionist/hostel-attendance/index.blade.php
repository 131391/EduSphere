@extends('layouts.receptionist')

@section('title', 'Hostel Census Registry - Receptionist')
@section('page-title', 'Hostel Census')
@section('page-description', 'Capture and synchronize real-time residential presence across institutional blocks')

@section('content')
<div class="space-y-6" x-data="hostelAttendanceManagement()" x-init="init()">
    
    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Residents" :value="$stats['total_residents']" icon="fas fa-users" color="blue" />
        <x-stat-card label="Present Today" :value="$stats['present_today']" icon="fas fa-check-double" color="emerald" />
        <x-stat-card label="Absent Today" :value="$stats['absent_today']" icon="fas fa-user-clock" color="rose" />
        <x-stat-card label="Active Blocks" :value="$hostels->count()" icon="fas fa-building" color="amber" />
    </div>

    <!-- Header Section -->
    <x-page-header title="Hostel Residential Census" description="Capture and synchronize real-time residential presence across institutional blocks" icon="fas fa-hotel">
        <a href="{{ route('receptionist.hostel-attendance.report') }}" 
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-file-invoice mr-2 text-xs"></i>
            Attendance Reports
        </a>
    </x-page-header>

    <!-- Configuration Selector (Parameters) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden shadow-teal-100/10">
        <div class="p-6">
            <div class="flex flex-col md:flex-row items-end gap-6">
                <!-- Hostel Selection -->
                <div class="flex-1 w-full space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Residential Block</label>
                    <div class="relative group">
                        <select id="hostel_id" x-model="formData.hostel_id" @change="loadStudents()"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border-slate-200 dark:bg-gray-700/50 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">Select Hostel Block</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-600 opacity-50 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-building text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Session Date -->
                <div class="w-full md:w-64 space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Census Date</label>
                    <div class="relative group">
                        <input type="date" x-model="formData.attendance_date" @change="loadStudents()" max="{{ date('Y-m-d') }}"
                               class="w-full h-12 pl-10 pr-4 bg-slate-50 border-slate-200 dark:bg-gray-700/50 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none shadow-sm cursor-pointer">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-600 opacity-50 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-calendar-check text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Sync Control -->
                <div class="w-full md:w-auto">
                    <button @click="save()" :disabled="!canSubmit || submitting"
                            class="w-full h-12 px-8 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all duration-300 shadow-lg shadow-teal-500/20 disabled:opacity-50 disabled:grayscale flex items-center justify-center gap-3">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <i class="fas fa-cloud-upload-alt text-sm" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Propagating...' : 'Sync Census'">Sync Census</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Census Manifest Section -->
        <div class="bg-white dark:bg-gray-800 rounded-b-2xl border-t border-gray-100 dark:border-gray-700 overflow-hidden relative min-h-[400px] ajax-table-wrapper">
            <x-table.loading-overlay />
            
            <!-- Manifest Header: Only visible when students are loaded -->
            <div x-show="students.length > 0" x-cloak class="px-7 py-5 bg-slate-50/50 dark:bg-gray-800/50 border-b border-slate-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border-2 border-teal-500/10 flex items-center justify-center text-teal-600 shadow-sm">
                        <i class="fas fa-users-viewfinder text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest leading-tight">Occupancy Manifest</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1"><span x-text="students.length" class="text-teal-600"></span> Registered Inmates</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 p-1 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm">
                    <button type="button" @click="checkAll()"
                        class="px-5 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white text-[10px] font-black rounded-lg transition-all uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-check-double text-[8px]"></i>
                        Mark All Present
                    </button>
                    <button type="button" @click="uncheckAll()"
                        class="px-5 py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white text-[10px] font-black rounded-lg transition-all uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-times text-[8px]"></i>
                        Mark All Absent
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto text-sm">
                <table class="w-full text-left border-collapse">
                    <thead x-show="students.length > 0" x-cloak>
                        <tr class="bg-gray-50/30 dark:bg-gray-700/10 border-b border-slate-100 dark:border-gray-700">
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Resident Identity</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cluster Context</th>
                            <th class="px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Presence State</th>
                            <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Observational Log</th>
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
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center text-slate-600 dark:text-gray-400 font-bold border border-slate-200 dark:border-gray-600 uppercase text-[10px] shadow-sm" x-text="student.name.split(' ').map(n => n[0]).join('')"></div>
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
                                        <div class="w-24 h-9 p-1 bg-rose-50 dark:bg-rose-900/20 rounded-xl border border-rose-100 dark:border-rose-900/30 relative transition-all duration-300 peer-checked:bg-emerald-500 peer-checked:border-emerald-600 shadow-sm">
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
                                           class="w-full max-w-[200px] h-9 px-4 bg-slate-50/50 dark:bg-gray-700/50 border border-transparent focus:bg-white dark:focus:bg-gray-800 focus:border-teal-500/30 rounded-xl transition-all text-[10px] font-bold placeholder:text-gray-300 shadow-sm">
                                </td>
                            </tr>
                        </template>

                        <!-- Initial State Row (Hidden once data loads) -->
                        <tr x-show="students.length === 0 && !loading" x-cloak>
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 rounded-3xl flex items-center justify-center mb-6 border border-slate-100 dark:border-gray-600">
                                        <i class="fas fa-users-viewfinder text-3xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Census Matrix Uninitialized</h3>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest max-w-sm mx-auto mt-2 leading-relaxed">
                                        Select a residential block from the parameters above to initiate the student occupancy verified census protocol.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Verification Footer: Only visible when students are loaded -->
            <div x-show="students.length > 0" x-cloak class="px-8 py-6 bg-slate-50/30 dark:bg-gray-700/10 border-t border-slate-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900/30 flex items-center justify-center text-emerald-600 shadow-sm">
                        <i class="fas fa-shield-check text-[10px]"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Institutional Security Protocol Verified</p>
                </div>
            </div>
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
