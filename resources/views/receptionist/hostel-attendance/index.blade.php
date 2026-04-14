@extends('layouts.receptionist')

@section('title', 'Hostel Registry - Receptionist')
@section('page-title', 'Hostel Registry')
@section('page-description', 'Track and synchronize student residential attendance metrics')

@section('content')
<div class="space-y-6" x-data="hostelAttendanceManagement()" x-init="init()">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Managed Blocks</p>
                <p class="text-3xl font-black text-gray-800">{{ $hostels->count() }}</p>
            </div>
            <div class="bg-indigo-100 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-building text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Residents</p>
                <p class="text-3xl font-black text-gray-800" x-text="students.length || '0'">0</p>
            </div>
            <div class="bg-emerald-100 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-users text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group text-emerald-600">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Synchronized</p>
                <p class="text-3xl font-black" x-text="checkedStudents.length || '0'">0</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group text-red-600">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Exceptions</p>
                <p class="text-3xl font-black" x-text="students.length - checkedStudents.length || '0'">0</p>
            </div>
            <div class="bg-red-50 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Configuration Header --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-4 w-full lg:w-auto">
                <a href="{{ route('receptionist.hostel-bed-assignments.index') }}" 
                   class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Hostel Registry</h2>
                    <p class="text-sm text-gray-500 font-medium">Capture real-time occupancy and presence metrics</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                <div class="w-full sm:w-64">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic px-1">Hostel Block</label>
                    <div class="relative group">
                        <select name="hostel_id" x-model="formData.hostel_id" id="hostel_id"
                                class="modal-input-premium pl-10"
                                :class="errors.hostel_id ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                            <option value="">Select Block</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                            <i class="fas fa-building text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <div class="w-full sm:w-48">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic px-1">Registry Date</label>
                    <input type="date" x-model="formData.attendance_date" @change="loadStudents(); clearError('attendance_date')"
                           max="{{ date('Y-m-d') }}"
                           class="w-full px-5 py-3 bg-white border border-gray-100 rounded-xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 shadow-sm text-sm font-bold"
                           :class="errors.attendance_date ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                </div>

                <div class="w-full sm:w-auto pt-6 lg:pt-0">
                    <button @click="save()" :disabled="!canSubmit || submitting"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-sm font-black rounded-xl transition-all shadow-xl shadow-emerald-100 disabled:opacity-50 group">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                        </template>
                        <i x-show="!submitting" class="fas fa-check-double mr-2 group-hover:scale-110 transition-transform"></i>
                        <span x-text="submitting ? 'Propagating...' : 'Finalize Registry'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Registry Grid --}}
    <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden relative min-h-[400px]">
        {{-- Loading Overlay --}}
        <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-10 flex items-center justify-center transition-all">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                <p class="text-xs font-black text-indigo-600 uppercase tracking-widest">Querying Matrix...</p>
            </div>
        </div>

        {{-- Global Error Announcement --}}
        <template x-if="Object.keys(errors).length > 0">
            <div class="m-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="text-xs font-black text-red-700 uppercase tracking-widest">Synchronization Exceptions</span>
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

        <template x-if="students.length > 0">
            <div class="p-0">
                <div class="bg-gray-50/50 border-b border-gray-100 px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-users-viewfinder"></i>
                            Occupancy Segment
                        </h3>
                        <span class="px-3 py-1 bg-white border border-gray-100 rounded-lg text-[10px] font-black text-indigo-600" x-text="students.length + ' Nodes Loaded'"></span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="checkAll()" class="px-4 py-2 bg-white border border-gray-200 text-indigo-600 text-[10px] font-black uppercase rounded-lg hover:bg-indigo-50 transition-colors">Select All</button>
                        <button @click="uncheckAll()" class="px-4 py-2 bg-white border border-gray-200 text-red-600 text-[10px] font-black uppercase rounded-lg hover:bg-red-50 transition-colors">Clear All</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/30">
                            <tr>
                                <th class="px-8 py-5 text-left">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" @change="toggleAll($event)" :checked="checkedStudents.length === students.length"
                                               class="w-5 h-5 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Status</span>
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-[10px] text-gray-400 uppercase font-black tracking-widest">Resident Identity</th>
                                <th class="px-6 py-5 text-left text-[10px] text-gray-400 uppercase font-black tracking-widest">Residential Node</th>
                                <th class="px-8 py-5 text-left text-[10px] text-gray-400 uppercase font-black tracking-widest">Observations / Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="(student, index) in students" :key="student.id">
                                <tr class="hover:bg-indigo-50/10 transition-colors group">
                                    <td class="px-8 py-6">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" 
                                                   :checked="checkedStudents.includes(String(student.id))"
                                                   @change="updateStatus(student.id, $event.target.checked)"
                                                   class="w-6 h-6 rounded-xl border-gray-200 text-emerald-600 focus:ring-emerald-500/20 transition-all cursor-pointer shadow-sm">
                                            <span class="text-xs font-black uppercase tracking-widest" :class="checkedStudents.includes(String(student.id)) ? 'text-emerald-600' : 'text-gray-400'" x-text="checkedStudents.includes(String(student.id)) ? 'Present' : 'Absent'"></span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col">
                                            <span class="font-black text-gray-800" x-text="student.name"></span>
                                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter" x-text="student.admission_no + ' • ' + (student.class_name || 'N/A') + ' ' + (student.section_name || '')"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-700 text-xs" x-text="'Room ' + student.room_name"></span>
                                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter" x-text="student.floor_name + ' • Unit ' + student.bed_no"></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <input type="text" x-model="student.remarks"
                                               placeholder="Add observational note..."
                                               class="w-full px-4 py-2 bg-gray-50/50 border border-transparent rounded-xl focus:bg-white focus:border-indigo-100 transition-all text-xs font-bold placeholder:text-gray-300">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        {{-- Initial/Empty State --}}
        <div x-show="!loading && students.length === 0" class="flex flex-col items-center justify-center py-24 px-8 text-center animate-fade-in">
            <div class="w-24 h-24 bg-indigo-50 rounded-[2.5rem] flex items-center justify-center text-indigo-200 mb-8 rotate-12 group-hover:rotate-0 transition-transform duration-500">
                <i class="fas fa-users-viewfinder text-5xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-800 tracking-tight mb-2" x-text="formData.hostel_id ? 'Matrix Exhausted' : 'Segment Unselected'"></h3>
            <p class="text-sm text-gray-500 max-w-sm font-medium" x-text="formData.hostel_id ? 'No student nodes are currently mapped to this residential block.' : 'Please select a hostel block to initialize the occupancy matrix.'"></p>
        </div>
    </div>
</div>

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
                if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Operational failure: Matrix retrieval blocked.' });
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

        toggleAll(event) {
            if (event.target.checked) this.checkAll();
            else this.uncheckAll();
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
                        await window.Toast.fire({
                            icon: 'success',
                            title: result.message || 'Attendance synchronized'
                        });
                    }
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
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
<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-out forwards;
    }
</style>
@endpush
@endsection
