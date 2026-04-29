@extends('layouts.school')

@section('title', 'Hostel Attendance')
@section('page-title', 'Hostel Attendance')
@section('page-description', 'Mark daily attendance for hostel students')

@section('content')
<div class="space-y-6" x-data="hostelAttendanceManagement()">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Residents"  :value="$stats['total_residents']" icon="fas fa-users"       color="blue"   />
        <x-stat-card label="Present Today"    :value="$stats['present_today']"   icon="fas fa-check-circle" color="emerald"/>
        <x-stat-card label="Absent Today"     :value="$stats['absent_today']"    icon="fas fa-times-circle" color="rose"   />
        <x-stat-card label="Hostels"          :value="$hostels->count()"         icon="fas fa-building"     color="amber"  />
    </div>

    <x-page-header title="Hostel Attendance" description="Select a hostel and date, then mark each student present or absent" icon="fas fa-hotel">
        <a href="{{ route('school.hostel.attendance.report') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-history mr-2 text-xs"></i>
            View Reports
        </a>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1 w-full space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-building mr-1 text-teal-500"></i> Hostel
                </label>
                <select id="hostel_id" x-model="formData.hostel_id" @change="loadStudents()"
                    class="no-select2 w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none">
                    <option value="">- Select Hostel -</option>
                    @foreach($hostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-56 space-y-1.5">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calendar-alt mr-1 text-teal-500"></i> Date
                </label>
                <input type="date" x-model="formData.attendance_date" @change="loadStudents()"
                    max="{{ date('Y-m-d') }}"
                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all outline-none">
            </div>

            <div class="w-full md:w-auto">
                <button @click="save()" :disabled="!canSubmit || submitting"
                    class="w-full h-11 px-8 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <i class="fas fa-save" x-show="!submitting"></i>
                    <span x-text="submitting ? 'Saving...' : 'Save Attendance'"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden relative"
         :class="students.length > 0 ? '' : 'min-h-[360px]'">

        <x-table.loading-overlay showVar="loading" />

        <div x-show="students.length > 0" x-cloak
            class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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

        <div x-show="students.length > 0" x-cloak class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="(student, index) in students" :key="student.id">
                    <div class="rounded-xl border transition-all duration-200 overflow-hidden"
                        :class="checkedStudents.includes(student.id)
                            ? 'border-emerald-200 bg-emerald-50/50 dark:bg-emerald-900/10 dark:border-emerald-800/50'
                            : 'border-rose-200 bg-rose-50/40 dark:bg-rose-900/10 dark:border-rose-800/50'">
                        <div class="flex items-center justify-between px-4 pt-4 pb-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0 shadow-sm"
                                    :class="checkedStudents.includes(student.id)
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-rose-400 text-white'"
                                    x-text="student.name ? student.name.trim().split(/\s+/).filter(n => n.length).map(n => n[0].toUpperCase()).slice(0,2).join('') : '?'">
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="student.name"></div>
                                    <div class="text-[11px] text-gray-400 font-medium" x-text="student.admission_no"></div>
                                </div>
                            </div>

                            <label class="relative cursor-pointer shrink-0 ml-2">
                                <input type="checkbox"
                                    :checked="checkedStudents.includes(student.id)"
                                    @change="updateStatus(student.id, $event.target.checked)"
                                    class="sr-only peer">
                                <div class="w-12 h-6 rounded-full transition-colors duration-200 peer-checked:bg-emerald-500 bg-rose-400 flex items-center px-1">
                                    <div class="w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-6"
                                        :class="checkedStudents.includes(student.id) ? 'translate-x-6' : 'translate-x-0'"></div>
                                </div>
                            </label>
                        </div>

                        <div class="px-4 pb-3 flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-layer-group text-[9px] text-gray-400"></i>
                                <span x-text="'Floor ' + student.floor_name"></span>
                            </span>
                            <span class="text-gray-300">|</span>
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-door-open text-[9px] text-gray-400"></i>
                                <span x-text="'Room ' + student.room_name"></span>
                            </span>
                            <span class="text-gray-300">|</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-teal-600">
                                <i class="fas fa-bed text-[9px]"></i>
                                <span x-text="'Bed ' + student.bed_no"></span>
                            </span>
                        </div>

                        <div class="px-4 pb-4 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                    :class="checkedStudents.includes(student.id)
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                        : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'">
                                    <i class="fas" :class="checkedStudents.includes(student.id) ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span x-text="checkedStudents.includes(student.id) ? 'Present' : 'Absent'"></span>
                                </span>
                            </div>
                            <input type="text" x-model="student.remarks"
                                placeholder="Add a note (optional)"
                                class="w-full h-8 px-3 text-xs bg-white dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-400 outline-none transition-all placeholder:text-gray-300 dark:text-gray-200">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="students.length === 0 && !loading" x-cloak
            class="flex flex-col items-center justify-center py-24 px-6 text-center">
            <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/20 rounded-2xl flex items-center justify-center mb-5 border border-teal-100 dark:border-teal-800/40">
                <i class="fas fa-hotel text-3xl text-teal-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 dark:text-white mb-1">No Students Loaded</h3>
            <p class="text-sm text-gray-400 max-w-xs">
                Select a hostel from the filter above to load the student list and mark attendance.
            </p>
        </div>

        <div x-show="students.length > 0" x-cloak
            class="px-6 py-4 bg-gray-50/60 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-400 font-medium">
                <i class="fas fa-info-circle mr-1 text-teal-400"></i>
                Attendance for <span class="font-bold text-gray-600 dark:text-gray-300" x-text="formData.attendance_date"></span>
                &nbsp;-&nbsp;
                <span class="text-emerald-600 font-bold" x-text="presentCount"></span> present,
                <span class="text-rose-500 font-bold" x-text="absentCount"></span> absent
            </p>

            <button @click="save()" :disabled="!canSubmit || submitting"
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
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelAttendanceManagement', () => ({
        loading: false,
        submitting: false,
        errors: {},
        formData: {
            hostel_id: '',
            attendance_date: '{{ date('Y-m-d') }}',
            academic_year_id: '{{ $academicYears->firstWhere('is_current', true)?->id ?? '' }}',
        },
        students: [],
        checkedStudents: [],

        get presentCount() {
            return this.checkedStudents.length;
        },

        get absentCount() {
            return this.students.length - this.checkedStudents.length;
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
                const response = await fetch('{{ route('school.hostel.attendance.get-students') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ hostel_id: this.formData.hostel_id })
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({}));
                    throw new Error(err.message || `Server error ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    this.students = result.students.map(student => ({ ...student, id: String(student.id), remarks: '' }));
                    this.checkedStudents = this.students.map(student => student.id);
                } else {
                    this.students = [];
                    this.checkedStudents = [];
                    if (window.Toast) window.Toast.fire({ icon: 'warning', title: result.message || 'No students found.' });
                }
            } catch (error) {
                console.error('Hostel attendance load failed:', error);
                this.students = [];
                this.checkedStudents = [];
                if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message || 'Failed to load students.' });
            } finally {
                this.loading = false;
            }
        },

        updateStatus(studentId, isChecked) {
            const id = String(studentId);
            if (isChecked) {
                if (!this.checkedStudents.includes(id)) {
                    this.checkedStudents.push(id);
                }
            } else {
                this.checkedStudents = this.checkedStudents.filter(item => item !== id);
            }
        },

        checkAll() {
            this.checkedStudents = this.students.map(student => student.id);
        },

        uncheckAll() {
            this.checkedStudents = [];
        },

        async save() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            try {
                const attendanceData = this.students.map(student => ({
                    student_id: parseInt(student.id, 10),
                    is_present: this.checkedStudents.includes(student.id),
                    remarks: student.remarks || ''
                }));

                const response = await fetch('{{ route('school.hostel.attendance.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        hostel_id: this.formData.hostel_id,
                        academic_year_id: this.formData.academic_year_id,
                        attendance_date: this.formData.attendance_date,
                        attendance_data: attendanceData
                    })
                });

                const result = await response.json().catch(() => ({ message: 'Invalid server response' }));

                if (response.ok && result.success) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message || 'Attendance saved.' })
                            .then(() => { window.location.href = window.location.href; });
                    } else {
                        window.location.href = window.location.href;
                    }
                } else {
                    throw new Error(result.message || 'Failed to save attendance.');
                }
            } catch (error) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message || 'Failed to save attendance' });
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>
@endpush
