@extends('layouts.school')

@section('title', 'Fee Waivers')

@section('content')
<div x-data="waiverManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-hand-holding-heart text-xs"></i>
                    </div>
                    Fee Waivers & Concessions
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track student fee reductions and scholarship adjustments</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Apply Waiver
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('school.waivers.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Search Student</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <select name="student_id" class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm font-medium text-gray-700 appearance-none">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->admission_no }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all font-bold text-sm shadow-sm active:scale-95">
                Apply Filters
            </button>
        </form>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student',
                'label' => 'STUDENT DETAILS',
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 font-bold text-xs ring-2 ring-emerald-50 ring-offset-1">
                            ' . substr($row->student->full_name, 0, 1) . '
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-700">' . e($row->student->full_name) . '</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">' . e($row->student->admission_no) . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'waiver_details',
                'label' => 'CONCESSION',
                'render' => function($row) {
                    $percentLabel = $row->waiver_percentage ? '<span class="text-[9px] px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded-md ml-2">' . $row->waiver_percentage . '%</span>' : '';
                    return '<div>
                                <div class="flex items-center">
                                    <span class="text-sm font-bold text-emerald-600">₹ ' . number_format($row->waiver_amount, 2) . '</span>
                                    ' . $percentLabel . '
                                </div>
                                <div class="text-[10px] text-gray-400 italic">off base fee of ₹' . number_format($row->actual_fee, 2) . '</div>
                            </div>';
                }
            ],
            [
                'key' => 'period',
                'label' => 'PERIOD / SESSION',
                'render' => function($row) {
                    return '<div>
                                <div class="text-xs font-bold text-gray-700">' . e($row->academicYear->name) . '</div>
                                <div class="text-[10px] font-medium text-teal-600 uppercase tracking-tighter">' . e($row->fee_period) . '</div>
                            </div>';
                }
            ],
            [
                'key' => 'reason',
                'label' => 'JUSTIFICATION',
                'render' => function($row) {
                    return '<div class="text-xs text-gray-500 italic max-w-xs truncate" title="' . e($row->reason) . '">' . e($row->reason) . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-waiver', { detail: { id: " . $row->id . ", name: 'Waiver for " . addslashes($row->student->full_name) . "' } }))";
                },
                'title' => 'Remove Waiver',
            ],
        ];
    @endphp

    <div x-on:open-delete-waiver.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$waivers"
            :actions="$tableActions"
            empty-message="No active fee waivers found"
            empty-icon="fas fa-hand-holding-heart"
        >
            Waivers List
        </x-data-table>
    </div>

    <!-- Apply Waiver Modal -->
    <x-modal name="waiver-modal" title="Apply Student Waiver" maxWidth="2xl">
        <form @submit.prevent="submitForm" method="POST" class="p-0">
            @csrf
            <div class="px-8 py-8 space-y-6">
                <!-- Target Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Student <span class="text-red-500">*</span></label>
                        <select x-model="formData.student_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-gray-700 appearance-none shadow-sm">
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_no }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Period & Year -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Academic Year</label>
                        <select x-model="formData.academic_year_id" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:border-emerald-500 transition-all font-medium text-gray-700">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Fee Period</label>
                        <input type="text" x-model="formData.fee_period" placeholder="e.g. Monthly" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:border-emerald-500 font-medium text-gray-700">
                    </div>
                </div>

                <!-- Calculation Section -->
                <div class="bg-emerald-50/50 p-6 rounded-2xl border border-emerald-100/50 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-emerald-600 uppercase tracking-widest mb-1.5 ml-1">Base Fee Amount (₹)</label>
                            <input type="number" step="0.01" x-model.number="formData.actual_fee" @input="recalculate('actual')" class="w-full px-4 py-2.5 bg-white border border-emerald-100 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 font-bold text-emerald-800 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-emerald-600 uppercase tracking-widest mb-1.5 ml-1">Duration (Months)</label>
                            <input type="number" x-model="formData.upto_months" class="w-full px-4 py-2.5 bg-white border border-emerald-100 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 font-bold text-emerald-800 shadow-inner">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-emerald-100/30">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Waiver Percentage (%)</label>
                            <div class="relative">
                                <input type="number" step="0.01" x-model.number="formData.waiver_percentage" @input="recalculate('percent')" class="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:border-emerald-500 font-bold text-gray-700 shadow-sm">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-300 font-bold">%</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center pt-6 text-gray-300 font-bold italic">OR</div>
                        <div class="md:col-span-2 -mt-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Fixed Waiver Amount (₹)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-300 font-bold text-xs">₹</div>
                                <input type="number" step="0.01" x-model.number="formData.waiver_amount" @input="recalculate('amount')" class="w-full pl-8 pr-4 py-2.5 bg-white border border-gray-100 rounded-xl focus:border-emerald-500 font-bold text-emerald-700 shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Justification -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Justification / Remarks <span class="text-red-500">*</span></label>
                    <textarea x-model="formData.reason" rows="3" placeholder="Explain the criteria for this waiver..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 font-medium text-gray-700 resize-none shadow-sm"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button type="button" @click="closeModal()" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 rounded-xl">Cancel</button>
                <button type="submit" :disabled="submitting" class="px-8 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-bold rounded-xl shadow-lg flex items-center gap-2 active:scale-95 disabled:opacity-50 transition-all">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span></template>
                    <span x-text="submitting ? 'Applying...' : 'Confirm Waiver'"></span>
                </button>
            </div>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('waiverManagement', () => ({
        submitting: false,
        formData: {
            student_id: '',
            academic_year_id: '{{ $academicYear->id ?? '' }}',
            fee_period: 'Monthly',
            actual_fee: 0,
            upto_months: 12,
            waiver_percentage: 0,
            waiver_amount: 0,
            reason: ''
        },

        async submitForm() {
            this.submitting = true;
            this.clearErrors();
            try {
                const response = await fetch('{{ route('school.waivers.store') }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify(this.formData)
                });
                const result = await response.json();
                
                if (response.status === 422) {
                    this.displayErrors(result.errors);
                } else if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else { 
                    throw new Error(result.message || 'Failed to apply waiver'); 
                }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { 
                this.submitting = false; 
            }
        },

        displayErrors(errors) {
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[x-model="formData.${field}"]`);
                if (input) {
                    input.classList.add('!border-red-500');
                    input.classList.add('!ring-red-500/10');
                    
                    let errorMsg = input.closest('div').querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('p');
                        errorMsg.className = 'error-message text-red-500 text-[10px] mt-1 font-bold italic';
                        input.closest('div').appendChild(errorMsg);
                    }
                    errorMsg.innerText = errors[field][0];
                }
            });
        },

        clearErrors() {
            document.querySelectorAll('.\\!border-red-500').forEach(el => {
                el.classList.remove('!border-red-500');
                el.classList.remove('!ring-red-500/10');
            });
            document.querySelectorAll('.error-message').forEach(el => el.remove());
        },

        recalculate(source) {
            const actual = parseFloat(this.formData.actual_fee) || 0;
            if (source === 'percent' || source === 'actual') {
                const percent = parseFloat(this.formData.waiver_percentage) || 0;
                this.formData.waiver_amount = parseFloat((actual * percent / 100).toFixed(2));
            } else if (source === 'amount') {
                const amount = parseFloat(this.formData.waiver_amount) || 0;
                this.formData.waiver_percentage = actual > 0 ? parseFloat((amount / actual * 100).toFixed(2)) : 0;
            }
        },

        openAddModal() {
            this.formData = {
                student_id: '',
                academic_year_id: '{{ $academicYear->id ?? '' }}',
                fee_period: 'Monthly',
                actual_fee: 0,
                upto_months: 12,
                waiver_percentage: 0,
                waiver_amount: 0,
                reason: ''
            };
            this.$dispatch('open-modal', 'waiver-modal');
        },

        async confirmDelete(waiver) {
            if (confirm(`Are you sure you want to remove the waiver for "${waiver.name}"?`)) {
                try {
                    const response = await fetch(`/school/waivers/${waiver.id}`, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });
                    const result = await response.json();
                    if (response.ok) {
                        if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        throw new Error(result.message || 'Deletion failed');
                    }
                } catch (e) { 
                    if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                }
            }
        },

        closeModal() { this.$dispatch('close-modal', 'waiver-modal'); }
    }));
});
</script>
@endpush
@endsection
