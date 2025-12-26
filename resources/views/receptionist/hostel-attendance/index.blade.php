@extends('layouts.receptionist')

@section('title', 'Student Attendance - Hostel')

@section('content')
<div class="space-y-6" x-data="hostelAttendanceManagement()" x-init="init()">
    <!-- Success Message -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Student Attendance</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Mark attendance for students assigned to hostels</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('receptionist.hostel-bed-assignments.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <form id="attendanceForm" method="POST" action="{{ route('receptionist.hostel-attendance.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Hostel Selection -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Select Hostel <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="hostel_id" 
                        id="hostel_id"
                        x-ref="hostelSelect"
                        class="w-full px-4 py-2 border @error('hostel_id') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                        <option value="">Select Hostel</option>
                        @foreach($hostels as $hostel)
                            <option value="{{ $hostel->id }}" {{ old('hostel_id') == $hostel->id ? 'selected' : '' }}>
                                {{ $hostel->hostel_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('hostel_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attendance Date -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="attendance_date" 
                        id="attendance_date"
                        x-model="formData.attendance_date"
                        value="{{ old('attendance_date', date('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2 border @error('attendance_date') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                    @error('attendance_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end mb-6">
                <button 
                    type="submit"
                    :disabled="!canSubmit"
                    class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors shadow-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-check mr-2"></i>
                    Submit
                </button>
            </div>

            <!-- Students List -->
            <div x-show="students.length > 0" class="mt-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            <i class="fas fa-users mr-2"></i>
                            Students List (<span x-text="students.length"></span>)
                        </h3>
                        <div class="flex gap-2">
                            <button 
                                type="button"
                                @click="checkAll()"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                            >
                                <i class="fas fa-check-double mr-2"></i>
                                Check All
                            </button>
                            <button 
                                type="button"
                                @click="uncheckAll()"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Uncheck All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-teal-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    <input 
                                        type="checkbox" 
                                        @change="toggleAll($event)"
                                        :checked="checkedStudents.length === students.length && students.length > 0"
                                        class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-4 h-4"
                                    >
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Admission No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Class</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Floor</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Bed No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(student, index) in students" :key="student.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input 
                                            type="checkbox" 
                                            :id="'student_' + student.id"
                                            :checked="checkedStudents.includes(String(student.id))"
                                            @change="updateAttendanceStatus(student.id, $event.target.checked)"
                                            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-4 h-4"
                                        >
                                        <input type="hidden" :name="'students[' + index + '][student_id]'" :value="student.id">
                                        <input type="hidden" :name="'students[' + index + '][is_present]'" :id="'is_present_' + student.id" :value="checkedStudents.includes(String(student.id)) ? '1' : '0'">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="student.admission_no"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <span x-text="student.class_name || 'N/A'"></span>
                                        <span x-show="student.section_name" x-text="' - ' + student.section_name" class="text-gray-500"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.floor_name || 'N/A'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.room_name || 'N/A'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.bed_no || 'N/A'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input 
                                            type="text" 
                                            :name="'students[' + index + '][remarks]'"
                                            placeholder="Optional remarks..."
                                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                        >
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div x-show="students.length === 0 && formData.hostel_id" class="text-center py-12">
                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">No students found for the selected hostel.</p>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function hostelAttendanceManagement() {
    return {
        formData: {
            hostel_id: '',
            attendance_date: '{{ old('attendance_date', date('Y-m-d')) }}',
        },
        students: [],
        checkedStudents: [],

        init() {
            // Initialize Select2 for hostel dropdown
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        const $hostelSelect = $('#hostel_id');
                        
                        // Check if Select2 is already initialized
                        if ($hostelSelect.hasClass('select2-hidden-accessible')) {
                            $hostelSelect.select2('destroy');
                        }
                        
                        $hostelSelect.select2({
                            placeholder: 'Select Hostel',
                            allowClear: true,
                            width: '100%'
                        }).on('select2:select select2:change', (e) => {
                            this.formData.hostel_id = e.target.value || $hostelSelect.val();
                            this.loadStudents();
                        });

                        // Apply error styling if needed
                        @if($errors->has('hostel_id'))
                            setTimeout(() => {
                                $hostelSelect.next('.select2-container').find('.select2-selection').addClass('border-red-500');
                            }, 50);
                        @endif

                        // Sync Select2 value with Alpine.js if old value exists
                        @if(old('hostel_id'))
                            setTimeout(() => {
                                $hostelSelect.val('{{ old('hostel_id') }}').trigger('change');
                                this.formData.hostel_id = '{{ old('hostel_id') }}';
                                this.loadStudents();
                            }, 200);
                        @endif
                    }
                }, 100);
            });
        },

        get canSubmit() {
            return this.formData.hostel_id && 
                   this.formData.attendance_date && 
                   this.students.length > 0;
        },

        async loadStudents() {
            if (!this.formData.hostel_id) {
                this.students = [];
                this.checkedStudents = [];
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const response = await fetch('{{ route('receptionist.hostel-attendance.get-students') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        hostel_id: this.formData.hostel_id,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to load students');
                }

                const data = await response.json();
                
                if (data.success && Array.isArray(data.students)) {
                    this.students = data.students;
                    // Check all students by default
                    this.checkedStudents = this.students.map(s => String(s.id));
                    
                    // Initialize hidden inputs after DOM update
                    this.$nextTick(() => {
                        this.students.forEach(student => {
                            const hiddenInput = document.getElementById('is_present_' + student.id);
                            if (hiddenInput) {
                                hiddenInput.value = '1';
                            }
                        });
                    });
                } else {
                    this.students = [];
                    this.checkedStudents = [];
                }
            } catch (error) {
                this.students = [];
                this.checkedStudents = [];
            }
        },

        checkAll() {
            this.checkedStudents = this.students.map(s => String(s.id));
            // Update all hidden inputs
            this.students.forEach(student => {
                const hiddenInput = document.getElementById('is_present_' + student.id);
                if (hiddenInput) {
                    hiddenInput.value = '1';
                }
            });
        },

        uncheckAll() {
            this.checkedStudents = [];
            // Update all hidden inputs
            this.students.forEach(student => {
                const hiddenInput = document.getElementById('is_present_' + student.id);
                if (hiddenInput) {
                    hiddenInput.value = '0';
                }
            });
        },

        toggleAll(event) {
            if (event.target.checked) {
                this.checkAll();
            } else {
                this.uncheckAll();
            }
        },

        updateAttendanceStatus(studentId, isChecked) {
            const hiddenInput = document.getElementById('is_present_' + studentId);
            if (hiddenInput) {
                hiddenInput.value = isChecked ? '1' : '0';
            }
        },
    };
}
</script>
@endpush
@endsection

