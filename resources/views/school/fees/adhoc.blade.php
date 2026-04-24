@extends('layouts.school')

@section('title', 'Assign Ad-Hoc Fee')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8" x-data="adhocFeeForm()">
    <!-- Header -->
    <div class="mb-8 sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Assign Ad-Hoc Fee</h1>
            <p class="mt-2 text-sm text-gray-600">Bill students directly for miscellaneous items like Library Fines or Events.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('school.fees.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="mr-2 fas fa-arrow-left"></i> Back to Ledger
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <form @submit.prevent="submitForm" class="p-6 space-y-6">
            @csrf

            <!-- Selection Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Academic Year -->
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year <span class="text-red-500">*</span></label>
                    <select id="academic_year_id" name="academic_year_id" x-model="formData.academic_year_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="">Select Academic Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Class -->
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700">Class <span class="text-red-500">*</span></label>
                    <select id="class_id" name="class_id" x-model="formData.class_id" @change="fetchStudents" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Miscellaneous Fee -->
                <div>
                    <label for="miscellaneous_fee_id" class="block text-sm font-medium text-gray-700">Fee Item <span class="text-red-500">*</span></label>
                    <select id="miscellaneous_fee_id" name="miscellaneous_fee_id" x-model="formData.miscellaneous_fee_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="">Select Fee Item</option>
                        @foreach($miscFees as $fee)
                            <option value="{{ $fee->id }}">{{ $fee->name }} ({{ number_format($fee->amount, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fee Period -->
                <div>
                    <label for="fee_period" class="block text-sm font-medium text-gray-700">Fee Period <span class="text-red-500">*</span></label>
                    <input type="month" id="fee_period_input" x-model="rawFeePeriod" @change="formatPeriod" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    <input type="hidden" name="fee_period" x-model="formData.fee_period">
                    <p class="mt-1 text-xs text-gray-500" x-show="formData.fee_period">Target Period: <span x-text="formData.fee_period" class="font-semibold text-indigo-600"></span></p>
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date <span class="text-red-500">*</span></label>
                    <input type="date" id="due_date" name="due_date" x-model="formData.due_date" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                </div>
            </div>

            <!-- Student Selection Table -->
            <div class="pt-6 border-t border-gray-200" x-show="formData.class_id">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Select Students to Bill</h3>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center text-sm font-medium text-gray-700">
                            <input type="checkbox" x-model="selectAll" @change="toggleAllStudents" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2">Select All</span>
                        </label>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800" x-text="`${formData.student_ids.length} selected`"></span>
                    </div>
                </div>

                <div class="mt-4">
                    <div x-show="loadingStudents" class="py-12 text-center">
                        <i class="text-indigo-500 fas fa-circle-notch fa-spin fa-2x"></i>
                        <p class="mt-2 text-sm text-gray-500">Loading students...</p>
                    </div>

                    <div x-show="!loadingStudents && students.length === 0" class="py-12 text-center text-gray-500">
                        No active students found in this class.
                    </div>

                    <div x-show="!loadingStudents && students.length > 0" class="overflow-hidden border border-gray-200 shadow sm:rounded-lg max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="sticky top-0 bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Select</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Admission No</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Student Name</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Section</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="student in students" :key="student.id">
                                    <tr class="transition-colors hover:bg-gray-50" :class="{'bg-indigo-50': isSelected(student.id)}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" :value="student.id" x-model="formData.student_ids" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap" x-text="student.admission_no"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap" x-text="student.full_name"></td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap" x-text="student.section_name"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-5 border-t border-gray-200">
                <button type="button" @click="window.location.href='{{ route('school.fees.index') }}'" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Cancel
                </button>
                <button type="submit" :disabled="isSubmitting || formData.student_ids.length === 0" class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white transition-colors bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">Assign to <span x-text="formData.student_ids.length"></span> Students</span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <i class="mr-2 fas fa-circle-notch fa-spin"></i> Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function adhocFeeForm() {
        return {
            formData: {
                academic_year_id: '{{ $academicYears->firstWhere("is_current", true)?->id ?? "" }}',
                class_id: '',
                miscellaneous_fee_id: '',
                fee_period: '',
                due_date: '',
                student_ids: []
            },
            rawFeePeriod: '',
            students: [],
            selectAll: false,
            loadingStudents: false,
            isSubmitting: false,

            formatPeriod() {
                if (this.rawFeePeriod) {
                    const date = new Date(this.rawFeePeriod + '-01');
                    this.formData.fee_period = date.toLocaleString('en-US', { month: 'long', year: 'numeric' });
                } else {
                    this.formData.fee_period = '';
                }
            },

            async fetchStudents() {
                if (!this.formData.class_id) {
                    this.students = [];
                    this.formData.student_ids = [];
                    this.selectAll = false;
                    return;
                }

                this.loadingStudents = true;
                this.students = [];
                this.formData.student_ids = [];
                this.selectAll = false;

                try {
                    const response = await fetch(`/api/school/classes/${this.formData.class_id}/students`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) throw new Error('Failed to fetch students');
                    
                    const data = await response.json();
                    if (data.success && Array.isArray(data.data)) {
                        this.students = data.data;
                    }
                } catch (error) {
                    console.error('Error fetching students:', error);
                    window.Swal && Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load students for the selected class.'
                    });
                } finally {
                    this.loadingStudents = false;
                }
            },

            toggleAllStudents() {
                if (this.selectAll) {
                    this.formData.student_ids = this.students.map(s => s.id.toString());
                } else {
                    this.formData.student_ids = [];
                }
            },

            isSelected(id) {
                return this.formData.student_ids.includes(id.toString());
            },

            async submitForm() {
                if (this.formData.student_ids.length === 0) {
                    window.Swal && Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select at least one student to assign the fee.'
                    });
                    return;
                }

                if (!this.formData.fee_period) {
                    window.Swal && Swal.fire({
                        icon: 'warning',
                        title: 'Missing Input',
                        text: 'Please select a fee period.'
                    });
                    return;
                }

                this.isSubmitting = true;

                try {
                    const response = await fetch('{{ route("school.ad-hoc-fees.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.Swal && Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("school.fees.index") }}';
                        });
                    } else {
                        throw new Error(result.message || 'Validation failed');
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    window.Swal && Swal.fire({
                        icon: 'error',
                        title: 'Assignment Failed',
                        text: error.message || 'An unexpected error occurred while assigning the fee.'
                    });
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endpush
@endsection
