@extends('layouts.school')

@section('title', 'Assign Ad-Hoc Fee')

@section('content')
<div x-data="adhocFeeForm()" class="space-y-6">
    
    <!-- Header Section -->
    <x-page-header title="Assign Ad-Hoc Fee" description="Bill students directly for miscellaneous items like Library Fines or Events." icon="fas fa-file-invoice-dollar">
        <a href="{{ route('school.fees.index') }}" 
            class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-xs"></i>
            Back to Ledger
        </a>
    </x-page-header>

    <!-- Form Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <form @submit.prevent="submitForm" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Section 1: Fee Configuration -->
            <div class="px-6 py-3 -mx-6 sm:-mx-8 -mt-6 sm:-mt-8 mb-6 bg-gray-50 dark:bg-gray-800/70 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">1</span>
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-100">Fee Configuration</h2>
            </div>

            <!-- Selection Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Academic Year -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Academic Year <span class="text-red-600 font-bold">*</span></label>
                    <select x-model="formData.academic_year_id" 
                        class="modal-input-premium no-select2 appearance-none" required>
                        <option value="">2026-2027 (Current)</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }} {{ $year->is_current === \App\Enums\YesNo::Yes ? '(Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Class -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Class <span class="text-red-600 font-bold">*</span></label>
                    <select x-model="formData.class_id" @change="fetchStudents" 
                        class="modal-input-premium no-select2 appearance-none" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fee Item -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Fee Item <span class="text-red-600 font-bold">*</span></label>
                    <select x-model="formData.miscellaneous_fee_id" 
                        class="modal-input-premium no-select2 appearance-none" required>
                        <option value="">Select Fee Item</option>
                        @foreach($miscFees as $fee)
                            <option value="{{ $fee->id }}">{{ $fee->name }} (₹{{ number_format($fee->amount, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fee Period -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Fee Period <span class="text-red-600 font-bold">*</span></label>
                    <input type="text" x-model="formData.fee_period" placeholder="e.g. {{ date('F Y') }}"
                        class="modal-input-premium" required>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Enter the month and year, e.g. "April 2026"</p>
                </div>

                <!-- Due Date -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Due Date <span class="text-red-600 font-bold">*</span></label>
                    <input type="date" x-model="formData.due_date" 
                        class="modal-input-premium cursor-pointer" required>
                </div>
            </div>

            <!-- Section 2: Student Selection -->
            <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6" x-show="formData.class_id">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">2</span>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-100">Select Students to Bill</h3>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" x-model="selectAll" @change="toggleAllStudents" 
                                class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:bg-gray-700">
                            <span class="ml-2">Select All</span>
                        </label>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" 
                            x-text="`${formData.student_ids.length} selected`"></span>
                    </div>
                </div>

                <div x-show="loadingStudents" class="py-12 text-center">
                    <i class="text-indigo-500 fas fa-circle-notch fa-spin fa-2x"></i>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading students...</p>
                </div>

                <div x-show="!loadingStudents && students.length === 0" class="py-12 text-center">
                    <i class="fas fa-users text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">No active students found in this class.</p>
                </div>

                <div x-show="!loadingStudents && students.length > 0" 
                    class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700/50 z-10">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-left">Select</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-left">Admission No</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-left">Student Name</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-left">Section</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="student in students" :key="student.id">
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" 
                                    :class="{'bg-indigo-50 dark:bg-indigo-900/20': isSelected(student.id)}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="student.id" x-model="formData.student_ids" 
                                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:bg-gray-700">
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap" x-text="student.admission_no"></td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 whitespace-nowrap" x-text="student.full_name"></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="student.section_name"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700 gap-3">
                <button type="button" @click="window.location.href='{{ route('school.fees.index') }}'" 
                    class="btn-premium-cancel px-8">
                    Cancel
                </button>
                <button type="submit" :disabled="isSubmitting || formData.student_ids.length === 0" 
                    class="btn-premium-primary min-w-[200px] !from-indigo-600 !to-violet-600 shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="isSubmitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"></span>
                    </template>
                    <span x-text="isSubmitting ? 'Processing...' : `Assign to ${formData.student_ids.length} Students`"></span>
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
                academic_year_id: '{{ $academicYears->firstWhere("is_current", \App\Enums\YesNo::Yes)?->id ?? "" }}',
                class_id: '',
                miscellaneous_fee_id: '',
                fee_period: '{{ date("F Y") }}',
                due_date: '{{ date("Y-m-d", strtotime("+10 days")) }}',
                student_ids: []
            },
            students: [],
            selectAll: false,
            loadingStudents: false,
            isSubmitting: false,

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
                    const response = await fetch(`/school/ad-hoc-fees/students/${this.formData.class_id}`, {
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
                    if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to load students for the selected class.' });
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
                    if (window.Toast) window.Toast.fire({ icon: 'warning', title: 'Please select at least one student to assign the fee.' });
                    return;
                }

                if (!this.formData.fee_period) {
                    if (window.Toast) window.Toast.fire({ icon: 'warning', title: 'Please select a fee period.' });
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
                        if (window.Toast) {
                            window.Toast.fire({ icon: 'success', title: result.message }).then(() => {
                                window.location.href = '{{ route("school.fees.index") }}';
                            });
                        } else {
                            window.location.href = '{{ route("school.fees.index") }}';
                        }
                    } else {
                        throw new Error(result.message || 'Validation failed');
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message || 'An unexpected error occurred while assigning the fee.' });
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endpush
@endsection
